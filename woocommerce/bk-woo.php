<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// add_action('woocommerce_before_main_content', 'bk_sign_price_calculator');
add_action('woocommerce_before_shop_loop', 'bk_sign_price_calculator');
require_once BK_ABSPATH . '/classes/class-FontsDatabaseHelper.php';

function bk_get_fonts() {
  $bk_fonts = array(
    "bkf-amazone" => "Amazone",
    "bkf-brock-script" => "Brock Script",
    "bkf-dancing-script" => "Dancing Script",
    "bkf-edwardian-script" => "Edwardian Script",
    "bkf-freestyle-script" => "Freestyle Script",
    "bkf-french-script" => "French Script",
    "bkf-great-vibes" => "Great Vibes"
  );
  return $bk_fonts;
}

function bk_get_fontkey($font_name) {
  $font_name_lower = trim(strtolower($font_name));
  $out = preg_replace('/\s+/', '-', $font_name_lower);
  return 'bkf-'.$out;
}

function bk_get_tax_percent() {
  $misc_options = get_option( 'bk_misc_options' , array() );
  return $misc_options['hst_gst_tax'];
}

function bk_get_installation_rate() {
  $misc_options = get_option( 'bk_misc_options' , array() );
  return $misc_options['installation_rate'];
}

function bk_get_delivery_rate() {
  $misc_options = get_option( 'bk_misc_options' , array() );
  return $misc_options['delivery_rate'];
}

function print_custom_fonts() {
  $fonts_available = FontsDatabaseHelper::get_usable_fonts(); ?>
  <select name="bk-fonts" id="bk-fonts" class="bk-select">
    <option data-name="bkf-amazone" selected>Select Font</option>
    <?php
      $available_fonts_arr = array();
      foreach ($fonts_available as $key => $value) {
        $fkey = bk_get_fontkey($value->name);
        $available_fonts_arr[$fkey] = trim($value->name);
      }
      asort($available_fonts_arr);
      foreach ($available_fonts_arr as $key => $value) { ?>
        <option data-name="<?php echo esc_attr($key);?>"><?php echo $value; ?></option>
      <?php }
    ?>
  </select>
  <?php
}

function print_wall_colors() {
  $wall_colors = array(
    'red-brick' => 'Red Brick',
    'beige-brick' => 'Beige Brick',
    'stone-block' => 'Stone Block',
    'stucco' => 'Stucco',
    'concrete' => 'Concrete',
    'siding' => 'Siding',
    'siding-light' => 'Siding',
    'reset' => 'Default',
  );
  foreach($wall_colors as $key => $value) {
    $classes = 'button bk-button bkw-'.esc_attr($key);
    if('reset' === $key) {
      $classes = 'button bk-button bkw-'.esc_attr($key).' active';
    }
  ?>
  <button class="<?php echo esc_attr($classes);?>" data-wcolor="<?php echo esc_attr($key);?>"><?php echo $value; ?></button>
<?php }
}

function print_materials($bk_cpc) {
  $bk_mat = get_option('bk_material_options', array());
  $bk_materials_arr = array(
    'dibond'  => array(
      'name'  => 'Dibond',
      'class' => 'bk-m-dibond',
      'price' => $bk_cpc['dibond'],
      'isvisible'=>$bk_mat['dibond'],
    ),
    'acrylic'  => array(
      'name'  => 'Acrylic +Plus',
      'class' => 'bk-m-acrylic active',
      'price' => $bk_cpc['acrylic'],
      'isvisible'=>$bk_mat['acrylic'],
    ),
    'painted-aluminium'  => array(
      'name'  => 'Painted Aluminium',
      'class' => 'bk-m-pa',
      'price' =>  $bk_cpc['painted_aluminium'],
      'isvisible'=>$bk_mat['painted_aluminium'],
    ),
    'brushed-dibond'  => array(
      'name'  => 'Brushed Dibond',
      'class' => 'bk-m-brushed bk-m-bd',
      'price' =>  $bk_cpc['brushed_dibond'],
      'isvisible'=>$bk_mat['brushed_dibond'],
    ),
    'brushed-aluminium'  => array(
      'name'  => 'Brushed Aluminium',
      'class' => 'bk-m-brushed bk-m-ba',
      'price' => $bk_cpc['brushed_aluminium'],
      'isvisible'=>$bk_mat['brushed_aluminium'],
    ),
    'brushed-stainless'  => array(
      'name'  => 'Brushed Stainless',
      'class' => 'bk-m-brushed bk-m-bs',
      'price' => $bk_cpc['brushed_stainless'],
      'isvisible'=>$bk_mat['brushed_stainless'],
    ),
    'brushed-brass'  => array(
      'name'  => 'Brushed Brass',
      'class' => 'bk-m-bbrass',
      'price' => $bk_cpc['brushed_brass'],
      'isvisible'=>$bk_mat['brushed_brass'],
    ),
    'brushed-copper'  => array(
      'name'  => 'Brushed Copper',
      'class' => 'bk-m-bcopper',
      'price' => $bk_cpc['brushed_copper'],
      'isvisible'=>$bk_mat['brushed_copper'],
    ),
    'brushed-bronze'  => array(
      'name'  => 'Brushed Bronze',
      'class' => 'bk-m-bbronze',
      'price' => $bk_cpc['brushed_bronze'],
      'isvisible'=>$bk_mat['brushed_bronze'],
    ),
  );
  foreach ($bk_materials_arr as $key => $value) {
    $eclass = esc_attr($value['class']);
    $eprice6 = esc_attr($value['price']['6inch']);
    $eprice8 = esc_attr($value['price']['8inch']);
    $eprice10 = esc_attr($value['price']['10inch']);
    $ename = esc_html($value['name']);
      if($value['isvisible']){
  ?>
    <button class="button bk-button <?php echo $eclass;?>" data-price6="<?php echo $eprice6; ?>" data-price8="<?php echo $eprice8; ?>" data-price10="<?php echo $eprice10; ?>" data-name="bkwf-<?php echo $key;?>"><?php echo $ename; ?></button>
 <?php } }
}

function bk_sign_price_calculator() {
  $bk_cpc = get_option('bk_cpc_options', array());
  $bk_misc = get_option('bk_misc_options', array());
  if(is_shop()) {
     ?>
    <div>
      <div id="bk-font-pricing">
        <div class="bk-row bk-row-center">
          <p class="bk-address-wrap">
            <input type="text" id="bk-address-field" class="bk-address-field" placeholder="Type your address here"
            name="bk-address" value="" required/>
          </p>
          <p class="bk-field bk-grow-none">
            <?php print_custom_fonts(); ?>
          </p>
          <p class="bk-shadow-field bk-grow-none">
            <label for="bk-shadow">Shadow</label>
            <input type="checkbox" id="bk-shadow" name="bk-shadow" class="bk-shadow" value=""/>
          </p>
          <div class="bk-field bk-wall-colors">
            <?php print_wall_colors(); ?>
          </div>
        </div>
        <div class="bk-row bk-font-preview-wrap bkw-reset">
          <p id="bk-font-preview" class="bk-field bk-font-preview bkf-amazone">
            Preview address here
          </p>
        </div>
        <div class="bk-row bk-name-meta">
          <p class="bk-field">
            Letter Height: <span class="bk-size-height">6</span> inches x Approximate Width <span id="bk-size-width">86.00</span> inches (based on fonts averaged character width) <br />*Actual address sign width measurement will be called out in design proof.
          </p>
          <p class="bk-field bk-characters-wrap">
            Characters <span id="bk-characters">20</span>
          </p>
        </div>
        <div class="bk-row bk-sel-wrap">
          <div class="bk-field bk-sel-order">
            <div class="bk-row bk-lh-wrap">
              <p class="bk-field bk-lh-text">
                Choose a letter height
              </p>
              <p class="bk-field bk-letter-height">
              <button class="button bk-button active" data-inch="5" id="bk-lh-5">5 inch</button>
                <button class="button bk-button " data-inch="6" id="bk-lh-6">6 inch</button>
                <button class="button bk-button " data-inch="7" id="bk-lh-7">7 inch</button>
                <button class="button bk-button" data-inch="8" id="bk-lh-8">8 inch</button>
                <button class="button bk-button " data-inch="9" id="bk-lh-9">9 inch</button>
                <button class="button bk-button" data-inch="10" id="bk-lh-10">10 inch</button>
                <button class="button bk-button " data-inch="11" id="bk-lh-11">11 inch</button>
                <button class="button bk-button " data-inch="12" id="bk-lh-12">12 inch</button>
              </p>
            </div>
            <div class="bk-row bk-material-wrap">
              <p class="bk-field bk-material-text">
                Choose 1/4 inch material to be pin mounted with spacers
              </p>
              <p class="bk-field bk-material">
              <?php print_materials($bk_cpc); ?>
              </p>
            </div>
          </div>
          <div class="bk-field bk-wall-font">
            <?php
						$pimg_options = get_option( 'bk_pimg_options' , array() );
						foreach ($pimg_options as $key => $value) {
							$img_class = 'bkwf-'.$key;
							if($key === 'acrylic') {
								$img_class = 'bkwf-'.$key.' active';
							}
							echo '<img src="'.esc_url($value).'" alt="Preview font on the wall" class="'.$img_class.'"/>';
						}
						?>
          </div>
          <div class="bk-field bk-sel-summary">
            <p class="bk-m-inline-block">Letter Height: <span class="bk-size-height bk-sel-height bk-sel-action">6</span> <span class="bk-sel-action">inch</span></p>
            <p class="bk-m-inline-block">Material: <span class="bk-sel-material bk-sel-action">Acrylic +Plus</span></p>
            <p class="bk-m-inline-block">Font: <span class="bk-sel-font bk-sel-action">Amazone</span></p>
            <div class="bk-delivery-option">
              <button class="button bk-button" id="bk-install-basic" data-install="basic">Install Yourself</button>
              <button class="button bk-button active" id="bk-install-professional" data-install="professional">Professional Installation</button>
            </div>
            <div class="bk-price-calc">
              <div class="bk-total-hst">
                <p>Total: $<span class="bk-total">400.00</span> CAD</p>
                <p id="installation-rate">Installation: $<span><?php echo bk_get_installation_rate(); ?> CAD</span></p>
                <p id="delivery-rate" class="bk-d-none">FREE SHIPPING: $<span><?php echo bk_get_delivery_rate(); ?> CAD</span></p>
                <p>HST <span id="tax-percent"><?php echo bk_get_tax_percent(); ?></span>%: $<span class="bk-tax">68.25</span> CAD</p>
              </div>
              <p class="bk-grand-total-wrap">= $<span class="bk-grand-total">593.25</span><span class="bk-grand-total-currency">CAD</span></p>
            </div>
            <div class="bk-place-order">
              <button id="bk-order" class="button bk-button">Place Order</button>
            </div>
            <p><strong><em>100% moneyback until proof approval.</em></strong></p>
            <p>After placing order, goto <a href="https://housename.ca/image-upload/">Image Upload</a> in the footer links or email your house image to <a href="mailto:sales@housename.ca">sales@housename.ca</a>. After receiving your payment and image we will mock-up your address sign, the same or next day.</p>
          </div>
        </div>
      </div>
    </div>
  <?php }
}
add_action( 'wp_ajax_bk_ajax_add_to_cart', 'bk_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_bk_ajax_add_to_cart', 'bk_ajax_add_to_cart' );

function bk_ajax_add_to_cart() {
	ob_start();
	$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
	$quantity = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
  $bk_text = $_POST['bk_text'];
  $bk_tprice = $_POST['bk_tprice'];
  $bk_size = $_POST['bk_size'];
  $bk_material = $_POST['bk_material'];
  $bk_font = $_POST['bk_font'];
  $bk_installation = $_POST['bk_installation'];
  $bk_installation_price = $_POST['bk_installation_price'];
	$bk_wall_surface = $_POST['bk_wall_surface'];

	$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
  $cart_item_data = array(
    'bk-characters' => strlen($bk_text),
    'bk-price' => $bk_tprice,
    'bk-text'=> $bk_text,
    'bk-size'=> $bk_size,
    'bk-material'=> $bk_material,
    'bk-font' => $bk_font,
    'bk-installation' => $bk_installation,
    'bk-installation-price' => $bk_installation_price,
		'bk-wall-surface' => $bk_wall_surface,
  );
  $bkaddtocart = WC()->cart->add_to_cart($product_id,$quantity,0,array(), $cart_item_data);
	if ($passed_validation && $bkaddtocart) {
		do_action( 'woocommerce_ajax_added_to_cart', $product_id );
		if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
			wc_add_to_cart_message( $product_id );
		}
		$sdata = array(
      'success' => true,
      'product_id' => $product_id,
      'redirect_url' => esc_url(get_permalink(wc_get_page_id('checkout')))
    );
    echo json_encode( $sdata );
	}
	else {
		$data = array(
  		'error' => true,
      'product_id' => $product_id,
  		'redirect_url' => esc_url(get_permalink(wc_get_page_id('cart'))),
    );
		echo json_encode( $data );
	}
	die();
}

add_action( 'woocommerce_before_calculate_totals', 'bkh_update_cart_price', 10, 1 );
function bkh_update_cart_price( $cart_object ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
    foreach ( $cart_object->get_cart() as $cart_item ) {
			$product_id = $cart_item['product_id'];
			if($product_id == 1192) {
				$id = $cart_item['data']->get_id();
	      $total_price = $cart_item['bk-price'];
	      $cart_item['data']->set_price( $total_price );
			}
    }
}

function bkh_display_cart_data( $item_data, $cart_item ) {
    if ( empty( $cart_item['bk-characters'] ) ) {
        return $item_data;
    }
		$installation = 'Professional';
		if('basic' === $cart_item['bk-installation']) {
			$installation = 'DIY Kit/Shipping';
		}
    $item_data[] = array(
        'key'     => __( 'Approx Size', 'bkh' ),
        'value'   => wc_clean( $cart_item['bk-size'] ),
        'display' => '',
    );
    $item_data[] = array(
        'key'     => __( 'Material', 'bkh' ),
        'value'   => wc_clean( $cart_item['bk-material'] ),
        'display' => '',
    );

    $item_data[] = array(
        'key'     => __( 'Font', 'bkh' ),
        'value'   => wc_clean( $cart_item['bk-font'] ),
        'display' => '',
    );

    $item_data[] = array(
        'key'     => __( 'Text', 'bkh' ),
        'value'   => wc_clean( $cart_item['bk-text'] ),
        'display' => '',
    );

    $item_data[] = array(
        'key'     => __( 'Characters', 'bkh' ),
        'value'   => wc_clean( $cart_item['bk-characters'] ),
        'display' => '',
    );

    $item_data[] = array(
        'key'     => __( 'Installation Method', 'bkh' ),
        'value'   => wc_clean( $installation ),
        'display' => '',
    );

    $item_data[] = array(
        'key'     => __( 'Installation Price', 'bkh' ),
        'value'   => wc_clean( $cart_item['bk-installation-price'] ),
        'display' => '',
    );

		$item_data[] = array(
        'key'     => __( 'Wall Surface', 'bkh' ),
        'value'   => wc_clean( $cart_item['bk-wall-surface'] ),
        'display' => '',
    );

    return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'bkh_display_cart_data', 10, 2 );

function bkh_add_cart_meta_data( $item, $cart_item_key, $values, $order ) {
    if ( empty( $values['bk-characters'] ) ) {
        return;
    }
    $item->add_meta_data( __( 'Approx Size', 'bkh' ), $values['bk-size'] );
    $item->add_meta_data( __( 'Material', 'bkh' ), $values['bk-material'] );
    $item->add_meta_data( __( 'Font', 'bkh' ), $values['bk-font'] );
    $item->add_meta_data( __( 'Text', 'bkh' ), $values['bk-text'] );
    $item->add_meta_data( __( 'Characters', 'bkh' ), $values['bk-characters'] );
		$item->add_meta_data( __( 'Wall Surface', 'bkh' ), $values['bk-wall-surface'] );
    $item->add_meta_data( __( 'Installation Method', 'bkh' ), $values['bk-installation'] );
    $item->add_meta_data( __( 'Installation Price', 'bkh' ), $values['bk-installation-price'] );
}

add_action( 'woocommerce_checkout_create_order_line_item', 'bkh_add_cart_meta_data', 10, 4 );


// add_action( 'woocommerce_review_order_before_order_total', 'bkh_add_hst_installation_charges' );
// function bkh_add_hst_installation_charges() {
//   bkh_get_installation_price();
// }

function bkh_get_installation_price() {
  $install_price = 0;
  foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product = $cart_item['data'];

		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			// print_r($cart_item['bk-installation-price']);
      if(isset($cart_item['bk-installation-price'])) {
        $install_pricep = $cart_item['bk-installation-price'];
        $install_price += floatval($install_pricep);
      }
		}
	}
  return $install_price;
}

add_action( 'woocommerce_cart_calculate_fees','bkh_custom_fees' );
function bkh_custom_fees() {

	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
		return;

	$installation = bkh_get_installation_price();
	$percentage = bk_get_tax_percent()*0.01;
	$products_ids_array = array();
	foreach( WC()->cart->get_cart() as $cart_item ){
    $products_ids_array[] = $cart_item['product_id'];
	}
	if(in_array(1192, $products_ids_array)){
		$stax = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total + $installation) * $percentage;

	  WC()->cart->add_fee( 'Installation', $installation, true, '' );
	  WC()->cart->add_fee( 'Tax', $stax, true, '' );
	} else {
		$stax = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total ) * $percentage;
	  WC()->cart->add_fee( 'Tax', $stax, true, '' );
	}
}
