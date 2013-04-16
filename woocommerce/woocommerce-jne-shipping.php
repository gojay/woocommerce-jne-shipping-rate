<?php
/* 
 * Check if WooCommerce is active
 * http://wcdocs.woothemes.com/codex/extending/create-a-plugin/
 */
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) &&
   ( !class_exists('woocommerce_shipping_method'))) 
	return; 
	
add_action('plugins_loaded', 'woocommerce_jne_rate_init', 0);	
function woocommerce_jne_rate_init()
{
	// include woocommerce jne rate shipping method
	include 'class-wc-jne-rate.php';
		
	// http://wcdocs.woothemes.com/codex/extending/shipping-method-api
	add_filter( 'woocommerce_shipping_methods', 'woocommerce_jne_rate_add_method' );					
	
	// woocommerce jne scripts
	add_action( 'wp_enqueue_scripts', 'woocommerce_jne_rate_scripts' );					
			
	// http://wcdocs.woothemes.com/snippets/states-not-in-core
	add_filter( 'woocommerce_states', 'woocommerce_jne_rate_add_provinces', 10, 1 );		
			
	// http://wcdocs.woothemes.com/snippets/change-the-default-state-and-country-on-the-checkout
	// add_filter( 'default_checkout_country', 'woocommerce_jne_rate_default_checkout_country' );		
			
	// http://wcdocs.woothemes.com/snippets/add-a-custom-currency-symbol
	add_filter( 'woocommerce_currencies', 'woocommerce_jne_rate_add_rupiah_currency' );	
	add_filter( 'woocommerce_currency_symbol', 'woocommerce_jne_rate_add_rupiah_currency_symbol', 10, 2 );
	
	// Adding custom shipping and billing fields
	add_filter( 'woocommerce_checkout_fields' , 'woocommerce_jne_rate_add_checkout_fields' );
	
	// validate custom shipping and billing fields
	add_action('woocommerce_checkout_process', 'woocommerce_jne_rate_checkout_field_process');
	
	// Update the order meta with field value
	add_action('woocommerce_checkout_update_order_meta', 'woocommerce_jne_rate_checkout_field_update_order_meta');
}

/*
 * Callback woocommerce_shipping_methods
 */ 
function woocommerce_jne_rate_add_method( $methods )
{	
	$methods[] = 'WC_JNE_Rate'; 
	return $methods;
}

/*
 * Callback wp_enqueue_scripts
 */ 
function woocommerce_jne_rate_scripts()
{
	// woocommerce
	wp_enqueue_script('jquery-cookie', 
		JNE_PLUGIN_WOO_URL . '/js/jquery.cookie.js', 
		array( 'jquery' )
	);
	wp_enqueue_script('woocommerce-jne', 
		JNE_PLUGIN_WOO_URL . '/js/woocommerce-jne.js', 
		array( 'jquery' )
	);
}

/*
 * Callback woocommerce_states
 */ 
function woocommerce_jne_rate_add_provinces( $states )
{
	global $jne;
	
	$provinces = $jne->getProvinces();
	
	// get jne shipping options
	$jne_settings   = get_option( 'woocommerce_jne_rates' );					
	// filter data provinsi berdasarkan provinsi yg dipilih pada jne shipping options
	if( $allowed = $jne_settings['provinces'] )
	{
		$provinces = array_filter( $provinces, function($provinsi) use($allowed){
			return in_array( $provinsi['value'], $allowed );
		});
	}
	
	$stateID = array();
	foreach( $provinces as $provinsi )
	{
		$stateID[$provinsi['value']] = $provinsi['text'];
	}
	
	$states['ID'] = $stateID;
	
	return $states;		
}
	
/*
 * Callback default_checkout_country
 */ 	
function woocommerce_jne_rate_default_checkout_country() 
{
	return 'ID';
}

/*
 * Callback woocommerce_currencies
 */ 
function woocommerce_jne_rate_add_rupiah_currency( $currencies ) 
{
	$currencies['RP'] = __( 'Indonesian Rupiah (RP)', 'woocommerce' );
	return $currencies;
}

/*
 * Callback woocommerce_currency_symbol
 */ 
function woocommerce_jne_rate_add_rupiah_currency_symbol( $currency_symbol, $currency ) 
{
	switch( $currency ) {
		case 'RP': 
			$currency_symbol = 'Rp '; 
			break;
	}
	return $currency_symbol;
}
 
/*
 * Callback woocommerce_checkout_fields
 *
 * hapus billing n shipping city field lama
 * tambah billing n shipping city field baru dengan combobox
 * atur urutan form field :
	[ fisrt_name ][ last name ]
	[ company_name ]
	[ address_1 ]
	[ address_2 ]
	[ country ][ state ]
	[ city ][ postal ]	
	[ email ][ phone ]
 */ 
function woocommerce_jne_rate_add_checkout_fields( $fields ) 
{		
	$allowed_fields = array('billing', 'shipping');
	
	// city field
	$city_field = array(
		'type' 			=> 'select',
		'label' 		=> 'City',
		'placeholder' 	=> 'City',
		'required' 		=> true,
		'class' 		=> array('form-row-first', 'update_totals_on_change'),
		'clear' 		=> false,
		'options'		=> array(
			'' => __( 'Select an option', 'woocommerce' )
		)
	);
	
	// hapus billing n shipping city field
	unset($fields['billing']['billing_city']);
	unset($fields['shipping']['shipping_city']);
	
	foreach( $fields as $type => $field )
	{
		if( in_array($type, $allowed_fields) )
		{
			// atur class billing address
			$fields[$type][$type.'_address_1']['class'] = array('form-row-wide');
			$fields[$type][$type.'_address_2']['class'] = array('form-row-wide');
			unset($fields[$type][$type.'_address_2']['label_class']);
			
			// ubah class postcode field
			$postcode_field  = $fields[$type][$type.'_postcode'];
			$postcode_field['class'] = array('form-row-last');
			$postcode_field['clear'] = true;
				  
			// nilai offset postcode field
			$offset_postcode = array_search($type.'_postcode', array_keys($fields[$type]));
			$offset_before_postcode = $offset_postcode - 1;
			$offset_after_postcode  = $offset_postcode + 1;
			
			// nilai offset state field
			$offset_state = array_search($type.'_state', array_keys($fields[$type]));
			// atur posisi fields
			$fields[$type] = array_slice($fields[$type], 0, $offset_postcode, true) +
							 array_slice($fields[$type], $offset_after_postcode, 2, true) +
							 array($type.'_city' => $city_field) +
							 array($type.'_postcode' => $postcode_field) +
							 array_slice($fields[$type], $offset_state, null, true);
		}		
	}
	
	return $fields;
}

/*
 * Callback woocommerce_checkout_process
 */ 
function woocommerce_jne_rate_checkout_field_process()
{
	global $woocommerce;
	
	if( !$_POST['billing_city'] )
		$woocommerce->add_error( __('Please choose a billing city', 'woocommerce') );
	if( isset($_POST['shiiping_city']))
	{
		if( !$_POST['shiiping_city'] )
			$woocommerce->add_error( __('Please choose a shipping city', 'woocommerce') );
	}
}

/*
 * Callback woocommerce_checkout_update_order_meta
 */ 
function woocommerce_jne_rate_checkout_field_update_order_meta( $order_id )
{
	if ( $_POST['billing_city'] ) 
		update_post_meta( $order_id, '_billing_city', esc_attr($_POST['billing_city']));
	if ( $_POST['shipping_city'] ) 
		update_post_meta( $order_id, '_shipping_city', esc_attr($_POST['shipping_city']));
}


/*
 * edit class-wc-shipping plugin woocommerce
 * 
 * file			: woocommerce/classes/shipping/class-wc-shipping.php
 * method 		: calculate_shipping_for_package [line:242]
 * description  : manually, tidak menggunakan trasient
 * alternative  : tambah package['destination']['city'] dari $_POST['post_data']
				  untuk membuat 'package hash' trasient
 */