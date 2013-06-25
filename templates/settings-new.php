<form method="post" action="" id="form-jne-settings">
	<input type="hidden" name="action" value="update" />				
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="jne_display"> Jumlah baris yang ditampilkan </label><br/>
					<span class="description"> Masukkan angka jumlah baris yang akan ditampilkan pada daftar tarif JNE </span>
				</th>
				<td>
					<input name="jne_display" type="text" id="jne_display" class="small-text" value="<?php echo $jne_settings['display'] ?>"> 
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="jne_provinces"> Provinsi </label><br/>
					<span class="description"> Pilih provinsi yang diperbolehkan untuk ditampilkan </span>
				</th>
				<td>
					<p>
						<label>
							<input type="checkbox" id="select-all-provinces"
							<?php echo ( !$jne_settings['provinces'] ) ? 'checked' : '' ; ?>/> Pilih semua
						</label>
					</p>
					
					<div class="jne-setting-provinces">
					<?php 
					$i = 1; 

					echo 'NEWNEWNEWNEWNEWNEWNEWNEWNEWNEWNEW';
										
					foreach( $provinsi as $prov ) : 
						$checked = 'checked';
						if( $jne_settings['provinces'] )
						{
							$checked = in_array( $prov['key'], $jne_settings['provinces'] ) ? 'checked' : '' ;
						}
						?>
						<label>
							<input type="checkbox" name="jne_provinsi[]" 
							value="<?php echo $prov['key'] ?>" class="cb-provinsi"
							<?php echo $checked;  ?> /> <?php echo $prov['value'] ?> 
						</label>
						<?php if( ($i % 10) == 0 ) : ?>
						</div>
						<div class="jne-setting-provinces">
						<?php endif; ?>
					<?php $i++; endforeach; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<input type="hidden" name="action" value="save" />
		<input type="submit" class="button-primary" value="Save Changes" />	    
	</p>
</form>
<!-- css -->
<style>
.jne-setting-provinces { display:inline-table; width: 200px }
.jne-setting-provinces label{ display:block }
</style>
<!-- /css -->
<!-- javascript -->
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#select-all-provinces').click(function(){
			var checked = this.checked
			jQuery('input.cb-provinsi').each(function(){
				this.checked = checked;
			})
		})
	})	

	jQuery('#form-jne-settings').submit(function(){
		var form = jQuery(this).serialize(),
			cb = jQuery('input.cb-provinsi'),											
			cbChecked = cb.filter(':checked').length;
		
		if( cbChecked == 0 )
		{
			alert('Silahkan pilih provinsi. provinsi tidak boleh kosong');
			return false;
		}
	})
</script>
<!-- /javascript -->