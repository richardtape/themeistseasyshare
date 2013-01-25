<?php
/*
Plugin Name: Themeists Easy Share
Plugin URI: http://www.themeists.com/plugins/easy-share
Description: Easily share links to your profiles on other websites
Version: 1.0.0
Author: THemeists
Author URI: http://www.themeists.com
*/

if( !class_exists( 'ThemeistsEasyShare' ) ):


	class ThemeistsEasyShare
	{

		/**
		 * We need two arrays for the available services ( easily extendable by filters in a child theme )
		 * and for our settings ( if we're not using a Themeists theme )
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 */
		
		var $settings;
		var $services;


		/**
		 * We might not be using a themeists theme ( which means we can't add anything to the options panel ). By default,
		 * we'll say we are not. We check if the theme's author is Themeists to set this to true during instantiation.
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 */
		
		var $using_themeists_theme = false;
		

		/**
		 * Set ourselves up with the basic social services we support by default and the initial part of the
		 * url for those services ( or placeholders )
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 * @param None
		 * @return None
		 */
		
	    function __construct() 
	    {	

	    	$theme_data = wp_get_theme();
			$theme_author = $theme_data->display( 'Author', false );

			if( strtolower( trim( $theme_author ) ) == "themeists" )
				$this->using_themeists_theme = true;

	    	$this->services = array
	    	( 

				'Behance' => 'http://www.behance.net/username',
				'Blogger' => 'http://username.blogspot.com',
				'Delicious' => 'http://delicious.com/username',
				'DeviantART' => 'http://username.deviantart.com/',
				'Digg' => 'http://digg.com/username',
				'Dribbble' => 'http://dribbble.com/username',
				'Evernote' => 'http://www.evernote.com',
				'Facebook' => 'http://www.facebook.com/username',
				'Flickr' => 'http://www.flickr.com/photos/username',
				'Forrst' => 'http://forrst.me/username',
				'GitHub' => 'https://github.com/username',
				'Google+' => 'http://plus.google.com/userID',
				'Instagram' => 'http://instagr.am/p/picID',
				'Lastfm' => 'http://www.last.fm/user/username',
				'LinkedIn' => 'http://www.linkedin.com/in/username',
				'Mail' => 'mailto:user@name.com',
				'MySpace' => 'http://www.myspace.com/userID',
				'Path' => 'https://path.com/p/picID',
				'Pinterest' => 'http://pinterest.com/username',
				'Posterous' => 'http://username.posterous.com',
				'Reddit' => 'http://www.reddit.com/user/username',
				'RSS' => 'http://example.com/feed',
				'ShareThis' => 'http://sharethis.com',
				'Skype' => 'skype:username',
				'StumbleUpon' => 'http://www.stumbleupon.com/stumbler/username',
				'Tumblr' => 'http://username.tumblr.com',
				'Twitter' => 'http://twitter.com/username',
				'Vimeo' => 'http://vimeo.com/username',
				'WordPress' => 'http://username.wordpress.com',
				'YouTube' => 'http://www.youtube.com/user/username'

	    	 );

			//Run this array through a filter so people can add to/amend the list easily
			$this->services = apply_filters( 'themeists_easy_share_services', $this->services );
	    	
	    	//If we're on a themeists theme, we add our options to our theme options panel, otherwise
	    	//we add a new menu item
			if( $this->using_themeists_theme )
			{

				add_action( 'of_set_options_after_defaults', array( &$this, 'add_to_themeists_options_panel' ) );

			}
			else
			{

				add_action( 'admin_init', array( &$this, 'admin_init' ) );
	        	add_action( 'admin_menu', array( &$this, 'admin_menu' ), 99 );

			}
	        

	        //we need to add a filter to plugins_url as we use symlinks in our dev setup
			add_filter( 'plugins_url', array( &$this, 'local_dev_symlink_plugins_url_fix' ), 10, 3 );

		}/* __construct() */

		/* =================================================================================== */


		/**
		 * As we are using a themeists theme, we can:
		 * Add our options to our theme options panel rather than add a new menu item
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 * @param None
		 * @return Adds options to options panel
		 */
		
		function add_to_themeists_options_panel()
		{

			global $options;

			// External Heading ================================================

			$options[] = array(
				'name' => __( 'Easy Share', 'themeistseasyshare' ),
				'type' => 'heading'
			);

			//Add the opening message
			$options[] = array(
				'name' => __( 'Themeists Easy Share Settings', 'themeistseasyshare' ),
				'desc' => __( $this->get_section_intro(), 'themeistseasyshare' ),
				'class' => 'highlight', //Blank, highlight, warning, vital
				'type' => 'info'
			);

			//Add each service
			foreach( $this->services as $service => $help )
				$this->themeists_add_profile( $service, $help );

			//Add the control for the 'open in new or same window' option
			$open_array = array(
				'same_window' => __( 'Open in Same Window', 'themeistseasyshare' ),
				'new_window' => __( 'Open in New Window', 'themeistseasyshare' )
			);

			$open_array = apply_filters( 'themeists_easy_share_open_options', $open_array );

			$options[] = array(
				'name' => __( 'Open In', 'themeistseasyshare' ),
				'desc' => __( 'what should happen when someone clicks on a share icon', 'themeistseasyshare' ),
				'id' => 'themeists_easy_share_opens_in',
				'std' => 'same_window',
				'type' => 'select',
				'class' => 'small', //mini, tiny, small
				'options' => $open_array
			);

			//Add the control for the size
			$size_array = array(
				'16px' => __( '16px', 'themeistseasyshare' ),
				'32px' => __( '32px', 'themeistseasyshare' )
			);

			$size_array = apply_filters( 'themeists_easy_share_sizes', $size_array );

			$options[] = array(
				'name' => __( 'Icon Size', 'themeistseasyshare' ),
				'desc' => __( 'What size would you like the icons?', 'themeistseasyshare' ),
				'id' => 'themeists_easy_share_size',
				'std' => '16px',
				'type' => 'select',
				'class' => 'mini', //mini, tiny, small
				'options' => $size_array
			);

			//Add the instructions message
			$options[] = array(
				'name' => __( 'How to use', 'themeistseasyshare' ),
				'desc' => __( $this->get_setting_instructions(), 'themeistseasyshare' ),
				'class' => 'warning', //Blank, highlight, warning, vital
				'type' => 'info'
			);
			

		}/* add_to_themeists_options_panel() */

		/* =================================================================================== */
		
		function admin_init()
		{

			register_setting( 'themeists-easyshare', 'themeists_easy_share_settings', array( &$this, 'settings_validate' ) );
			add_settings_section( 'themeists-easyshare', '', array( &$this, 'section_intro' ), 'themeists-easyshare' );
			$this->settings = get_option( 'themeists_easy_share_settings' );
			
			foreach( $this->services as $service=>$help )
				$this->add_profile( $service, $service, $help );
			
			add_settings_field( 'size', __( 'Icon Size', 'themeistseasyshare' ), array( &$this, 'setting_size' ), 'themeists-easyshare', 'themeists-easyshare' );
			add_settings_field( 'links', __( 'Open Links', 'themeistseasyshare' ), array( &$this, 'setting_links' ), 'themeists-easyshare', 'themeists-easyshare' );
			add_settings_field( 'preview', __( 'Preview', 'themeistseasyshare' ), array( &$this, 'setting_preview' ), 'themeists-easyshare', 'themeists-easyshare' );
			add_settings_field( 'instructions', __( 'Shortcode and Template Tag', 'themeistseasyshare' ), array( &$this, 'setting_instructions' ), 'themeists-easyshare', 'themeists-easyshare' );

		}/* admin_init() */

		/* =================================================================================== */
		
		function admin_menu() 
		{

			$icon_url = plugins_url( '/images/favicon.jpg', __FILE__ );
			$page_hook = add_menu_page( __( 'ThemeistsEasyShare Settings', 'themeistseasyshare' ), __( 'Easy Share', 'themeistseasyshare' ), 'update_core', 'themeists-easyshare', array( &$this, 'settings_page' ), $icon_url );
			add_submenu_page( 'themeists-easyshare', __( 'Settings', 'themeistseasyshare' ), __( 'ThemeistsEasyShare Settings', 'themeistseasyshare' ), 'update_core', 'themeists-easyshare', array( &$this, 'settings_page' ) );

		}/* admin_menu() */

		/* =================================================================================== */
		
		function settings_page()
		{

			?>

			<div class="wrap">
				<?php screen_icon('themes'); ?>
				<h2>ThemeistsEasyShare Settings</h2>
				
				<?php if( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){ ?>
				<div id="setting-error-settings_updated" class="updated settings-error"> 
					<p><strong><?php _e( 'Settings saved. ', 'themeistseasyshare' ); ?></strong></p>
				</div>
				<?php } ?>
				<form action="options.php" method="post">
					<?php settings_fields( 'themeists-easyshare' ); ?>
					<?php do_settings_sections( 'themeists-easyshare' ); ?>
					<p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'themeistseasyshare' ); ?>" /></p>
				</form>
			</div>

			<?php

		}/* settings_page() */

		/* =================================================================================== */
		
		/**
		 * Introductory text
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 * @param None
		 * @return The text
		 */
		
		function get_section_intro()
		{

			return __( '<p>ThemeistsEasyShare allows you to display snazzy social icons on your site. Customize the output of ThemeistsEasyShare with this settings page. Select the services to be used and basic configuration settings.</p> ', 'themeistseasyshare' );

		}/* section_intro() */

		/* =================================================================================== */

		/**
		 * echo's the intro text from $this->get_section_intro()
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 * @param None
		 * @return echo output
		 */
		
		function secton_intro()
		{

			echo $this->get_section_intro();

		}/* secton_intro() */

		/* =================================================================================== */
		
		/**
		 * If we're not using a themeists theme, we add each of the available services as a settings
		 * to it's own menu item.
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 * @param (string) $id - ID of this service
		 * @param (string) $title - The title for it
		 * @param (string) $help - The hint text (which we use as placeholder text)
		 * @return None. Calls add_settings_field()
		 */
		
		function add_profile( $id, $title, $help = '' )
		{

			$args = array( 
				'id' => $id,
				'help' => $help
			 );
			
			add_settings_field( $id, __( $title, 'themeistseasyshare' ), array( &$this, 'setting_profile' ), 'themeists-easyshare', 'themeists-easyshare', $args );

		}/* add_profile() */

		/* =================================================================================== */

		/**
		 * If we're using a themeists theme, we simply add options to our options panel. This function
		 * is called after the tab is added in $this->add_to_themeists_options_panel() above
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 * @param (string) $id - ID of this service
		 * @param (string) $title - The title for it
		 * @param (string) $help - The hint text (which we use as placeholder text)
		 * @return None. Adds options to options tab
		 */
		
		function themeists_add_profile( $service, $help )
		{

			global $options;
			$options[] = array(
				'name' => __( $service, '' ),
				'desc' => __( $help, '' ),
				'id' => 'themeists_share_' . $service,
				'std' => '',
				'type' => 'text'
			);

		}/* themeists_add_profile() */

		/* =================================================================================== */
		
		function setting_profile( $args )
		{

			if( !isset( $this->settings[$args['id']] ) ) $this->settings[$args['id']] = '';
			
			echo '<input type="text" placeholder="' . $args['help'] . '" name="themeists_easy_share_settings[' . $args['id'] . ']" class="regular-text" value="' . $this->settings[$args['id']] . '" /> ';

		}/* setting_profile() */

		/* =================================================================================== */
		
		function setting_size()
		{

			if( !isset( $this->settings['size'] ) ) $this->settings['size'] = '16px';
			
			echo '<select name="themeists_easy_share_settings[size]">
			<option value="16px"' . ( ( $this->settings['size'] == '16px' ) ? ' selected="selected"' : '' ) . '>16px</option>
			<option value="32px"' . ( ( $this->settings['size'] == '32px' ) ? ' selected="selected"' : '' ) . '>32px</option>
			</select>';

		}/* setting_size() */

		/* =================================================================================== */

		function setting_links()
		{

			if( !isset( $this->settings['links'] ) )
				$this->settings['links'] = 'same_window';
			
			echo '<select name="themeists_easy_share_settings[links]">
			<option value="same_window"' . ( ( $this->settings['links'] == 'same_window' ) ? ' selected="selected"' : '' ) . '>In same window</option>
			<option value="new_window"' . ( ( $this->settings['links'] == 'new_window' ) ? ' selected="selected"' : '' ) . '>In new window</option>
			</select>';

		}/* setting_links() */

		/* =================================================================================== */
		
		function setting_preview()
		{

			if( $this->settings ) echo $this->do_social();

		}/* setting_preview() */

		/* =================================================================================== */
		

		function setting_instructions()
		{

			echo $this->get_setting_instructions();

		}/* setting_instructions() */

		/* =================================================================================== */

		function get_setting_instructions()
		{

			return  __( '<p>To use ThemeistsEasyShare in your posts and pages you can use the shortcode:</p>
			<p><code>[themeists_easy_share]</code></p>
			<p>To use ThemeistsEasyShare manually in your theme template use the following PHP code:</p>
			<p><code>&lt;?php if( function_exists( \'themeists_easy_share\' ) ) themeists_easy_share(); ?&gt;</code></p>
			<p>You can optionally pass in a "size" and "services" parameter to both of the above to override the default values eg:</p>
			<p><code>[themeists_easy_share size="16px" services="Twitter,Facebook,Google+"]</code></p>
			<p><code>&lt;?php if( function_exists( \'themeists_easy_share\' ) ) themeists_easy_share( \'16px\', array( \'Twitter\',\'Facebook\',\'Google+\' ) ); ?&gt;</code></p>', 'themeistseasyshare' );

		}/* get_setting_instructions() */

		/* =================================================================================== */
		
		function settings_validate( $input )
		{

			foreach( $this->services as $service=>$help )
			{

				$input[$service] = strip_tags( $input[$service] );
				if( $service != 'Skype' ) $input[$service] = esc_url_raw( $input[$service] );

			}
			return $input;
		
		}/* settings_validate() */

		/* =================================================================================== */
		
		function do_social( $size = '', $services_wl = array() )
		{

			if( $this->using_themeists_theme )
			{

				$options = array();
				$options['size'] = of_get_option( 'themeists_easy_share_size', '16px' );
				$options['links'] = of_get_option( 'themeists_easy_share_opens_in', 'same_window' );
				//$options['service'] = of_get_option( '' );

				//Each option is stored in something akin to 'themeists_share_' . $service and we need
				//to get $options[$servive], so loop over each one
				foreach( $this->services as $service => $help )
				{

					$this_name = strtolower( 'themeists_share_' . $service );
					$this_value = of_get_option( $this_name );

					if( $this_value && $this_value != "" )
						$options[$service] = $this_value;

				}

			}
			else
			{

				$options = get_option( 'themeists_easy_share_settings' );
				if( !isset( $options['size'] ) ) $options['size'] = '16px';
				if( $size == '16px' ) $options['size'] = '16px';
				if( $size == '32px' ) $options['size'] = '32px';
				if( !isset( $options['links'] ) ) $options['links'] = 'same_window';

			}
			
			echo '<div class="themeists-easyshare size-' . $options['size'] . '">';

			$icon_path = apply_filters( 'themeists_easy_share_icon_path', plugins_url( '/images/' . $options['size'] . '/', __FILE__ ) );
			
			if( empty( $services_wl ) )
			{
				
				$done = array();
				foreach( $this->services as $service => $help )
				{
					
					if( array_key_exists( $service, $options ) && isset( $options[$service] ) && $options[$service] )
					{

						$icon_output = '<a href="' . $options[$service] . '" class="' . $service . '"' . ( ( $options['links'] == 'new_window' ) ? ' target="_blank"' : '' ) . '><img src="' . $icon_path . $service . '.png" alt="' . $service . '" /></a> ';

						echo apply_filters( 'themeists_easy_share_output', $icon_output );

					}
					$done[$service] = true;

				}

			}
			else
			{

				foreach( $services_wl as $service )
				{

					if( isset( $options[$service] ) && $options[$service] )
					{

						$icon_output = '<a href="' . $options[$service] . '" class="' . $service . '"' . ( ( $options['links'] == 'new_window' ) ? ' target="_blank"' : '' ) . '><img src="' . $icon_path . $service . '.png" alt="' . $service . '" /></a> ';

						echo apply_filters( 'themeists_easy_share_output', $icon_output );

					}

				}

			}
			
			echo '</div>';

		}/* do_social() */

		/* =================================================================================== */

		/**
		 * Edit the plugins_url() url to be appropriate for this widget ( we use symlinks on local dev )
		 *
		 * @author Richard Tape
		 * @package ThemeistsEasyShare
		 * @since 1.0
		 */
		
		function local_dev_symlink_plugins_url_fix( $url, $path, $plugin )
		{

			// Do it only for this plugin
			if ( strstr( $plugin, basename( __FILE__ ) ) )
				return str_replace( dirname( __FILE__ ), '/' . basename( dirname( $plugin ) ), $url );

			return $url;

		}/* local_dev_symlink_plugins_url_fix() */
		
	}/* class ThemeistsEasyShare */


endif;

/* ======================================================================================

Now we need to instantiate and then load our widget and functions files

====================================================================================== */

global $themeists_easy_share;
$themeists_easy_share = new ThemeistsEasyShare();


/* =================================================================================== */

require_once( 'themeists-easy-share-widget.php' );

require_once( 'themeists-easy-share-functions.php' );


?>