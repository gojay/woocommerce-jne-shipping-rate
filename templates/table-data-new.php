<?php if( $paging['pages'] > 1 ) : ?>
	<?php echo $anchor ?>
<?php endif ?>

<table id="tbTax" class="table table-bordered">
	
	<tr class="well">
		<th class="text-center">Provinsi</th>
		<th class="text-center">Kota/Kabupaten</th>
		<th class="text-center">Kecamatan</th>
		<th class="text-center">Layanan</th>
		<th class="text-center">Tarif</th>
		<th class="text-center">ETD</th>
	</tr>
	 
	<?php echo $output ?>
	
</table>

<?php if( $paging['pages'] > 1 ) : ?>
	<?php echo $anchor ?>
<?php endif ?>