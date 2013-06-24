<?php
include_once 'spreadsheet/OLERead.php';
include_once 'spreadsheet/reader.php';

class Parse_JNE2
{
	private $_columns;
	private $_excel;
	private $_properties = array();

	private $_data;
	private $_provinces;
	
	public function __construct( $options = array() )
	{		
		$columns = array(
			'code' 		=> 2,
			'provinsi' 	=> 3,
			'kota' 		=> 4,
			'kecamatan' => 5,
			'k_code' 	=> 6,
			'tarif' => array(
				'reg' => array(
					'harga' => 7,
					'etd' 	=> 8
				),
				'oke' => array(
					'harga' => 9,
					'etd' 	=> 10
				),
				'yes' => array(
					'harga' => 11
				)
			)
		);

		foreach ($columns as $k => $v){
			$options[$k] = array_key_exists($k, $options) ? $options[$k] : $v;
		}

		$this->_columns = $columns;
	}

	/**
	 * [__set description]
	 * @param [type] $key
	 * @param [type] $name
	 */
	public function __set( $key, $name )
	{
		$this->_properties[$key] = $name;
	}

	public function __get( $key )
	{
		return $this->_properties[$key];
	}

	public function getProperties()
	{
		return $this->_properties;
	}

	public function setFilename( $filename )
	{
		if( !isset($this->_properties['filename']) )
			$this->_properties['filename'] = $filename;
	}

	public function getFilename()
	{
		return $this->_properties['filename'];
	}

	public function getExcel()
	{
		return $this->_excel;
	}
	
	/*
	 * get cache
	 * data cache diambil dari callback dgn method sesuai parameternya	 
	 * hasil array callback dikonversi ke string dengan serialize
	 * kemudian simpan kedalam file cache .txt, sesuai nama parameternya 
	 *
	 * @param String
	 * @return Array
	 */
	private function _getCache( $action )
	{
		$cache_file = sprintf( JNE_PLUGIN_DATA_DIR . DIRECTORY_SEPARATOR . 'caches/%s_%s.cache', $action, $this->getFilename() );
		if( file_exists($cache_file) ){	
			$data = unserialize(file_get_contents($cache_file));
		}
		else {			
			$data = call_user_func( array($this, '_get'.ucwords($action)) );
			file_put_contents($cache_file, serialize($data));
		}
		
		return $data;
	}
	
	/*
	 * get cache populate
	 */
	public function populate()
	{
		$this->_data = $this->_getCache( 'populate' );
	}

	private function _getpopulate()
	{
		if( !isset($this->_properties['filename']) )
			throw new Exception('File xls not found');

		$this->_excel = new Spreadsheet_Excel_Reader();		
		$this->_excel->read( JNE_PLUGIN_DATA_DIR . DIRECTORY_SEPARATOR . $this->_properties['filename'] . '.xls' ); 

		$start = $this->start;
		$end = $this->_excel->sheets[0]['numRows'];

		$cells = $this->_excel->sheets[0]['cells'];			
		
		$data = array();
		while( $start <= $end )
		{
			// ambil data kolom
			$cols = $cells[$start];

			$data[$start] = array(
				'code' 		=>  $cols[$this->_columns['code']],
				'provinsi'  =>  $cols[$this->_columns['provinsi']],
				'kota' 		=>  $cols[$this->_columns['kota']],
				'kecamatan' =>  $cols[$this->_columns['kecamatan']],
				'k_code' 	=>  $cols[$this->_columns['k_code']],
				'tarif' => array(
					'reg' => array(
						'harga' => $cols[$this->_columns['tarif']['reg']['harga']],
						'etd' 	=> $cols[$this->_columns['tarif']['reg']['etd']],
					)
				)
			);	

			if( is_numeric($cols[$this->_columns['tarif']['oke']['harga']]) ) {
				$data[$start]['tarif']['oke'] = array(
					'harga' => $cols[$this->_columns['tarif']['oke']['harga']],
					'etd' 	=> $cols[$this->_columns['tarif']['oke']['etd']]
				);
			}	

			if( is_numeric($cols[$this->_columns['tarif']['yes']['harga']]) ) {
				$data[$start]['tarif']['yes'] = array(
					'harga' => $cols[$this->_columns['tarif']['yes']['harga']]
				);
			}

			// increase				
			$start++;
		}

		return $data;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function getProvinces()
	{
		$this->_provinces = array_unique(
			array_map(function($k){
				return JNE_normalize(array_pop(
					array_values(
						array_intersect_key($k, array_flip(array('provinsi')))
					)
				));
			}, $this->_data)
		);

		return $this->_provinces;
	}

	public function getCities( $index )
	{
		$code = $this->_data[$index]['k_code'];
		$kota = array_filter($this->_data, function($data) use($code) {
			return preg_match('/\b'. $code .'\b/i', $data['k_code']);
		});	
		
		$_kota = array();
		foreach( $kota as $k => $v )
		{
			if( !in_array_r($v['kota'], $_kota) )
			{
				$_kota[$k] = array(
					'name' 	=> $v['kota'],
					'kecamatan' => array(
						$k => array(
							'kode' 	=> $v['code'],
							'nama'  => $v['kecamatan']
						) 
					)
				);
				$index = $k;
			}	
			else {
				$_kota[$index]['kecamatan'][$k] = array(
					'kode' 	=> $v['code'],
					'nama'  => $v['kecamatan']
				);
			}
		}

		return $_kota;
	}

	public function getTax( $index )
	{
		return $this->_data[$index]['tarif'];
	}
}
