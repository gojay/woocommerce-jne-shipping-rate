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
							<?php echo ( count($this->jne_settings['provinces']) ) ? 'checked' : '' ; ?>/> <?php _e( 'Select all', 'woocommerce' ) ?>
						</label>
					</p>
					
					<div class="jne-setting-provinces">
					<?php 
					$i = 1; 
					$provinsi = $jne->getProvinces();
					foreach( $provinsi as $prov ) : 
						if( $allowed = $this->jne_settings['provinces'] )
							$checked = in_array( $prov['value'], $allowed ) ? 'checked' : '' ;
						else
							$checked = 'checked';
						?>
						<label>
							<input type="checkbox" name="jne_provinsi[]" value="<?php echo $prov['value'] ?>" class="cb-provinsi"
							<?php echo $checked;  ?> /> <?php echo $prov['text'] ?> 
						</label>
						<?php if( ($i % 10) == 0 ) : ?>
						</div>
						<div class="jne-setting-provinces">
						<?php endif; ?>
					<?php $i++; endforeach; ?>
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
					<input name="jne_weight" type="text" id="jne_display" class="small-text" value="<?php echo $this->jne_settings['weight'] ?>" placeholder="1.00"> 
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
		if ('save' == $_POST['action']) 
		{
			$provinces = $_POST['jne_provinsi'];
			$settings = array(
				'display' => $_POST['jne_display'],
				'provinces' => ( count($provinces) == count($provinsi) ) ? array() : $provinces,
				'weight' => $_POST['jne_weight']
			);
			
			update_option( $this->jne_shipping_rate_option, $settings);	
		} 
	}
	
	public function is_available( $package ) 
	{
		global $woocommerce, $current_user;
		
		//debug($_POST,'_POST');
		
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
			// update shipping method	
			if( $_POST['action'] == 'woocommerce_update_shipping_method' ){	
				$this->_chosen_city = $_SESSION['_chosen_city'];			
			} 			
			// checkout billing / shipping
			elseif( $_POST['action'] == 'woocommerce_update_order_review' ) 				
			{  		
				if( isset($_POST['state']) )
				{			
					parse_str( $_POST['post_data'] );
					
					$chosen_state = $_POST['state'];
					if( $_POST['s_state'] ){
						$chosen_state = ( $_POST['state'] == $_POST['s_state'] ) ? $_POST['state'] : $_POST['s_state'];
					}
					
					// pertama kali
					if( !$_SESSION['_chosen_state'] )
					{
						// set session state
						$_SESSION['_chosen_state'] = $chosen_state;	 
						if( is_user_logged_in() )
						{
							$user_id = $current_user->data->ID;
							$billing_city = get_user_meta($user_id, 'billing_city', true);
							$shipping_city = get_user_meta($user_id, 'shipping_city', true);
							$this->_chosen_city = ( $shipping_city ) ? $shipping_city : $billing_city ;			
						} else {
							$this->_chosen_city = $_SESSION['_chosen_city'];
						}
					}					
							
					// 'chosen shipping city' berdasarkan shipping city
					// menggunakan billing city, jika shipping city kosong ( Ship to billing address )
					// return false, jika billing city kosong ( required )
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
		global $jne;	
		
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
	
	/*
	 * ambil nilai index kota dari post data
	 * @param String
	 * @return void
	private function get_shipping_city( $data )
	{
		// Parses the string into variables
		// http://id1.php.net/parse_str
		parse_str( $data );
		// 'chosen shipping city' berdasarkan shipping city
		// menggunakan billing city, jika shipping city kosong ( Ship to billing address )
		// return false, jika billing city kosong ( required )
		$_chosen_city = false;
		if( $billing_city )
			$_chosen_city = ( $shipping_city ) ? $shipping_city : $billing_city;
			
		return $_chosen_city;
	}
	 */
	
	/*
	 * hitung berat per item
	 * jika berat kosong / nol, ambil nilai default dari setting
	 * jumlah berat adalah pembulatan
	 * @return int
	 */
	private function calculate_weight()
	{
		global $woocommerce;
		
		if ( sizeof($woocommerce->cart->cart_contents) > 0 )
		{
			foreach( $woocommerce->cart->cart_contents as $cart_product )
			{
				$weight = ( $cart_product['data']->weight ) ? $cart_product['data']->weight : $this->jne_settings['weight'] ;
				$total_weight += $weight * $cart_product['quantity'];
			}
		}
		
		return ceil( $total_weight );
	}	
} 		