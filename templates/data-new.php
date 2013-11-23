<?php
/* parameters */
$limit   	= isset($_GET['limit'])  ? $_GET['limit']  : 100;
$offset  	= isset($_GET['offset']) ? $_GET['offset'] : 0;

/* pagination options */
$paging = array(
	'offset'	=> $offset,
	'limit'		=> $limit,
	'total'		=> count( $rows ),
	'page'		=> 0,
	'pages'		=> 1
);	

/* 
 * buat bari table JNE 
 * definisikan index awal dan akhir
 * index awal = offset
 * index akhir = index awal + limit
 */
$x = $paging['offset'];
$until = $x + $paging['limit'];

$output = '';

while( $x <= $until ) 
{		

	/* baris data sesuai index */
	if( $x > 0 && $row = $rows[$x] )
	{
		$first = array_shift(array_keys($row['tarif'])); // ambil array key elemen pertama
		$count = count($row['tarif']);					 // jumlah data tarif
		foreach( $row['tarif'] as $layanan => $tarif )
		{
			/* buat output table baris */

			$etd = (isset($tarif['etd'])) ? $tarif['etd'] : 'N/A' ;
			
			if( $first == $layanan )
			{
				$output .= '
					<tr>
						<td rowspan="'. $count .'" class="row-center">' . $row['provinsi'] . '</td>
						<td rowspan="'. $count .'" class="row-center">' . JNE_normalize( $row['kota'] ) . '</td>
						<td rowspan="'. $count .'" class="row-center">' . JNE_normalize( $row['kecamatan'] ) . '</td>
						<td class="text-center">' . strtoupper( $layanan ) . '</td>
						<td class="text-center">' . JNE_rupiah( $tarif['harga'] ) . '</td>
						<td class="text-center">' . $etd . '</td>
					</tr>';		
			} 
			else
			{
				$output .= '
					<tr>
						<td class="text-center">' . strtoupper( $layanan ) . '</td>
						<td class="text-center">' . JNE_rupiah( $tarif['harga'] ) . '</td>
						<td class="text-center">' . $etd . '</td>
					</tr>';
			}
		}
	}
	
	$x++;
}

// ajax pagination		
$paging['pages'] = ceil($paging['total'] / $paging['limit']);
$paging['page']  = intval( $paging['offset']/$paging['limit'] ) + 1;
// output pagination numbers
$anchor = JNE_pagination($paging, 10);

include('table-data-new.php');

