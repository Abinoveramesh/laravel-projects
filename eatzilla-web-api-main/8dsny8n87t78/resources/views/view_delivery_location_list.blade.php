@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')


 <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">Delivery Location</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/delivery_location_list">Delivery Location List</a>
                </li>
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <div class="content-body">
        <section id="icon-tabs">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title"></h4>
                  <a class="heading-elements-toggle"><i class="la la-ellipsis-h font-medium-3"></i></a>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                      <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                      <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                      <li><a data-action="close"><i class="ft-x"></i></a></li>
                    </ul>
                  </div>
                </div>

                <div class="card-content collapse show">
                  <div class="card-body">
                    <fieldset>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <!-- <label for="city_name">{{ trans('constants.city')}}</label> -->
                              <input type="hidden" class="form-control" name="city" id="city_name" placeholder="City" value="@if($city->city) {{$city->city}} @endif" disabled="">
                            </div>
                          </div>
                          
                          <div class="col-md-6">
                            <div class="form-group">
                              <!-- <label  for="projectinput4">{{ trans('constants.status')}}</label> -->
                              <input type="hidden" name="status" id="" class="form-control" value="@if( $city->status==1) {{ trans('constants.active')}} @else {{ trans('constants.inactive')}} @endif" disabled>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-6">
                              <!-- <label  for="projectinput4">{{trans('constants.country')}}</label> -->
                                  <input type="hidden" class="form-control" disabled value="{{$country->country}}">
                            </select>
                          </div>
                          <div class="col-md-6">
                              <!-- <label  for="projectinput4">{{trans('constants.state')}}</label> -->
                                <input type="hidden" value="{{$state->state}}" class="form-control" disabled="">
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <!-- <label for="email">{{ trans('constants.region')}}</label> -->
                              <input type="hidden" name="delete-button" id="delete-button"  value="Delete Polygon" onclick="event.preventDefault();" class="btn btn-primary btn-sm" style="float:right;">
                              <div id="map_polygon" style="width: 100%; height: 400px; position: relative; overflow: hidden; background-color: rgb(229, 227, 223);margin-bottom:15px;"></div>
                              <textarea id="geofence_latlng" name="geofence_latlng" style="display:none;">@isset($city->city_geofencing->polygons) {{$city->city_geofencing->polygons}} @endisset</textarea>
                                <input type="hidden" id="latitude" name="latitude" value="@isset($city->city_geofencing->latitude){{$city->city_geofencing->latitude}} @endisset">
                                <input type="hidden" id="longitude" name="longitude" value="@isset($city->city_geofencing->longitude) {{ $city->city_geofencing->longitude}} @endisset">
                            </div>
                          </div>
                        </div>
                        </div>
                      </fieldset>
                    </div>
                  </div>
                </div>
              </div>
            </div>
              </section>
            </div>
          </div>

  <script type="text/javascript">
    

var mapnew ='';
var value = document.getElementById('geofence_latlng').value;
value = value.trim();
function initAutocomplete() {    
    // var lat = document.getElementById('latitude1').value;
    // var lng = document.getElementById('longitude1').value;
    // lat = (lat!='') ? lat : '33.312805';
    // lng = (lng!='') ? lng : '43.081466';

    var lat = document.getElementById('latitude').value;
    var lng = document.getElementById('longitude').value;

         mapnew = new google.maps.Map(document.getElementById('map_polygon'), {
            zoom: 13,
            center: new google.maps.LatLng(lat, lng),
            // mapTypeId: google.maps.MapTypeId.ROADMAP,
            noClear:true
        });
        // map.controls[google.maps.ControlPosition.RIGHT_BOTTOM]
        //   .push(document.getElementById('save-button'));
        // map.controls[google.maps.ControlPosition.RIGHT_BOTTOM]
        //   .push(document.getElementById('delete-button'));
        var polyOptions = {
            strokeWeight: 3,
            fillOpacity: 0.2
        };
        var tempLat = [];
        var temp_arr = [];
        var latArray = [];
        var shapes={
          collection:{},
          selectedShape:null,
          add:function(e){
            var shape=e.overlay,
                that=this;              
            shape.type=e.type;
            shape.id=new Date().getTime()+'_'+Math.floor(Math.random()*1000);
            this.collection[shape.id]=shape;
            this.setSelection(shape);
            google.maps.event.addListener(shape,'click',function(){
                that.setSelection(this);
            });            
            google.maps.event.addListener(shape.getPath(), 'set_at', function() {
        
      
                shapes.save();
            });
            google.maps.event.addListener(shape.getPath(), 'insert_at', function() {
                shapes.save();
            });            
            shapes.save();
          },
          setSelection:function(shape){
            if(this.selectedShape!==shape){
              this.clearSelection();
              this.selectedShape = shape;
              // shape.set('draggable',true);
              // shape.set('editable',true);
            }
          },
          deleteSelected:function(){
          
            if(this.selectedShape){
              var shape= this.selectedShape;
              this.clearSelection();
              shape.setMap(null);
              delete this.collection[shape.id];
              shapes.save();
              document.getElementById('geofence_latlng').value="";
            }
           },
          
          
          clearSelection:function(){
            if(this.selectedShape){
              this.selectedShape.set('draggable',false);
              this.selectedShape.set('editable',false);
              this.selectedShape=null;
            }
          },
          save:function(){
            var collection=[];
            for(var k in this.collection){
              var shape=this.collection[k],
                  types=google.maps.drawing.OverlayType;
              switch(shape.type){
                case types.POLYGON:
                    var locations = shape.getPath().getArray();
                    locations.forEach(this.mkArray);
                    temp_arr.push(temp_arr[0]);
                    latArray.push(temp_arr);
                    collection.push(latArray);
                    temp_arr =[] ; latArray = [];
                    // console.log('locations',shape,shape.getPath(),locations.toString());
                   // collection.push({ type:shape.type,path:google.maps.geometry.encoding.encodePath(shape.getPath())});
                  break;
                default:
                  // alert('implement a storage-method for '+shape.type)
              }
            }
            //collection is the result
           //  console.log("array"+collection);
            document.getElementById('geofence_latlng').value = JSON.stringify(collection);
          },
          mkArray:function(item,index){
            tempLat.push(item.lng());
            tempLat.push(item.lat());
            temp_arr.push(tempLat);
            tempLat = [];
          },
          newPolyLine:function(polyOptions){
            var polyLine = new google.maps.Polyline(polyOptions);
            polyLine.setMap(mapnew);
            google.maps.event.addListener(polyLine, 'click', function (event) {
                shapes.setSelection(polyLine);
            });  
            var overlay = {
              overlay: polyLine, 
              type: google.maps.drawing.OverlayType.POLYGON
            };
            return overlay;
          },
          newPolyOptions:function(path){
                return new google.maps.Polygon({
                    path:path,
                    fillOpacity:0.5,
                    clickable:true,
                    // draggable: true
                });
            },
            mapToLatLng:function(source, index, array){
                return new google.maps.LatLng(parseFloat(source[1]), parseFloat(source[0]));
            },
            toLatLng:function(array){
              return array.map(this.mapToLatLng);
            }
        };

        
         console.log('value',value);
        if(value!=""){
            value = JSON.parse(value);
            //value = value[0];
            // console.log('value',value,value[0].length);
            for (var i = value.length - 1; i >= 0; i--) {   
                // console.log(i);
                // console.log(value[i][0]);
                shapes.add(shapes.newPolyLine(shapes.newPolyOptions(shapes.toLatLng(value[i][0]))));
            }
            shapes.save();
        }
        
        //  var drawingManager = new google.maps.drawing.DrawingManager({
        //     drawingControl: true,
        //     drawingControlOptions: {
        //         drawingModes: [google.maps.drawing.OverlayType.POLYGON]
        //     },
        //     //drawingMode: google.maps.drawing.OverlayType.POLYGON,
        //     drawingMode: null,
        //     polygonOptions: polyOptions,
        //     map: mapnew
        // });

        // google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
        //     drawingManager.setDrawingMode(null);
        //     shapes.add(e);
        // });


        // google.maps.event.addListener(drawingManager, 
        //                               'drawingmode_changed', 
        //                               function(){shapes.clearSelection();});
        // google.maps.event.addListener(mapnew, 
        //                               'click', 
        //                               function(){shapes.clearSelection();});
        // google.maps.event.addDomListener(document.getElementById('delete-button'), 
        //                               'click', 
        //                               function(){shapes.deleteSelected();});
        // google.maps.event.addDomListener(document.getElementById('save-button'), 
        //                               'click', 
        //                               function(){shapes.save();});


        /** Search box related script - start **/
        var input = document.getElementById('city_name');
        var searchBox = new google.maps.places.SearchBox(input);
        searchBox.bindTo('bounds', mapnew);

        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();
            if(places[0] && places[0].geometry)
            {
              $loc=places[0].geometry;
            }else{
              $loc=places.geometry;
            }
            var lat =$loc.location.lat(),
              lng =$loc.location.lng();
            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lng;
            mapnew.setCenter(new google.maps.LatLng(parseFloat(lat),parseFloat(lng)));
        });
        /** Search box related script - end **/

    }

  function load(lat,lng,pickup){
        if(pickup&&pickup!=''){
        if (pickup.geometry.viewport) {
                    mapnew.fitBounds(pickup.geometry.viewport);
                } else {
                    mapnew.setCenter(pickup.geometry.location);
                    mapnew  .setZoom(15);  // Why 17? Because it looks good.
            }
        }
        else{
        mapnew.setCenter(new google.maps.LatLng(lat, lng));
        mapnew.setZoom(10);  
        }

    }

    //google.maps.event.addDomListener(window, 'load', initMap);
    </script>

<script src="https://maps.googleapis.com/maps/api/js?key={{GOOGLE_API_KEY}}&libraries=geometry,places,drawing&callback=initAutocomplete" ></script>

    <script type="text/javascript"
     src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js">
    </script> 
    
    <script type="text/javascript"
     src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js">
    </script>
    <script type="text/javascript"
     src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.pt-BR.js">
    </script>
</body>
</html> 

         
 @endsection