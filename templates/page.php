<?php get_header() ?>

<div id="jne" class="bootstrap">
	<div class="row">
		<table class="table table-bordered">
			<tr>
				<th class="textcenter">Cari Ongkos Kirim dari Jakarta</th>
			</tr>
			<tr>
				<td class="textcenter">
					<form id="formSearch" class="form-inline" method="get">		
						<div class="group">
							<label>Provinsi</label>
							<select id="combobox_provinsi" class="field-text"> 
								<option value=""> Semua </option>
							</select>
						</div>	
						<div class="group">
							<label>Kota <span id="loading-kota" class="hide" style="position:absolute; margin:0 10px; vertical-align: middle;">
							<img src="<?php echo JNE_PLUGIN_ASSET_URL ?>/img/ajax-spin.gif" style="vertical-align: middle;"/> loading kota </label></span>
							<select id="combobox_kota" class="field-text" name="index"> 
								<option> Pilih Kota </option>
							</select>
						</div>
					</form>					
				</td>
			</tr>
		</table>
		<div id="loading">
			<img src="<?php echo JNE_PLUGIN_ASSET_URL ?>/img/loader-bar.gif" alt="ajax-loader" class="aligncenter"/> 
		</div>
		<div id="taxResults"></div>	
	</div>
</div>

<?php get_footer() ?>
