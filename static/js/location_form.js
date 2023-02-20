var lovematch_geocoder;
var lovematch_delayRequest;

$(document).ready(function(){
    if (google.maps == undefined)
    {
        var content = '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDTC9SqCpE4czt0j3DDI-Z-rfthNFCA51Q"></script>';
        $('head').append(content);
    }
    
    if (typeof jQuery == 'undefined') {
        var content = '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>';
        $('head').append(content);
    }
    
    lovematch_geocoder = new google.maps.Geocoder();
    
    $("#lovematch_location_city").keypress(function(){
        if (lovematch_delayRequest != null)
        {
            clearTimeout(lovematch_delayRequest);
        }
        lovematch_delayRequest = setTimeout(function(){lovematch_requestGeocoding();},500);
    });
    
    $("#lovematch_location_city").blur(function(){
        setTimeout(function(){
            $('#lovematch_location_result').css({'display':'none'});
        },300);
    });
});

function lovematch_requestGeocoding()
{
    lovematch_delayRequest = null;
    $("#lovematch_location_loading").css('display', 'inline');
    var address = $("#lovematch_location_city").val();

    lovematch_ajaxRequest = lovematch_geocoder.geocode({'address': address},function(result,status){
        $("#lovematch_location_loading").css({'display':'none'});
        lovematch_displayResults(result,status);
    });
}

function lovematch_displayResults(result,status)
{
    var even = true;
    var output = [];
    $('#lovematch_location_result').html('');
    $("#lovematch_location_result").css({'display':'block'});
    if(result.length == 0)
    {
        $("#lovematch_location_result").css({'display':'none'});
    }
    
    
    for(key in result)
    {
        var text = result[key]['formatted_address'];
        var lat = result[key]['geometry']['location'].lat();
        var lng = result[key]['geometry']['location'].lng();

        var row = $('<div></div>')
                  .html(text)
                  .attr({'data-lat':lat,'data-lng':lng})
                  .addClass("resultItem")
                  .click(function(){
                      $("#lovematch_location_lat").val($(this).attr('data-lat'));
                      $("#lovematch_location_lng").val($(this).attr('data-lng'));
                      $("#lovematch_location_city").val($(this).html());
                      $('#lovematch_location_result').html('');
                      $("#lovematch_location_result").css({'display':'none'});
                  });

        if(even)
        {
            row.addClass('even');
        }
        else
        {
            row.addClass('odd');
        }

        even = !even;
        output[key]=row;
    }
    
    
    
    $('#lovematch_location_result').html(output);
}
