<?php
session_start();
/*
 *
 * WC_JNE_Rate
 * 
 * JNE shipping method for woocommerce plugin
 *
 * @class 		WC_JNE_Shipping_Rate
 * @package		WooCommerce
 * @category	Shipping
 * @author		Dani Gojay
 *
 */
class WC_JNE_Rate extends WC_Shipping_Method 
{
	private $_chosen_city;

	/* tooltip content (weight details) */
	private $_show_tooltip = true;
	private $_tooltip_content;
	
	public function __construct()
	{		
		$this->id            = 'jne_shipping_rate';
		$this->method_title  = __('JNE Shipping Rate', 'woocommerce');
		
		$this->jne_shipping_rate_option = 'jne_settings';
		$this->admin_page_heading 		= __('JNE Rates', 'woocommerce');
		
		// Saving admin options
		// http://wcdocs.woothemes.com/codex/extending/settings-api/#section-3
		add_action('woocommerce_update_options_shipping_'.$this->id, array(
			&$this, 
			'process_admin_options'
		));
		// saving jne rates
		add_action('woocommerce_update_options_shipping_'.$this->id, array(
			&$this, 
			'process_jne_shipping_rate'
		));

		$this->init();
	}	
	
	public function init()
	{				
		// Load the form fields.
		$this->init_form_fields();
 
		// Load the settings.
		// http://wcdocs.woothemes.com/codex/extending/settings-api/#section-4
		$this->init_settings();

		// Define user set variables
		$this->enabled		  = $this->settings['enabled'];
		$this->title 		  = $this->settings['title'];
		$this->availability   = $this->settings['availability'];
		$this->countries 	  = $this->settings['countries'];
		
		$this->jne_settings   = get_option( $this->jne_shipping_rate_option );
	}
	
	public function init_form_fields()
	{
		global $woocommerce;
		
		$this->form_fields = array(
			'enabled' 		=> array(
				'title'         => __( 'Enable/Disable', 'woocommerce' ), 
				'type'          => 'checkbox', 
				'label'         => __( 'Enable this shipping method', 'woocommerce' ), 
				'default'       => 'yes',
			), 
			'title' 		=> array(
				'title'         => __( 'Method Title', 'woocommerce' ), 
				'type'          => 'text', 
				'description'   => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ), 
				'default'       => __( 'JNE Rate', 'woocommerce' ),
			),
			'availability' 	=> array(
				'title' 	    => __( 'Method availability', 'woocommerce' ),
				'type' 	    	=> 'select',
				'default' 	    => 'all',
				'class'	    	=> 'availability',
				'options'	    => array(
									'all' 	    => __('All allowed countries', 'woocommerce'),
									'specific' 	=> __('Specific Countries', 'woocommerce')
								)
			),
			'countries' 	=> array(
				'title' 	    => __( 'Specific Countries', 'woocommerce' ),
				'type' 	    	=> 'multiselect',
				'class'	    	=> 'chosen_select',
				'css'	    	=> 'width: 450px;',
				'default' 	    => '',
				'options'	    => $woocommerce->countries->countries
			)
		);
	}
	
	public function admin_options() 
	{
		global $woocommerce, $jne;

		$provinsi = $jne->getProvinces();
		?>
		<h3><?php echo $this->admin_page_heading; ?></h3>
		<p><?php echo $this->admin_page_description; ?></p>
		<table class="form-table">
		<!-- Generate the HTML For the settings form. -->
		<?php $this->generate_settings_html(); ?>
			<!-- jumlah baris -->
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="jne_display"> <?php _e( 'Number of rows', 'woocommerce' ) ?> </label><br/>
					<span class="description"> <?php _e( 'Masukkan jumlah baris yang ditampilkan pada daftar tarif JNE', 'woocommerce' ) ?> </span>
				</th>
				<td>
					<input name="jne_display" type="number" id="jne_display" class="small-text" value="<?php echo $this->jne_settings['display'] ?>"> 
				</td>
			</tr>
			<!-- provinsi -->
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="jne_provinces"> <?php _e( 'Select Provinces', 'woocommerce' ) ?> </label><br/>
					<span class="description"> <?php _e( 'Pilih provinsi yang diperbolehkan untuk pengiriman JNE', 'woocommerce' ) ?> </span>
				</th>
				<td>
					<p>
						<label>
							<input type="checkbox" id="select-all-provinces"
							<?php echo ( count($this->jne_settings['provinces']) == 0 ) ? 'checked' : '' ; ?>/> <?php _e( 'Select all', 'woocommerce' ) ?>
						</label>
					</p>
					
					<div class="jne-setting-provinces">
					<?php 
					$i = 1; 
					foreach( $provinsi as $index => $prov ) : 
						$checked = ( $allowed = $this->jne_settings['provinces'] ) 
										? (in_array( $index, $allowed ) ? 'checked' : '')
										: 'checked' ;
						?>
						<label>
							<input type="checkbox" name="jne_provinsi[]" value="<?php echo $index ?>" class="cb-provinsi"
							<?php echo $checked;  ?> /> <?php echo $prov ?> 
						</label>
						<?php if( ($i % 10) == 0 ) : ?>
						</div>
						<div class="jne-setting-provinces">
						<?php 
						endif; 
						$i++; 
					endforeach; ?>
					</div>
				</td>
			</tr>
			<!-- berat -->
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="jne_weight"> <?php _e( 'Default Weight (kg)', 'woocommerce' ) ?> </label><br/>
					<span class="description"> <?php _e( 'Masukkan berat \'default\' (dalam desimal) jika  berat produk adalah kosong atau nol', 'woocommerce' ) ?> </span>
				</th>
				<td>
					<input name="jne_weight" type="text" class="small-text" value="<?php echo $this->jne_settings['weight'] ?>" placeholder="1.00"> 
				</td>
			</tr>
			<!-- toleransi -->
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="jne_tolerance"> <?php _e( 'Tolerance', 'woocommerce' ) ?> </label><br/>
					<span class="description"> <?php _e( 'Masukkan nilai toleransi JNE', 'woocommerce' ) ?> </span>
				</th>
				<td>
					<input name="jne_tolerance" type="text" class="small-text" value="<?php echo $this->jne_settings['tolerance'] ?>" placeholder="0.00"> 
				</td>
			</tr>
		</table><!--/.form-table-->
		<input type="hidden" name="action" value="save" />
		<!-- css -->
		<style>
		.jne-setting-provinces { display:inline-table; width: 200px }
		.jne-setting-provinces label{ display:block }
		</style>
		<!-- /css -->
		<!-- javascript -->
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#select-all-provinces').click(function(){
					var checked = this.checked
					jQuery('input.cb-provinsi').each(function(){
						this.checked = checked;
					})
				})
			})	

			jQuery('#form-jne-settings').submit(function(){
				var form = jQuery(this).serialize(),
					cb = jQuery('input.cb-provinsi'),											
					cbChecked = cb.filter(':checked').length;
				
				if( cbChecked == 0 )
				{
					alert('Silahkan pilih provinsi. provinsi tidak boleh kosong');
					return false;
				}
			})
		</script>
		<!-- /javascript -->
		<?php
	}
	
	public function process_jne_shipping_rate()
	{
		global $jne;

		if ('save' == $_POST['action']) 
		{
			$provinsi  = $jne->getProvinces();
			$provinces = $_POST['jne_provinsi'];
			$settings = array(
				'display' => $_POST['jne_display'],
				'provinces' => ( count($provinces) == count($provinsi) ) ? array() : $provinces,
				'weight' => $_POST['jne_weight'],
				'tolerance' => $_POST['jne_tolerance']
			);
			
			update_option( $this->jne_shipping_rate_option, $settings);	
		} 
	}
	
	public function is_available( $package ) 
	{
		global $woocommerce, $current_user, $jne;

		if ( $this->enabled == "no" || 
			 $this->availability != 'specific' || 
			 !in_array( $package['destination']['country'], $this->countries) 
			) 
			return false;			
				
		// cart 'calculate_shipping'
		if( isset($_POST['calc_shipping_city']) ) 
		{	
			$this->_chosen_city = $_POST['calc_shipping_city'];	
			$_SESSION['_chosen_city'] = $this->_chosen_city;					
		} 
		// post action
		elseif( isset($_POST['action']) )
		{
			// tidak tampil d checkout
			$this->_show_tooltip = false;

			// update shipping method	
			if( $_POST['action'] == 'woocommerce_update_shipping_method' ){	
				$this->_chosen_city = $_SESSION['_chosen_city'];			
			} 			
			// checkout billing / shipping
			elseif( $_POST['action'] == 'woocommerce_update_order_review' ) 				
			{  		
				if( isset($_POST['state']) )
				{			
					/* 
					 * parse string post data kedalam variable (ajax woocommerce)
					 * contoh :
					 * post_data => billing_country=ID&billing_first_name=Jhon&billing_last_name=Doe&....
					 * $billing_country = ID
					 * $billing_first_name = Jhon
					 * $billing_last_name = Doe
					 */
					parse_str( $_POST['post_data'] );

					/**
					 * aksi pilih/update kota pd checkout
					 *
					 * menggunakan billing city, jika shipping city kosong ( Ship to billing address )
					 *
					 * return false, jika billing city kosong ( required ), 
					 * artinya tidak ada paket layanan JNE yg tersedia
					 *
					 */
					if( $billing_city )
					{
						if( $_SESSION['_chosen_state'] != $chosen_state ) {							
							$_SESSION['_chosen_state'] = $chosen_state;
							$this->_chosen_city = false;
						} else {
							$this->_chosen_city = ($shipping_city) ? $shipping_city : $billing_city;
							$_SESSION['_chosen_city'] = $this->_chosen_city;
						}
					} 
					else {

						/**
						 * tidak ada aksi pemilihan kota
						 *  
						 * 1. perpindahan cart -> checkout
						 * 2. menuju halaman checkout 
						 * 3. refresh halaman checkout
						 */

						/* 
						 * define provinsi
						 * jika shipping state tidak null,
						 * provinsi adalah shipping state
						 */
						$chosen_state = ( $_POST['s_state'] ) ? $_POST['state'] : $_POST['s_state'];
					
						/* 
						 * untuk pertama kalinya, set kedalanm SESSION 
						 * set provinsi
						 * set kota
						 */
						if( !$_SESSION['_chosen_state'] )
						{
							// set session state
							$_SESSION['_chosen_state'] = $chosen_state;
							// jika user adalah member, set pilihan kota dari account details
							if( is_user_logged_in() )
							{
								$user_id = $current_user->data->ID;
								$billing_city = get_user_meta($user_id, 'billing_city', true);
								$shipping_city = get_user_meta($user_id, 'shipping_city', true);
								$this->_chosen_city = ( $shipping_city ) ? $shipping_city : $billing_city ;	
							} else {
								// jika tamu, set pilihan kota dari session
								$this->_chosen_city = $_SESSION['_chosen_city'];
							}
						}	
						else {
							$this->_chosen_city = $chosen_state;
						}
					}						
				}	
			} 
		}	
		// update_cart
		else {		
			if( isset($_SESSION['_chosen_city']) ) {
				$this->_chosen_city = $_SESSION['_chosen_city'];
			}
		}

		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', true );
	} 
	
	public function calculate_shipping( $package = array() )
	{				
		global $woocommerce, $jne;	
		
		if( $this->_chosen_city !== false && (sizeof($woocommerce->cart->cart_contents) > 0) )
		{
			$index_kota = $this->_chosen_city;
			$carts = $woocommerce->cart->cart_contents;
			
			// hitung berat total
			$total_weight = $this->_calculate_weight( $carts );
			
			if( $taxes = $jne->getTax( $index_kota ) )
			{
				foreach( $taxes as $layanan => $tarif )
				{				
					// hitung tarif per berat item
					$harga = $tarif['harga'];
					$cost  = $harga * $total_weight;
					// daftarkan tarif
					$rate  = array(
						'id'    => $this->id . '_' . $layanan,
						'label' => sprintf('%s (<a href="#shipping_method" class="btn btn-success tooltip-jne-weight" rel="popover" data-content="It\'s so simple to create a tooltop for my website!" data-original-title="Twitter Bootstrap Popover">%s kg</a> x %s)',
										$this->title . ' ' . strtoupper( $layanan ),
										$total_weight,
										JNE_rupiah( $harga )
									),
						'cost'  => $cost
					);
					$this->add_rate($rate);
				}
				// print tooltip content
				if( $this->_show_tooltip ){
					echo '<div id="weight-details" style="display:none;"><table class="jne-weight-content">'. $this->_tooltip_content .'</table></div>';
				}
			}
		}
	}
	
	/*
	 * hitung berat per item
	 * jika memiliki dimensi, nilai berat adalah perhitungan volumetrik [(LxWxH / 6000) * berat]. 
	 	Apabila hitungan volumetrik lebih berat dari berat aktual, maka biaya kirim dihitung berdasarkan berat volumetrik.
	 	lihat di http://www.jne.co.id/index.php?mib=produk.detail&id=2008081110202009
	 * jika berat kosong / nol, ambil nilai default dari setting
	 * jumlah berat adalah pembulatan dgn toleransi
	 * @return int
	 */
	private function _calculate_weight( $carts )
	{
		$total_weight = 0;

		foreach( $carts as $cart_product )
		{
			$product = $cart_product['data'];
			// jika berat kosong (null), berat default diambil dari nilai berat setting JNE
			$weight = ( $product->weight ) ? $product->weight : $this->jne_settings['weight'] ;
			// detai content 
			$content = '<tr class="row-jne-weight-data"><td class="col-1">'. $product->post->post_title;
			// memiliki volume
			if( $product->length && $product->width && $product->height ) {
				// hitung volume
				$volume = $product->length * $product->width * $product->height;
				// hitung volumetrik
				$volumetik = ($volume / 6000) * $weight;
				// Apabila hitungan volumetrik lebih berat dari berat aktual, maka biaya kirim dihitung berdasarkan berat volumetrik.
				$weight = ($volumetik > $weight) ? $volumetik : $weight ;
				// detai content 
				$content .= sprintf(' (%sx%sx%s)', $product->length, $product->width, $product->height);
			} 
			// detai content 
			$content .= '</td><td class="col-2">:</td><td class="col-3">'. $this->_floor_dec($weight).' kg</td></tr>';

			// hitung berat per kuantitas
			$weight = $weight * $cart_product['quantity'];
			// increase
			$weights += $weight;

			// set detai content 
			$this->_tooltip_content .= $content;
		}
		
		// prehitungan toleransi
		if($weights > 1) {
			$tolerance = $this->jne_settings['tolerance'];
			$_weights = $this->_floor_dec($weights, 2);
			$intval = intval($weights);
			$diff = $_weights - $intval;
			$total_weight = $diff > $tolerance ? ceil($weights) : $intval;

			/* uncomment this for debugging
			jne_rate_debug(array(
				'weights' => array(
					'default' => $weights,
					'precision' => $_weights
				),
				'fraction' => array(
					'up' => ceil($weights),
					'down' => $intval
				),
				'diff' => array(
					'default' => $weights - $intval,
					'precision' => $diff
				),
				'tolerance' => $tolerance,
				'total_weight' => $total_weight
			));
			*/
		
			$this->_tooltip_content .= '<tr class="row-jne-weight-tolerance"><td class="col-1 text-right">Tolerance</td><td class="col-2">:</td><td class="col-3">'.$tolerance.' kg</td></tr>';
		} 
		else $total_weight = 1;
			
		$this->_tooltip_content .= '<tr class="row-jne-weight-total"><td class="col-1 text-right">Total</td><td class="col-2">:</td><td class="col-3">'.$total_weight.' kg</td></tr>';

		return $total_weight;
	}	

	/**
	 * Floor decimal numbers with precision
	 * issue : http://floating-point-gui.de/basic/
	 * source : http://id1.php.net/manual/en/function.floor.php#108371
	 */
	private function _floor_dec($number, $precision = 1, $separator = '.')
	{
	    $numberpart=explode($separator,$number);
	    $numberpart[1]=substr_replace($numberpart[1],$separator,$precision,0);
	    if($numberpart[0]>=0)
	    {$numberpart[1]=floor($numberpart[1]);}
	    else
	    {$numberpart[1]=ceil($numberpart[1]);}

	    $ceil_number= array($numberpart[0],$numberpart[1]);
	    return implode($separator,$ceil_number);
	}
} 		