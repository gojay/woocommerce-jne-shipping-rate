# Wordpress Plugin WooCommerce JNE Shipping Method (v.2.1)

## Tutorial
http://gojayincode.com/wordpress-plugin-jne-part-3-woocommerce/

## Changelog
1. Update perhitungan ulang volumetrik (class-wc-jne-rate-new.php)
	- sumber http://www.jne.co.id/index.php?mib=produk.detail&id=2008081110202009
	- Apabila hitungan volumetrik lebih berat dari berat aktual, maka biaya kirim dihitung berdasarkan berat volumetrik. (line:383) [tolong dicek, jika salah perhitungan :)]

2. Update Logic JNE Package untuk checkout (class-wc-jne-rate-new.php & woocommerce-jne-new.js)
	- woocommerce update order review (line:253)
	- fix set pemilihan combobox city, lebih diutamakn berdasarkan session, bukan cookies (javascript)

## Add Features
1. Update harga JNE (April 2013) Jakarta
2. Setting nilai toleransi JNE
3. Perhitungan volumetrik (jika produk di memiliki dimensi)

## Installation
1. **Install WooCommerce** 
	+ install plugin WooCommerce
	+ Create WooCommerce page
	+ Create JNE Page : pages > Add new > isi content nya adalah [jne]
		+ template : full width (optional)
		+ uncheck  : allow comments (optional)
	+ [import dummy data]
		- File dummy data : [woocommerce plugin directory] > dummy_data.xml
		- Import : tools > import > WordPress > Choose File > dummy_data.xml
		- update tiap harga produk woocommerce (rupiah) = * 10000
	
2. **Appearance**
	+ [install theme]
		- Theme mystile (theme dari woocommerce) download [disini](https://dl.dropboxusercontent.com/u/110272111/mystile.zip)
		- Install : Appearance > Themes > Install Themes (tab) > upload > Choose file (mystile) > install Now > active theme
	+ [WooCommerce Theme] : Theme Options > WooCommerce > Layout > check all
	+ [Widgets] : install widgets (optional). Jjika anda ingin menampilkan JNE tracking code sebagai widget, tambahkan kedalam Primary Widget
		- customize widgets : Appearance > Widgets 
			+ Primary : 
				- WooCommerce Price Filter
				- WooCommerce Product Categories
				- WooCommerce Recently Viewed Products
				- JNE Express Across Nation
			+ Footer 1 : 
				- WooCommerce Best Sellers
				- WooCommerce On-Sale
			+ Footer 2 : 
				- WooCommerce Top Rated Products
			+ Footer 3 : 
				- WooCommerce Recent Products
			+ Footer 4 : 
				- Woo Subscribe/Connect
			
3. **Settings** 
	+ [permalink] : Settings > Permalinks > Post name (/%postname%/)
	+ [WooCoomerce Settings] : WooCommerce > Settings
		- General
			- Base Location 	  : Indonesia - DKI Jakarta
			- Currency 		  : Indonesia Rupiah (Rp)
			- Allowed Countries   : Specific Countries > Indonesia
	

4. **install WooCommerce jne shipping rate** 
	+ [WooCoomerce Shipping] : WooCommerce > Settings > Shipping
		- Free Shipping (optional) : Disable Free Shipping (Uncheck)
		- JNE Shipping Rate :  Specific Countries > Indonesia
		- Shipping Options 
			- Shipping Methods : check shipping method JNE Rate (default)

5. **Edit WC_Shipping** 

File : [WooCommerce]/classes/class-wc-shipping.php(line:271)

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