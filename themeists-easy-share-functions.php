<?php

	/* ======================================================================================

		Make some functions available to themes such as template tags and shortcodes.

	====================================================================================== */

	/**
	 * Add a template tag which will allow people to easily use this in their themes
	 *
	 * @author Richard Tape
	 * @package ThemeistsEasyShare
	 * @since 1.0
	 * @param 
	 * @return 
	 */
	
	function themeists_easy_share( $size = '', $services_wl = array() )
	{

		global $themeists_easy_share;
		$themeists_easy_share->do_social( $size, $services_wl );
	
	}/* themeists_easy_share() */


	/* =================================================================================== */

	/**
	 * Make an easy to use shortcode for people to use in posts/pages/textwidgets
	 *
	 * @author Richard Tape
	 * @package Chemistry
	 * @since 0.7
	 * @param (array) $atts - The attributes passed from the [shortcode] call
	 * @return The appropriate markup
	 */

	function themeists_easy_share_shortcode( $atts )
	{

		extract( shortcode_atts( array(
			'size' => '',
			'services' => ''
		), $atts ) );

		global $themeists_easy_share;

		$services_wl = array();

		if( $themeists_easy_share->services )
			$services_wl = explode( ',', str_replace( ' ', '', esc_attr( $services ) ) );
		
		return $themeists_easy_share->do_social( esc_attr( $size ), $services_wl );

	}/* themeists_easy_share_shortcode() */

	add_shortcode( 'themeists_easy_share', array( &$this, 'shortcode' ) );
	

?>