/**
* 2014 DPD Polska Sp. z o.o.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* prestashop@dpd.com.pl so we can send you a copy immediately.
*
*  @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
*  @copyright 2014 DPD Polska Sp. z o.o.
*  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*  International Registered Trademark & Property of DPD Polska Sp. z o.o.
*/

$(document).ready(function(){
	if (redirect_and_open)
	{
		toggleShipmentCreationDisplay();
		window.location = dpdpoland_pdf_uri + '?printLabels&id_package_ws=' + redirect_and_open + '&printout_format=' + printout_format + '&token=' + encodeURIComponent(dpdpoland_token) +
			'&_PS_ADMIN_DIR_=' + encodeURIComponent(_PS_ADMIN_DIR_)+'&returnOnErrorTo=' + encodeURIComponent(window.location.href);
	}

	updateParcelsListData();

	$('#dpdpoland_shipment_parcels input[type="text"]').live('keypress', function(){
		$(this).addClass('modified');
		$(this).siblings('p.preference_description').slideDown('fast');
	});

	$('#dpdpoland_shipment_parcels input[type="text"]').live('on', 'paste', function(){
		$(this).addClass('modified');
		$(this).siblings('p.preference_description').slideDown('fast');
	});

	$('#dpdpoland_shipment_parcels input[type="text"]').live('on', 'input', function(){
		$(this).addClass('modified');
		$(this).siblings('p.preference_description').slideDown('fast');
	});

	$('#dpdpoland_recipient_address_selection').live('change', function(){
        $('#ajax_running').slideDown();
		$('#dpdpoland_recipient_address_container .dpdpoland_address').fadeOut('fast');

        var id_address = $(this).val();

        $.ajax({
            type: "POST",
            async: true,
            url: dpdpoland_ajax_uri,
            dataType: "html",
            global: false,
            data: "ajax=true&token=" + encodeURIComponent(dpdpoland_token) +
                  "&id_shop=" + encodeURIComponent(dpdpoland_id_shop) +
                  "&id_lang=" + encodeURIComponent(dpdpoland_id_lang) +
                  "&getFormatedAddressHTML=true" +
                  "&id_address=" + encodeURIComponent(id_address),
            success: function(address_html)
            {
                $('#ajax_running').slideUp();
				$('#dpdpoland_recipient_address_container .dpdpoland_address').html(address_html).fadeIn('fast');
            },
            error: function()
            {
                $('#ajax_running').slideUp();
            }
        });
	});

	$('#dpdpoland_shipment_creation #add_parcel').click(function(){
		var max_parcel_number = $('#dpdpoland_shipment_parcels tbody').find('input[name$="[number]"]:last').attr('value');
		var new_parcel_number = Number(max_parcel_number)+1;

		var $tr_parcel = $('<tr />');

		var $input_parcel_number = $('<input />').attr({'type' : 'hidden', 'name' : 'parcels['+new_parcel_number+'][number]'}).val(new_parcel_number);
		var $td_parcel_number = $('<td />').addClass('center').append(new_parcel_number).append($input_parcel_number);
		$tr_parcel.append($td_parcel_number);

		var $input_content_hidden = $('<input />').attr({'type' : 'hidden', 'name' : 'parcels['+new_parcel_number+'][content]'});
		var $input_content = $('<input />').attr({'type' : 'text', 'size' : '46', 'name' : 'parcels['+new_parcel_number+'][content]'});
		var $td_content = $('<td />').append($input_content_hidden);
		$td_content.append($input_content);
		var $modified_message = $('<p />').attr({'class' : 'preference_description clear', 'style' : 'display: none; width: auto;'});
		$modified_message.append(modified_field_message);
		$td_content.append($modified_message);
		$tr_parcel.append($td_content);

		var $input_weight = $('<input />').attr({'type' : 'text', 'size' : '10', 'name' : 'parcels['+new_parcel_number+'][weight]', 'value' : '0.000000'});
		var $td_weight = $('<td />').append($input_weight);
		var $modified_message = $('<p />').attr({'class' : 'preference_description clear', 'style' : 'display: none; width: auto;'});
		$modified_message.append(modified_field_message);
		$td_weight.append($modified_message);
		$tr_parcel.append($td_weight);

		var $input_height = $('<input />').attr({'type' : 'text', 'size' : '10', 'name' : 'parcels['+new_parcel_number+'][height]', 'value' : '0.000000'});
		var $td_height = $('<td />').append($input_height);
		var $modified_message = $('<p />').attr({'class' : 'preference_description clear', 'style' : 'display: none; width: auto;'});
		$modified_message.append(modified_field_message);
		$td_height.append($modified_message);
		$tr_parcel.append($td_height);

		var $input_length = $('<input />').attr({'type' : 'text', 'size' : '10', 'name' : 'parcels['+new_parcel_number+'][length]', 'value' : '0.000000'});
		var $td_length = $('<td />').append($input_length);
		var $modified_message = $('<p />').attr({'class' : 'preference_description clear', 'style' : 'display: none; width: auto;'});
		$modified_message.append(modified_field_message);
		$td_length.append($modified_message);
		$tr_parcel.append($td_length);

		var $input_width = $('<input />').attr({'type' : 'text', 'size' : '10', 'name' : 'parcels['+new_parcel_number+'][width]', 'value' : '0.000000'});
		var $td_width = $('<td />').append($input_width);
		var $modified_message = $('<p />').attr({'class' : 'preference_description clear', 'style' : 'display: none; width: auto;'});
		$modified_message.append(modified_field_message);
		$td_width.append($modified_message);
		$tr_parcel.append($td_width);

		$('<td />').addClass('parcel_dimension_weight').text('0.000').appendTo($tr_parcel);

		var $td_delete_parcel = $('<td />');
		var $img_delete_parcel = $('<img />').attr({'src' : '../img/admin/delete.gif'}).addClass('delete_parcel').appendTo($td_delete_parcel);
		$tr_parcel.append($td_delete_parcel);

		$('#dpdpoland_shipment_parcels tbody tr:last').after($tr_parcel);

		var $new_parcel_option = $('<option />').val(new_parcel_number).text(new_parcel_number);
		$('#dpdpoland_shipment_products').find('select.parcel_selection').append($new_parcel_option);
	});

	$('#dpdpoland_shipment_parcels .delete_parcel').live('click', function(){
		var $tr_parcel = $(this).parent().parent();

		var deleted_parcel_number = $tr_parcel.find('input[name$="[number]"]').attr('value');
		var max_parcel_number = $('#dpdpoland_shipment_parcels tbody').find('input[name$="[number]"]:last').val();

		$('#dpdpoland_shipment_products select.parcel_selection option[value="'+deleted_parcel_number+'"]').remove();

		/* deleting parcel from the middle of list */
		if(deleted_parcel_number != max_parcel_number)
			recalculateParcels(deleted_parcel_number);

		$tr_parcel.remove();
	});

	$("#dpdpoland_select_product").autocomplete(dpdpoland_ajax_uri,
		{
			minChars: 3,
			max: 10,
			width: 500,
			selectFirst: false,
			scroll: false,
			dataType: "json",
			highlightItem: true,
			formatItem: function(data, i, max, value, term) {
				return value;
			},
			parse: function(data) {
				var products = new Array();
				if (typeof(data.products) != 'undefined')
					for (var i = 0; i < data.products.length; i++)
						products[i] = { data: data.products[i], value: data.products[i].name };
				return products;
			},
			extraParams: {
				ajax: true,
				token: dpdpoland_token,
				getProducts: 'true',
				id_lang: dpdpoland_id_lang,
				id_shop: dpdpoland_id_shop
			}
		}
	)
	.result(function(event, data, formatted) {
		$(this).val(formatted);
		$('#dpdpoland_add_product_container #dpdpoland_selected_product_id_product').attr('value', data.id_product);

		if (!data.id_product_attribute) {
			data.id_product_attribute = 0;
		}

		$('#dpdpoland_add_product_container #dpdpoland_selected_product_id_product_attribute').attr('value', data.id_product_attribute);
		$('#dpdpoland_add_product_container #dpdpoland_selected_product_weight_numeric').attr('value', data.weight_numeric);
		$('#dpdpoland_add_product_container #dpdpoland_selected_product_weight').attr('value', data.weight);
		$('#dpdpoland_add_product_container #dpdpoland_selected_product_name').attr('value', data.name);
	});

	$('#dpdpoland_add_product').live('click', function(){
		var id_product = $('#dpdpoland_add_product_container #dpdpoland_selected_product_id_product').attr('value');

		if (Number(id_product))
		{
			var id_product_attribute = $('#dpdpoland_add_product_container #dpdpoland_selected_product_id_product_attribute').attr('value');
			var weight_numeric = $('#dpdpoland_add_product_container #dpdpoland_selected_product_weight_numeric').attr('value');
			var weight = $('#dpdpoland_add_product_container #dpdpoland_selected_product_weight').attr('value');
			var product_name = $('#dpdpoland_add_product_container #dpdpoland_selected_product_name').attr('value');

			var $tr_product = $('<tr />');

			var new_product_index = $('#dpdpoland_shipment_products tbody tr').length;

			var $input_id_product = $('<input />').attr({'type' : 'hidden', 'name' : 'dpdpoland_products['+new_product_index+'][id_product]', 'value' : id_product});
			var $input_id_product_attribute = $('<input />').attr({'type' : 'hidden', 'name' : 'dpdpoland_products['+new_product_index+'][id_product_attribute]', 'value' : id_product_attribute});
			var $td_parcel_reference = $('<td />').addClass('parcel_reference').append($input_id_product, $input_id_product_attribute, id_product+'_'+id_product_attribute);
			$td_parcel_reference.appendTo($tr_product);

			var $input_weight_hidden = $('<input />').attr({'type' : 'hidden', 'name' : 'parcel_weight', 'value' : weight_numeric});
			$('<td />').addClass('product_name').text(product_name).appendTo($tr_product);
			$('<td />').addClass('parcel_weight').append($input_weight_hidden, weight).appendTo($tr_product);

			var $parcels_selection = $('#dpdpoland_shipment_products select.parcel_selection:first').clone();
			$parcels_selection.attr('name', 'dpdpoland_products['+new_product_index+'][parcel]').find('option:first').attr('selected', 'selected');
			$('<td />').append($parcels_selection).appendTo($tr_product);


			var $td_delete_product = $('<td />');
			var $img_delete_parcel = $('<img />').attr({'src' : '../img/admin/delete.gif'}).addClass('delete_product').appendTo($td_delete_product);
			$tr_product.append($td_delete_product);

			$('#dpdpoland_shipment_products tbody tr:last').after($tr_product);

			$('#dpdpoland_add_product_container #dpdpoland_selected_product_id_product').attr('value', 0);
			$('#dpdpoland_add_product_container #dpdpoland_selected_product_id_product_attribute').attr('value', 0);
			$('#dpdpoland_add_product_container #dpdpoland_selected_product_weight_numeric').attr('value', 0);
			$('#dpdpoland_add_product_container #dpdpoland_selected_product_weight').attr('value', 0);
			$('#dpdpoland_add_product_container #dpdpoland_selected_product_name').attr('value', 0);
			$('#dpdpoland_select_product').attr('value', '');
		}
	});

	$('#dpdpoland_shipment_products .delete_product').live('click', function(){
		$(this).parents('tr:first').remove();
	});

	$('#save_and_print_labels').click(function(){

		var available = true;
		$('#dpdpoland_shipment_products .parcel_selection').each(function(){
			if ($(this).val() == '' || $(this).val() == 0)
			{
				available = false;
				alert(dpdpoland_parcels_error_message);
			}
		});

		if (!available)
			return false;

		$('#ajax_running').slideDown();
		$('#dpdpoland_msg_container').slideUp().html('');

        $.ajax({
            type: "POST",
            async: true,
            url: dpdpoland_ajax_uri,
            dataType: "json",
            global: false,
            data: "ajax=true&token=" + encodeURIComponent(dpdpoland_token) +
				  "&id_order=" + encodeURIComponent(id_order) +
                  "&id_shop=" + encodeURIComponent(dpdpoland_id_shop) +
                  "&id_lang=" + encodeURIComponent(dpdpoland_id_lang) +
				  "&printout_format="+encodeURIComponent($('input[name="dpdpoland_printout_format"]:checked').val()) +
                  "&savePackagePrintLabels=true&" + $('#dpdpoland :input').serialize(),
            success: function(resp)
            {
				if (resp.error)
				{
					$('#dpdpoland_msg_container').hide().html('<p class="error alert alert-danger">'+resp.error+'</p>').slideDown();
					$.scrollTo('#dpdpoland', 400, { offset: { top: -100 }});
				}
				else
				{
					id_package_ws = resp.id_package_ws;
					window.location = dpdpoland_pdf_uri + resp.link_to_labels_pdf+'&_PS_ADMIN_DIR_='+encodeURIComponent(_PS_ADMIN_DIR_)+'&returnOnErrorTo='+encodeURIComponent(window.location.href);
				}

                $('#ajax_running').slideUp();
            },
            error: function()
            {
                $('#ajax_running').slideUp();
            }
        });
	});

	$('#print_labels').live('click', function(){
		$('#ajax_running').slideDown();
		$('#dpdpoland_msg_container').slideUp().html('');

        $.ajax({
            type: "POST",
            async: true,
            url: dpdpoland_ajax_uri,
            dataType: "json",
            global: false,
            data: "ajax=true&token=" + encodeURIComponent(dpdpoland_token) +
				  "&id_order=" + encodeURIComponent(id_order) +
                  "&id_shop=" + encodeURIComponent(dpdpoland_id_shop) +
                  "&id_lang=" + encodeURIComponent(dpdpoland_id_lang) +
                  "&printLabels=true"+
				  "&dpdpoland_printout_format="+encodeURIComponent($('input[name="dpdpoland_printout_format"]:checked').val()) +
				  "&id_package_ws="+encodeURIComponent(id_package_ws) +
				  "&_PS_ADMIN_DIR_="+encodeURIComponent(_PS_ADMIN_DIR_),
            success: function(resp)
            {
				if (resp.error)
				{
					$('#dpdpoland_msg_container').hide().html('<p class="error alert alert-danger">'+resp.error+'</p>').slideDown();
				}
				else
				{
					window.location = dpdpoland_pdf_uri + resp.link_to_labels_pdf+'&_PS_ADMIN_DIR_='+encodeURIComponent(_PS_ADMIN_DIR_)+'&returnOnErrorTo='+encodeURIComponent(window.location.href);
				}

                $('#ajax_running').slideUp();
            },
            error: function()
            {
                $('#ajax_running').slideUp();
            }
        });
	});

	$("#dpdpoland_shipment_parcels input").live("change keyup paste", function(){
		var default_value = 0;
		var $inputs_container = $(this).parents('tr:first');

		var height = $inputs_container.find('input[name$="[height]"]').attr('value');
		var length = $inputs_container.find('input[name$="[length]"]').attr('value');
		var width = $inputs_container.find('input[name$="[width]"]').attr('value');

		var dimention_weight = Number(length)*Number(width)*Number(height)/Number(_DPDPOLAND_DIMENTION_WEIGHT_DIVISOR_);
		if (dimention_weight > 0) {
			dimention_weight = dimention_weight.toFixed(3);
		}
		else
		{
			dimention_weight = default_value.toFixed(3);
		}
		$inputs_container.find('td.parcel_dimension_weight').text(dimention_weight);
	});

	$('select[name="dpdpoland_SessionType"]').change(function(){
		if ($(this).val() == 'domestic_with_cod') {
			$('#dpdpoland_cod_amount_container').fadeIn();
		}
		else{
			$('#dpdpoland_cod_amount_container').fadeOut();
		}
	});

	$('#dpdpoland_shipment_products select.parcel_selection').live('change', function(){
		updateParcelsListData();
	});
});

function updateParcelsListData()
{
	var attr = $('#dpdpoland_shipment_creation select[name="dpdpoland_SessionType"]').attr('disabled');
	if (typeof attr !== 'undefined' && attr !== false) {
		return;
	}

	var default_value = 0;
	var products_count = $('#dpdpoland_shipment_products .parcel_reference').length;
    $('#dpdpoland_shipment_parcels td:nth-child(2) input[type="text"]').not('.modified').attr('value', '');
	$('#dpdpoland_shipment_parcels td:nth-child(3) input[type="text"]').not('.modified').attr('value', default_value.toFixed(6));
    $('#dpdpoland_shipment_parcels td:nth-child(2) input[type="hidden"]').attr('value', '');

    $('#dpdpoland_shipment_products .parcel_reference').each(function(){
        var product_weight = $(this).parent().find('td:nth-child(3)').find('input[type="hidden"]').val();
		product_weight = Number(product_weight);
		var product_id = $(this).find('input[type="hidden"]:nth-child(1)').val();
		if ($(this).find('input[type="hidden"]:nth-child(2)').val() != '')
		{
			product_id = product_id + '_' + $(this).find('input[type="hidden"]:nth-child(2)').val();
		}

        var parcel_id = $(this).siblings().find('select').val();

        var description = '';

        var $parcel_description_field = $('#dpdpoland_shipment_parcels tbody tr:nth-child('+parcel_id+') td:nth-child(2)').find('input[type="text"]');
		var $parcel_weight_field = $('#dpdpoland_shipment_parcels tbody tr:nth-child('+parcel_id+') td:nth-child(3)').find('input[type="text"]');
		var $parcel_height_field = $('#dpdpoland_shipment_parcels tbody tr:nth-child('+parcel_id+') td:nth-child(4)').find('input[type="text"]');
		var $parcel_lenght_field = $('#dpdpoland_shipment_parcels tbody tr:nth-child('+parcel_id+') td:nth-child(5)').find('input[type="text"]');
		var $parcel_width_field = $('#dpdpoland_shipment_parcels tbody tr:nth-child('+parcel_id+') td:nth-child(6)').find('input[type="text"]');
		var $parcel_dimension_weight_field = $('#dpdpoland_shipment_parcels tbody tr:nth-child('+parcel_id+') td:nth-child(7)');

        var $parcel_description_safe = $parcel_description_field.siblings('input[type="hidden"]:first');
		var weights = $parcel_weight_field.val();
		weights = Number(weights);
		weights = weights + product_weight;

        if ($parcel_description_safe.attr('value') == '')
            description = product_id;
        else
            description = $parcel_description_safe.attr('value') + ', ' + product_id;

		if (!$parcel_weight_field.hasClass('modified'))
		{
			$parcel_weight_field.attr('value', weights.toFixed(6));
		}

		if (!$parcel_description_field.hasClass('modified'))
		{
			$parcel_description_field.attr('value', description);
			$parcel_description_safe.attr('value', description);
		}

		if (products_count == 1)
		{
			$('#dpdpoland_shipment_parcels td:nth-child(4) input[type="text"]').not('.modified').attr('value', default_value.toFixed(6));
			$('#dpdpoland_shipment_parcels td:nth-child(5) input[type="text"]').not('.modified').attr('value', default_value.toFixed(6));
			$('#dpdpoland_shipment_parcels td:nth-child(6) input[type="text"]').not('.modified').attr('value', default_value.toFixed(6));
			$('#dpdpoland_shipment_parcels td:nth-child(7)').not('.modified').text(default_value.toFixed(3));

			$parcel_height_field.attr('value', $('#product_height').val());
			$parcel_lenght_field.attr('value', $('#product_length').val());
			$parcel_width_field.attr('value', $('#product_width').val());

			if (!$parcel_height_field.hasClass('modified') &&
				!$parcel_lenght_field.hasClass('modified') &&
				!$parcel_width_field.hasClass('modified')
			)
			{
				var value = $parcel_height_field.val() * $parcel_lenght_field.val() * $parcel_width_field.val() / _DPDPOLAND_DIMENTION_WEIGHT_DIVISOR_;
				$parcel_dimension_weight_field.text(value.toFixed(3));
			}
		}
    });
}

function displayErrorInShipmentArea(errorText) {
	$('#dpdpoland_msg_container').hide().html('<p class="error alert alert-danger">'+errorTextr+'</p>').slideDown();
}

function recalculateParcels(deleted_parcel_number)
{
	$('#dpdpoland_shipment_parcels input[name$="[number]"]').each(function(){
		var parcel_number = Number($(this).attr('value'));

		if(parcel_number > deleted_parcel_number)
		{
			var updated_parcel_number = parcel_number-1;
			var $input = $(this).attr('value', updated_parcel_number);
			$(this).parent().text(updated_parcel_number).append($input);
			$(this).parent().parent().find('input[name^="parcels"]').each(function(){
				$(this).attr('name', $(this).attr('name').replace(parcel_number, updated_parcel_number));
			});
			$('#dpdpoland_shipment_products select.parcel_selection option[value="'+parcel_number+'"]').attr('value', updated_parcel_number).text(updated_parcel_number);
		}
	});
}

function toggleShipmentCreationDisplay()
{
	var $display_cont = $('#dpdpoland_shipment_creation');
	var $legend = $display_cont.siblings('legend').find('a');
	var fieldset_title_substitution = $legend.attr('rel');
	var current_fieldset_title = $legend.text();
	var $dpd_fieldset = $('fieldset#dpdpoland');

	if ($dpd_fieldset.hasClass('extended'))
	{
		$display_cont.slideToggle(function(){
			$dpd_fieldset.removeClass('extended');
		});
	}
	else
	{
		$dpd_fieldset.addClass('extended');
		$display_cont.slideToggle();
	}

	$legend.attr('rel', current_fieldset_title).text(fieldset_title_substitution);
}