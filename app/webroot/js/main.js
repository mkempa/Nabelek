
function makeMap() {
    if ($("#map-container").length > 0) {
        var lat = $("#view-record-latitude").val();
        var lon = $("#view-record-longitude").val();
        var mapOptions = {
            center: new google.maps.LatLng(48.172873, 17.066532),
            zoom: 7,
            scrollwheel: true,
            mapTypeId: google.maps.MapTypeId.HYBRID
        };
        var map = new google.maps.Map($("#map-container").get(0), mapOptions);
        if (lat !== "" && lon !== "") {
            var center = new google.maps.LatLng(lat, lon);
            map.setCenter(center);
            var marker = new google.maps.Marker({
                position: center,
                map: map
            });
            return;
        }
        $("#map-message").text("No coordinates for this record");
    }
}

$(document).ready(function () {
    
    $(".preview").click(function (e) {
        e.preventDefault();
        var $img = $(this).find("img");
        $("#thumbnail").attr("src", $img.attr("src"));
        var data = $img.attr("data");
        //..img/thumbs/SAV0000001_a_thumb.jpg
        var viewhref = $("#view").attr("href");
        var liov = viewhref.lastIndexOf("/");
        var repl = viewhref.replace(viewhref.substr(liov + 1), data);
        $("#view").attr("href", repl);
        var downurl = "http://dataflos.sav.sk:8080/scans/nabelek/jpeg/";
        $("#download").attr("href", downurl + data + ".jpg");
    });

    makeMap();

    $("[data-toggle='tooltip']").tooltip();
    
});