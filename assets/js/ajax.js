jQuery(function($){	
	
	$('body').bind('load-jne', function(){
		$('#loading').ajaxStart(function(){
			$(this).css('visibility','visible');
			$('#taxResults').hide();
		}).ajaxStop(function(){
			$(this).css('visibility','hidden');
			$('#taxResults').show();
		})	
		loadAll()
	})
	
	var getJSON = function( param ){
		return $.getJSON( jne_params.ajaxurl, param, null );
	}
	
	var getHTML = function( param ){
		return $.get( jne_params.ajaxurl, param, null );
	}
	
	/* load all */
	var loadAll = function(){
		/*
		 * As of jQuery 1.5, the solution is much simpler. Each of the ajax functions were changed to return a Deferred object  which manages the callbacks for a call. (The Deferred object is beyond the scope of this post, but I encourage you to read the documentation for a more thorough explanation.)
		 * http://api.jquery.com/category/deferred-object/
		 *
		 * Provides a way to execute callback functions based on one or more objects, usually Deferred objects that represent asynchronous events.
		 * http://api.jquery.com/jQuery.when/
		 */		
		$.when( 
				getJSON( { action:'JNE-ajax', nonce:jne_params.ajaxJNENonce, get:'provinsi' } ),				
				getHTML( { action:'JNE-ajax', nonce:jne_params.ajaxJNENonce, get:'index' } )				
			).done(function( data, html ){				
				var jsonProvinsi = data[0]
				/* combobox */
				if( jsonProvinsi.error )
					alert( jsonProvinsi.message )
				else {
					var combobox = $('#combobox_kota')						
					
					$.each( jsonProvinsi, function(i, item){
						$('#combobox_provinsi').append($('<option></option>')
											   .val(item.value)
											   .text(item.text));	
					})
				}	
				/* table */		
				$('#taxResults').html( html[0] );
			});
	}
	 
	// load hanya di page jne
	if( jne_params.is_jne )
	{
		$('body').trigger('load-jne')
	}
	
	/* aksi combobox provinsi */
	$('#combobox_provinsi').live('change', function(){
		var index_provinsi = $(this).find('option:selected').val(),
			combobox 	   = $('#combobox_kota')	
			
		if( index_provinsi )
		{
			combobox.empty()
			$('#loading-kota').show()
			/*
			 * Provides a way to execute callback functions based on one or more objects, usually Deferred objects that represent asynchronous events.
			 * http://api.jquery.com/jQuery.when/
			 */
			$.when( 
				getJSON( { action:'JNE-ajax', nonce:jne_params.ajaxJNENonce, get:'kota', provinsi:index_provinsi } ),				
				getHTML( { action:'JNE-ajax', nonce:jne_params.ajaxJNENonce, get:'index', index_provinsi:index_provinsi } )				
			).done(function( data, html ){
				var jsonKota = data[0]
				/* combobox */
				if( jsonKota.error )
					alert( jsonKota.message )
				else {					
					combobox.html( '<option value=""> Pilih kota </option>')
					$.each( jsonKota.data, function (key, cat) {
						var group = $('<optgroup>', { label:key });
						// option combobox kota
						$.each(cat,function(i,item) {
							$("<option/>",{ value:item.index, text:item.nama }).appendTo(group);
						});
						// add to group
						group.appendTo( combobox );
					});
					
					// hide loading
					$('#loading-kota').hide();
				}	
				/* table */		
				$('#taxResults').html( html[0] );
			});
				
		} else {
			loadAll()
		}
	});
	
	/* aksi combobox kota */
	$('#combobox_kota').live('change', function(){
		var index_kota = $(this).find('option:selected').val();
		if( index_kota )
		{
			$.get( jne_params.ajaxurl, { action:'JNE-ajax', nonce:jne_params.ajaxJNENonce, get:'index', index_kota:index_kota }, 
				function( html ){
					$('#taxResults').html( html );
				}
			);	
		}
	});
	
	/* aksi pagination */
	$('.pagination a').live('click', function(){
		var offset = $(this).data('parameter');		
		
		$.get( jne_params.ajaxurl, { action:'JNE-ajax', nonce:jne_params.ajaxJNENonce, get:'pagination', offset:offset },
			function( html ){
				$('#taxResults').html(html);
			}
		);
		
		return false;
	})
	
	/* modal popup */
	
	var img_loading,	
		input_awb = $('form.form-search').find('input[name="awb"]');
		
	/* set default html img loading */
	$('#trackingModal, #taxModal').on('hidden', function(){
		$(this).find('.modal-body').html( img_loading )
	})
	/* 
	 * Tracking Modal
	 * jangan tampilkan modal, jika input AWB == null (KOSONG) 
	 * http://stackoverflow.com/questions/11736249/killing-close-a-twitter-boostrap-modal-already-opened#answer-11742343
	 */
	$('#trackingModal').on('show', function (e) {
		var awb = input_awb.val();
		if (!awb) 
		{
			e && e.preventDefault()
			/*
			 * focus input search 
			 * show tooltip
			 */
			input_awb.each(function(){
				$(this).focus(); 		
				$(this).tooltip('show'); 
			})
		}
	}).on('shown', function (e) {
		var bodyModal = $(this).find('.modal-body')	,
				  awb = input_awb.val();
		
		// overlay wrap bootstrap
		$('body').find('.modal-backdrop').wrap('<div class="bootstrap" />')	
				  
		// ambil img loading		  
		img_loading = bodyModal.html()	
		
		/* @return html */
		$.get( jne_params.ajaxurl, { 
								action:'JNE-ajax', 
								nonce:jne_params.ajaxJNENonce, 
								get:'show_tracking_in_modal', 
								awb:awb 
			}, 
			function( html ){
				/*
				 * filtering html
				 * tambahkan class 'table' pada element table
				 * tambahkan style 'background-color' untuk tr
				 * tambahkan style 'font-weight' untuk tr children (td)
				 */
				bodyModal.html( html )
						 .find('table')
						 .addClass('table')
						 .end()
							 .find('tr.trackH')
							 .css('background-color','#ddd')
								 .children()
								 .css('font-weight','bold')
				input_awb.val('')
			}
		)
		
	});
	
	/* Tax Modal */
	$('#taxModal').on('shown', function (e) {
		var bodyModal = $(this).find('.modal-body')	
		
		// overlay wrap bootstrap
		$('body').find('.modal-backdrop').wrap('<div class="bootstrap" />')	
		
		// ambil img loading		
		img_loading = bodyModal.html()	
		
		/* @return html */
		$.get( jne_params.ajaxurl, { 
								action:'JNE-ajax', 
								nonce:jne_params.ajaxJNENonce, 
								get:'show_jne_in_modal' 
							},
			function( html ){
				bodyModal.html( html )				
				$('body').trigger('load-jne')
			}
		)
	});
})