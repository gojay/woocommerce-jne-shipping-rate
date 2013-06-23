<?php get_header() ?>

<div id="jne" class="bootstrap">
	<table class="table table-bordered">
		<tr>
			<th class="text-center">Cari Ongkos Kirim dari Jakarta</th>
		</tr>
		<tr>
			<td class="text-center">
				<form id="formSearch" class="form-inline" method="get">		
					<div class="group">
						<label>Provinsi</label>
						<select id="combobox_provinsi" class="field-text"> 
							<option value=""> Semua </option>
						</select>
					</div>	
					<div class="group">
						<span id="loading-kota" class="hide" style="position:absolute; margin: 2px 0 0 40px !important">
							<img src="<?php echo JNE_PLUGIN_ASSET_URL ?>/img/ajax-spin.gif" style="vertical-align:middle; display:inline;"/> loading kota
						</span>
						<label>Kota</label>
						<select id="combobox_kota" class="field-text" name="index"> 
							<option> Pilih Kota </option>
						</select>
					</div>
				</form>					
			</td>
		</tr>
	</table>
	<div id="loading" class="text-center">
		<img src="<?php echo JNE_PLUGIN_ASSET_URL ?>/img/loader-bar.gif" alt="ajax-loader" style="display:inline;"/> 
	</div>
	<div id="taxResults"></div>
	</div>
</div>

<?php get_footer() ?>
