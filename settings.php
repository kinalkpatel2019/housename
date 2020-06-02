<?php
class FoSettingsPage
{
    /**
     * The seperator used when inserting more then 1 font format or font weights
     * to the database. The urls are joined with the seperator to
     * create a string a parsed back to urls when needed.
     */
    const CUSTOM_FONT_URL_SPERATOR = ';';
    const FACBOOK_APP_ID = "";

    const DEFAULT_CSS_TITLE = "/* This Awesome CSS file was created by BK Fonts Plugin :) */\n\n";

    /**
     * Holds the option values for the general section.
     */
    private $general_options;

    /**
     * Holds the option values for the elements section.
     */
    private $elements_options;

    /**
     * Holds the option values for the advanced section.
     */
    private $advanced_options;

    /**
     * Holds all the fonts available.
     * An Objects array that contains the information on each font.
     */
    private $available_fonts;

    /**
     * Holds all the usable to be used in the website from available fonts.
     * An Objects array that contains the information on each font.
     */
    private $usable_fonts;

    /**
     * Holds all the usable fonts from the database.
     * An Objects array that contains the information on each font.
     */
    private $usable_fonts_db;

    /**
     * Holds the known fonts available.
     * An Objects array that contains the information on each font.
     */
    private $known_fonts;

    /**
     * Holds the custom fonts available.
     * An Objects array that contains the information on each font.
     */
    private $custom_fonts;

    /**
     * Holds the google fonts available.
     * An Objects array that contains the information on each font.
     */
    private $google_fonts;

    /**
     * Holds the early access google fonts static list. (No API for full list exists)
     * An Objects array that contains the information on each font.
     */
    private $earlyaccess_fonts;

    /**
     * Holds the number of google fonts to load per request
     */
    private $fonts_per_link;

    /**
     * Holds the list of the supported font files for this settings.
     */
    private $supported_font_files;

    /**
     * Holds the error, if any, recieved from uploading a font.
     */
    private $recent_error;

    /**
     * Holds the known elements id and title to select a font for.
     */
    private $elements;

    /**
     * Holds the all font weights options from 300 to 800.
     */
    private $font_weights;

    /**
     * Holds the value if it should include font link (aka include google fonts for the settings page).
     * If set to false. loads only the usable fonts.
     */
    private $include_font_link;

    /**
     * Holds a list of all the custom elements from the database.
     */
    private $custom_elements;

    /**
     * The selected font to manage in last step.
     */
    private $selected_manage_font;

    /**
     * Should create a css file (override if exists) based on recent actions made.
     */
    private $should_create_css;

    /**
     * Is the current user admin or not.
     */
    private $is_admin;

    /**
     * Is the google fonts list from a static resource or
     * is it from google request.
     */
    private $is_google_static;

    public function __construct($should_hook = true)
    {
        require_once BK_ABSPATH . 'classes/class-ElementsTable.php';
        require_once BK_ABSPATH . 'classes/class-FontsDatabaseHelper.php';

        $this->fonts_per_link = 150;
        $this->supported_font_files = array('.woff', '.woff2', '.ttf','.otf', '.svg', '.eot');
        $this->custom_fonts = array();
        $this->available_fonts = array();
        $this->usable_fonts = array();
        $this->google_fonts = array();
        $this->general_options = array();
        $this->elements_options = array();
        $this->advanced_options = array();
        $this->usable_fonts_db = array();
        $this->should_create_css = false;
        $this->is_google_static = false;
        $this->is_admin = current_user_can('manage_options');
        $this->elements = array();

         $this->font_weights = array('regular');

        if($should_hook){
            add_action( 'network_admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
            add_action( 'wp_ajax_edit_custom_elements', array( $this, 'edit_custom_elements_callback' ) );
        }
    }

    /**
     * Register all the required scripts for this page.
     */
    public function register_scripts() {
        add_action( 'admin_footer', array( $this, 'add_footer_styles' ) );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'fo-settings-script', plugins_url( 'assets/js/settings.js', __FILE__ ) , array( 'jquery' ) );

        $data = array(
                    'usable_fonts' => $this->usable_fonts,
                    'available_fonts' => $this->available_fonts,
                    'options_values' => $this->elements_options,
                    'labels' => array(
                                    'default_label' => __('Not Stated', 'bk-fonts'),
                                    'light' => __('Light', 'bk-fonts'),
                                    'regular' => __('Normal', 'bk-fonts'),
                                    'semibold' => __('Semi-Bold', 'bk-fonts'),
                                    'bold' => __('Bold', 'bk-fonts'),
                                    'extrabold' => __('Extra-Bold', 'bk-fonts'),
                                    'black' => __('Black', 'bk-fonts'),
                                    'italic' => __('Italic', 'bk-fonts'),
                                ),
                );

        wp_localize_script( 'fo-settings-script', 'data', $data );

        wp_enqueue_style( 'fo-settings-css', plugins_url( 'assets/css/settings.css', __FILE__ ) );
        wp_enqueue_style( 'fontawesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ) );
    }

    public function add_footer_styles(){ ?>
        <script type="text/javascript" >

            jQuery(document).ready(function() {

                // Go to the previusly selected tab.
                var hash = jQuery.trim( window.location.hash );
                jQuery("#tabs").tabs({
                  active: hash.substr(1)
                });

                var textBefore = '';

                // Exit focus from the text when clicking 'Enter' but don't submit the form.
                jQuery('table.custom_elements').find('td input:text').on('keyup keypress', function(e) {
                  var keyCode = e.keyCode || e.which;
                  if (keyCode === 13) {
                    jQuery(this).blur();
                    e.preventDefault();
                    return false;
                  }
                });

                jQuery('table.custom_elements').find('td input:checkbox').change(function () {

                    // Change the No to Yes or Yes to No labels.
                    var $field = jQuery(this);
                    var value = $field.prop('checked') ? 1 : 0;
                    var item = jQuery(this).siblings('span');
                    if(value){
                        item.css('color', 'darkgreen');
                        item.text("<?php _e('Yes', 'bk-fonts'); ?>");
                    }else{
                        item.css('color', 'darkred');
                        item.text("<?php _e('No', 'bk-fonts'); ?>");
                    }

                    // Send ajax request named 'edit_custom_elements' to change the column to value text
                    // where id.
                    var data = {
                            'action': 'edit_custom_elements',
                            'id': parseInt($field.closest('tr').find('.check-column input').val()),
                            'column': $field.attr('name'),
                            'text': value
                    };

                    jQuery.post(ajaxurl, data, function(response) {
                            // Show message for success and error for 5 seconds.
                            if(response){
                                jQuery('.custom_elements_message.fo_success').show().delay(5000).fadeOut();
                            }else{
                                jQuery('.custom_elements_message.fo_warning').show().delay(5000).fadeOut();
                                $field.prop('checked', !value);
                            }
                    });
                });

                jQuery('table.custom_elements').find('td select').on('focus', function () {
                    var $field = jQuery(this);

                    // Store the current value on focus and on change
                    textBefore = $field.val();
                }).change(function() {
                    var $field = jQuery(this);
                    var text = $field.val();

                    if (textBefore !== text) {

                        // Send ajax request named 'edit_custom_elements' to change the column to value text
                        // where id.
                        var data = {
                            'action': 'edit_custom_elements',
                            'id': parseInt($field.closest('tr').find('.check-column input').val()),
                            'column': $field.attr('name'),
                            'text': text
                        };

                        jQuery.post(ajaxurl, data, function(response) {
                            if(response){
                                jQuery('.custom_elements_message.fo_success').show().delay(5000).fadeOut();
                            }else{
                                jQuery('.custom_elements_message.fo_warning').show().delay(5000).fadeOut();
                                $field.val(textBefore);
                            }
                        });
                    }
                });

                jQuery('table.custom_elements').find('td input:text').on('focus', function () {
                    var $field = jQuery(this);

                    // Store the current value on focus and on change
                    textBefore = $field.val();
                }).blur(function() {
                    var $field = jQuery(this);
                    var text = $field.val();

                    // Set back previous value if empty
                    if (text.length <= 0) {
                        $field.val(textBefore);
                        return;
                    }

                    if (textBefore !== text) {

                        // Send ajax request named 'edit_custom_elements' to change the column to value text
                        // where id.
                        var data = {
                            'action': 'edit_custom_elements',
                            'id': parseInt($field.closest('tr').find('.check-column input').val()),
                            'column': $field.attr('name'),
                            'text': text
                        };

                        jQuery.post(ajaxurl, data, function(response) {

                            // Show message for success and error for 3 seconds.
                            if(response){
                                jQuery('.custom_elements_message.fo_success').show().delay(5000).fadeOut();
                            }else{
                                jQuery('.custom_elements_message.fo_warning').show().delay(5000).fadeOut();
                                $field.val(textBefore);
                            }
                        });
                    }
                });
            });
        </script> <?php
    }

    public function edit_custom_elements_callback() {
        global $wpdb;

        $table_name = $wpdb->prefix . BK_ELEMENTS_DATABASE;
        $wpdb->update(
        $table_name,
        array( $_POST['column'] => $_POST['text'] ), // change the column selected with the new value.
        array('id' => $_POST['id']) // where id
        );

        // Initialize what is a must for the elements file.
        $this->load_custom_elements();
        $this->elements_options = get_option( 'fo_elements_options' );
        $this->advanced_options = get_option( 'fo_advanced_options' );

        $this->create_elements_file();

        wp_die(true); // this is required to terminate immediately and return a proper response
    }

    /**
     * Add options settings page in the wordpress settings.
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        $hook = add_options_page(
            'Settings Admin',
            __('Housename', 'bk-fonts'),
            'manage_fonts',
            'font-setting-admin',
            array( $this, 'create_font_settings_page' )
        );

        add_action( 'load-' . $hook, array( $this, 'init_page' ) );
        add_action( 'load-' . $hook, array( $this, 'register_scripts' ) );
        add_filter( 'option_page_capability_fo_general_options', array($this, 'options_capability') );
        add_filter( 'option_page_capability_fo_elements_options', array($this, 'options_capability') );
        add_filter( 'option_page_capability_fo_advanced_options', array($this, 'options_capability') );
    }

    // Allows to tell wordpress that the options named fo_general_options & fo_elements_options
    // can be saved by manage_fonts capability and by any role with this capability.
	public function options_capability( $cap ) {
    	return 'manage_fonts';
	}

    public function init_page(){
        // First in this page. Make sure to handle all events happend and crete
        if (isset($_POST['submit_upload_font'])){
            if($args = $this->validate_upload()){
                $this->upload_file($args);
                $this->should_create_css = true;
            }else{
                add_action( 'admin_notices', array($this, 'upload_failed_admin_notice') );
            }
        }

        if (isset($_POST['submit_usable_font'])){
            if($args = $this->validate_add_usable()){
                $this->use_font($args);
                $this->should_create_css = true;
            }else{
                add_action( 'admin_notices', array($this, 'use_font_failed_admin_notice') );
            }
        }

        if (isset($_POST['delete_usable_font'])){
            if($args = $this->validate_delete_usable()){
                $this->delete_font($args);
                $this->should_create_css = true;
                wp_cache_delete ( 'alloptions', 'options' );

            }else{
                add_action( 'admin_notices', array($this, 'delete_font_failed_admin_notice') );
            }
        }

        if(isset($_POST['submit_custom_elements'])){
            if($args = $this->validate_custom_elements()){
                $this->add_custom_elements($args);
                $this->should_create_css = true;
            }else{
                add_action( 'admin_notices', array($this, 'add_custom_elements_failed_admin_notice') );
            }
        }

        if(isset($_GET['action']) && ($_GET['action'] == 'delete' || $_GET['action'] == 'bulk-delete') && isset($_GET['custom_element'])){
            $this->should_create_css = true;
        }

    	$this->init();
        $this->create_css_file();
    }

    public function create_css_file($force = false){
        if(((!isset($_GET['settings-updated']) || !$_GET['settings-updated']) && !$this->should_create_css) && !$force){
            return;
        }

        /* ========= Create the declartions file ========= */
        $this->create_declration_file();

        /* ========= Create the elements file ========= */
        $this->create_elements_file();
    }

    private function create_elements_file(){
        global $fo_css_directory_path;
        global $fo_elements_css_file_name;

        $content = self::DEFAULT_CSS_TITLE;

        // Add the known elements css.
        foreach ($this->elements_options as $key => $value) {
            if(strpos($key, 'important') || strpos($key, 'weight') || !$value)
                continue;

            $strip_key = str_replace('_font', '', $key);
            $important = $this->elements_options[$key . '_important'];
            $important_content =  $important ? '!important' : '';
            if(array_key_exists($key . '_weight', $this->elements_options)){
                $weight = fo_get_weight_style_value($this->elements_options[$key . '_weight']);
            }else{
                $weight = fo_get_weight_style_value("");
            }

            $font_weight = $weight['weight'] ? sprintf("font-weight:%s%s;", $weight['weight'], $important_content) : '';
            $font_style = $weight['style'] ? sprintf("font-style:%s;", $weight['style']) : '';
            $content .= sprintf("%s { font-family: '%s'%s; %s %s }\n", $strip_key, $value, $important_content, $font_weight, $font_style);
        }

        // Add custom elements css.
        foreach ($this->custom_elements as $custom_element_db) {
            // if name is valid create a css for it.
            if($custom_element_db->name){
                $important_content = $custom_element_db->important ? '!important' : '';
                $weight = fo_get_weight_style_value($custom_element_db->font_weight);
                $font_weight = $weight['weight'] ? sprintf("font-weight:%s%s;", $weight['weight'], $important_content) : '';
                $font_style = $weight['style'] ? sprintf("font-style:%s;", $weight['style']) : '';
                $content .= sprintf("%s { font-family: '%s'%s; %s %s}\n", $custom_element_db->custom_elements, $custom_element_db->name, $important_content, $font_weight, $font_style);
            }
        }

        // Add additional CSS
        if(array_key_exists('additional_css', $this->advanced_options)){
            $content .= "\n" . $this->advanced_options['additional_css'];
        }

        // If there is any css to write. Create the directory if needed and create the file.
        fo_try_write_file($content, $fo_css_directory_path, $fo_elements_css_file_name, array($this, 'generate_css_failed_admin_notice'));
    }

    private function create_declration_file(){
        global $fo_css_directory_path;
        global $fo_declarations_css_file_name;

        $content = self::DEFAULT_CSS_TITLE;
        $custom_fonts_content = '';
        $google_fonts = array();
        foreach ($this->usable_fonts_db as $usable_font_db) {
            $weights = array();

            // Get all the font weights from the known elements section
            // and add them to include in the font file request.
            foreach ($this->elements as $id => $title) {
                if($this->elements_options[$id] == $usable_font_db->name){
                    if(array_key_exists($id . '_weight', $this->elements_options)){
                        $weight = fo_get_weight_style_value($this->elements_options[$id . '_weight']);
                        if(($weight['weight'] || $weight['style']) && !in_array($weight, $weights)){
                            $weights[] = $weight;
                        }
                    }
                }
            }

            // Get all the font weights in custom elements assosiated with
            // this font to include in font file request.
            foreach ($this->custom_elements as $custom_element_db) {
                if($custom_element_db->font_id === $usable_font_db->id){
                    $weight = fo_get_weight_style_value($custom_element_db->font_weight);
                    if(($weight['weight'] || $weight['style']) && !in_array($weight, $weights)){
                        $weights[] = $weight;
                    }
                }
            }

            $usable_font = $this->usable_fonts[$usable_font_db->name];
            switch ($usable_font->kind) {
                case 'custom':
                foreach ($usable_font->files as $custom_weight => $urls) {
                    // Set all the urls content under the same src.
                    $urls_content_arr = array();
                    $eot_fix = '';
                    $svg_url = '';
                    foreach ($urls as $url) {
                        $isEOT = strpos($url, 'eot') !== false;
                        $isSVG = strpos($url, 'svg') !== false;

                        // In eot we fix IE<9 the format by adding another src.
                        if($isEOT) {
                            $eot_fix = "src: url('" . $url . "');";
                        }

                        $url_str = "url('" . fo_get_font_url($url, $isEOT, $isSVG) . "') format('" . fo_get_font_format($url) . "')";
                        if($isSVG) {
                            // Dont save in array to make sure svg is last on the list.
                            $svg_url = $url_str;
                        } else {
                            $urls_content_arr[] = $url_str;
                        }
                    }

                    // If the svg exists push it last.
                    if($svg_url !== '') {
                        array_push($urls_content_arr, $svg_url);
                    }

                    $urls_content = implode(",\n", $urls_content_arr) . ';';
                    $styles = fo_get_weight_style_value($custom_weight);
                    $custom_fonts_content .= "
@font-face {
    font-family: '" . $usable_font->family . "';
    " . $eot_fix . "
    src: " . $urls_content . "\n";

                    $custom_fonts_content .= $styles['weight'] ? "font-weight: " . $styles['weight'] . ";\n" : "";
                    $custom_fonts_content .= $styles['style'] ? "font-style: " . $styles['style'] . ";\n" : "";
                    $custom_fonts_content .= "}\n";
                    }

                    break;
                case 'regular':
                default:
                    break;
            }
        }

        // Add Google fonts to the css. MUST BE FIRST.
        if(!empty($google_fonts)){
            // We are assuming not to much google fonts. If it is, we need to split the request.
           // $content .= "<link href='http://fonts.googleapis.com/css?family=". implode("|", $google_fonts) . "' rel='stylesheet' type='text/css'>\n";
            $google_requests = array();
            foreach ($google_fonts as $font_name => $weights) {
                if(!empty($weights)){
                    $full_weights = array();
                    foreach ($weights as $style) {
                        $full_weights[] = $style['weight'] . $style['style'];
                    }

                    $google_requests[] = $font_name . ":" . implode(',', $full_weights);
                }else{
                    $google_requests[] = $font_name;
                }
            }

            $content .= "@import url('//fonts.googleapis.com/css?family=". implode("|", $google_requests) . "');\n";
        }

        // Add the custom fonts css that was created before.
        $content .= $custom_fonts_content;

        // If there is any declartions css to write.
        fo_try_write_file($content, $fo_css_directory_path, $fo_declarations_css_file_name, array($this, 'generate_css_failed_admin_notice'));
    }

    /**
     * Initialize the class private fields. Options, Google fonts list, known fonts, available fonts,
     * and all the usable fonts.
     */
    public function init(){
        $this->general_options = get_option( 'fo_general_options' , array() );
        $this->elements_options = get_option( 'fo_elements_options', array() );
        $this->advanced_options = get_option( 'fo_advanced_options', array() );

        $this->custom_elements_table = new ElementsTable();

        $this->include_font_link = isset( $this->general_options['include_font_link'] ) && $this->general_options['include_font_link'];

        if(isset($this->general_options['google_key']) && $this->general_options['google_key']){
            // Add Google fonts.
            $response = wp_remote_get("https://www.googleapis.com/webfonts/v1/webfonts?sort=alpha&key=" . $this->general_options['google_key'], array('timeout' => 60));
            if( wp_remote_retrieve_response_code( $response ) == 200){
           		$this->google_fonts = json_decode(wp_remote_retrieve_body($response))->items;
           	}else{
                // Show the most detailed message in the error and display it to the user.
                if ( is_wp_error( $response ) ) {
                    $error = wp_strip_all_tags( $response->get_error_message() );
                }else{
                    $error = json_decode(wp_remote_retrieve_body($response))->error->errors[0];
                }

                add_settings_error('google_key', '', __('Google API key is not valid: ', 'bk-fonts') . ' [' . $error->reason . '] ' . $error->message, 'error');
            }
        }

        if(empty($this->google_fonts)){
            // Get a static google fonts list.
            require_once BK_ABSPATH . '/helpers/bkh-fonts.php';

            $this->google_fonts = json_decode(fo_get_all_google_fonts_static_response())->items;
            $this->is_google_static = true;
        }

        // Add known fonts.
        $this->known_fonts = fo_get_known_fonts_array();

        // Add early access google fonts. (this list is static, no api to get full list)
        $this->earlyaccess_fonts = $this->get_early_access_fonts_array();

        // Merge (and sort) the early access google fonts list with the google fonts.
        if(!empty($this->google_fonts)){
            $this->google_fonts = array_merge($this->google_fonts, $this->earlyaccess_fonts);
            fo_array_sort($this->google_fonts);

            $this->available_fonts = array_merge($this->available_fonts, $this->google_fonts, $this->known_fonts);
        }else{
            $this->available_fonts = array_merge($this->available_fonts, $this->earlyaccess_fonts, $this->known_fonts);
        }

        // Get all usable fonts and add them to a list.
        $this->load_usable_fonts();
        $this->load_custom_elements();
    }

    /**
     * Options page callback
     */
    public function create_font_settings_page(){
        if(isset($_GET['manage_font_id'])){
        		foreach ($this->usable_fonts_db as $font_db) {
        			 if(intval($_GET['manage_font_id']) == $font_db->id){

                        // If name is made up/ deleted or unavailable for now just break for now.
                        if(!array_key_exists($font_db->name, $this->usable_fonts))
                            break;

	                	$this->selected_manage_font = $this->usable_fonts[$font_db->name];
        				$this->custom_elements_table->prepare_items_by_font($this->custom_elements, $font_db->id, $this->selected_manage_font);
	                	break;
	                }
        		}
        }

        // Load the google fonts if selected or if not specified. else load just whats usable.
        if($this->include_font_link)
            fo_print_links($this->google_fonts, $this->fonts_per_link);

        ?>
        <div class="wrap">
            <h1><?php _e('Font Settings', 'bk-fonts'); ?></h1>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">

                <div id="tabs" class="<?php echo is_rtl() ? ' rtl' : 'ltr'; ?>">
                    <ul class="tabs">
                        <li><a href="#tabs-1"><?php _e('Manage Fonts', 'bk-fonts'); ?></a></li>
                        <li><a href="#tabs-2"><?php _e('Manage Price', 'bk-fonts'); ?></a></li>
                    </ul>
                    <div id="tabs-1">
                      <?php include('general-settings.php'); ?>
                    </div>
                    <div id="tabs-2">
                      <?php include('price-settings.php'); ?>
                    </div>
                </div>
        </div>
        <?php
    }

    private function validate_upload(){
        if(!isset( $_POST['add_custom_font_nonce'] ) || !wp_verify_nonce( $_POST['add_custom_font_nonce'], 'add_custom_font' )){
            $this->recent_error = __('Session ended, please try again.', 'bk-fonts');
            return false;
        }

        $args['font_name'] = sanitize_text_field( $_POST['font_name'] );
        if(!$args['font_name']){
            $this->recent_error = __('Font name is empty or invalid.', 'bk-fonts');
            return false;
        }

        $args['font_weight'] = 'reg';
        if(!$args['font_weight']){
            $this->recent_error = __('Font weight is empty or invalid.', 'bk-fonts');
            return false;
        }

        if(!isset($_FILES['font_file'])){
            $this->recent_error = __('Font file is not selected.', 'bk-fonts');
            return false;
        }

        $args['font_file'] = fo_rearray_files($_FILES['font_file']);

        $i = 0;
        foreach ($args['font_file'] as $file) {
            if(!$file['name']){
                unset($args['font_file'][$i]);
            }

            $i++;
        }

        if(empty($args['font_file'])){
            $this->recent_error = __('Font file(s) not selected.', 'bk-fonts');
            return false;
        }

        return $args;
    }

    private function validate_add_usable(){
        if(!isset( $_POST['add_usable_font_nonce'] ) || !wp_verify_nonce( $_POST['add_usable_font_nonce'], 'add_usable_font' )){
            $this->recent_error = __('Session ended, please try again.', 'bk-fonts');
            return false;
        }

        $args['usable_font'] = sanitize_text_field( $_POST['usable_font'] );
        if(!$args['usable_font']){
            $this->recent_error = __('Usable font is empty or invalid.', 'bk-fonts');
            return false;
        }

        return $args;
    }

    private function validate_custom_elements(){
        if(!isset( $_POST['add_custom_elements_nonce'] ) || !wp_verify_nonce( $_POST['add_custom_elements_nonce'], 'add_custom_elements' )){
            $this->recent_error = __('Session ended, please try again.', 'bk-fonts');
            return false;
        }

        $args['custom_elements'] = sanitize_text_field( $_POST['custom_elements'] );
        if(!$args['custom_elements']){
            $this->recent_error = __('Custom elements is empty or invalid.', 'bk-fonts');
            return false;
        }

        $args['important'] = isset($_POST['important']) ? 1 : 0;

        $args['font_id'] = $_POST['font_id'];

        $args['font_weight'] =  'bold';

        return $args;
    }

    private function validate_delete_usable(){
        if(!isset( $_POST['delete_usable_font_nonce'] ) || !wp_verify_nonce( $_POST['delete_usable_font_nonce'], 'delete_usable_font' )){
            $this->recent_error = __('Session ended, please try again.', 'bk-fonts');
            return false;
        }

        $args['font_id'] = intval( $_POST['font_id'] );
        $args['font_name'] = sanitize_text_field( $_POST['font_name'] );
        if(!$args['font_id'] || !$args['font_name']){
            $this->recent_error = __('Something went horribly wrong. Ask the support!', 'bk-fonts');
            return false;
        }

        return $args;
    }

    private function upload_file($args = array()){
        $urls = array();

        foreach ($args['font_file'] as $file) {
            $movefile = fo_upload_file($file, array($this, 'fo_upload_dir'));
            if(!$movefile || isset( $movefile['error'] )){
                $this->recent_error = $movefile['error'];
                add_action( 'admin_notices', array($this, 'upload_failed_admin_notice') );
                return false;
            }

            // Save relative url in the database.
            $urls[] = substr($movefile['url'], strlen(get_site_url()));
        }

        // Find the font if does exist.
        $usable_font = FontsDatabaseHelper::get_usable_font($args['font_name']);
        $urls_str = implode(self::CUSTOM_FONT_URL_SPERATOR, $urls);
        if($usable_font){
            $urls_str = $usable_font->url . self::CUSTOM_FONT_URL_SPERATOR . $urls_str;
        }

        $this->save_usable_font_to_database($args['font_name'], $urls_str, true, $usable_font);

        add_action( 'admin_notices', array($this, 'upload_successfull_admin_notice') );
    }

    private function use_font($args = array()){
            add_action( 'admin_notices', array($this, 'use_font_successfull_admin_notice') );
            $this->save_usable_font_to_database($args['usable_font']);
    }

    private function add_custom_elements($args = array()){
            add_action( 'admin_notices', array($this, 'add_custom_elements_successfull_admin_notice') );
            $this->save_custom_elements_to_database($args['font_id'], 'reg', $args['custom_elements'], $args['important']);
    }

    private function delete_font($args = array()){
            global $fo_css_directory_path;

            // Delete all the known elements for this font and reset them back to default.
            $elements_options = get_option('fo_elements_options', array());
            foreach ($this->elements as $element_id => $element_display_name) {
                if(array_key_exists($element_id, $elements_options) && $elements_options[$element_id] == $args['font_name']){
                    $elements_options[$element_id] = '';
                }
            }

            update_option('fo_elements_options', $elements_options);

            // Delete all custom elements for this font.
            $table_name = BK_ELEMENTS_DATABASE;
            $this->delete_from_database($table_name, 'font_id', $args['font_id']);

            global $wpdb;

            $usable_fonts = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . BK_USABLE_FONTS_DATABASE . ' ORDER BY id DESC');
            foreach ($usable_fonts as $usable_font) {
                if($usable_font->name == $args['font_name']){
                    if(!$usable_font->custom)
                        break;

                   $urls = explode(self::CUSTOM_FONT_URL_SPERATOR, $usable_font->url);
                   foreach ($urls as $url) {
                        if(in_array($url, $this->font_weights))
                            continue;

                        // Delete the old file.
                        $file_name = basename($url);
                        if(file_exists($fo_css_directory_path . '/' . $file_name))
                            wp_delete_file($fo_css_directory_path . '/' . $file_name);
                    }
                }
            }

            // Delete this font from the website.
            $table_name = BK_USABLE_FONTS_DATABASE;
            $this->delete_from_database($table_name, 'id', $args['font_id']);

            add_action( 'admin_notices', array($this, 'delete_font_successfull_admin_notice') );
    }

    private function delete_from_database($table_name, $field_name, $field_value){
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . $table_name, array( $field_name => $field_value ) );
    }

    private function save_custom_elements_to_database($id, $font_weight, $custom_elements, $important){
        global $wpdb;
        $table_name = $wpdb->prefix . BK_ELEMENTS_DATABASE;

        $wpdb->insert(
        $table_name,
        array(
            'font_id' => $id,
            'custom_elements' => $custom_elements,
            'font_weight' => '',
            'important' => $important ? 1 : 0,
        ));
    }

    private function save_usable_font_to_database($name, $url = '',$is_custom = false, $update = false){
        global $wpdb;
        $table_name = $wpdb->prefix . BK_USABLE_FONTS_DATABASE;

        if($update){
            $wpdb->update(
                    $table_name,
                    array('url' => $url),
                    array('name' => $name));
        }else{
            $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'url' => $url,
                'custom' => $is_custom ? 1 : 0,
            ));
        }
    }


    public function add_custom_elements_failed_admin_notice() {
      ?>
        <div class="error notice">
            <p><?php echo __( 'Error adding custom elements: ', 'bk-fonts' ) . $this->recent_error; ?></p>
        </div>
        <?php
    }

    public function add_custom_elements_successfull_admin_notice() {
        ?>
        <div class="updated notice">
            <p><?php _e( 'Custom elements added to your website!', 'bk-fonts' ); ?></p>
        </div>
        <?php
    }

    public function use_font_successfull_admin_notice() {
        ?>
        <div class="updated notice">
            <p><?php _e( 'Font can now be used in your website!', 'bk-fonts' ); ?></p>
        </div>
        <?php
    }

    public function delete_font_successfull_admin_notice() {
        ?>
        <div class="updated notice">
            <p><?php _e( 'Font deleted from your website!', 'bk-fonts' ); ?></p>
        </div>
        <?php
    }

    public function upload_successfull_admin_notice() {
        ?>
        <div class="updated notice">
            <p><?php _e( 'The file(s) uploaded successfully!', 'bk-fonts' ); ?></p>
        </div>
        <?php
    }

    public function upload_failed_admin_notice() {
        ?>
        <div class="error notice">
            <p><?php echo __( 'Error uploading the file: ', 'bk-fonts' ) . $this->recent_error; ?></p>
        </div>
        <?php
    }

    public function use_font_failed_admin_notice() {
        ?>
        <div class="error notice">
            <p><?php echo __( 'Error adding font to website fonts: ', 'bk-fonts' ) . $this->recent_error; ?></p>
        </div>
        <?php
    }

    public function delete_font_failed_admin_notice() {
        ?>
        <div class="error notice">
            <p><?php echo __( 'Error deleting font: ', 'bk-fonts' ) . $this->recent_error; ?></p>
        </div>
        <?php
    }

    public function generate_css_failed_admin_notice() {
        ?>
        <div class="error notice">
            <p><?php echo __( 'Failed to open or create the css file. Check for permissions.', 'bk-fonts' ); ?></p>
        </div>
        <?php
    }

    /**
     * Override the default upload path.
     *
     * @param   array   $dir
     * @return  array
     */
    public function fo_upload_dir( $dir ) {
        $base_url =   $url = fo_get_all_http_url( $dir['baseurl'] );

        return array(
            'path'   => $dir['basedir'] . '/bk-fonts',
            'url'    => $base_url . '/bk-fonts',
            'subdir' => '/bk-fonts',
        ) + $dir;
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'fo_general_options', // Option group
            'fo_general_options', // Option name
            array( $this, 'general_sanitize' ) // Sanitize
        );
        register_setting(
            'fo_elements_options', // Option group
            'fo_elements_options', // Option name
            array( $this, 'elements_sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_elements', // ID
            '', // Title
            array( $this, 'print_elements_section_info' ), // Callback
            'font-setting-admin' // Page
        );

        // Add all the elements to the elements section.
        foreach ($this->elements as $id => $title) {
            add_settings_field(
                $id, // ID
                htmlspecialchars($title), // Title
                array( $this, 'fonts_list_field_callback' ), // Callback
                'font-setting-admin', // Page
                'setting_elements', // Section
                $id // Parameter for Callback
            );

             add_settings_field(
                $id . '_weight', // ID
                __('Font Weight', 'bk-fonts'), // Title
                array( $this, 'fonts_weight_list_field_callback' ), // Callback
                'font-setting-admin', // Page
                'setting_elements', // Section
                $id . '_weight' // Parameter for Callback
           );

            add_settings_field(
                $id . '_important', // ID
                '', // Title
                array( $this, 'is_important_element_field_callback' ), // Callback
                'font-setting-admin', // Page
                'setting_elements', // Section
                $id . '_important' // Parameter for Callback
            );
        }

        register_setting(
            'bk_misc_options',
            'bk_misc_options',
            array( $this, 'cpc_options_sanitize' )
        );
        register_setting(
          'bk_cpc_options',
          'bk_cpc_options',
          array( $this, 'cpc_options_sanitize' )
        );
        register_setting(
          'bk_pimg_options',
          'bk_pimg_options',
          array( $this, 'cpc_options_sanitize' )
        );
        /* material */
        register_setting(
            'bk_material_options',
            'bk_material_options',
            array( $this, 'cpc_material_sanitize' )
          );
        /* material */ 
        add_settings_section(
          'setting_general', // ID
          '', // Title
          '',
          'font-setting-admin' // Page
        );
        add_settings_section(
          'bk_misc_settings', // ID
          '', // Title
          '',
          'font-setting-admin' // Page
        );
        add_settings_section(
          'bk_pimg_settings', // ID
          '', // Title
          '',
          'font-setting-admin' // Page
        );
        /* material section */
        add_settings_section(
            'bk_material_settings', // ID
            '', // Title
            '',
            'font-setting-admin' // Page
        );
        /* material */
        add_settings_field(
            'bk_material_dibond',
            'Dibond',
            array( $this, 'print_material_section_info_dibond' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_acrylic',
            'Acrylic',
            array( $this, 'print_material_section_info_acrylic' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_painted_aluminium',
            'Painted Aluminium',
            array( $this, 'print_material_section_info_painted_aluminium' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_brushed_dibond',
            'Brushed Dibond',
            array( $this, 'print_material_section_info_brushed_dibond' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_brushed_aluminium',
            'Brushed Aluminium',
            array( $this, 'print_material_section_info_brushed_aluminium' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_brushed_stainless',
            'Brushed Stainless',
            array( $this, 'print_material_section_info_brushed_stainless' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_brushed_brass',
            'Brushed Brass',
            array( $this, 'print_material_section_info_brushed_brass' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_brushed_copper',
            'Brushed Copper',
            array( $this, 'print_material_section_info_brushed_copper' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        add_settings_field(
            'bk_material_brushed_bronze',
            'Brushed Bronze',
            array( $this, 'print_material_section_info_brushed_bronze' ),
            'font-setting-admin',
            'bk_material_settings'
        );
        /* material */
        add_settings_section(
          'cpc_settings_section',
          'cpc Settings Section',
          array( $this, 'print_elements_section_info' ),
          'font-setting-admin'
        );
        add_settings_field(
          'cpc_dibond',
          'Dibond',
          array( $this, 'dibond_callback' ),
          'font-setting-admin',
          'setting_general'
        );
        add_settings_field(
          'cpc_acrylic',
          'Acrylic',
          array( $this, 'acrylic_callback' ),
          'font-setting-admin',
          'setting_general'
        );
        add_settings_field(
          'cpc_pa',
          'Painted Aluminium',
          array( $this, 'pa_callback' ),
          'font-setting-admin',
          'setting_general'
        );

        add_settings_field(
          'cpc_brushed_dibond',
          'Brushed Dibond',
          array( $this, 'brushed_dibond_callback' ),
          'font-setting-admin',
          'setting_general'
        );
        add_settings_field(
          'cpc_ba',
          'Brushed Aluminium',
          array( $this, 'ba_callback' ),
          'font-setting-admin',
          'setting_general'
        );
        add_settings_field(
          'cpc_bs',
          'Brushed Stainless',
          array( $this, 'brushed_stainless_callback' ),
          'font-setting-admin',
          'setting_general'
        );

        add_settings_field(
          'cpc_brushed_brass',
          'Brushed Brass',
          array( $this, 'brushed_brass_callback' ),
          'font-setting-admin',
          'setting_general'
        );
        add_settings_field(
          'cpc_brushed_copper',
          'Brushed Copper',
          array( $this, 'brushed_copper_callback' ),
          'font-setting-admin',
          'setting_general'
        );
        add_settings_field(
          'cpc_brushed_bronze',
          'Brushed Bronze',
          array( $this, 'brushed_bronze_callback' ),
          'font-setting-admin',
          'setting_general'
        );

        /* Misc Settings */
        add_settings_field(
          'bk_character_spacing',
          'Character Spacing Average',
          array( $this, 'bk_character_spacing_callback' ),
          'font-setting-admin',
          'bk_misc_settings'
        );
        add_settings_field(
          'bk_misc_tax',
          'HST GST Tax',
          array( $this, 'bk_hst_gst_cb' ),
          'font-setting-admin',
          'bk_misc_settings'
        );
        add_settings_field(
          'bk_installation_rate',
          'Installation Rate',
          array( $this, 'bk_installation_cb' ),
          'font-setting-admin',
          'bk_misc_settings'
        );
        add_settings_field(
          'bk_delivery_rate',
          'Delivery Rate',
          array( $this, 'bk_delivery_cb' ),
          'font-setting-admin',
          'bk_misc_settings'
        );

        /* Product Images */
        add_settings_field(
          'bk_dibond_pimg',
          'Dibond',
          array( $this, 'bk_dibond_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_acrylic_pimg',
          'Acrylic',
          array( $this, 'bk_acrylic_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_pa_pimg',
          'Painted Aluminium',
          array( $this, 'bk_pa_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_bd_pimg',
          'Brushed Dibond',
          array( $this, 'bk_bd_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_ba_pimg',
          'Brushed Aluminium',
          array( $this, 'bk_ba_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_bs_pimg',
          'Brushed Stainless',
          array( $this, 'bk_bs_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_bbrass_pimg',
          'Brushed Brass',
          array( $this, 'bk_bbrass_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_bc_pimg',
          'Brushed Copper',
          array( $this, 'bk_bc_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
        add_settings_field(
          'bk_bbronze_pimg',
          'Brushed Bronze',
          array( $this, 'bk_bbronze_pimg_cb' ),
          'font-setting-admin',
          'bk_pimg_settings'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function general_sanitize( $input )
    {
        $new_input = array();

        if( !isset( $input['include_font_link'] ) )
            $new_input['include_font_link'] =  0 ;
        else
        	$new_input['include_font_link'] = $input['include_font_link'];

        if( !isset( $input['uninstall_all'] ) )
            $new_input['uninstall_all'] =  0 ;
        else
            $new_input['uninstall_all'] = $input['uninstall_all'];

        // Do not allow change in permissions if user is not admin.
        if(!$this->is_admin)
        	return $new_input;

        // Get the old permissions.
       	$this->general_options = get_option( 'fo_general_options' );
       	$old_permissions = isset($this->general_options['permissions']) ? $this->general_options['permissions'] : array();

       	// Get the new permissions.
       	$new_input['permissions'] = isset($input['permissions']) ? $input['permissions'] : array();
       	if($new_input != $old_permissions){

	        // Remove previus capabilities.
            foreach ($old_permissions as $value) {
            	if($value != BK_DEFAULT_ROLE){
	           		$prev_role = get_role($value);
	           		$prev_role->remove_cap('manage_fonts');
	           	}
            }

            // Add the new capabilities to the new roles.
            foreach ($new_input['permissions'] as $value) {
	           	$prev_role = get_role($value);
	            $prev_role->add_cap('manage_fonts');
            }
        }

        return $new_input;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function advanced_sanitize( $input )
    {
        // Retreive old inputs for backup.
        $advanced_options = get_option( 'fo_advanced_options', array() );
        $new_input = array();
        if( isset( $input['additional_css'] ) )
            $new_input['additional_css'] = sanitize_textarea_field( $input['additional_css'] );
        else
            $new_input['additional_css'] = $advanced_options['additional_css'];

        return $new_input;
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function elements_sanitize( $input )
    {
        $new_input = array();
        foreach ($this->elements as $id => $title) {
            if( isset( $input[$id] ) ){
                $new_input[$id] = sanitize_text_field( $input[$id] );
            }else{
                $new_input[$id] = '';
            }

            if( !isset( $input[$id . '_important'] ) )
                $new_input[$id . '_important'] =  0 ;
            else
                $new_input[$id . '_important'] = intval($input[$id . '_important']);

            if( isset( $input[$id . '_weight'] ) )
                $new_input[$id . '_weight'] = sanitize_text_field($input[$id . '_weight']);

        }

        return $new_input;
    }

    function cpc_options_sanitize($input){
        return $input;
    }
    function cpc_material_sanitize($input){
        return $input;
    }
    /**
     * Print the Section text
     */
    public function print_general_section_info()
    {
        _e('This is the general settings for the site.', 'bk-fonts');
    }

    /**
     * Print the Section text
     */
    public function print_advanced_css_section_info()
    {
        _e('This is the advanced css settings for the plugin.', 'bk-fonts');
    }

    /**
     * Print the Section text
     */
    public function print_elements_section_info()
    {
    }

    /**
     * Get the settings option for google key array and print one of its values
     */
    public function google_key_callback()
    {
        $faq_url = '#';
        $value = isset( $this->general_options['google_key'] ) ? esc_attr( $this->general_options['google_key']) : '';
        printf(
            '<div class="validate"><input type="text" id="google_key" name="fo_general_options[google_key]" value="%s" class="large-text %s %s" placeholder="Ex: AIzaSyB1I0couKSmsW1Nadr68IlJXXCaBi9wYwM" /><span></span></div>', $value , $this->is_google_static ? '' : 'valid', is_rtl() ? 'rtl' : 'ltr'
        );

        if($this->is_google_static){
            echo '<span style="color:#0073aa;font-weight: 500;">' . sprintf( __('The plugin uses static google fonts list. In order to get the most current fonts list, Google requires an API key, generated via their interface. For more information read <a href="%s" target="_blank">our FAQ</a>.'), esc_url( $faq_url )) . '</span>';
        }
    }

    public function dibond_callback() {
        bk_print_material_setting('dibond');
    }
    public function acrylic_callback() {
        bk_print_material_setting('acrylic');
    }
    public function pa_callback() {
        bk_print_material_setting('painted_aluminium');
    }
    public function brushed_dibond_callback() {
        bk_print_material_setting('brushed_dibond');
    }
    public function ba_callback() {
        bk_print_material_setting('brushed_aluminium');
    }
    public function brushed_stainless_callback() {
        bk_print_material_setting('brushed_stainless');
    }
    public function brushed_brass_callback() {
        bk_print_material_setting('brushed_brass');
    }
    public function brushed_copper_callback() {
        bk_print_material_setting('brushed_copper');
    }
    public function brushed_bronze_callback() {
        bk_print_material_setting('brushed_bronze');
    }
    public function bk_character_spacing_callback() {
        bk_character_spacing_setting();
    }
    /*material */
    public function print_material_section_info_dibond(){
        bk_print_material_section_info('dibond');
    }
    public function print_material_section_info_acrylic(){
        bk_print_material_section_info('acrylic');
    }
    public function print_material_section_info_painted_aluminium(){
        bk_print_material_section_info('painted_aluminium');
    }
    public function print_material_section_info_brushed_dibond(){
        bk_print_material_section_info('brushed_dibond');
    }
    public function print_material_section_info_brushed_aluminium(){
        bk_print_material_section_info('brushed_aluminium');
    }
    public function print_material_section_info_brushed_stainless(){
        bk_print_material_section_info('brushed_stainless');
    }
    public function print_material_section_info_brushed_brass(){
        bk_print_material_section_info('brushed_brass');
    }
    public function print_material_section_info_brushed_copper(){
        bk_print_material_section_info('brushed_copper');
    }
    public function print_material_section_info_brushed_bronze(){
        bk_print_material_section_info('brushed_bronze');
    }
    /* material */
    public function bk_hst_gst_cb() {
      $misc_options = get_option( 'bk_misc_options' , array() );
      $value = isset( $misc_options['hst_gst_tax'] ) ?
      esc_attr($misc_options['hst_gst_tax']) : '';
      printf(
          '<input type="text" name="bk_misc_options[hst_gst_tax]" value="%s" class="small-text %s" placeholder="10" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_installation_cb() {
      $misc_options = get_option( 'bk_misc_options' , array() );
      $value = isset( $misc_options['installation_rate'] ) ?
      esc_attr($misc_options['installation_rate']) : '';
      printf(
          '<input type="text" name="bk_misc_options[installation_rate]" value="%s" class="small-text %s" placeholder="10" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_delivery_cb() {
      $misc_options = get_option( 'bk_misc_options' , array() );
      $value = isset( $misc_options['delivery_rate'] ) ?
      esc_attr($misc_options['delivery_rate']) : '';
      printf(
          '<input type="text" name="bk_misc_options[delivery_rate]" value="%s" class="small-text %s" placeholder="10" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_dibond_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['dibond'] ) ?
      esc_attr($pimg_options['dibond']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[dibond]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_acrylic_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['acrylic'] ) ?
      esc_attr($pimg_options['acrylic']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[acrylic]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_pa_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['painted-aluminium'] ) ?
      esc_attr($pimg_options['painted-aluminium']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[painted-aluminium]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_bd_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['brushed-dibond'] ) ?
      esc_attr($pimg_options['brushed-dibond']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[brushed-dibond]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_ba_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['brushed-aluminium'] ) ?
      esc_attr($pimg_options['brushed-aluminium']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[brushed-aluminium]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_bs_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['brushed-stainless'] ) ?
      esc_attr($pimg_options['brushed-stainless']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[brushed-stainless]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_bbrass_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['brushed-brass'] ) ?
      esc_attr($pimg_options['brushed-brass']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[brushed-brass]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_bc_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['brushed-copper'] ) ?
      esc_attr($pimg_options['brushed-copper']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[brushed-copper]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    public function bk_bbronze_pimg_cb() {
      $pimg_options = get_option( 'bk_pimg_options' , array() );
      $value = isset( $pimg_options['brushed-bronze'] ) ?
      esc_attr($pimg_options['brushed-bronze']) : '';
      printf(
          '<input type="url" name="bk_pimg_options[brushed-bronze]" value="%s" class="large-text %s" placeholder="" />', $value, is_rtl() ? 'rtl' : 'ltr'
      );
    }
    /**
     * Get the settings option for additional css and print its values
     */
    public function additional_css_callback()
    {
        $value = isset( $this->advanced_options['additional_css'] ) ? $this->advanced_options['additional_css'] : '';
        echo '<div id="editor">' . $value . '</div>';
        echo '<textarea style="display: none;" id="additional_css" name="fo_advanced_options[additional_css]">'.$value.'</textarea>';
        echo '<span style="font-size: 11px;font-style:italic;">' . __('This custom css will be added to the elements css file. Please use with caution.', 'bk-fonts') . '</span>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function uninstall_all_callback()
    {
        $checked = isset($this->general_options['uninstall_all']) && $this->general_options['uninstall_all'] ? 'checked="checked"' : '';
        printf(
            '<fieldset>
                <legend class="screen-reader-text"><span>%s</span></legend>
                <label for="uninstall_all">
                    <input name="fo_general_options[uninstall_all]" type="checkbox" id="uninstall_all" value="1" %s>
                    %s
                </label>
            </fieldset>',
            __('Uninstall All Option', 'bk-fonts'),
            $checked,
            __('When checked uninstalling the plugin will delete all of it\'s content including uploaded fonts and database.', 'bk-fonts')
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function include_font_link_callback()
    {
        $checked = isset($this->general_options['include_font_link']) && $this->general_options['include_font_link'] ? 'checked="checked"' : '';
        printf(
            '<fieldset>
                <legend class="screen-reader-text"><span>%s</span></legend>
                <label for="include_font_link">
                    <input name="fo_general_options[include_font_link]" type="checkbox" id="include_font_link" value="1" %s>
                    %s
                </label>
            </fieldset>',
            __('Include Font Family Preview', 'bk-fonts'),
            $checked,
            __('Show font preview when listing the fonts (might be slow)', 'bk-fonts')
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function permissions_callback(){
        $wp_roles = new WP_Roles();
		$roles = $wp_roles->get_names();
        $checked_values = !isset($this->general_options['permissions']) ? array(BK_DEFAULT_ROLE) : $this->general_options['permissions'];

		foreach ($roles as $role_value => $role_name) {
			$checked = $role_value == 'administrator' || in_array($role_value, $checked_values) ? 'checked' : '';

			echo '<p><input type="checkbox"'.disabled("administrator", $role_value, false).' name="fo_general_options[permissions][]" value="' . $role_value . '" '.$checked.'>'.translate_user_role($role_name).'</input></p>';
  		}
    }

    /**
     * Prints the main fonts list.
     */
    public function fonts_list_field_callback($name)
    {
        $this->print_usable_fonts_list($name);
    }

    public function fonts_weight_list_field_callback($name){
        echo '<select id="'.$name.'" name="fo_elements_options['.$name.']" class="known_element_fonts_weights" style="min-width: 125px;">';
        echo '</select>';
    }

    public function fonts_weight_list_field($name){
        echo '<select id="'.$name.'" name="'.$name.'" class="known_element_fonts_weights" style="min-width: 125px;">';
        echo '</select>';
    }

    public function print_fonts_weights_list($name){
         echo '<select id="'.$name.'" name="'.$name.'" class="known_element_fonts_weights" style="min-width: 125px;">';
         foreach ($this->font_weights as $weight)
            echo fo_print_font_weight_option($weight, 'regular');
        echo '</select>';
    }

    /**
     * Prints the main fonts list.
     */
    public function is_important_element_field_callback($name)
    {
        $this->print_is_important_checkbox_options($name);
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function print_is_important_checkbox_options($name)
    {
         $checked = !isset($this->elements_options[$name]) || (isset($this->elements_options[$name]) && $this->elements_options[$name]) ? 'checked="checked"' : '';
        printf(
            '<fieldset>
                <legend class="screen-reader-text"><span>%s</span></legend>
                <label for="%s">
                    <input name="fo_elements_options[%s]" type="checkbox" id="%s" value="1" %s>
                    %s
                </label>
            </fieldset>',
            __('Important', 'bk-fonts'),
            $name, $name, $name,
            $checked,
            __('Include !important to this element to always apply.', 'bk-fonts')
        );
    }

    public function print_is_important_checkbox($name, $checked = true)
    {
        printf(
            '<fieldset>
                <legend class="screen-reader-text"><span>%s</span></legend>
                <label for="%s">
                    <input name="%s" type="checkbox" id="%s" value="1" %s>
                    %s
                </label>
            </fieldset>',
            __('Important', 'bk-fonts'),
            $name, $name, $name,
            checked(true, $checked, false),
            __('Include !important to this element to always apply.', 'bk-fonts')
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    private function print_usable_fonts_list($name)
    {
        $selected = isset( $this->elements_options[$name] ) ? esc_attr( $this->elements_options[$name]) : '';
        echo '<select id="'.$name.'" name="fo_elements_options['.$name.']" class="known_element_fonts">';

        echo '<option value="" '. selected('', $selected, false) . '>' . __('Default', 'bk-fonts') . '</option>';

        //fonts section
        foreach($this->usable_fonts as $font)
        {
          $font_name = $font->family;
          $is_selected = selected($font_name, $selected, false);
          echo '<option value="'.$font_name.'" style="font-family: '.$font_name.';" '.$is_selected.'>'.$font_name.'</option>';
        }

        echo '</select>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    private function print_custom_elements_usable_fonts_list($name, $default = '', $validity = '')
    {
        echo '<select id="'.$name.'" name="'.$name.'" required oninvalid="this.setCustomValidity(\'' . $validity . '\')" oninput="setCustomValidity(\'\')">';

        if($default){
        	 echo '<option value="">'.$default.'</option>';
        }

        //fonts section
        foreach($this->usable_fonts_db as $font)
        {
          $font_name = $font->name;
          $selected = isset($_GET[$name]) && $font->id == $_GET[$name];
          echo '<option value="' . $font->id . '" style="font-family: '.$font_name.';" ' . selected($selected) . '>'.$font_name.'</option>';
        }

        echo '</select>';
    }

    /**
     * Get the settings option array and print one of its values
     */
    private function print_available_fonts_list($name, $default = "")
    {
        echo '<select id="'.$name.'" name="'.$name.'">';
        if($default){
            echo '<option value="">'.$default.'</option>';
        }

        //fonts section
        foreach($this->available_fonts as $font)
        {
          $font_name = $font->family;
          echo '<option value="'.$font_name.'" style="font-family: '.$font_name.';">'.$font_name.'</option>';
        }

        echo '</select>';
    }

    private function load_usable_fonts(){
        $this->usable_fonts_db = FontsDatabaseHelper::get_usable_fonts();
        foreach ( $this->usable_fonts_db as $usable_font) {

            // Find the font from the lists.
            if($usable_font->custom){
                // Set the urls that will be used in the file.
                $weight_files = array();
                $variants = array();

                $current_weight = '';
                foreach (explode(self::CUSTOM_FONT_URL_SPERATOR, $usable_font->url) as $url) {
                    if(!$url)
                        continue;

                    // If this is infact a font weight, change the current weight
                    // and add all urls from now to this font weight.
                    if(in_array($url, $this->font_weights)){
                        $current_weight = $url;

                        if(!in_array($url, $variants))
                            $variants[] = $url;

                        continue;
                    }

                    // first time and no weight yet? must be font
                    // from before 2.0. So add normal weight by default.
                    if(!$current_weight){
                        $current_weight = 'regular';
                        $variants[] = 'regular';
                    }

                    // If the array does not contain the weight, add it.
                    if(!array_key_exists($current_weight, $weight_files)){
                        $weight_files[$current_weight] = array();
                    }

                    // Add the current url to the current weight array.
                    array_push($weight_files[$current_weight], fo_get_full_url($url));
                }

                $font_obj = (object) array( 'family' => $usable_font->name, 'files' => (object) $weight_files, 'kind' => 'custom', 'variants' => $variants);
                $this->usable_fonts[$font_obj->family] = $font_obj;
                $this->custom_fonts[$font_obj->family] = $font_obj;

            }else{
                $i = 0;
                foreach ($this->available_fonts as $available_font) {
                    if($available_font->family == $usable_font->name){
                        $this->usable_fonts[$available_font->family] = $available_font;

                        // Remove the found font from avaiable since it is already used.
                        array_splice($this->available_fonts, $i, 1);
                        break;
                    }

                    $i++;
                }
            }
        }
    }

    private function load_custom_elements(){
        $this->custom_elements = FontsDatabaseHelper::get_custom_elements();
    }

    private function get_early_access_fonts_array(){
        return array(
        (object) array( 'family' => 'Open Sans Hebrew', 'kind' => 'earlyaccess', 'variants' => array('regular'), 'files' => (object) array('regular' => array('//fonts.googleapis.com/earlyaccess/opensanshebrew.css'))),
        (object) array( 'family' => 'Open Sans Hebrew Condensed', 'kind' => 'earlyaccess', 'variants' => array('regular'), 'files' => (object) array('regular' => array('//fonts.googleapis.com/earlyaccess/opensanshebrewcondensed.css'))),
        (object) array( 'family' => 'Noto Sans Hebrew', 'kind' => 'earlyaccess', 'variants' => array('regular'), 'files' => (object) array('regular' => array('//fonts.googleapis.com/earlyaccess/notosanshebrew.css'))),
            );
    }
}
