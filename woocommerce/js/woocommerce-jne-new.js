jQuery(function($) {

	// tooltip weight
	$('.tooltip-jne-weight').tipTip({ content: $('#weight-details').html() });

	function appendCombobox(provinsi, cb, callback) {
		console.log('appendCombobox', 'provinsi', provinsi);
		$.getJSON(jne_params.ajaxurl, {
			action: 'jne-new-ajax',
			nonce: jne_params.ajaxJNENonce,
			get: 'kota',
			provinsi: provinsi,
			group: true
		}, function(jsonKota) {
			console.log('appendCombobox', 'jsonKota', jsonKota);

			cb.html('<option value="">' + woocommerce_params.i18n_select_state_text + '</option>');

			$.each(jsonKota.data, function(key, cat) {
				// create group
				var group = $('<optgroup>', {
					label: key
				});
				// option combobox kota
				$.each(cat, function(i, item) {
					$("<option/>", {
						value: item.index,
						text: item.name
					}).appendTo(group);
				});
				// add to group
				group.appendTo(cb);
			});

			callback();
		});
	}

	/* ====================================== CART ====================================== */

	// cookie
	// https://github.com/carhartl/jquery-cookie
	var chosen_shipping_city = $.cookie("chosen_shipping_city");

	$('select#calc_shipping_state').live('change', function() {
		var country = $('select.country_to_state').val(),
			provinsi = $(this).val(),
			parent_state = $(this).parents('p'),
			combobox_city = $('select#calc_shipping_city');

			if (jne_params.woocommerce.jne_is_enabled && (country != 'ID' || !provinsi)) return;

		// hilangkan class required/validasi
		var rowField = $(this).parents('.form-row');
		if (rowField.hasClass('required')) rowField.removeClass('required');

		// sudah ada element combobox city
		if (combobox_city.length) {
			// kosongkan cb city
			combobox_city.empty();
			// set loading ala woocommerce
			combobox_city
				.parent()
				.block({
				message: null,
				overlayCSS: {
					background: '#fff url(' + woocommerce_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
					opacity: 0.6
				}
			});
		}
		// buat element combobox city
		else {
			// buat element combobox
			var el = '<p class="form-row form-row-wide"><select name="calc_shipping_city" id="calc_shipping_city"></select></p>';
			// letakkan setelah parent cb state
			parent_state.after('<div class="clear"></div>' + el);
			combobox_city = $('select#calc_shipping_city');
			// set loading ala woocommerce
			combobox_city
				.parent()
				.block({
				message: null,
				overlayCSS: {
					background: '#fff url(' + woocommerce_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
					opacity: 0.6
				}
			});
		}

		appendCombobox(provinsi, combobox_city, function() {
			combobox_city.parent().unblock();
			// set selected
			var value = chosen_shipping_city;
			var s_city = jne_params.woocommerce.chosen_shipping_city;
			if (chosen_shipping_city !== s_city) {
				var date = new Date();
				date.setTime(date.getTime() + (30 * 60 * 1000));
				$.cookie("chosen_shipping_city", s_city, {
					expires: date,
					path: '/'
				});
				value = s_city;
			}
			console.log('city', value);
			if (value) combobox_city.val(value);
				// event change / aksi pilih combobox city
			combobox_city.change(function() {
				if ($(this).val() === '') return;
				// hilangkan class required/validasi jika value tidak kosong
				var rowField = $(this).parents('.form-row');
				if (rowField.hasClass('required')) rowField.removeClass('required');
			});

		});
	});

	/*
	 *
	 * cek validasi & set cookie saat submit 'Shipping Calculator'
	 *
	 * @condition :
	 * - country == ID
	 * @validation
	 * - state null
	 * - city null
	 * @return
	 * - set cookie
	 *
	 */
	$('form.shipping_calculator').submit(function(e) {
		// country
		var country = $('select#calc_shipping_country').val();
		// state
		var stateField = $('select#calc_shipping_state'),
			state = stateField.val();
		// city
		var cityField = $('select#calc_shipping_city'),
			city = cityField.val();

		// return jika 'country' bukan ID/indonesia
		if (country != 'ID') return;
		// cek validasi 'state' dan 'city'
		if (state === '' || city === '') {
			console.log('validation', state, city);
			// add class required
			if (state === '') stateField.parents('.form-row').addClass('required');
			if (city === '') cityField.parents('.form-row').addClass('required');
				// return false, atau e.preventDefault()
			return false;
		}
		// set cookie, nilai city 30 menit
		var date = new Date();
		date.setTime(date.getTime() + (30 * 60 * 1000));
		// across pages
		$.cookie("chosen_shipping_city", city, {
			expires: date,
			path: '/'
		});
	});

	if ($('select#calc_shipping_state').length) $('select#calc_shipping_state').trigger('change');

	/* ====================================== CHECKOUT ====================================== */

	// style combobox ala woocommerce dgn chosen
	if (woocommerce_params.is_checkout == 1) $('select#billing_city, select#shipping_city').chosen();

	// aksi combobox pilih state pd checkout
	$('select#billing_state, select#shipping_state').live('change', function() {
		var field = $(this).attr('id').split('_')[0],
			country = $('#' + field + '_country').val(),
			provinsi = $(this).val(),
			cbCity = $('#' + field + '_city'),
			cbParent = cbCity.parent();

		if (jne_params.woocommerce.jne_is_enabled &&
			(country != 'ID' || !provinsi))
			return;

		// kosongkan combobox
		cbCity.empty();
		// set loading ala woocommerce
		cbParent.block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + woocommerce_params.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});

		appendCombobox(provinsi, cbCity, function() {

			// console.log('billing_city', cbCity.parents('.form-row')[0].className.match(/(\d+)/))

			/*
			 * if user is logged in
			 * woocommerce checkout sebagai user
			 * woocommerce account dan acount edit billing address page 
			 * url: http://{WOOCOMMERCE}/my-account/edit-address/?address=billing
			 *
			 */
			var city;
			if (jne_params.is_logged_in) {
				// ambil index kota dari row parent (p.form-row)
				var formRow = cbCity.parents('.form-row');
				// match digit (regex)
				var match_index_city = formRow[0].className.match(/(\d+)/);
				if (match_index_city)
					city = match_index_city[0];
			} else {
				// ambil nilai kota dari cookie,
				// jika null, ambil dari session (jne_params woocommerce)
				var chosen_shipping_city = $.cookie("chosen_shipping_city");
				city = (jne_params.woocommerce.chosen_shipping_city) ? jne_params.woocommerce.chosen_shipping_city : chosen_shipping_city ;
				console.log('city', city);
			}
			// ambil index kota dari cookie
			cbCity.val(city);
			/* 
			 * check jQuery chosen plugin is loaded
			 * set update combobox list dengan chosen
			 */
			if (jQuery().chosen) cbCity.chosen().trigger("liszt:updated");

			// unblock parent/remove loading
			cbParent.unblock();
		});
	});

	if ($('select#billing_state').length) $('select#billing_state').trigger('change');
		/* 
	 * if user is logged in
	 * woocommerce checkout sebagai user
	 * lakukan trigger event change combobox shipping state
	 * woocommerce account edit shipping address
		url: http://{WOOCOMMERCE}/my-account/edit-address/?address=shipping
	 */
	if (jne_params.is_logged_in) $('select#shipping_state').trigger('change');

	/*
	 * Ship to billing address
	 *
	 * atau pengiriman ke billing address
	 * kosongkan combobox shipping state dan shipping city
	 *
	 */

	$('#shiptobilling-checkbox').change(function(e) {
		if (e.target.checked) {
			// shipping state
			$('#shipping_state').val('')
				.chosen().trigger("liszt:updated");
			// shipping city
			$('#shipping_city').val('')
				.chosen().trigger("liszt:updated");

			$('select#shipping_state').trigger('change');
		} else {
			$('select#billing_city').trigger('change');
		}
	});
});