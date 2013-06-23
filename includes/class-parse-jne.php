<?php
include_once 'spreadsheet/OLERead.php';
include_once 'spreadsheet/reader.php';

class Parse_JNE
{
	private $_excel;
	private $_all;
	
	private $_start;
	private $_end;
	
	public function __construct( $file )
	{
		// initialize reader object
		$this->_excel = new Spreadsheet_Excel_Reader();
		// read spreadsheet data
		$this->_excel->read( JNE_PLUGIN_DATA_DIR . '/'. $file );
		
		$this->_start = 10;
		$this->_end = $this->_excel->sheets[0]['numRows'];
		
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
		$cache_file = sprintf( JNE_PLUGIN_DATA_DIR . '/caches/%s.cache', $action );
		if( file_exists($cache_file) ){
			$data = unserialize(file_get_contents($cache_file));
		} else {	
			$data = call_user_func( array($this, '_get'.ucwords($action)) );
			file_put_contents($cache_file, serialize($data));
		}
		
		return $data;
	}
	
	/*
	 * get cache provinces
	 */
	public function getProvinces()
	{
		return array_map( 'JNE_normalize', $this->_getCache( 'provinces' ) );
	}

	/**
	 * ambil data provinsi
	 * @return Array
	 */	
	private function _getProvinces()
	{
		$cells = $this->_excel->sheets[0]['cells'];	
		
		$index = $this->_start;
		
		$data = array();
		while( $index <= $this->_end )
		{
			// ambil data kolom
			$cols = $cells[$index];
			// kolom 3 adalah nama provinsi
			if( $provinsi = $cols[3] )
			{
				$data[] = array(
					'text'  => $provinsi,
					'value' => $index
				);
			}
				
			$index++;
		}
		
		return $data;
	}
	/*
	 * get cache populate
	 */
	public function getPopulate()
	{
		return $this->_getCache( 'populate' );
	}

	/**
	 * ambil semua data secara "Nested"
	 * @return Array
	 */
	private function _getPopulate()
	{		
		$cells = $this->_excel->sheets[0]['cells'];
		$index = $this->_start;	
		
		$data = array();
		$idProvinsi = 0; 
		while( $index <= $this->_end )
		{
			$cols = $cells[$index];
			if( $provinsi = $cols[3] )
			{
				$idProvinsi = $index;
				$data[$index] = array(
					'provinsi'  => $provinsi,
					'kotamadya' => array()
				);
			} 
			else 
			{
				if( $cols[1] )
				{
					$data[$idProvinsi]['kotamadya'][$index] = array(
						'nama' => $cols[1],
						'kecamatan' => $this->_getKecamatan($index)
					);
				}
			}
			$index++;
		}
		
		return $data;
	}

	/**
	 * ambil data kecamatan
	 */
	private function _getKecamatan( $kota )
	{
		$cells = $this->_excel->sheets[0]['cells'];	
		
		// return array kosong, jika nama kota (kolom 1) pada index tersebut kosong
		if( empty($cells[$kota][1]) )
			return array();	
		
		// index kecamatan dimulai pd index kota + 1
		$index = $kota + 1;
		
		// ambil data YES
		$yes = $this->_getYES();
		
		$data = array();
		while( $index <= $this->_end )
		{
			$cols = $cells[$index];
			
			// selesai sampai menemukan kota berikutnya
			// atau terdapat isi/tidak kosong (nama kota) pada kolom 1 sesuai indexnya
			if( !empty($cols[1]) )
				break;
				
			// kolom 2 adalah nama kecamatan
			if( isset($cols[2]) )
			{			
				$kode = $cols[4];
				$kecamatan = array(
					'index' => $index,
					'kode'  => $kode,
					'nama'  => $cols[2],
					'tarif'  => array(
						'reg' => $cols[5],
						'oke' => $cols[6]
					)
				);
				
				// ambil data YES, jika kode sesuai dengan array key YES
				if( array_key_exists($kode, $yes) )
				{
					// merging recursive tarif yes
					$kecamatan = array_merge_recursive( 
									$kecamatan, 
									array( 'tarif' => array('yes' => $yes[$kode]) )
								);
				}
				
				// set kedalam array
				array_push( $data, $kecamatan );
			}	
			$index++;
		}
		
		//sorting by name
		uasort ( $data , array( &$this, 'sortByName' ) ); 
		
		return $data;
	}
	
	public function sortByName( $a, $b )
    {
        $name1 = $a['nama'];
        $name2 = $b['nama'];
        return strnatcmp( $name1 , $name2 ); 
    }
	
	/*
	 * get cache rows
	 */
	public function getRows()
	{
		return $this->_getCache( 'rows' );
	}
	
	/**
	 * ambil data untuk ditampilkan pada baris tabel
	 */
	private function _getRows()
	{
		$cells = $this->_excel->sheets[0]['cells'];	
		$index = $this->_start;	
		
		// ambil data YES
		$YES = $this->_getYES();
		
		$data = array(); $index_provinsi; $provinsi; $kotamadya;		
		while( $index <= $this->_end )
		{
			// ambil baris data
			$cols = $cells[$index];
			
			// definisikan index_provinsi, nama_provinsi, dan kotamadya
			// kolom 3 adalah nama provinsi
			if( !empty($cols[3]) )
			{
				$index_provinsi = $index;	
				$nama_provinsi	= $cols[3];	
			}
			// kolom 1 adalah kotamadya			
			if( !empty($cols[1]) )
				$kotamadya = $cols[1];			
			
			// kolom 2 adalah kecamatan
			if( !empty($cols[2]) )
			{				
				$kode = $cols[4];
				$kecamatan = array(
								'index' 	=> $index,
								'provinsi'  => array(
									'index' => $index_provinsi,
									'nama'  => $nama_provinsi
								),
								'kotamadya' => $kotamadya,
								'kecamatan' => $cols[2],
								'kode' 		=> $kode,
								'tarif'  	=> array(
									'reg' => $cols[5],
									'oke' => $cols[6]
								)
							);
				
				// ambil data YES, jika kode sesuai dengan array key YES
				if( array_key_exists($kode, $YES) )
				{
					// merging recursive tarif yes
					$kecamatan = array_merge_recursive( 
									$kecamatan, 
									array('tarif' => array('yes' => $YES[$kode])) 
								);
				}
				
				// set kedalam array
				array_push( $data, $kecamatan );
			}
			$index++;
		}
		
		return $data;
	}
	
	/**
	 * ambil data YES
	 */
	private function _getYES()
	{
		$sheets = $this->_excel->sheets[1];	
		$cells  = $sheets['cells'];
		
		// index dimulai pada baris ke 8
		$index = 8;
		$until = $sheets['numRows'];
		
		$data = array();
		while($index <= $until)
		{
			$cols  = $cells[$index];
			
			$kode  = $cols[4];
			$tarif = $cols[5];
			
			if( $kode )		
				$data[$kode] = $tarif;
			
			$index++;
		}
		
		return $data;
	}
}
