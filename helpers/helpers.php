<?php
    function fo_do_settings_section($page, $section_name){
        global $wp_settings_sections, $wp_settings_fields;

        if ( ! isset( $wp_settings_sections[$page] ) )
            return;

        foreach ( (array) $wp_settings_sections[$page] as $section_from_page ) {
            if($section_name !== $section_from_page['id'])
                continue;

            if ( $section_from_page['title'] )
                echo "<h2>{$section_from_page['title']}</h2>\n";

            if ( $section_from_page['callback'] )
                call_user_func( $section_from_page['callback'], $section_from_page );

            if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section_from_page['id']] ) )
                continue;

            echo '<table class="form-table">';
            do_settings_fields( $page, $section_from_page['id'] );
            echo '</table>';
        }
    }

    function fo_websafe_font_names($font) {
        return str_replace(' ', '+', $font->family);
    }

    function fo_print_links($fonts, $fonts_per_link = 150){
        if(empty($fonts))
            return;

        // Create list of names with no spaces.
        $font_names = array_map('fo_websafe_font_names', $fonts);

        // Prepare to load the fonts in bulks to improve performance. Cannot include all.
        for ($i=0; $i < count($font_names); $i+=$fonts_per_link) {
            $calculated_length = count($font_names) - $i > $fonts_per_link ? $fonts_per_link : count($font_names) - $i;
            $font_names_to_load = array_slice($font_names, $i, $calculated_length);
            echo "<link href='//fonts.googleapis.com/css?family=". implode("|", $font_names_to_load) . "' rel='stylesheet' type='text/css'>";
        }
    }

    function fo_upload_file($uploadedfile, $upload_dir_callback, $should_override = false){
        if ( ! function_exists( 'wp_handle_upload' ) )
            require_once( ABSPATH . 'wp-admin/includes/file.php' );

        $upload_overrides = array( 'test_form' => false );
        if($should_override){
            $upload_overrides['unique_filename_callback'] = 'fo_unique_filename_callback';
        }
        // Register our path override.
        add_filter( 'upload_dir', $upload_dir_callback );

        $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

        // Set everything back to normal.
        remove_filter( 'upload_dir', $upload_dir_callback );

        return $movefile;
    }

    function fo_unique_filename_callback($dir, $name, $ext){
        return $name.$ext;
    }

    function fo_get_font_format($url){
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        switch ($extension) {
            case 'ttf':
                return 'truetype';
            case 'otf':
                return 'opentype';
            case 'eot':
                return 'embedded-opentype';
            default:
                return $extension;
        }
    }

    function fo_get_font_url($url, $isEOT, $isSVG) {
        // In eot we fix the second src with query string.
        $url = $isEOT ? $url . "?#iefix" : $url;
        // Add svg font name to url.
        $url = $isSVG ? $url . "#" . basename($url) : $url;

        return $url;
    }

    function fo_print_source($kind){
        switch ($kind) {
            case 'webfonts#webfont':
                _e('Google', 'font-organizer');
                break;
            case 'standard':
                _e('Standard', 'font-organizer');
                break;
            case 'custom':
                _e('Custom', 'font-organizer');
                break;
            case 'earlyaccess':
                _e('Google (Early Access)', 'font-organizer');
                break;
            default:
                _e(ucfirst($kind), 'font-organizer');
                break;
        }
    }

    function fo_print_font_weight_option($weight, $selected = false){
        $style = fo_get_weight_style_value($weight);
        $font_style = $style['weight'] ? 'font-weight:' . $style['weight'] . ";" : '';
        $font_style .= $style['style'] ? 'font-style:' . $style['style'] . ";" : '';
         switch($weight){
                case "300":
                    return "<option style=\"".$font_style."\" value=\"300\"" . selected($weight, $selected, false) . ">" . __('Light', 'font-organizer') . "</option>";
                case "300italic":
                    return "<option style=\"".$font_style."\" value=\"300italic\"" . selected($weight, $selected, false) . ">" . __('Light', 'font-organizer') . " " . __('Italic', 'font-organizer'). "</option>";
                case "regular":
                    return "<option style=\"".$font_style."\" value=\"regular\"" . selected($weight, $selected, false) . ">" . __('Normal', 'font-organizer'). "</option>";
                case "italic":
                    return "<option style=\"".$font_style."\" value=\"italic\"" . selected($weight, $selected, false) . ">" . __('Normal', 'font-organizer') . " " . __('Italic', 'font-organizer'). "</option>";
                case "600":
                    return "<option style=\"".$font_style."\" value=\"600\"" . selected($weight, $selected, false) . ">" . __('Semi-Bold', 'font-organizer'). "</option>";
                case "600italic":
                    return "<option style=\"".$font_style."\" value=\"600italic\"" . selected($weight, $selected, false) . ">" . __('Semi-Bold', 'font-organizer') . " " . __('Italic', 'font-organizer'). "</option>";
                case "700":
                    return "<option style=\"".$font_style."\" value=\"700\"" . selected($weight, $selected, false) . ">" . __('Bold', 'font-organizer'). "</option>";
                case "700italic":
                    return "<option style=\"".$font_style."\" value=\"700italic\"" . selected($weight, $selected, false) . ">" . __('Bold', 'font-organizer') . " " . __('Italic', 'font-organizer'). "</option>";
                case "800":
                    return "<option style=\"".$font_style."\" value=\"800\"" . selected($weight, $selected, false) . ">" . __('Extra-Bold', 'font-organizer'). "</option>";
                case "800italic":
                    return "<option style=\"".$font_style."\" value=\"800italic\"" . selected($weight, $selected, false) . ">" . __('Extra-Bold', 'font-organizer') . " " . __('Italic', 'font-organizer'). "</option>";
                case "900":
                    return "<option style=\"".$font_style."\" value=\"900\"" . selected($weight, $selected, false) . ">" . __('Black', 'font-organizer'). "</option>";
                case "900italic":
                    return "<option style=\"".$font_style."\" value=\"900italic\"" . selected($weight, $selected, false) . ">" . __('Black', 'font-organizer') . " " . __('Italic', 'font-organizer'). "</option>";
        }

        return "";
    }

    function fo_get_font_weight($weight){
         switch($weight){
                case "300":
                    return __('Light', 'font-organizer');
                case "300italic":
                    return __('Light', 'font-organizer') . " " . __('Italic', 'font-organizer');
                case "regular":
                    return __('Normal', 'font-organizer');
                case "italic":
                    return __('Normal', 'font-organizer') . " " . __('Italic', 'font-organizer');
                case "600":
                    return __('Semi-Bold', 'font-organizer');
                case "600italic":
                    return __('Semi-Bold', 'font-organizer') . " " . __('Italic', 'font-organizer');
                case "700":
                    return __('Bold', 'font-organizer');
                case "700italic":
                    return __('Bold', 'font-organizer') . " " . __('Italic', 'font-organizer');
                case "800":
                    return __('Extra-Bold', 'font-organizer');
                case "800italic":
                    return __('Extra-Bold', 'font-organizer') . " " . __('Italic', 'font-organizer');
                case "900":
                    return __('Black', 'font-organizer');
                case "900italic":
                    return __('Black', 'font-organizer') . " " . __('Italic', 'font-organizer');
        }

        return "";
    }

    function fo_get_weight_style_value($weight){
        if(!$weight)
            return array('weight' => '', 'style' => '');

        $italic_position = strpos($weight, 'italic');
        $style = "";
        if ($italic_position !== false) {
            $style = 'italic';
            $weight = substr($weight, 0, $italic_position);
        }

        $weight = $weight == "regular" ? "normal" : $weight;

        return array('weight' => $weight, 'style' => $style);
    }

    function fo_enqueue_fonts_css($only_declarations = false){
        global $fo_css_base_url_path;
        global $fo_css_directory_path;
        global $fo_declarations_css_file_name;
        global $fo_elements_css_file_name;

        $declartions_full_file_url = $fo_css_base_url_path . '/' . $fo_declarations_css_file_name;
        if(file_exists($fo_css_directory_path . '/' . $fo_declarations_css_file_name)){
            wp_enqueue_style('fo-fonts-declaration', $declartions_full_file_url);
        }

        if($only_declarations)
            return;

        $elements_full_file_url = $fo_css_base_url_path . '/' . $fo_elements_css_file_name;
        if(file_exists($fo_css_directory_path . '/' . $fo_elements_css_file_name)){
            wp_enqueue_style('fo-fonts-elements', $elements_full_file_url);
        }
    }

    /**
     * Create or override the file given with the content.
     * Create the directory if needed and create or override the file.
     */
    function fo_try_write_file($content, $base_dir, $file_name, $failed_callback){
        if($content){

            // Make sure directory exists.
            if(!is_dir($base_dir))
                 mkdir($base_dir, 0755, true);

            $fhandler = fopen($base_dir . '/' . $file_name, "w");
            if(!$fhandler){
                add_action( 'admin_notices', $failed_callback );
                return false;
            }

            fwrite($fhandler, $content);
            fclose($fhandler);
            return true;
        }

        return false;
    }

    function fo_rearray_files($file){
        $file_ary = array();
        $file_count = count($file['name']);
        $file_key = array_keys($file);

        for($i=0;$i<$file_count;$i++)
        {
            foreach($file_key as $val)
            {
                $file_ary[$i][$val] = $file[$val][$i];
            }
        }
        return $file_ary;
    }

    function cmp_font($a, $b){
        $al = strtolower($a->family);
        $bl = strtolower($b->family);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }

    function fo_get_all_http_url($url){
        if ( is_ssl() )
            $url = str_replace( 'http://', 'https://', $url );
        else
            $url = str_replace('https://', 'http://', $url);
        return $url;
    }

    function fo_get_full_url($url){
        // Fix full url is saved in database. Create the full url
        // with the current website url.
        if (stripos($url, get_site_url()) !== false) {
            $relative_url = substr($url, strlen(get_site_url()));
        } else {
            $relative_url = $url;
        }

        $full_url = get_site_url(null, $relative_url);

        // Fix everyone saved with http or https and let the browser decide.
        $full_url = str_replace('http://',  '//', $full_url);
        $full_url = str_replace('https://', '//', $full_url);

        return $full_url;
    }

    function fo_array_sort(&$array){
        return usort($array, 'cmp_font');
    }

    function fo_get_known_fonts_array()
    {
        return array();
    }

function bk_print_material_row($material, $key) {?>
  <tr class="bk-trow ">
      <th scope="row">
        <label for="font_name" class="required"><?php _e($material, 'bk-fonts'); ?></label>
      </th>
      <td>
        <span>6 Inch:</span>
        <input type="text" id="<?php echo esc_attr($key);?>"  name="<?php echo esc_attr($key.'[]');?>" value="" class="small-text required" maxlength="5" />
      </td>
      <td>
        <span>8 Inch:</span>
        <input type="text" id="<?php echo esc_attr($key);?>"  name="<?php echo esc_attr($key.'[]');?>" value="" class="small-text required" maxlength="5" />
      </td>
      <td>
        <span>10 Inch:</span>
        <input type="text" id="<?php echo esc_attr($key);?>"  name="<?php echo esc_attr($key.'[]');?>" value="" class="small-text required" maxlength="5" />
      </td>
  </tr>
<?php }

function bk_print_material_section_info($material){
    $material_options = get_option( 'bk_material_options' , array() );
    $name1 = "bk_material_options[".$material."]";
    $value1 = isset( $material_options[$material]) ?
    esc_attr($material_options[$material]) : '';
    $checkedtrue="";
    $checkedfalse="";
    if($value1)
        $checkedtrue='checked=""';
    else
        $checkedfalse='checked=""';
    echo '<div class="bk-cpc-input">';
    echo '<input type="radio" name="'.$name1.'" value="1" class="small-text " placeholder="10" '.$checkedtrue.'/>&nbsp;Enable';
    echo '<input type="radio" name="'.$name1.'" value="0" class="small-text " placeholder="10" '.$checkedfalse.'/>&nbsp;Disable';
    /*printf(
        '<input type="radio" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name1, $value1, is_rtl() ? 'rtl' : 'ltr'
    );*/
    echo '</div>';
}
function bk_print_material_setting($material) {
  $cpc_options = get_option( 'bk_cpc_options' , array() );
  // wp_die(print_r($cpc_options));
  $option = $material;
  $name1 = "bk_cpc_options[".$material."][5inch]";
  $name2 = "bk_cpc_options[".$material."][6inch]";
  $name3 = "bk_cpc_options[".$material."][7inch]";
  $name4 = "bk_cpc_options[".$material."][8inch]";
  $name5 = "bk_cpc_options[".$material."][9inch]";
  $name6 = "bk_cpc_options[".$material."][10inch]";
  $name7 = "bk_cpc_options[".$material."][11inch]";
  $name8 = "bk_cpc_options[".$material."][12inch]";

  $value1 = isset( $cpc_options[$option]['5inch'] ) ?
  esc_attr($cpc_options[$option]['5inch']) : '';
  $value2 = isset( $cpc_options[$option]['6inch'] ) ?
  esc_attr($cpc_options[$option]['6inch']) : '';
  $value3 = isset( $cpc_options[$option]['7inch'] ) ?
  esc_attr($cpc_options[$option]['7inch']) : '';
  $value4 = isset( $cpc_options[$option]['8inch'] ) ?
  esc_attr($cpc_options[$option]['8inch']) : '';
  $value5 = isset( $cpc_options[$option]['9inch'] ) ?
  esc_attr($cpc_options[$option]['9inch']) : '';
  $value6 = isset( $cpc_options[$option]['10inch'] ) ?
  esc_attr($cpc_options[$option]['10inch']) : '';
  $value7 = isset( $cpc_options[$option]['11inch'] ) ?
  esc_attr($cpc_options[$option]['11inch']) : '';
  $value8 = isset( $cpc_options[$option]['12inch'] ) ?
  esc_attr($cpc_options[$option]['12inch']) : '';


  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>5 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name1, $value1, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>6 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name2, $value2, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>7 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name3, $value3, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>8 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name4, $value4, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>9 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name5, $value5, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>10 Inch: </span>";
    printf(
        '<input type="text"  name="%s" value="%s" class="small-text %s" placeholder="10" />', $name6, $value6, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>11 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name7, $value7, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>12 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name8, $value8, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
}

function bk_character_spacing_setting() {
  $misc_options = get_option( 'bk_misc_options' , array() );
  $name1 = "bk_misc_options[spacing][5inch]";
  $name2 = "bk_misc_options[spacing][6inch]";
  $name3 = "bk_misc_options[spacing][7inch]";
  $name4 = "bk_misc_options[spacing][8inch]";
  $name5 = "bk_misc_options[spacing][9inch]";
  $name6 = "bk_misc_options[spacing][10inch]";
  $name7 = "bk_misc_options[spacing][11inch]";
  $name8 = "bk_misc_options[spacing][12inch]";

  $value1 = isset( $misc_options['spacing']['5inch'] ) ?
  esc_attr($misc_options['spacing']['5inch']) : '';
  $value2 = isset( $misc_options['spacing']['6inch'] ) ?
  esc_attr($misc_options['spacing']['6inch']) : '';
  $value3 = isset( $misc_options['spacing']['7inch'] ) ?
  esc_attr($misc_options['spacing']['7inch']) : '';
  $value4 = isset( $misc_options['spacing']['8inch'] ) ?
  esc_attr($misc_options['spacing']['8inch']) : '';
  $value5 = isset( $misc_options['spacing']['9inch'] ) ?
  esc_attr($misc_options['spacing']['9inch']) : '';
  $value6 = isset( $misc_options['spacing']['10inch'] ) ?
  esc_attr($misc_options['spacing']['10inch']) : '';
  $value7 = isset( $misc_options['spacing']['11inch'] ) ?
  esc_attr($misc_options['spacing']['11inch']) : '';
  $value8 = isset( $misc_options['spacing']['12inch'] ) ?
  esc_attr($misc_options['spacing']['12inch']) : '';

  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>5 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name1, $value1, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>6 Inch: </span>";
    printf(
        '<input type="text" name="%s" value="%s" class="small-text %s" placeholder="10" />', $name2, $value2, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>7 Inch: </span>";
    printf(
        '<input type="text"  name="%s" value="%s" class="small-text %s" placeholder="10" />', $name3, $value3, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>8 Inch: </span>";
    printf(
        '<input type="text"  name="%s" value="%s" class="small-text %s" placeholder="10" />', $name4, $value4, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>9 Inch: </span>";
    printf(
        '<input type="text"  name="%s" value="%s" class="small-text %s" placeholder="10" />', $name5, $value5, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>10 Inch: </span>";
    printf(
        '<input type="text"  name="%s" value="%s" class="small-text %s" placeholder="10" />', $name6, $value6, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>11 Inch: </span>";
    printf(
        '<input type="text"  name="%s" value="%s" class="small-text %s" placeholder="10" />', $name7, $value7, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
  echo '<div class="bk-cpc-input">';
    echo "<span class='bk-cpc-size'>12 Inch: </span>";
    printf(
        '<input type="text"  name="%s" value="%s" class="small-text %s" placeholder="10" />', $name8, $value8, is_rtl() ? 'rtl' : 'ltr'
    );
  echo '</div>';
}
