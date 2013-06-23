<?php
/*
 *
 * JNE Shipping Rate
 *
 * @class 		JNE_Shipping_Rate
 * @package		Wordpress 
 * @category	Plugin
 * @author		Dani Gojay
 *
 */ 			

class JNE_Shipping_Rate
{
	/**
	 * define nonce ( Creates a random, one time use token ) for
	 * AJAX Handler
	 */
	const NONCE_AJAX = 'ajax-jne-nonce';
	
	public function __construct()
	{		
		// shortcode
		add_shortcode('jne', array(
			&$this,
			'display_page'
		));
		
		/*
		// admin settings
		add_action('admin_menu', array(
			&$this,
			'jne_setting_menu'
		));
		*/
		
		// widget		
		add_action('widgets_init', array(
			&$this,
			'register_widget'
		));
		
		/* 
		 * LESS implementation
		 * http://carlorizzante.com/2011/how-to-implement-less-in-wordpress-first-approach/ 
		 */
		add_action('wp_head', array(
			&$this,
			'load_less'
		));
		
		/* register scripts */
		add_action('wp_enqueue_scripts', array(
			&$this,
			'register_scripts'
		));
		
		/**
		 * setting AJAX Handler 
		 * see more documentation at http://codex.wordpress.org/AJAX_in_Plugins
		 */
		add_action('wp_ajax_nopriv_JNE-ajax', array(
			&$this,
			'ajax_handler'
		));
		add_action('wp_ajax_JNE-ajax', array(
			&$this,
			'ajax_handler'
		));
		// ajax JNE 2 (new)
		add_action('wp_ajax_nopriv_jne-new-ajax', array(
			&$this,
			'ajax_new_handler'
		));
		add_action('wp_ajax_jne-new-ajax', array(
			&$this,
			'ajax_new_handler'
		));
	}
	
	/* installation required */
	public function install()
	{
		if (!get_option('jne_settings')) 
		{
			$settings = array(
				'display' => 200,
				'provinces' => array(),
				'weight' => '1.00'
			);
			add_option('jne_settings', $settings, '', 'yes');
		}
		
		// a:2:{s:7:"display";i:200;s:9:"provinces";a:0:{}}
	}
	
	/**
	 * Callback shortcode 
	 */
	public function display_page()
	{		
		include( JNE_PLUGIN_TPL_DIR . '/page-new.php');		
	}
	
	/**
	 * Callback admin_menu
	 */
	public function jne_setting_menu()
	{
		// add sub menu in the Admin "Settings" Menu  
		$settings_page = add_options_page(
			'JNE Settings',								// Menu Name
			'JNE Settings',								// Menu Title
			'manage_options',							// Capability/Access Privilege – 9 – or 'manage_options'
														// only Administrator access 
														// see http://codex.wordpress.org/Roles_and_Capabilities
			'setting_jne',								// Menu Slug or basename(__FILE__) => authorbio
			array( &$this, 'display_setting_menu' )		// Callback
		);
		
		// add_action( 'admin_print_styles-' . $settings_page, array( $this, 'setting_admin_styles') );
	}
	
   /*
	* It will be called only on your plugin admin page, enqueue our stylesheet here
	public function setting_admin_styles() 
	{
       wp_enqueue_style( 'settingPluginStyle', JNE_PLUGIN_ASSET_URL . '/css/admin-settings.css' );
	}
	*/
	
	/**
	 * Callback add_options_page
	 */
	public function display_setting_menu()
	{
		global $jne;
		
		// get provinces
		$provinsi = $jne->getProvinces();
		
		// action save
		if ('save' == $_REQUEST['action']) 
		{
			$provinces = $_POST['jne_provinsi'];
			$settings = array(
				'display' => $_POST['jne_display'],
				'provinces' => ( count($provinces) == count($provinsi) ) ? array() : $provinces
			);
			
			update_option('jne_settings', $settings);
				
			?><p><div id="message" class="updated" >Settings saved successfully</div></p><?php		
		} 
		// get settings option
		$jne_settings = get_option('jne_settings');
		?>
		
		<div class="wrap">
			<?php echo $message ?>
			<div id="icon-options-general" class="icon32"><br/></div>
			<h2>JNE Settings</h2>
			
			<!-- settings template -->
			<?php include( JNE_PLUGIN_TPL_DIR . '/settings.php'); ?>
			
		</div>
		<?php
	}
	
	/**
	 *
	 * Callback widgets_init
	 * 
	 */
	public function register_widget()
	{
		include 'widget/widget-jne.php';
		register_widget( 'JNE_Widget' );
	}
	
	/**
	 *
	 * Callback wp_head
	 *
	 * Name spacing Twitter Bootstrap for ease of Interpolation
	 * http://thorpesystems.com/2012/07/name-spacing-twitter-bootstrap-for-ease-of-interpolation/
	 * 
	 */
	public function load_less()
	{
		if ( !is_admin() )
		{
			// Actually printing the lines we need to load LESS in the HEAD
			print "\n<!-- Loading LESS styles and js -->\n";
			print "<link rel='stylesheet/less' id='style-less-css' href='" . JNE_PLUGIN_ASSET_URL . "/less/bootstrap.less' type='text/css' media='screen, projection' />\n";
			print "<link rel='stylesheet/less' id='style-less-css' href='" . JNE_PLUGIN_ASSET_URL . "/less/responsive.less' type='text/css' media='screen, projection' />\n";
			print "<script type='text/javascript' src='" . JNE_PLUGIN_ASSET_URL . "/less/less-1.3.1.min.js'></script>\n\n";
		}
	}
	
	/**
	 * Callback wp_enqueue_scripts
	 * 
	 * register scripts ( front end )
	 */
	public function register_scripts()
	{
		global $post, $current_user;
		
		// register styles
		wp_enqueue_style('jne-css', JNE_PLUGIN_ASSET_URL . '/css/style.css');
		
		// jquery core
		wp_enqueue_script('jquery');
		
		// bootstrap modal
		wp_enqueue_script('bootstrap-modal', JNE_PLUGIN_ASSET_URL . '/js/bootstrap-modal.js', array(
			'jquery'
		));
		// bootstrap tooltip
		wp_enqueue_script('bootstrap-tooltip', JNE_PLUGIN_ASSET_URL . '/js/bootstrap-tooltip.js', array(
			'jquery'
		));
		
		// ajax
		/*wp_enqueue_script('jne-ajax', JNE_PLUGIN_ASSET_URL . '/js/ajax.js', array(
			'jquery'
		));*/
		// new
		wp_enqueue_script('jne-new-ajax', JNE_PLUGIN_ASSET_URL . '/js/ajax-new.js', array(
			'jquery'
		));
		
		/**
		 * Localizes a script, but only if script has already been added
		 * see more documentation at http://codex.wordpress.org/Function_Reference/wp_localize_script
		 */
		
		if( is_user_logged_in() )
		{
			$user_id = $current_user->data->ID;
			$jne_params['is_logged_in'] = true;
			$_SESSION['_chosen_city'] = get_user_meta($user_id, 'billing_city', true);
		}
		
		$woocommerce_jne_settings = get_option('woocommerce_jne_shipping_rate_settings');
		$jne_params =  array(
			'ajaxurl' 		=> admin_url('admin-ajax.php'),
			'ajaxJNENonce' 	=> wp_create_nonce(self::NONCE_AJAX), // create nonce for AJAX
			'is_jne' 		=> ( $post->post_content == '[jne]' ),
			'is_logged_in' 	=> is_user_logged_in(),
			'woocommerce' 	=> array(
				'jne_is_enabled' 		=> ( $woocommerce_jne_settings['enabled'] == 'yes' ),
				'chosen_shipping_city' 	=> $_SESSION['_chosen_city']
			)			
		);
		
		// wp_localize_script( 'jne-ajax', 'jne_params', $jne_params );
		wp_localize_script( 'jne-new-ajax', 'jne_params', $jne_params );
	}

	public function ajax_new_handler()
	{
		global $jne;
		
		$jne_settings = get_option('jne_settings');		
		// nonce
		$nonce = $_GET['nonce'];
		
		// if don't have nonce, set error
		if ( !wp_verify_nonce($nonce, self::NONCE_AJAX) )
			die('error');

		$get = $_GET['get'];
		switch( $get ){
			/* @return JSON */	
			case 'provinsi':
				$provinsi = JNE_sortProvinsi( $jne->getProvinsi() );
				header('content-type', 'application/json');
				echo json_encode( $provinsi );
				break;
				
			/* @return JSON */	
			case 'kota':	
				$provinsi = $_GET['provinsi'];
				if( $provinsi )
				{
					if($kota = $jne->getKota( $provinsi )){
						$data = array_map(function($d){
							return array_pop(array_intersect_key($d, array_flip(array('name'))));
						}, $kota);
						$response = array( 'data' => $data );
					} 
					else {
						$response = array( 
							'error' => true, 
							'message' => 'kota tidak ditemukan' 
						);
					}				
				}
				else
					$response = array( 
						'error' => true, 
						'message' => 'provinsi kosong' 
					);	
					
				header('content-type', 'application/json');
				echo json_encode( $response );
				break;
			
			/* @return String html */		
			case 'pagination':
			case 'index':
				$data = $jne->getData();
				
				$index_kota = $_GET['index_kota'];
				$index_provinsi = $_GET['index_provinsi'];
				
				/* filter data berdasarkan provinsi */
				if( isset($index_provinsi) )
				{
					$code = $data[$index_provinsi]['k_code'];
					$byProvinsi = array_filter($data, function($d) use($code){
						return preg_match('/\b'. $code .'\b/i', $d['k_code']);
					});

					$rows = array();
					foreach( $byProvinsi as $filter )
						$rows[] = $filter;
				}
				/* filter data berdasarkan kota */
				else if( isset($index_kota) )
				{				
					$kota = $data[$index_kota]['kota'];
					$byProvinsi = array_filter($data, function($d) use($kota){
						return preg_match('/\b'. $kota .'\b/i', $d['kota']);
					});

					$rows = array();
					foreach( $byProvinsi as $filter )
						$rows[] = $filter;
				}
				/* tampilkan semua */
				else {
					$rows = JNE_sortAll( $data );
				}
					
				include( JNE_PLUGIN_TPL_DIR . '/data-new.php' );
				break;
				
			/* @return String html */
			case 'show_tracking_in_modal':
				include 'includes/html-dom/simple_html_dom.php';
				
				$awb = $_GET['awb'];	
				
				if (!function_exists("curl_init"))
				{
					die('Aktifkan ekstensi CURL pada PHP anda...');
				}
						
				$chp = curl_init();   
				curl_setopt($chp, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
				curl_setopt($chp, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($chp, CURLOPT_RETURNTRANSFER, 1);    

				$url = "http://jne.co.id/index.php?mib=tracking.detail&awb=".$awb;
				curl_setopt($chp, CURLOPT_URL, $url);
				curl_setopt($chp, CURLOPT_REFERER, "http://www.jne.co.id/index.php");
				curl_setopt($chp, CURLOPT_URL, $url);
				curl_setopt($chp, CURLOPT_CONNECTTIMEOUT,0); 
				curl_setopt($chp, CURLOPT_TIMEOUT, 400); //timeout in seconds
				$content = curl_exec($chp);

				$html = str_get_html($content);
				echo $html->find('td.content', 2)->innertext;
						
				curl_close($chp);
				break;
				
			/* @return String html */	
			case 'show_jne_in_modal':
				include( JNE_PLUGIN_TPL_DIR . '/page-modal-new.php');
				break;
		} 
		
		exit;
	}
	
	/* 
	 * Callback AJAX Handler
	 */
	public function ajax_handler()
	{		
		global $jne;
		
		$jne_settings = get_option('jne_settings');		
		// nonce
		$nonce = $_GET['nonce'];
		
		// if don't have nonce, set error
		if ( !wp_verify_nonce($nonce, self::NONCE_AJAX) )
			die('error');

		$get = $_GET['get'];
		switch( $get ){
			/* @return JSON */	
			case 'provinsi':
				$provinsi = $jne->getProvinces();
				
				/* filter data provinsi berdasarkan provinsi2 yg dipilih pada jne settings */
				if( $displayed = $jne_settings['provinces'] )
				{
					$provinsi = array_filter( $provinsi, function($prov) use($displayed){
						return in_array( $prov['value'], $displayed );
					});
				}
		
				header('content-type', 'application/json');
				echo json_encode( $provinsi );
				break;
				
			/* @return JSON */	
			case 'kota':	
				$provinsi = $_GET['provinsi'];
				if( $provinsi )
				{
					$populate = $jne->getPopulate();		
				
					$response = array();
					if( array_key_exists($provinsi, $populate) )
					{
						$kotamadya = $populate[$provinsi]['kotamadya'];
						
						$data = array();
						foreach( $kotamadya as $kota )
						{
							$data[$kota['nama']] = $kota['kecamatan'];
						}
						
						// kirim response
						$response = array( 'data' => $data );
					} 
					else
						$response = array( 
							'error' => true, 
							'message' => 'index provinsi tidak ditemukan' 
						);
				}
				else
					$response = array( 
						'error' => true, 
						'message' => 'nilai provinsi kosong' 
					);	
					
				header('content-type', 'application/json');
				echo json_encode( $response );
				break;
			
			/* @return String html */		
			case 'pagination':
			case 'index':	
				
				$data = $jne->getRows();				
				
				$index_kota = $_GET['index_kota'];
				$index_provinsi = $_GET['index_provinsi'];
				
				/* filter data berdasarkan provinsi2 yg dipilih pada jne settings */
				if( $displayed = $jne_settings['provinces'] )
				{
					$filtered = array_filter( $data, function($rows) use($displayed){
						return in_array( $rows['provinsi']['index'], $displayed );
					});
					
					$newData = array();
					foreach( $filtered as $filter )
						$newData[] = $filter;
						
					$data = $newData;
				}
		
				/* filter data berdasarkan index provinsi */
				if( isset($index_provinsi) )
				{
					$filtered = array_filter($data, function($item) use($index_provinsi) {
						return $item['provinsi']['index'] == $index_provinsi;
					});
					
					$newData = array();
					foreach( $filtered as $filter )
						$newData[] = $filter;
						
					$data = $newData;
				}
				/* filter data berdasarkan kota */
				else if( isset($index_kota) )
				{
					$filtered = array_filter($data, function($item) use($index_kota) {
						return $item['index'] == $index_kota;
					});
					
					$newData = array();
					foreach( $filtered as $filter )
						$newData[] = $filter;
						
					$data = $newData;
				}
		
				include( JNE_PLUGIN_TPL_DIR . '/data.php');
				break;
				
			/* @return String html */
			case 'show_tracking_in_modal':
				include 'includes/html-dom/simple_html_dom.php';
				
				$awb = $_GET['awb'];	
				
				if (!function_exists("curl_init"))
				{
					die('Aktifkan ekstensi CURL pada PHP anda...');
				}
						
				$chp = curl_init();   
				curl_setopt($chp, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
				curl_setopt($chp, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($chp, CURLOPT_RETURNTRANSFER, 1);    

				$url = "http://jne.co.id/index.php?mib=tracking.detail&awb=".$awb;
				curl_setopt($chp, CURLOPT_URL, $url);
				curl_setopt($chp, CURLOPT_REFERER, "http://www.jne.co.id/index.php");
				curl_setopt($chp, CURLOPT_URL, $url);
				curl_setopt($chp, CURLOPT_CONNECTTIMEOUT,0); 
				curl_setopt($chp, CURLOPT_TIMEOUT, 400); //timeout in seconds
				$content = curl_exec($chp);

				$html = str_get_html($content);
				echo $html->find('td.content', 2)->innertext;
						
				curl_close($chp);
				break;
				
			/* @return String html */	
			case 'show_jne_in_modal':
				include( JNE_PLUGIN_TPL_DIR . '/page-modal.php');
				break;
		} 
		
		exit;
	}
	
}