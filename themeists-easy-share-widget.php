<?php

	if( !class_exists( 'themeists_easy_share_widget' ) )
	{

		class themeists_easy_share_widget extends WP_Widget
		{
		
			
			/**
			 * The name shown in the widgets panel
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 */
			
			const name 		= 'Themeists Easy Share';

			/**
			 * For helping with translations
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 */

			const locale 	= THEMENAME;

			/**
			 * The slug for this widget, which is shown on output
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 */
			
			const slug 		= 'themeists_easy_share_widget';
		

			/* ============================================================================ */
		
			/**
			 * The widget constructor. Specifies the classname and description, instantiates
			 * the widget, loads localization files, and includes necessary scripts and
			 * styles. 
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 * @param None
			 * @return None
			 */
			
			function themeists_easy_share_widget()
			{
		
				load_plugin_textdomain( self::locale, false, plugin_dir_path( dirname( __FILE__ ) ) . '/lang/' );

		
				$widget_opts = array (

					'classname' => 'themeists_easy_share_widget', 
					'description' => __( 'Shows links to your chosen social profiles', self::locale )

				);

				$control_options = array(

					'width' => '400'

				);

				//Register the widget
				$this->WP_Widget( self::slug, __( self::name, self::locale ), $widget_opts, $control_options );
		
		    	// Load JavaScript and stylesheets
		    	$this->register_scripts_and_styles();
		
			}/* themeists_easy_share_widget() */
		

			/* ============================================================================ */


			/**
			 * Outputs the content of the widget.
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 * @param (array) $args - The array of form elements
			 * @param (array) $instance - The saved options from the widget controls
			 * @return None
			 */
			

			function widget( $args, $instance )
			{
		
				extract( $args );
		
				$title = apply_filters( 'widget_title', $instance['title'] );
				$desc = $instance['description'];
				$size = $instance['size'];
		
				echo $before_widget;
				
					if ( !empty( $title ) ) echo $before_title . $title . $after_title;
					
						if( $desc ) echo '<p>'. $desc .'</p>';
					
					global $themeists_easy_share;
					
					$themeists_easy_share->do_social( $size, array(), $args );
				
				echo $after_widget;
		
			}/* widget() */


			/* ============================================================================ */

		
			/**
			 * Processes the widget's options to be saved.
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 * @param $new_instance	The previous instance of values before the update.
			 * @param @old_instance	The new instance of values to be generated via the update. 
			 * @return $instance The saved values
			 */
			
			function update( $new_instance, $old_instance )
			{
		
				$instance = $old_instance;
				
				$instance['title'] = strip_tags( $new_instance['title'] );
				$instance['description'] = strip_tags( $new_instance['description'], '<a><b><strong><i><em>' );
				$instance['size'] = strip_tags( $new_instance['size'] );
				
				return $instance;
		
			}/* update() */


			/* ============================================================================ */


			/**
			 * Generates the administration form for the widget.
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 * @param $instance	The array of keys and values for the widget.
			 * @return None
			 */
			

			function form( $instance )
			{
		
				$instance = wp_parse_args(

					(array)$instance,
					array(
						'title' => '',
						'description' => '',
						'size' => '16px'
					)

				);
		
		    	?>
		    	
		    		<p>
						<label for="<?php echo $this->get_field_id( 'title' ); ?>">
							<?php _e( "Title", THEMENAME ); ?>
						</label>
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
					</p>

					<p>
						<label for="<?php echo $this->get_field_id( 'description' ); ?>">
							<?php _e( "Description", THEMENAME ); ?>
						</label>
						<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'description' ); ?>" name="<?php echo $this->get_field_name( 'description' ); ?>" value="<?php echo $instance['description']; ?>" />
					</p>

					<p>
						<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Size:' ); ?></label> 
						<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
							<option value=""<?php selected( $instance['size'], '' ); ?>>Default</option>
							<option value="16px"<?php selected( $instance['size'], '16px' ); ?>>16px</option>
							<option value="32px"<?php selected( $instance['size'], '32px' ); ?>>32px</option>
						</select>
					</p>
		    	
		    	<?php
		
			}/* form() */


			/* ============================================================================ */
		

			/**
			 * Registers and enqueues stylesheets for the administration panel and the
			 * public facing site.
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 * @param None
			 * @return None
			 */
			

			private function register_scripts_and_styles()
			{

				if( is_admin() )
				{

		      		//$this->load_file('friendly_widgets_admin_js', '/themes/'.THEMENAME.'/admin/js/widgets.js', true);

				}
				else
				{ 

		      		//$this->load_file('friendly_widgets', '/themes/'.THEMENAME.'/theme_assets/js/widgets.js', true);

				}

			}/* register_scripts_and_styles() */


			/* ============================================================================ */


			/**
			 * Helper function for registering and enqueueing scripts and styles.
			 *
			 * @author Richard Tape
			 * @package themeists_easy_share_widget
			 * @since 1.0
			 * @param $name 		The ID to register with WordPress
			 * @param $file_path	The path to the actual file
			 * @param $is_script	Optional argument for if the incoming file_path is a JavaScript source file.
			 * @return None
			 */
			
			function load_file( $name, $file_path, $is_script = false )
			{
		
		    	$url = content_url( $file_path, __FILE__ );
				$file = $file_path;
					
				if( $is_script )
				{

					wp_register_script( $name, $url, '', '', true );
					wp_enqueue_script( $name );

				}
				else
				{

					wp_register_style( $name, $url, '', '', true );
					wp_enqueue_style( $name );

				}
			
			}/* load_file() */
		
		
		}/* class themeists_easy_share_widget */

	}

	//Register The widget
	//register_widget( "themeists_easy_share_widget" );
	add_action( 'widgets_init', create_function( '', 'register_widget( "themeists_easy_share_widget" );' ) );

?>