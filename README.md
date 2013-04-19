# Wordpress Plugin WooCommerce JNE Shipping Method 

## Tutorial
http://gojayincode.com/wordpress-plugin-jne-part-3-woocommerce/

## Installation
1. Install Plugin woocommerce, JNE shipping Rate (1.1) dan WordPress Importer(untuk import dummy data dari woocommerce). Import data woocommerce([woocommerce_plugin] > dummy_data.xml) : tools > import > WordPress > Choose File > dummy_data.xml

2. Edit Product Woocommerce Edit product (dummy) WordPress dengan mengganti harga dan beratnya. (ambil contoh product 1up dan Barbie Fashion)

3. Edit Setting WooCommerce general Edit setting general Woocommerce (Woocommerce > Settings > General): Ganti format currency dan allowed countries

4. Edit Setting WooCommerce shipping method Edit setting shipping options Woccommerce (Woocommerce > Settings > General > Shipping > Shipping Options) : aktifkan JNE shipping method

5. Edit Setting WooCommerce shipping JNE shipping rate 

6. Edit setting shipping method JNE Shipping Rate Woccommerce (Woocommerce > Settings > General > Shipping > JNE Shipping Rate) : setting JNE Shipping Rate

7. Edit WC_Shipping [WooCommerce]/classes/class-wc-shipping.php(line:271) : 
		
'calculate_shipping_for_package'

```php
function calculate_shipping_for_package( $package = array() ) {
	if ( ! $this->enabled ) return false;
	if ( ! $package ) return false;
	
	$package['rates'] = array();

	foreach ( $this->load_shipping_methods( $package ) as $shipping_method ) {

		if ( $shipping_method->is_available( $package ) ) {
			
			// Reset Rates
			$shipping_method->rates = array();

			// Calculate Shipping for package
			$shipping_method->calculate_shipping( $package );

			// Place rates in package array
			if ( ! empty( $shipping_method->rates ) && is_array( $shipping_method->rates ) )
				foreach ( $shipping_method->rates as $rate )
					$package['rates'][$rate->id] = $rate;
		}

	}
	return $package;
}
```
