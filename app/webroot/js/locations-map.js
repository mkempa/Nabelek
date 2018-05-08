
/**
 * Recieve array where first element is barcode, second element is collection number
 * @param {type} val
 * @returns {String}
 */
function link(val) {
    return '<a href="http://www.nabelek.sav.sk/records/view/' + val[0] + '" target="_specimen">c.n. ' + val[1] + '</a>';
}

function makeMarkers(locs, map, infowindow) {
    $.each(locs, function (key, value) {
        var center = new google.maps.LatLng(value.lat, value.lon);
        var marker = new google.maps.Marker({
            position: center,
            map: map
        });
        var linked = value.group.map(link);
        var content = '<div id="nabelek-info-wrapper"><p>' + linked.join("</p>\n<p>") + '</p></div>';
        marker.addListener("click", function () {
            infowindow.setContent(content);
            infowindow.open(map, marker);
        });
    });
}



$(document).ready(function () {

    var infowindow = new google.maps.InfoWindow();

    //init map
    var mapOptions = {
        center: new google.maps.LatLng(34.6333333, 43.19166667),
        zoom: 5,
        scrollwheel: true,
        mapTypeId: google.maps.MapTypeId.HYBRID
    };
    var locationsMap = new google.maps.Map($("#locations-map").get(0), mapOptions);

    var jqxhr = $.getJSON('/js/locations.json', function (data) {
        makeMarkers(data, locationsMap, infowindow);
    }).fail(function () {
        console.log("Error loading locations");
    });

});
