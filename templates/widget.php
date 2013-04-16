<div class="bootstrap widget-jne">
	<form class="form-search">
		<div class="input-append">
			<input type="text" name="awb" 
				   class="span2 search-query" placeholder="Enter JNE Airwaybill"
				   rel="tooltip" title="Please enter JNE Airwaybill number">
			<button type="submit" class="btn" data-toggle="modal" data-target="#trackingModal">Submit</button>
	  </div>
	</form>

	<!-- Modal Tacking -->
	<div id="trackingModal" class="modal large hide fade" tabindex="-1" role="dialog" aria-labelledby="trackingModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			<h3 id="trackingModalLabel" class="textcenter">Tracking JNE</h3>
		</div>
		<div class="modal-body">
			<img src="<?php echo JNE_PLUGIN_ASSET_URL . '/img/loader-bar.gif' ?>" class="aligncenter" style="border:0" />
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
		</div>
	</div>

	<?php if( $show_jne ) : ?>
		<p><a href="#taxModal" role="button" class="btn-link" data-toggle="modal">Lihat daftar tarif JNE</a></p>
		<!-- Modal TAX -->
		<div id="taxModal" class="modal large hide fade" tabindex="-1" role="dialog" aria-labelledby="taxModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
				<h3 id="taxModalLabel" class="textcenter">Daftar tarif JNE</h3>
			</div>
			<div class="modal-body">
				<img src="<?php echo JNE_PLUGIN_ASSET_URL . '/img/loader-bar.gif' ?>" class="aligncenter" style="border:0" />
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal">Close</button>
			</div>
		</div>
	<?php endif; ?>
</div>