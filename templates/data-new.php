<?php
/* parameters */
$limit   	= (int) $_GET['limit'];
$offset  	= (int) $_GET['offset'];

/* pagination options */
$paging = array(
	'offset'	=> $offset,
	'limit'		=> ( $limit ) ? $limit : 100,
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

while( $x <= $until ) 
{		
	/* baris data sesuai index */
	if( $row = $rows[$x] )
	{
		$first = array_shift(array_keys($row['tarif'])); // ambil array key elemen pertama
		$count = count($row['tarif']);					 // jumlah data tarif
		foreach( $row['tarif'] as $layanan => $tarif )
		{
			/* buat output table */
			
			if( $first == $layanan )
			{
				$output .= '
					<tr>
						<td rowspan="'. $count .'" class="row-center">' . $row['provinsi'] . '</td>
						<td rowspan="'. $count .'" class="row-center">' . JNE_normalize( $row['kota'] ) . '</td>
						<td rowspan="'. $count .'" class="row-center">' . JNE_normalize( $row['kecamatan'] ) . '</td>
						<td class="text-center">' . strtoupper( $layanan ) . '</td>
						<td class="text-center">' . JNE_rupiah( $tarif['harga'] ) . '</td>
						<td class="text-center">' . $tarif['etd'] . '</td>
					</tr>';		
			} 
			else
			{
				$output .= '
					<tr>
						<td class="text-center">' . strtoupper( $layanan ) . '</td>
						<td class="text-center">' . JNE_rupiah( $tarif['harga'] ) . '</td>
						<td class="text-center">' . $tarif['etd'] . '</td>
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

