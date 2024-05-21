@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')


 <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{ trans('constants.edit')}} {{ trans('constants.city')}}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/city_list" class="brand-font-link-color">{{ trans('constants.city')}} {{ trans('constants.list')}}</a>
                </li>
                <li class="breadcrumb-item"><a href="" class="brand-font-link-color">{{ trans('constants.edit')}} {{ trans('constants.city')}}</a>
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
                    <form action="{{url('/')}}/admin/update_city" method="post"   class="icons-tab-steps wizard-notification">
                     <input type="hidden" name="_token" value="{{csrf_token()}}">
                     <input type="hidden" name="id" value="{{$city_data->id}}">

                    <fieldset>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for="city_name">{{ trans('constants.city')}}<span style="color: red;">*</span></label>
                              <input type="text" class="form-control" name="city" id="city_name" placeholder="City" required value="@if(old('city')){{ old('city') }} @else {{ $city_data->city }} @endif">
                              @if ($errors->has('city'))
                                  <div class="text-danger">{{ $errors->first('city') }}</div>
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
                                @isset($city_data->NewState)
                                  @if($city_data->new_state_id == $state->id)
                                    <option value="{{$state->id}}" selected="">{{$city_data->NewState->state}}</option>
                                  @else
                                    <option value="{{$state->id}}">{{$state->state}}</option>
                                  @endif
                                @else
                                <option value="{{$state->id}}">{{$state->state}}</option>
                                @endisset
                              @endforeach
                            </select>
                          </div>
                          <div class="col-md-6">
                              <label  for="projectinput4">{{trans('constants.state')}}<span style="color: red;">*</span></label>
                              <select name="state" id="state" class="form-control" required>
                                @isset($city_data->State)
                                <option value="{{$city_data->state_id}}">{{$city_data->State->state}}</option>
                                @else
                                <option value="" selected="" disabled="">Select {{trans('constants.state')}}</option>
                                @endisset
                              </select>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for="region" style="margin:5px;">{{ trans('constants.region')}}<span style="color: red;">*</span></label>
                              <input type="button" name="delete-button" id="delete-button"  value="Delete Polygon" onclick="event.preventDefault();" class="btn-sm success-button-style" style="float:right;margin:5px;">
                              <div id="map_polygon" style="width: 100%; height: 400px; position: relative; overflow: hidden; background-color: rgb(229, 227, 223);margin-bottom:15px;"></div>
                              <textarea id="geofence_latlng" name="geofence_latlng" style="display:none;">@if(old('geofence_latlng')) {{old('geofence_latlng')}}
                                @else @isset($city_data->city_geofencing->polygons) {{$city_data->city_geofencing->polygons}} @endisset
                                @endif</textarea>
                                <input type="hidden" id="latitude" name="latitude" value="@if(old('latitude')) {{ old('latitude') }} @else @isset($city_data->city_geofencing->latitude){{$city_data->city_geofencing->latitude}} @endisset @endif">
                                <input type="hidden" id="longitude" name="longitude" value="@if(old('longitude')) {{ old('longitude') }} @else @isset($city_data->city_geofencing->longitude) {{ $city_data->city_geofencing->longitude}} @endisset @endif">
                                @if ($errors->has('geofence_latlng'))
                                    <div class="text-danger">{{ $errors->first('geofence_latlng') }}</div>
                                @endif
                            </div>
                          </div>
                          
                          
                        </div>
                          <div class="row">
                           
                          </div>
                        <h4 class="card-title">{{trans('constants.delivery_charge_setting')}}</h4>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="number">First 3 KM Charge (Rupees ₹) <span style="color: red;">*</span></label>
                             <input type="number" class="form-control" name="delivery_charge_first_2_km" placeholder="Enter First 3 KM Charge In (Rupees ₹)" required value="{{$city_data->delivery_charge_first_2_km}}">
                            </div> 
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label  for="min_dist_delivery_price">Remaining Each KM Charge (Rupees ₹) <span style="color: red;">*</span></label>
                              <input type="number" class="form-control" name="delivery_charge_remaining_each_km" placeholder="Enter Remaining Each KM Charge In (Rupees ₹)" required value="{{$city_data->delivery_charge_remaining_each_km}}" >
                            </div>
                          </div>
                        </div>
                      <div class="form-actions">
                              <a href="{{url('/')}}/admin/city_list" class="btn cancel-button-style mr-1" style="padding: 10px 15px;">
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

      var value="";
      data.forEach(function(element) {

        value +="<option value='"+element.id+"'>"+element.state+"</option>";

      });

      $('#state').html(value);

    }
  });
});

      $(function() {
    $('#datetimepicker3').datetimepicker({
      pickDate: false
    });
  });

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
            zoom: 10,
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