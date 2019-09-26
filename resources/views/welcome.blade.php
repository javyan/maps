<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>Map Test</title>
        <meta name="description" content="Map Test">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <style>
            #map {
                height: 100%;
            }

            #location_list span {
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h3 class="text-muted">Map Test</h3>
            </div>
            <div class="row content" style="min-height: -webkit-fill-available;">
                <div class="col-md-7">
                    <div id="map"></div>
                </div>
                <div class="col-md-5">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2 class="panel-title text-center">Points
                        </div>
                        <div class="panel-body">

                            <!--<button id="add_point" type="button" class="btn btn-primary">Add point</button>-->
                            <br><br>
                            <label >Locations <span class="badge">Select locations at the map</span></label>
                            <br><br>
                            <div id="location_list">
                            </div>
                            <br><br>
                            <button id="calc_distance" type="button" class="btn btn-primary">Calculate</button>
                            <div id="output"></div>   
                            <div id="result"></div>   
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
            
            <div class="footer">
            
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script>
            var map;
            let locations = [];
            let acc_distance = 0;
            let acc_time = 0;
            function initMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: -34.397, lng: 150.644},
                zoom: 8
                });
                infoWindow = new google.maps.InfoWindow;

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                        };
                        map.setCenter(pos);
                    }, function() {
                        handleLocationError(true, infoWindow, map.getCenter());
                    });
                }
            
                map.addListener('click', function(e) {
                    addLocation(e.latLng, map);
                });
            
            };

            function addLocation(latLng, map) {
                var marker = new google.maps.Marker({
                    position: latLng,
                    map: map
                });
                locations.push(marker);
                $('#location_list').append('<span>Point added: Lat.' + latLng.lat() + ' - Lon.' + latLng.lng() + '</span><br>');
            };

            function handleLocationError(browserHasGeolocation, infoWindow, pos) {
                infoWindow.setPosition(pos);
                infoWindow.setContent(browserHasGeolocation ?
                                    'Error: The Geolocation service failed.' :
                                    'Error: Your browser doesn\'t support geolocation.');
                infoWindow.open(map);
            };

            $('#calc_distance').click(function() {
                            
                
                for (var i = 1; i < locations.length; i++) {
                    calculatePoints(locations[i-1],locations[i],i,i+1);
                };

                
                
            });

            function calculatePoints(origin, dest, oi, di) {

                var markersArray = [];

                var geocoder = new google.maps.Geocoder;

                var service = new google.maps.DistanceMatrixService;
                service.getDistanceMatrix({
                    origins: [origin.position],
                    destinations: [dest.position],
                    travelMode: 'DRIVING',
                    unitSystem: google.maps.UnitSystem.METRIC,
                    avoidHighways: false,
                    avoidTolls: false
                }, function(response, status) {
                    if (status !== 'OK') {
                        alert('Error was: ' + status);
                    } else {
                        var originList = response.originAddresses;
                        var destinationList = response.destinationAddresses;
                        
                        var showGeocodedAddressOnMap = function(asDestination) {
                            return function(results, status) {
                                if (status === 'OK') {
                                    //
                                } else {
                                alert('Geocode error: ' + status);
                                }
                            };
                        };

                        for (var i = 0; i < originList.length; i++) {
                            var results = response.rows[i].elements;
                            geocoder.geocode({'address': originList[i]},
                                showGeocodedAddressOnMap(false));
                            for (var j = 0; j < results.length; j++) {
                                geocoder.geocode({'address': destinationList[j]},
                                    showGeocodedAddressOnMap(true));
                                $("#output").append('P. ' + oi +' to P.' + di + ' : ' + originList[i] + ' to ' + destinationList[j] +
                                    ': ' + results[j].distance.text + ' in ' +
                                    results[j].duration.text + '<br>');
                                acc_distance += results[j].distance.value;
                                acc_time += results[j].duration.value;
                                $("#result").html('Total distance: ' +  Math.round(acc_distance/1000) + ' km <br>' + 'Total time: ' +  Math.round(acc_time/60) + 'mins');
                            
                            }
                        }
                    }
                });

            };

        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
    </body>
</html>