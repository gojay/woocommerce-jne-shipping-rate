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
public function calculate_shipping( $package = array() )
{				
	global $jne;	
	
	//debug($this->_chosen_city,'call calculate_shipping');
	//debug($_SESSION,'_SESSION calculate_shipping');
	
	if( $this->_chosen_city !== false )
	{
		$index_kota   = $this->_chosen_city;
		// hitung berat
		$total_weight = $this->calculate_weight();
		
		// ambil rows data
		$data = $jne->getRows();	
		// filter data berdasarkan index kota
		$filtered = array_filter($data, function($rows) use($index_kota) {
			return $rows['index'] == $index_kota;
		});
		
		if( $kota = array_pop($filtered) )
		{
			foreach( $kota['tarif'] as $layanan => $tarif )
			{				
				// hitung tarif per berat item
				$cost = $tarif * $total_weight;				
				$rate = array(
					'id'        => $this->id . '_' . $layanan,
					'label'     => sprintf('%s (%s kg x %s)',
										$this->title . ' ' . strtoupper( $layanan ),
										$total_weight,
										JNE_rupiah( $tarif )
									),
					'cost'      => $cost
				);
				$this->add_rate($rate);
			}
		}
	}
}
```
