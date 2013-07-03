# Wordpress Plugin WooCommerce JNE Shipping Method (v.2.2)

## Tutorial
http://gojayincode.com/wordpress-plugin-jne-part-3-woocommerce/

## Features:
+ WordPress 3.5 Ready
+ WooCommerce 2.0.+ Ready
+ Harga JNE Jakarta, Available
+ Perhitungan Berat Volumetrik (jika produk di memiliki dimensi)
+ Tracking JNE
+ Daftar Tarif JNE
+ Tooltip berat detail pada menu Cart (v.2.2) (update tooltip style di assets/css/style.css:100)

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

## ChangeLog (Update History):
	
1. Version 1.0.1 (Apr 16, 2013)
	+ Initial First Release
2. Version 2.0 (Apr 18, 2013)
	+ support wp.3.5.1 & woocommerce 2.0.7
3. Version 2.1 (Jun 24, 2013)
	+ NEW – Setting input field untuk Nilai Toleransi JNE
	+ NEW – Perhitungan Berat Volumetrik (See ref: http://www.jne.co.id/index.php?mib=produk.detail&id=2008081110202009 )
	+ Update – Harga JNE (April 2013) Jakarta
	+ Update – Added support for WooCommerce 2.0
	+ TWEAK – Perhitungan ulang volumetrik (class-wc-jne-rate-new.php)
	+ TWEAK – JNE Package re-build untuk Checkout (class-wc-jne-rate-new.php & woocommerce-jne-new.js)
	+ FIX – Set pemilihan combobox city, lebih diutamakan berdasarkan session, bukan cookies (javascript)
4. Version 2.2 (Jul 03, 2013)
	+ NEW - Tooltip berat detail pada menu Cart
	+ FIX - populasi kota
	+ TWEAK - perhitungan ulang toleransi

## Note
Untuk pengembangan gunakan method [jne_rate_debug](https://github.com/gojay/woocommerce-jne-shipping-rate/blob/master/jne-shipping-rate-functions.php). Kemudian aktifkan/set environtment **APPLICATION_ENV** dengan value **development**

contoh pada .htaccess wordpress
```
SetEnv APPLICATION_ENV development

# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```
