<?php
	
	/*
	Plugin Name: wpShopGermany - Händlerbund
	Text Domain: wphb
	Domain Path: /lang
	Plugin URI: http://wpshopgermany.maennchen1.de/
	Description: Integration der Rechtstextschnittstelle Händlerbund (https://partner.haendlerbund.de/partnerdoor.php?partnerid=partner_wp&bannerid=18)
	Author: maennchen1.de
	Version: 1.7
	Author URI: http://maennchen1.de/
	*/

	session_start();

	require_once dirname(__FILE__).'/classes/wphb.class.php';
	require_once dirname(__FILE__).'/functions.php';
	
	define('WPHB_URL_WP', preg_replace('/wp-content/', '', WP_CONTENT_URL));
	
	function wpshopgermany_hb_install()
	{
		
		update_option("mod_hb", "1");
	
	}
	
	function wpshopgermany_hb_uninstall()
	{
		
		delete_option("mod_hb");
	
	}
	
	if (get_option('wpshopgermany_installed') <= 0 && get_option("mod_hb") == 1)
	{
		
		$wphb = new wphb();

		add_action('admin_menu', array($wphb, "admin_menu"));
		add_action('wp_loaded', array($wphb, 'wp_loaded'));
		
	}
	
	register_activation_hook(__FILE__, 'wpshopgermany_hb_install');
	register_deactivation_hook(__FILE__, 'wpshopgermany_hb_uninstall');

?>