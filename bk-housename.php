<?php
/*
Plugin Name: Housename
Plugin URI: https://mu-bit.com/
Description: Custom plugin
Author: bravokeyl
Version: 1.0.0
Author URI: https://bravokeyl.com/
Text Domain: bk-fonts
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'BK_HOUSENAME_VERSION', '1.0.0' );
define( 'BK_ABSPATH', plugin_dir_path( __FILE__ ) );
define( 'BK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BK_USABLE_FONTS_DATABASE', 'bkf_usable_fonts' );
define( 'BK_ELEMENTS_DATABASE', 'bkf_elements' );
define( 'BK_DEFAULT_ROLE', 'administrator' );



require_once( BK_ABSPATH . '/helpers/helpers.php' );
require_once( BK_ABSPATH . '/woocommerce/bk-woo.php' );

global $bkf_db_version;
$bkf_db_version = '1.0.0';

$upload_dir = wp_upload_dir(); // Must create a temp variable for PHP 5.3.
global $fo_css_directory_path;
$fo_css_directory_path =  $upload_dir['basedir'] . '/bk-fonts';

global $fo_css_base_url_path;
$fo_css_base_url_path = $upload_dir['baseurl'] . '/bk-fonts';

// Fix ssl for base url.
$fo_css_base_url_path = fo_get_all_http_url( $fo_css_base_url_path );

global $fo_declarations_css_file_name;
$fo_declarations_css_file_name = 'bk-fonts.css';

global $fo_elements_css_file_name;
$fo_elements_css_file_name = 'bk-elements.css';

function fo_update_db_check() {
    global $bkf_db_version;

    if ( get_site_option( 'bk_db_version' ) != $bkf_db_version ) {
    	global $wpdb;

        fo_install();

        // As of 2.0 we added font weights.
		$row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
		WHERE table_name = '" . $wpdb->prefix . BK_ELEMENTS_DATABASE . "' AND column_name = 'font_weight'"  );

		if(empty($row)){
		   $wpdb->query("ALTER TABLE " . $wpdb->prefix . BK_ELEMENTS_DATABASE . " ADD font_weight varchar(255)");
		}

        // As of 1.2 we split the css file to declartions and elements.
        // Create the files and delete the old fo-fonts.css.
        global $fo_css_directory_path;

		require_once BK_ABSPATH . 'settings.php';

	    $settings_page = new FoSettingsPage(false);
	    $settings_page->init();
	    $settings_page->create_css_file(true);

	    // Delete the old file.
	    if(file_exists($fo_css_directory_path . '/fo-fonts.css'))
	    	unlink($fo_css_directory_path . '/fo-fonts.css');

    }

}

add_action( 'plugins_loaded', 'fo_update_db_check' );
register_activation_hook( __FILE__, 'fo_install' );
register_uninstall_hook( __FILE__, 'fo_uninstall' );
add_action( 'init', 'fo_init' );
add_action('plugins_loaded', 'fo_load_textdomain');

function fo_load_textdomain() {
	load_plugin_textdomain( 'bk-housename', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

function fo_init(){

	if( is_admin() ){
		require_once BK_ABSPATH . 'settings.php';

		// Add the declarations to the editor, so in preview you can see
		// the selected font family.
	    add_editor_style( '../../uploads/bk-fonts/fo-declarations.css' );

		add_filter( 'upload_mimes', 'fo_allow_upload_types' );
		add_filter( 'plugin_action_links', 'fo_add_action_plugin', 10, 5 );
		add_filter( 'tiny_mce_before_init', 'fo_add_tinymce_fonts' );
		add_action( 'admin_enqueue_scripts', 'fo_enqueue_declarations_fonts_css' );

	    $settings_page = new FoSettingsPage();
	}else{
		add_action( 'wp_enqueue_scripts', 'fo_enqueue_all_fonts_css' );
	}
}

function fo_enqueue_all_fonts_css(){
	fo_enqueue_fonts_css();
}

function fo_enqueue_declarations_fonts_css(){
	fo_enqueue_fonts_css(true);
}

function fo_add_tinymce_fonts($initArray){
	$usable_fonts = FontsDatabaseHelper::get_usable_fonts();
	$font_formats = array();
	foreach ($usable_fonts as $font) {
		$font_formats[] = $font->name . '=' . $font->name;
	}

	// Set the font families from the usable fonts list.
	$initArray['font_formats'] = implode(';', $font_formats);

	// Apply the filter to allow quick change in the font sizes list in tinymce editors.
	// The input is a string of the default standart font sizes spereated by spaces (' ').
	$sizes = apply_filters('fo_tinyme_font_sizes', "8px 10px 12px 14px 16px 20px 24px 28px 32px 36px 48px 60px");

	// Set font sizes.
	$initArray['fontsize_formats'] = $sizes;

	return $initArray;
}

function fo_allow_upload_types($existing_mimes = array()){
	$existing_mimes['ttf'] = 'application/x-font-ttf';
	$existing_mimes['eot'] = 'application/vnd.ms-fontobject';
	$existing_mimes['woff'] = 'application/font-woff';
	$existing_mimes['woff2'] = 'font/woff2';
	$existing_mimes['otf'] = 'font/otf';
	$existing_mimes['svg'] = 'image/svg+xml';

	return $existing_mimes;
}

function fo_uninstall(){
    global $fo_css_directory_path;
	global $wpdb;
	$roles = wp_roles();

	// Delete all content only if marked so in the system settings options.
    $general_options = get_option( 'fo_general_options', array() );
    if(!array_key_exists('uninstall_all', $general_options) || !$general_options['uninstall_all']){
    	return;
    }

	// Remove all capabilities added by this plugin.
	foreach ($roles as $role_name => $role) {
		if(array_key_exists('manage_fonts', $role['capabilities']) && $role['capabilities']['manage_fonts'])
			 $roles->remove_cap( $role_name, 'manage_fonts' );
	}

	// Remove all files in uploaded folder.
	$files_to_remove = scandir($fo_css_directory_path);
	foreach ($files_to_remove as $file_name) {
		wp_delete_file($fo_css_directory_path . '/' . $file_name);
	}

	// Remove all database content.
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . BK_USABLE_FONTS_DATABASE);
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . BK_ELEMENTS_DATABASE);

    // Delete db version option.
    delete_site_option('bk_db_version');
}

function bk_insert_default_fonts(){
  global $wpdb;
  $table_name = $wpdb->prefix . BK_USABLE_FONTS_DATABASE;
  $initial_fonts = array(
    "amazone" => "Amazone",
    "brock-script" => "Brock Script",
    "dancing-script" => "Dancing Script",
    "edwardian-script" => "Edwardian Script",
    "freestyle-script" => "Freestyle Script",
    "french-script" => "French Script",
    "great-vibes" => "Great Vibes"
  );
  $query = "INSERT INTO ".$table_name." (name, url, custom) VALUES ";
  $values = array();
  foreach($initial_fonts as $key => $value) {
    $url = '/wp-content/plugins/bk-fonts/assets/fonts/'.$key.'.ttf';
    $values[] = $wpdb->prepare( "(%s,%s,%d)", $value, $url, 1 );
  }
  $query .= implode(', ', $values);
  $wpdb->query($query);
}

function fo_install() {
	global $wpdb;
	global $bkf_db_version;

	$usable_table_name = $wpdb->prefix . BK_USABLE_FONTS_DATABASE;
	$elements_table_name = $wpdb->prefix . BK_ELEMENTS_DATABASE;

	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$sql = "CREATE TABLE IF NOT EXISTS $usable_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		url text DEFAULT NULL,
		custom int(1) DEFAULT 0,
		PRIMARY KEY  (id)
	) $charset_collate;";

	dbDelta( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS $elements_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		font_id mediumint(9) NOT NULL,
		font_weight varchar(255),
		important int(1) DEFAULT 0,
		custom_elements TEXT DEFAULT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	dbDelta( $sql );

	// Set the db version to current.
	update_site_option( 'bk_db_version', $bkf_db_version );

	// Set roles
	$role = get_role( 'administrator' );
	if(!$role->has_cap('manage_fonts'))
	 	$role->add_cap( 'manage_fonts' );

  // Default Fonts
  bk_insert_default_fonts();
}

function fo_add_action_plugin( $actions, $plugin_file ) {
	static $plugin;

	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);

	if ($plugin == $plugin_file) {

		$settings = array('settings' => '<a href="options-general.php?page=font-setting-admin">' . __('BK Housename', 'bk-housename') . '</a>');
    	$actions = array_merge($settings, $actions);

	}

	return $actions;
}


add_action('wp_enqueue_scripts', 'bkh_enqueue_scripts');
function bkh_enqueue_scripts() {
	wp_register_script( 'jquery-debounce', plugin_dir_url( __FILE__ ) . 'assets/jquery-debounce.js', array(), BK_HOUSENAME_VERSION, true );
	wp_enqueue_script( 'jquery-debounce' );

	wp_register_script( 'bk-housename-js', plugin_dir_url( __FILE__ ) . 'assets/bk-housename.js', array('jquery-debounce'), BK_HOUSENAME_VERSION, true );

	wp_register_style( 'bk-housename', plugin_dir_url( __FILE__ ) . 'assets/bk-housename.css', array(), BK_HOUSENAME_VERSION );

	if(is_shop() || is_cart() || is_checkout()) {
		wp_enqueue_script( 'bk-housename-js' );

		$misc_options = get_option('bk_misc_options', array());
		$material_options = get_option( 'bk_material_options' , array() );
		$misc_opt = array(
		  'hst_gst' => $misc_options['hst_gst_tax'],
		  'installation_rate' => $misc_options['installation_rate'],
			'delivery_rate' => $misc_options['delivery_rate'],
			'lwidth' => $misc_options['spacing'],
			'material_options'=>$material_options
		);
		wp_localize_script( 'bk-housename-js', 'bk_housename', $misc_opt );


	  wp_enqueue_style( 'dashicons');
		wp_enqueue_style( 'bk-housename');
	}
}

add_action('wp_head', 'bkh_preload_media', 8);
function bkh_preload_media() {
	if(is_shop()){
	?>
	<link rel="preload" href="<?php echo BK_PLUGIN_URL.'assets/fonts/Amazone.ttf';?>" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="<?php echo BK_PLUGIN_URL.'assets/fonts/BrockScript.ttf';?>" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="<?php echo BK_PLUGIN_URL.'assets/fonts/DancingScript.ttf';?>" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="<?php echo BK_PLUGIN_URL.'assets/fonts/EdwardianScript.ttf';?>" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="<?php echo BK_PLUGIN_URL.'assets/fonts/FreeStyleScript.ttf';?>" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="<?php echo BK_PLUGIN_URL.'assets/fonts/FrenchScript.ttf';?>" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="<?php echo BK_PLUGIN_URL.'assets/fonts/GreatVibes.ttf';?>" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/ARBONNIE.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/AlexBrush-Regular.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Allura-Regular.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/BLACKJAR.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/england.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Housegrind.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Meadowbrook.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Playball.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Rochester-Regular.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/SCRIPTBL.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/FUTURAM.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/VLADIMIR.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Wrexham-Script.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Yesteryear-Regular.ttf" as="font" type="font/ttf" crossorigin="anonymous">
	<link rel="preload" href="//housename.ca/wp-content/uploads/bk-fonts/Birds-of-Paradise-COMMERCIAL-VERSION.ttf" as="font" type="font/ttf" crossorigin="anonymous">

	<link rel="preload" href="//housename.ca/wp-content/plugins/bk-housename/assets/images/red-brick.jpg" as="image">
	<link rel="preload" href="//housename.ca/wp-content/plugins/bk-housename/assets/images/beige-brick.jpg" as="image">
	<link rel="preload" href="//housename.ca/wp-content/plugins/bk-housename/assets/images/stone-block.jpg" as="image">
	<link rel="preload" href="//housename.ca/wp-content/plugins/bk-housename/assets/images/stucco.jpg" as="image">
	<link rel="preload" href="//housename.ca/wp-content/plugins/bk-housename/assets/images/siding.jpg" as="image">
	<link rel="preload" href="//housename.ca/wp-content/plugins/bk-housename/assets/images/siding-light.jpg" as="image">
	<link rel="preload" href="//housename.ca/wp-content/plugins/bk-housename/assets/images/concrete.jpg" as="image">
<?php }
}
