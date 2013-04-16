<?php if( $paging['pages'] > 1 ) : ?>
	<?php echo $anchor ?>
<?php endif ?>

<table id="tbTax" class="table table-bordered">
	
	<tr class="well">
		<th class="textcenter">Provinsi Kotamadya/Kabupaten</th>
		<th class="textcenter">Kecamatan</th>
		<th class="textcenter">Layanan</th>
		<th class="textcenter">Tarif</th>
	</tr>
	 
	<?php echo $output ?>
	
</table>

<?php if( $paging['pages'] > 1 ) : ?>
	<?php echo $anchor ?>
<?php endif ?>