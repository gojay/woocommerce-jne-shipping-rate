<?php
/*
 * fungsi output debug (development)
 * @param Array/Object
 * @param String
 * @return String
 */
function jne_rate_debug( $data, $title = 'debug' )
{
	if( APPLICATION_ENV == 'production' ) return;
	echo '<h1>' . strtoupper( $title ) . '</h1>';
	echo '<pre>' . print_r( $data, 1 ) . '</pre>';
}

/*
 * Searches haystack for needle and 
 * returns an array of the key path if 
 * it is found in the (multidimensional) 
 * array, FALSE otherwise.
 *
 * @mixed array_searchRecursive ( mixed needle, 
 * array haystack [, bool strict[, array path]] )
 * 
 * @source : http://greengaloshes.cc/2007/04/recursive-multidimensional-array-search-in-php/
 */
function array_search_recursive( $needle, $haystack, $strict=false, $path=array() )
{
    if( !is_array($haystack) ) {
        return false;
    }
 
    foreach( $haystack as $key => $val ) {
        if( is_array($val) && $subPath = array_search_recursive($needle, $val, $strict, $path) ) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) ) {
            $path[] = $key;
            return $path;
        }
    }
    return false;
}

/*
 * AJAX pagination 
 * output paging, such as first, prev, numbers, next, last
 *
 * @param Array
 * @param Int
 * @return String
 */
function JNE_pagination($_paging = array(), $repeat = 5)
{
	$options = array(
		'total' => 0,
		'page'  => 0,
		'pages' => 0,
		'offset'=> 0,	
		'limit' => 0,		
	);
	
	$paging = array_replace_recursive($options, $_paging);
	
	$previous 		= $paging['offset'] - $paging['limit'];
	$next 			= $paging['offset'] + $paging['limit'];
		
	if($paging['total'] % $paging['limit'] != 0)
	{
		$last = ((intval($paging['total']/$paging['limit'])))*$paging['limit'];
	}
	else
	{
		$last = ((intval($paging['total']/$paging['limit']))-1)*$paging['limit'];
	}	
	
	$anc = "<div class='pagination pagination-centered'><ul>";
	if($previous < 0)
	{
		$anc .= "<li class='disabled'><a href='#'>First</a></li>";
		$anc .= "<li class='disabled'><a href='#'>Prev</a></li>";
	}
	else
	{
		$anc .= "<li><a href='#first' data-parameter='0'>First </a></li>";
		$anc .= "<li><a href='#prev' data-parameter='$previous'>Prev </a></li>";
	}
		
	/** Dont want the numbers just comment this block **/
	$norepeat = $repeat;
	$j = 1;
	$anch = "";
	for($i = $paging['page']; $i > 1; $i--)
	{
		$fpreviousPage = $i-1;
		$page = ceil($fpreviousPage*$paging['limit'])-$paging['limit'];
		$anch = "<li><a href='#page-$page' data-parameter='$page'>$fpreviousPage </a></li>".$anch;
		if($j == $norepeat) break;
		$j++;
	}
	$anc .= $anch;
	$anc .= "<li class='active'><a href='#'>".$paging['page']."</a></li>";
	$j = 1;
	for($i = $paging['page']; $i < $paging['pages']; $i++)
	{
		$fnextPage = $i+1;
		$page = ceil($fnextPage*$paging['limit'])-$paging['limit'];
		$anc .= "<li><a href='#page-$page' data-parameter='$page'>$fnextPage</a></li>";
		if($j == $norepeat) break;
		$j++;
	}
	/** end numbers **/
		
	if($next >= $paging['total'])
	{
		$anc .= "<li class='disabled'><a href='#'>Next</a></li>";
		$anc .= "<li class='disabled'><a href='#'>Last</a></li>";
	}
	else
	{
		$anc .= "<li><a href='#next' data-parameter='$next'>Next</a></li>";
		$anc .= "<li><a href='#last' data-parameter='$last'>Last</a></li>";
	}
	$anc .= "</ul></div>";
	
	return $anc;
}

/*
 * output format rupiah 
 *
 * @param String
 * @return String
 */
function JNE_rupiah( $amount )
{
	return 'Rp '.number_format($amount,0,'','.');
}

/*
 * normalize text,  
 * example : LOREM IPSUM -> Lorem Ipsum
 *
 * @param String/Array
 * @return String
 */
function JNE_normalize( $data )
{
	if(is_string($data))
		return ucwords( strtolower( $data ) );
		
	$data['text'] = ucwords( strtolower( $data['text'] ) );
	return $data;
}

/*
 * in array recursive
 * http://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array#answer-4128377
 * 
 * example :
 * $b = array(array("Mac", "NT"), array("Irix", "Linux"));
 * echo in_array_r("Irix", $b) ? 'found' : 'not found';
 */
function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

/*
 * urutan provinsi dari barat
 */
$orderProvinsi = array(
	'Aceh', 
	'Sumatera Utara', 
	'Bengkulu',
	'Jambi',
	'Riau',
	'Sumatera Barat',
	'Sumatera Selatan',
	'Sumatera Utara',
	'Lampung',
	'Bangka Belitung',
	'Kepulauan Riau',
	'Banten',
	'Jakarta',
	'Jawa Barat',
	'Jawa Tengah',
	'Jawa Timur',
	'Yogyakarta',
	'Bali',
	'Kalimantan Barat',
	'Kalimantan Selatan',
	'Kalimantan Tengah',
	'Kalimantan Timur',
	'Kalimantan Utara',
	'Gorontalo',
	'Nusa Tenggara Barat',
	'Nusa Tenggara Timur',
	'Sulawesi Barat',
	'Sulawesi Selatan',
	'Sulawesi Tengah',
	'Sulawesi Tenggara',
	'Sulawesi Utara',
	'Maluku',
	'Papua Barat',
	'Papua'
);

/*
 * urut provinsi berdasar urutan provinsi
 * preg_match boundary 'provinsi' insensitive
 * 
 * @return
 * 	values array(key, value)
 */
function JNE_sortProvinsi($array) 
{
	global $orderProvinsi;

    $ordered = array();
    foreach($orderProvinsi as $key) {
    	foreach($array as $index => $val ){
	    	if(preg_match('/\b'.$key.'\b/i', $val)) {
	    		$ordered[] = array(
	    			'key' => $index,
	    			'value'  => $array[$index]
    			);
	    	}    		
    	}
    }
    return $ordered;
}

/*
 * urut data berdasar urutan provinsi
 * preg_match boundary 'provinsi' insensitive
 * 
 * @return
 * 	values array
 */
function JNE_sortAll($array) 
{
	global $orderProvinsi;

    $ordered = array();
    foreach($orderProvinsi as $key) {
    	foreach($array as $index => $val ){
	    	if(preg_match('/\b'.$key.'\b/i', $val['provinsi'])) {
	    		$ordered[] = $array[$index];
	    	}    		
    	}
    }
    return $ordered;
}