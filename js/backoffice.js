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
    if ($('#credentials').length != 0) { //in configuration page only
        showHideCODModulesList();
    }
    
    $('#addClientNumber').click(function(){
        addClientNumber();
    });
    
    $('#dpd_standard_cod').change(function(){
        showHideCODModulesList();
		enableDisableZones();
    });

	$('#dpd_standard').change(function(){
        enableDisableZones();
    });

	$('#dpd_classic').change(function(){
        enableDisableZones();
    });

    $('select[name="pickupTime"]').live('change', function(){
        calculateTimeLeftForArrangePickup();
    });
    
    $('#pickupDate').live("change keyup paste", function(){
		getTimeFramesByDate();
	});
	
	$('input[name="downloadModuleCSVSettings"]').click(function(){
		window.location = dpdpoland_pdf_uri+"?downloadModuleCSVSettings&token="+encodeURIComponent(dpdpoland_token);
		return false;
	});
	
	toggleEnvelope();
	toggleParcel();
	togglePallet();

	enableDisableZones();
    
    if ($('#pickup_date').length)
		getTimeFramesByDate();
	
	$('#toggleEnvelope').change(function(){
		toggleEnvelope();
	});
	
	$('#toggleParcel').change(function(){
		toggleParcel();
	});
	
	$('#togglePallet').change(function(){
		togglePallet();
	});
});

function enableDisableZones()
{
	if ($('#dpd_standard').is(':checked'))
		$('.domestic_zone').removeAttr('disabled');
	else
		$('.domestic_zone').attr('disabled', 'disabled');

	if ($('#dpd_standard_cod').is(':checked'))
		$('.domestic_cod_zone').removeAttr('disabled');
	else
		$('.domestic_cod_zone').attr('disabled', 'disabled');

	if ($('#dpd_classic').is(':checked'))
		$('.classic_zone').removeAttr('disabled');
	else
		$('.classic_zone').attr('disabled', 'disabled');
}

function toggleEnvelope()
{
	if ($('#toggleEnvelope').is(':checked'))
		$('#envelopes_container').slideDown();
	else
		$('#envelopes_container').slideUp();
}

function toggleParcel()
{
	if ($('#toggleParcel').is(':checked'))
		$('#parcels_container').slideDown();
	else
		$('#parcels_container').slideUp();
}

function togglePallet()
{
	if ($('#togglePallet').is(':checked'))
		$('#pallets_container').slideDown();
	else
		$('#pallets_container').slideUp();
}

function getTimeFramesByDate()
{
    $('#ajax_running').slideDown();
    
    var current_date = $('#pickupDate').val();
    
    $.ajax({
        type: "POST",
        async: true,
        url: dpdpoland_ajax_uri,
        dataType: "json",
        global: false,
        data: "getTimeFrames=true&date="+encodeURIComponent(current_date)+
            "&token="+encodeURIComponent(dpdpoland_token)+
            "&id_shop="+encodeURIComponent(dpdpoland_id_shop)+
            "&id_lang="+encodeURIComponent(dpdpoland_id_lang),
        success: function(resp)
        {
            $('#timeframe_container').html(resp);
            calculateTimeLeftForArrangePickup();
        },
        error: function()
        {
            $('#ajax_running').slideUp();
        }
    });
}

function calculateTimeLeftForArrangePickup()
{
    if (!$('#ajax_running').is(':visible'))
        $('#ajax_running').slideDown();
    
    var current_timeframe = $('select[name="pickupTime"]').val();
    var current_date = $('#pickupDate').val();
    
    $.ajax({
        type: "POST",
        async: true,
        url: dpdpoland_ajax_uri,
        dataType: "json",
        global: false,
        data: "calculateTimeLeft=true&timeframe="+encodeURIComponent(current_timeframe)+
            "&date="+encodeURIComponent(current_date)+
            "&token="+encodeURIComponent(dpdpoland_token)+
            "&id_shop="+encodeURIComponent(dpdpoland_id_shop)+
            "&id_lang="+encodeURIComponent(dpdpoland_id_lang),
        success: function(resp)
        {
            $('#timeframe_container span.time_left').text(resp);
            $('#ajax_running').slideUp();
        },
        error: function()
        {
            $('#ajax_running').slideUp();
        }
    });
}

function showHideCODModulesList() {
    if ($('#dpd_standard_cod').is(':checked'))
    {
        $('.payment_modules_container').slideDown('fast');
    }
    else
    {
        $('.payment_modules_container').slideUp('fast');
    }
}

function addClientNumber() {
    $('#ajax_running').slideDown();
    
    $('#error_message').slideUp();
    $('#success_message').slideUp();
    
    var ajax_request_params = 'ajax=true&addDPDClientNumber=true';
    var client_number = $('#client_number').val();
    var client_name = $('#client_name').val();
    
    $.ajax({
        type: "POST",
        async: true,
        url: dpdpoland_ajax_uri,
        dataType: "json",
        global: false,
        data: ajax_request_params +
            "&client_number=" + encodeURIComponent(client_number) +
            "&name=" + encodeURIComponent(client_name) +
            "&token=" + encodeURIComponent(dpdpoland_token) +
            "&id_shop=" + encodeURIComponent(dpdpoland_id_shop) +
            "&id_lang=" + encodeURIComponent(dpdpoland_id_lang),
        success: function(resp)
        {
            if (resp.error) {
                $('#error_message').html(resp.error).slideDown('fast');
            }
            else
                $('#success_message').html(resp.message).slideDown('fast');
            
            displayPayerNumbersTable();
            $('#ajax_running').slideUp();
        },
        error: function()
        {
            $('#ajax_running').slideUp();
        }
    });
}

function deleteClientNumber(id_client_number) {
    $('#ajax_running').slideDown();
    
    $('#error_message').slideUp();
    $('#success_message').slideUp();
    
    var ajax_request_params = 'ajax=true&deleteDPDClientNumber=true';
    
    $.ajax({
        type: "POST",
        async: true,
        url: dpdpoland_ajax_uri,
        dataType: "json",
        global: false,
        data: ajax_request_params +
            "&client_number=" + encodeURIComponent(id_client_number) +
            "&token=" + encodeURIComponent(dpdpoland_token) +
            "&id_shop=" + encodeURIComponent(dpdpoland_id_shop) +
            "&id_lang=" + encodeURIComponent(dpdpoland_id_lang),
        success: function(resp)
        {
            if (resp.error) {
                $('#error_message').html(resp.error).slideDown('fast');
            }
            else
                $('#success_message').html(resp.message).slideDown('fast');
            
            displayPayerNumbersTable();
            $('#ajax_running').slideUp();
        },
        error: function()
        {
            $('#ajax_running').slideUp();
        }
    });
}

function displayPayerNumbersTable() {
    $('#ajax_running').slideDown();
    
    var ajax_request_params = 'ajax=true&getPayerNumbersTableHTML=true';
    
    $.ajax({
        type: "POST",
        async: true,
        url: dpdpoland_ajax_uri,
        dataType: "json",
        global: false,
        data: ajax_request_params +
            "&token=" + encodeURIComponent(dpdpoland_token) +
            "&id_shop=" + encodeURIComponent(dpdpoland_id_shop) +
            "&id_lang=" + encodeURIComponent(dpdpoland_id_lang),
        success: function(resp)
        {
            $('#client_numbers_table_container').html(resp);
            $('#ajax_running').slideUp();
        },
        error: function()
        {
            $('#ajax_running').slideUp();
        }
    });
}