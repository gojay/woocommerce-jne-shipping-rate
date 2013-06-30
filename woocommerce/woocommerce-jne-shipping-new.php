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
	include 'class-wc-jne-rate-new.php';
		
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
		JNE_PLUGIN_WOO_URL . '/js/woocommerce-jne-new.js', 
		array( 'jquery' )
	);
}

/*
 * Callback woocommerce_states
 */ 
function woocommerce_jne_rate_add_provinces( $states )
{
	global $jne;
	
	$provinces = JNE_sortProvinsi( $jne->getProvinces() );
	
	// get jne shipping options
	$jne_settings   = get_option( 'woocommerce_jne_rates' );					
	// filter data provinsi berdasarkan provinsi yg dipilih pada jne shipping options
	if( $allowed = $jne_settings['provinces'] )
	{
		$provinces = array_filter( $provinces, function($provinsi) use($allowed){
			return in_array( $provinsi['key'], $allowed );
		});
	}
	
	$stateID = array();
	foreach( $provinces as $provinsi )
	{
		$stateID[$provinsi['key']] = $provinsi['value'];
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
	
	// jne_rate_debug( $fields['billing'], 'original checkout fields' );
	
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
				  
			// nilai offset state field
			$offset_state = array_search($type.'_state', array_keys($fields[$type]));
			$offset_after_state  = $offset_state + 1;
							 
			/* 
			 * atur posisi fields
				country
				first name | last name
				company
				address1
				address2
				state(provinsi) | postcode / zip
				city
				email | phone
				
			* pd variable $city_field 
				comment : line 153
				uncomment : line 154
			*/
			
			$fields[$type] = array_slice($fields[$type], 0, $offset_after_state, true) + // country, first name | last name, company, address1, address2, state(provinsi) |
							 array($type.'_postcode' => $postcode_field) + array($type.'_city' => create_city_field($type) ) + // postcode / zip, city
							 array_slice($fields[$type], $offset_after_state, null, true); // email | phone
		}		
	}	
	
	return $fields;
}
function create_city_field( $type )
{
	global $current_user;
	
	// city field
	$field = array(
		'type' 			=> 'select',
		'label' 		=> 'City',
		'placeholder' 	=> 'City',
		'required' 		=> true,
		//'class' 		=> array('form-row-first', 'update_totals_on_change'),
		'class' 		=> array('form-row-wide', 'update_totals_on_change'),
		'clear' 		=> false,
		'options'		=> array(
			'' => __( 'Select an option', 'woocommerce' )
		)
	);
	
	if( is_user_logged_in() )
	{
		$user_id = $current_user->data->ID;
		$meta_key = ( $type == 'billing' ) ? 'billing_city' :  'shipping_city' ;
		$index_city =  get_user_meta($user_id, $meta_key, true);
		array_push($field['class'], $meta_key . '_' . $index_city);
	}
	
	return $field;
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
	global $jne;
	
	$city_state = get_city_state($_POST['billing_city']);
	if ( $_POST['billing_city'] ) {
		update_post_meta( $order_id, '_billing_city', esc_attr($city_state));
	}
	
	if ( isset($_POST['shipping_city']) ) {
		if( !empty($_POST['shipping_city']) ){
			$city_state = get_city_state($_POST['shipping_city']);
		}
		update_post_meta( $order_id, '_shipping_city', esc_attr($city_state));
	}
}
/*
 * Get city state
 * @params
 * 	index kota int
 * 
 * @return
 * 	Kecamatan, Kotamadya String or
 *  index kota int (if not found)
 */
function get_city_state( $index )
{
	global $jne;

	$data = $jne->getData();
	if( $city = $data[$index] ) {
		return JNE_normalize(sprintf('%s, %s', 
					trim($city['kecamatan']),
					$city['kota']
				));	
	}

	return $index;
}

/*
 * Hook Filter woocommerce_jne_custom_calculate_shipping_for_package
 * tambah package 'destination' 'city' untuk 'calculate shipping'
 * 
 * Hook filter dipanggil di [WooCommerce]/classes/class-wc-shipping.php (line:271)
 */
add_filter('woocommerce_jne_custom_calculate_shipping_for_package', 'custom_calculate_shipping_for_package', 10, 2);
function custom_calculate_shipping_for_package( $package, $post_data )
{
	global $current_user;
	
	if( $post_data )
	{
		parse_str($post_data);
		if( $billing_city ){
			$package['destination']['city'] = ( $shipping_city ) ? $shipping_city : $billing_city ;
		}
		elseif( is_user_logged_in() )
		{
			$user_id = $current_user->data->ID;
			$billing_city = get_user_meta($user_id, 'billing_city', true);
			$shipping_city = get_user_meta($user_id, 'shipping_city', true);
			$package['destination']['city'] = ( $shipping_city ) ? $shipping_city : $billing_city ;
		}		
	}
	
	return $package;
}

/*
 * Hook Filter woocommerce_my_account_my_address_formatted_address
 * ubah 'city' (nilai default index kota) menjadi nama Kecamatan, Kotamadya (dgn method 'get_city_state')
 * pada halaman woocommerce /my-account dan my-account/edit-address/
 *
 * Hook filter dipanggil pd [WooCommerce]/templates/myaccount/my-address.php (line:49)
 */
add_filter('woocommerce_my_account_my_address_formatted_address', 'woocommerce_jne_my_account_my_address_formatted_address', 10, 2);
function woocommerce_jne_my_account_my_address_formatted_address( $data, $customer_id )
{
	$data['city'] = get_city_state($data['city']);
	return $data;
}

add_filter( 'woocommerce_billing_fields', 'custom_woocommerce_billing_fields' );
function custom_woocommerce_billing_fields( $fields ) 
{	
	$fields['billing_city']	= create_city_field('billing');
	return $fields;
}
add_filter( 'woocommerce_shipping_fields', 'custom_woocommerce_shipping_fields' );
function custom_woocommerce_shipping_fields( $fields ) 
{
	$fields['shipping_city'] = create_city_field('shipping');
	return $fields;
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