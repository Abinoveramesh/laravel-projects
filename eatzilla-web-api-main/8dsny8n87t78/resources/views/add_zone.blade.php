@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')

 <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{ strtoUpper(trans('constants.add')) }} {{ strtoUpper(trans('constants.zone')) }}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/zones_list" class="brand-font-link-color">{{ strtoUpper(trans('constants.zone')) }} {{ strtoUpper(trans('constants.list')) }}</a>
                </li>
                <li class="breadcrumb-item"><a href="#" class="brand-font-link-color">{{ strtoUpper(trans('constants.add')) }} {{ strtoUpper(trans('constants.zone')) }}</a>
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

                    <form action="{{url('/')}}/admin/zone_add" method="post"   class="icons-tab-steps wizard-notification">
                     <input type="hidden" name="_token" value="{{csrf_token()}}">

                    <fieldset>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for="zone_name">{{trans('constants.zone')}}<span style="color: red;">*</span></label>
                              <input type="text" class="form-control" name="zone" id="zone_name" placeholder="zone" required value="{{ old('zone') }}">
                              @if ($errors->has('zone'))
                                  <div class="text-danger">{{ $errors->first('zone') }}</div>
                              @endif
                              <input type="hidden" name="status" value="1">
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                              <label  for="projectinput4">{{trans('constants.state_name')}}<span style="color: red;">*</span></label>
                              <select name="new_state" id="new_state" class="form-control" required>
                                <option value="" selected="" disabled="">Select {{trans('constants.state_name')}}</option>
                              @foreach($newState as $state)
                                  <option value="{{$state->id}}" @if($state_id == $state->id) selected @endif>{{$state->state}}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                              <label  for="projectinput4">{{trans('constants.state')}}<span style="color: red;">*</span></label>
                              <select name="state" id="state" class="form-control" required>
                                <option value="" selected="" disabled="">Select {{trans('constants.state')}}</option>
                                @isset($city)
                                  @foreach($city as $c)
                                    <option value="{{$c->id}}" @if($city_id == $c->id) selected  @endif>{{$c->state}} </option>
                                  @endforeach
                                @endisset
                              </select>
                          </div>
                        </div>
                        <br>
                        <div class="row">
                          <div class="col-md-6" @if($all_polycons == '') style="display:none;" @endif>
                            <div class="form-group">
                              <label for="email" class="zone-map">{{ trans('constants.city_polygons')}}</label>
                              <div id="city_map_polygon" style="width: 100%; height: 400px; position: relative; overflow: hidden; background-color: rgb(229, 227, 223);margin-bottom:15px;"></div>
                              <input type="hidden" id="get_map_id" name="get_map_id" value="0">
                              <textarea id="city_geofence_latlng" name="city_geofence_latlng" style="display:none;">@isset($all_polycons){{$all_polycons}}@endisset</textarea>
                                <input type="hidden" id="city_latitude" name="city_latitude" value="@if(isset($get_city_lat)) {{$get_city_lat}} @else {{ env('DEFAULT_LAT')}} @endif">
                                <input type="hidden" id="city_longitude" name="city_longitude" value="@if(isset($get_city_lat)) {{$get_city_lng}} @else {{ env('DEFAULT_LNG')}} @endif">
                            </div>
                          </div>
                          <div @if($all_polycons == '') class="col-md-12" @else class="col-md-6" @endif>
                            <div class="form-group">
                              <label for="email">{{ trans('constants.region')}}<span style="color: red;">*</span></label>
                              <input type="button" name="delete-button" id="delete-button"  type="button" value="Delete Polygon" class="btn btn-sm success-button-style" style="float:right;margin:5px;">
                              <div id="map_polygon" style="width: 100%; height: 400px; position: relative; overflow: hidden; background-color: rgb(229, 227, 223);margin-bottom:15px;"></div>
                              <input type="hidden" id="get_map_id" name="get_map_id" value="0">
                              <textarea id="geofence_latlng" name="geofence_latlng" style="display:none;"></textarea>
                                <input type="hidden" id="latitude" name="latitude" value="@if(isset($get_city_lat)) {{$get_city_lat}} @else {{ env('DEFAULT_LAT')}} @endif">
                                <input type="hidden" id="longitude" name="longitude" value="@if(isset($get_city_lat)) {{$get_city_lng}} @else {{ env('DEFAULT_LNG')}} @endif">
                                @if ($errors->has('geofence_latlng'))
                                    <div class="text-danger">{{ $errors->first('geofence_latlng') }}</div>
                                @endif
                            </div>
                          </div>
                        </div>
                        
                          <!-- <h4 class="card-title">{{trans('constants.admin_commision')}} {{trans('constants.setting')}}</h4> -->
                          
                          <div class="row">
                          <!-- <div class="col-md-6">
                            <div class="form-group">
                              <label for="password">{{trans('constants.admin_commision')}} In %<span style="color: red;">*</span></label>
                             <input type="text" class="form-control" name="admin_commission" id="pass" required value="{{ old('admin_commision') }}">
                             @if ($errors->has('admin_commision'))
                                  <div class="text-danger">{{ $errors->first('admin_commision') }}</div>
                              @endif
                             </div>
                            </div>
                            
                           
                          </div>
                        <h4 class="card-title">{{trans('constants.delivery_charge_setting')}}</h4>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="number">{{trans('constants.default_delivery_amt')}}<span style="color: red;">*</span></label>
                             <input type="text" class="form-control" name="default_delivery_amount" id="number" required value="{{ old('default_delivery_amount') }}">
                            </div> 
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label  for="min_dist_delivery_price">{{trans('constants.min_distance_baseprice')}}<span style="color: red;">*</span></label>
                              <input type="text" class="form-control" name="min_dist_delivery_price" required id="min_dist_delivery_price" value="{{ old('min_dist_delivery_price') }}" >
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="extra_fee_deliveryamount">{{trans('constants.extra_fee_amt')}}<span style="color: red;">*</span></label>
                             <input type="text" name="extra_fee_deliveryamount" class="form-control" required id="extra_fee_deliveryamount" value="{{ old('extra_fee_deliveryamount') }}">
                            </div> 
                          </div>
                        </div>
                     <h4 class="card-title">{{trans('constants.driver_charge_setting')}}</h4>
                        <div class="row"> -->
                          <!-- <div class="col-md-6">
                            <div class="form-group">
                              <label for="number">{{trans('constants.driver_baseprice')}}<span style="color: red;">*</span></label>
                             <input type="text" class="form-control" required name="driver_base_price" id="number" value="{{ old('driver_base_price') }}" >
                            </div> 
                         </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label  for="projectinput4">{{trans('constants.min_distance_baseprice')}}<span style="color: red;">*</span></label>
                              <input type="text" class="form-control" required name="min_dist_base_price" id="number" value="{{ old('min_dist_base_price') }}" >
                            </div>
                          </div> -->
                     <!-- </div>
                      <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="number">{{trans('constants.extra_fee_amt')}}<span style="color: red;">*</span></label>
                             <input type="text" name="extra_fee_amount" required class="form-control" id="number" value="{{ old('extra_fee_amount') }}">
                            </div> 
                         </div> -->
                          <!-- <div class="col-md-6">
                            <div class="form-group">
                              <label for="number">{{trans('constants.extra_fee_for_unit')}}</label>
                             <div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="defaultUnchecked" name="extra_fee_amount_each">
                                <label class="custom-control-label" for="defaultUnchecked">Km</label>
                              </div>
                              <!-- Default checked -->
                              <!--<div class="custom-control custom-radio">
                                <input type="radio" class="custom-control-input" id="defaultChecked" name="extra_fee_amount_each" checked>
                                <label class="custom-control-label" for="defaultChecked">Miles</label>
                              </div>
                            </div>
                          </div> -->
                     </div>
                      <!-- <h4 class="card-title">Night Fare Amount</h4>
                      <div class="row">
                          
                            <div class="col-md-6">
                             <div class="form-group">
                             <label  for="projectinput4">Amount</label>
                           <input type="text" class="form-control" name="night_fare_amount" id="number"  value="{{ old('night_fare_amount') }}">
                         </div>
                       </div>
                        <div class="col-md-6">
                             <div class="form-group">
                             <label  for="projectinput4">Driver Share % (Rest Will Be For Admin)</label>
                           <input type="text" class="form-control" name="night_driver_share" id="number"  value="{{ old('night_driver_share') }}" >
                         </div>
                       </div>
                     </div>
                     <div class="row">
                          
                            <div class="col-md-6" id="datetimepicker3">
                             <div class="form-group">
                             <label  for="projectinput4">Start Time</label>
                           <input type="text" class="form-control" data-format="hh:mm:ss" name="start_time" id="number"  >
                           <span class="add-on">
      <i data-time-icon="icon-time" data-date-icon="icon-calendar">
      </i>
    </span>
                         </div>
                       </div>
                        <div class="col-md-6">
                             <div class="form-group">
                             <label  for="projectinput4">End Time</label>
                           <input type="text" class="form-control" name="end_time" id="number"   >
                         </div>
                       </div>
                     </div>
                     <h4 class="card-title">Surge Fare Setting</h4>
                      <div class="row">
                          <div class="col-md-6">
                             <div class="form-group">
                             <label  for="projectinput4">Amount</label>
                           <input type="text" class="form-control" name="surge_fare_amount" id="number" value="{{ old('surge_fare_amount') }}" >
                         </div>
                       </div>
                        <div class="col-md-6">
                             <div class="form-group">
                             <label  for="projectinput4">Driver Share % (Rest Will Be For Admin)</label>
                           <input type="text" class="form-control" name="surge_driver_share" id="number" value="{{ old('surge_driver_share') }}" >
                         </div>
                       </div>
                          </div> -->
                          <!--  <div class="row">
                          
                        
                          </div> -->
                      <div class="form-actions">
                              <a href="{{url('/')}}/admin/zones_list" class="btn cancel-button-style mr-1" style="padding: 10px 15px;">
                                  <i class="ft-x"></i> Cancel
                              </a>
                              <button type="submit" class="btn mr-1 success-button-style" style="padding: 10px 15px;">
                               <i class="ft-check-square"></i> Save
                                </button>
                            </div>
                          </div>
                        </div>
                      </fieldset>
                    </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
              </section>
            </div>
          </div>
   
  <script type="text/javascript">

$(document).on('change','#new_state',function(){

  var new_state=$(this).val();
  $.ajax({
    type: "GET",
    url: "{{url('/')}}/admin/get_state_ajax/"+new_state,
    dataType:'json',
    success: function(data){

      var value="<option value='' selected='' disabled=''>Select City</option>";
      data.forEach(function(element) {

        value +="<option value='"+element.id+"'>"+element.state+"</option>";

      });

      $('#state').html(value);

    }
  });
});


$(document).on('change','#state',function() {
  var city = $(this).val();
  var state = $("#new_state").val()
  window.location.href = "{{url('/')}}/admin/get_polycan_city_wise/"+city+'/'+state
});

      $(function() {
    $('#datetimepicker3').datetimepicker({
      pickDate: false
    });
  });

  
var mapnew ='';
var value = document.getElementById('geofence_latlng').value;
var value1 = document.getElementById('city_geofence_latlng').value;
function initAutocomplete() {

    var lat = document.getElementById('latitude').value;
    var lng = document.getElementById('longitude').value;

         mapnew = new google.maps.Map(document.getElementById('map_polygon'), {
            zoom: 10,
            center: new google.maps.LatLng(lat, lng),
            noClear:true
        });

        var citylat = document.getElementById('city_latitude').value;
        var citylng = document.getElementById('city_longitude').value;
        citymapnew = new google.maps.Map(document.getElementById('city_map_polygon'), {
            zoom: 10,
            center: new google.maps.LatLng(citylat, citylng),
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
              shape.set('draggable',true);
              shape.set('editable',true);
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
              this.selectedShape.set('draggable',true);
              this.selectedShape.set('editable',true);
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
                    console.log(temp_arr);
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
            console.log("array"+collection);
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
                    draggable: true
                });
            },
            mapToLatLng:function(source, index, array){
                return new google.maps.LatLng(parseFloat(source[1]), parseFloat(source[0]));
            },
            toLatLng:function(array){
              return array.map(this.mapToLatLng);
            }
        };

        var shapes1={
          collection:{},
          selectedShape:null,
          add:function(e){
            var shape1=e.overlay,
                that=this;              
            shape1.type=e.type;
            shape1.id=new Date().getTime()+'_'+Math.floor(Math.random()*1000);
            this.collection[shape1.id]=shape1;           
            google.maps.event.addListener(shape1.getPath(), 'set_at', function() {
              shapes1.save();
            });
            google.maps.event.addListener(shape1.getPath(), 'insert_at', function() {
              shapes1.save();
            });            
            shapes1.save();
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
                    console.log(temp_arr);
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
            console.log("array"+collection);
            // document.getElementById('geofence_latlng').value = JSON.stringify(collection);
            // document.getElementById('city_geofence_latlng').value = JSON.stringify(collection);
          },
          mkArray:function(item,index){
            tempLat.push(item.lng());
            tempLat.push(item.lat());
            temp_arr.push(tempLat);
            tempLat = [];
          },
          newPolyLine:function(polyOptions){
            var polyLine = new google.maps.Polyline(polyOptions);
            polyLine.setMap(citymapnew);
            // google.maps.event.addListener(polyLine, 'click', function (event) {
            //     shapes1.setSelection(polyLine);
            // });  
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
                    // clickable:false,
                    // draggable: false
                });
            },
            mapToLatLng:function(source, index, array){
                return new google.maps.LatLng(parseFloat(source[1]), parseFloat(source[0]));
            },
            toLatLng:function(array){
              return array.map(this.mapToLatLng);
            }
        };
        if(value1!=''){
          value1 = JSON.parse(value1);
          for (var i = value1.length - 1; i >= 0; i--) {   
              shapes1.add(shapes1.newPolyLine(shapes1.newPolyOptions(shapes1.toLatLng(value1[i][0]))));
          }
          shapes1.save();
        }

        if(value!=''){
          console.log(JSON.parse(value))
          value = JSON.parse(value);
          for (var i = value.length - 1; i >= 0; i--) {   
              shapes.add(shapes.newPolyLine(shapes.newPolyOptions(shapes.toLatLng(value[i][0]))));
          }
          shapes.save();
        }
        
        var drawingManager = new google.maps.drawing.DrawingManager({
          drawingControl: true,
          drawingControlOptions: {
              drawingModes: [google.maps.drawing.OverlayType.POLYGON]
          },
          //drawingMode: google.maps.drawing.OverlayType.POLYGON,
          drawingMode: null,
          polygonOptions: polyOptions,
          map: mapnew
        });

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
            drawingManager.setDrawingMode(null);
            shapes.add(e);
        });


        google.maps.event.addListener(drawingManager, 
                                      'drawingmode_changed', 
                                      function(){shapes.clearSelection();});
        google.maps.event.addListener(mapnew, 
                                      'click', 
                                      function(){shapes.clearSelection();});
        google.maps.event.addDomListener(document.getElementById('delete-button'), 
                                      'click', 
                                      function(){shapes.deleteSelected();});
        // google.maps.event.addDomListener(document.getElementById('save-button'), 
        //                               'click', 
        //                               function(){shapes.save();});


        /** Search box related script - start **/
        var input = document.getElementById('zone_name');
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
            mapnew  .setZoom(15);
        });
        /** Search box related script - end **/

    }

  function load(lat,lng,pickup){
        if(pickup&&pickup!=''){
        if (pickup.geometry.viewport) {
                    mapnew.fitBounds(pickup.geometry.viewport);
                } else {
                    mapnew.setCenter(pickup.geometry.location);
                    mapnew  .setZoom(10);  // Why 17? Because it looks good.
            }
        }
        else{
        mapnew.setCenter(new google.maps.LatLng(lat, lng));
        mapnew.setZoom(10);  
        }

    }
    </script>

<script src="https://maps.googleapis.com/maps/api/js?key={{GOOGLE_API_KEY}}&libraries=geometry,places,drawing&callback=initAutocomplete" ></script>

    <script type="text/javascript"
     src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js">
    </script> 
    
    <script type="text/javascript"
     src="https://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js">
    </script>
    <script type="text/javascript"
     src="https://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.pt-BR.js">
    </script>
</body>
</html> 

         
 @endsection