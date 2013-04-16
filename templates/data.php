<?php
/* parameters */
$limit   	= (int) $_GET['limit'];
$offset  	= (int) $_GET['offset'];

/* pagination options */
$paging = array(
	'offset'	=> $offset,
	'limit'		=> ( $limit ) ? $limit : $jne_settings['display'],
	'total'		=> count( $data ),
	'page'		=> 0,
	'pages'		=> 1
);	

/* 
 * buat bari table JNE (semua)
 * definisikan index awal dan akhir
 * index awal = offset
 * index akhir = index awal + limit
 */
$x = $paging['offset'];
$until = $x + $paging['limit'];

while( $x <= $until ) 
{		
	/* baris data sesuai index */
	if( $row = $data[$x] )
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
						<td rowspan="'. $count .'" class="rowcenter">' . sprintf( '%s, %s', $row['provinsi']['nama'], $row['kotamadya'] ) . '</td>
						<td rowspan="'. $count .'" class="rowcenter">' . JNE_normalize( $row['kecamatan'] ) . '</td>
						<td class="textcenter">' . strtoupper( $layanan ) . '</td>
						<td class="textcenter">' . JNE_rupiah( $tarif ) . '</td>
					</tr>';		
			} 
			else
			{
				$output .= '
					<tr>
						<td class="textcenter">' . strtoupper( $layanan ) . '</td>
						<td class="textcenter">' . JNE_rupiah( $tarif ) . '</td>
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
$anchor = JNE_pagination($paging, 5);

include('table-data.php');

