@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')
  <style type="text/css">
    #map {
        width: 100%;
        height: 300px;
    }

    .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

  </style>
  <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
<div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{$title}}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/restaurant_list">{{ strtoUpper(trans('constants.restaurant'))}} {{ strtoUpper(trans('constants.list'))}}</a>
                </li>
                <li class="breadcrumb-item"><a href="#">{{$title}}</a>
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
                    <form action="{{url('/')}}/admin/add_to_restaurants" enctype="multipart/form-data" method="post" class="icons-tab-steps wizard-notification" id="add_restaurant">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        @if(isset($data))
                        <input type="hidden" class="form-control" value="{{$data->id}}" name="id" >
                         @endif
                    <fieldset>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="eventName2">{{trans('constants.restaurant')}} {{trans('constants.name')}} <span style="color: red;">*</span></label>
                              <input type="text" name="name" required value="@if(isset($data->restaurant_name)){{$data->restaurant_name}}@endif" class="form-control" id="eventName2">
                            </div>
                             <div class="form-group">
                              <label for="email">Email <span style="color: red;">*</span></label>
                             <input type="text" name="email" required value="@if(isset($data->email)){{$data->email}}@endif" class="form-control" id="email">
                             
                            </div>

                            <div class="form-group">
                              <label for="email">Password <span style="color: red;">*</span></label>
                             <input type="text" name="password" required value="@if(isset($data->org_password)){{$data->org_password}}@endif" class="form-control" id="password">
                             
                            </div>
                     
                             <div class="form-group">
                              <label for="content">Mobile number <span style="color: red;">*</span></label>
                             <input type="text" name="phone" id="phone" value="@if(isset($data->phone)){{$data->phone}}@endif" class="form-control" id="details">
                              <span class="error_message" id="phone_error"></span>
                            </div>

                             <div class="form-group">
                              <label for="content">Secondary Mobile number (optional)</label>
                             <input type="text" name="phone2" id="phone2" value="@if(isset($data->phone2)){{$data->phone2}}@endif" class="form-control" id="details2">
                            </div>

                           <!--  <div class="form-group">
                              <label for="pass">Password :</label>
                             <input type="password" class="form-control" id="password">
                             
                            </div> -->

                            <!--  <div class="form-group">
                              <label for="pass">Conform Password :</label>
                             <input type="password" class="form-control" id="con_password">
                             
                            </div> -->

                           <!--  <div class="form-group">
                              <label for="status">{{trans('constants.status')}} <span style="color: red;">*</span></label>
                              <select class="c-select form-control" id="status" name="status">
                                <option value="1" @isset($data->status) @if($data->status == 1)'selected' @endif @endisset>{{trans('constants.active')}}</option>
                                <option value="0" @isset($data->status) @if($data->status == 0)'selected' @endif @endisset>{{trans('constants.inactive')}}</option>
                                <option value="Active">Active</option> -->
                              <!-- </select>
                            </div> --> 

                             <div class="form-group">
                              <label for="eventName2">{{trans('constants.status')}}<span style="color: red;">*</span></label>
                             <label class="switch">
                              <input type="checkbox" name="status" value="1" 
                              @if(isset($data->status)){{($data->status==1)?"checked":"" }} @else checked @endif>                        
                              <span class="slider round"></span></label>
                           </div>
                           <div class="row">
                              <div class="col-xs-10">
                                <div class="form-group">
                                  <label for="status">{{trans('constants.cuisines')}} <span style="color: red;">*</span></label>
                                  <select class="c-select form-control select2" required multiple="multiple" id="cuisines" name="cuisines[]">
                                    @if(isset($cuisines))
                                      @foreach($cuisines as $val)
                                        <option value="{{$val->id}}" @if(isset($cuisine_ids)) {{ (in_array($val->id,$cuisine_ids)) ? 'selected' : '' }} @endif>{{$val->name}}</option>
                                      @endforeach
                                    @endif
                                    <!-- <option value="Active">Active</option> -->
                                  </select>
                                </div>
                              </div>
                              <div class="col-xs-2">
                                <input type="button" class="btn btn-success" value="Add +" id="add_cuisine" data-toggle="modal" data-target="#add_cuisine_modal">
                              </div>
                            </div>
                            <div class="form-group">
                              <label for="status">{{trans('constants.delivery_type')}} <span style="color: red;">*</span></label>
                              @php
                                if(isset($data->delivery_type)){
                                $delivery_type = json_decode($data->delivery_type);
                                if($delivery_type=='' || $delivery_type==0) $delivery_type=array();
                                }else{
                                  $delivery_type = array();
                                }
                              @endphp
                              <input type="checkbox" name="delivery_type[]" id="home_delivery" value="1"  class="chk form-control" @if(in_array(1, $delivery_type)) checked @endif>Home Delivery
                              <input type="checkbox" name="delivery_type[]" id="pickup" value="2" class="chk form-control" @if(in_array(2, $delivery_type)) checked @endif>Pickup
                              <!-- <input type="checkbox" onclick = "funchecktype()" name="delivery_type[]" id="dining" value="3" class="chk form-control" @if(in_array(3, $delivery_type)) checked @endif>Dining -->
                              <br>
                              <span class="error_message" id="delivery_type_error"></span>
                            </div>

                            <div class="form-group dining_count">
                               <label for="dining_count">Maximum Limit for Dining<span style="color: red;">*</span></label>
                                 <input type="text" class="form-control" id="dining_count"  value="@if(isset($data->max_dining_count)){{$data->max_dining_count}}@endif" name="dining_count" placeholder="Maximum Limit for Dining">
                             </div>
                            

                             <div class="form-group">
                            <label for="password-confirm">{{trans('constants.res_timing_weekday')}}</label>

                            <!-- <input id="everyday" type="checkbox" checked="" class="form-control" name="everyday" value="1"> -->
                        </div>


                        <!--=== Weekday / *  Add ====-->

                         @if(!isset($data))
                         <div class="row  everyday ">
                          <div id="dynamic_field">
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <label for="hours_opening">{{trans('constants.res_opens')}}<span style="color: red;">*</span></label>

                                    <div class="input-group clockpicker">
                                      <input type="text" id="timepicker" name="weekdays[opening_time][]" value="@if(isset($data->opening_time)){{$data->opening_time}}@else 00:00 @endif" required="" width="276" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <label for="hours_closing">{{trans('constants.res_close')}}<span style="color: red;">*</span></label>
                                    <div class="input-group clockpicker">
                                      <input type="text" id="timepicker0" name="weekdays[closing_time][]" value="@if(isset($data->closing_time)){{$data->closing_time}}@else 00:00 @endif" required="" width="276" />
                                    </div>
                                </div>
                            </div>
                              
                            <div class="col-xs-4">
                              <input type="button" class="btn btn-success" value="Add +" id="add">
                            </div>
                          </div>
                        </div>
                        <div class='everyday_dynamic'></div>
                        @endif

                        <!--=== End Weekday / *  Add ====-->

                        <!--=== 1st weekend time /* edit ====-->

                            @if(isset($weekdays))
                            @if(count($weekdays) !=0)

                            <?php $k = 1; ?>
                            @foreach($weekdays as $w)
                            <?php if($k==1){ ?>
                               <div class="row  everyday ">
                                  <div class="col-xs-4">
                                      <div class="form-group">
                                          <label for="hours_opening">{{trans('constants.res_opens')}}<span style="color: red;">*</span></label>

                                          <div class="input-group clockpicker">
                                            <input type="text" id="timepicker" name="weekdays[opening_time][]" value="@if(isset($w->opening_time)){{$w->opening_time}}@else 00:00 @endif" required="" width="276" />
                                          </div>
                                      </div>
                                  </div>
                                  <div class="col-xs-4">
                                      <div class="form-group">
                                          <label for="hours_closing">{{trans('constants.res_close')}}<span style="color: red;">*</span></label>
                                          <div class="input-group clockpicker">
                                            <input type="text" id="timepicker0" name="weekdays[closing_time][]" value="@if(isset($w->closing_time)){{$w->closing_time}}@else 00:00 @endif" required="" width="276" />
                                          </div>
                                      </div>
                                  </div>

                                  <div class="col-xs-4">
                                    <input type="button" class="btn btn-success" value="Add +" id="add">
                                  </div>
                              </div>

                            <?php } $k++?>
                            @endforeach

                        <!-- ==== End 1st weekday time /* edit===== -->


                        <!-- ======== other weekday times /* edit ===== -->
                          <?php $i = 1; ?>
                          @foreach($weekdays as $w)
                              <?php if($i!=1){ ?>
                                <div class="row  everyday ">
                                  <!-- <div id="dynamic_field"> -->
                                    <div id="row<?php echo $i ?>" class="dynamic-added">
                                      <div class="col-xs-4">
                                          <input type="text" id="timepicker<?php echo $i ?>" name="weekdays[opening_time][]" value="@if(isset($w->opening_time)){{$w->opening_time}}@else 00:00 @endif" required=""  />
                                      </div>
                                      <div class="col-xs-4">
                                        <input type="text" id="timepicker-c<?php echo $i ?>" name="weekdays[closing_time][]" value="@if(isset($w->closing_time)){{$w->closing_time}}@else 00:00 @endif" required=""  />
                                      </div>
                                      <div class=" col-xs-4">
                                        <button type="button" name="remove" id="<?php echo $i ?>" class="btn btn-danger btn_remove_weekday">X</button>
                                      </div>
                                    </div>
                                  <!-- </div> -->
                                </div>
                              <?php } $i++ ?>
                          @endforeach
                        @else
                        <div class="row  everyday ">
                          <div id="dynamic_field">
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <label for="hours_opening">{{trans('constants.res_opens')}}<span style="color: red;">*</span></label>

                                    <div class="input-group clockpicker">
                                      <input type="text" id="timepicker" name="weekdays[opening_time][]" value="@if(isset($data->opening_time)){{$data->opening_time}}@else 00:00 @endif" required="" width="276" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <label for="hours_closing">{{trans('constants.res_close')}}<span style="color: red;">*</span></label>
                                    <div class="input-group clockpicker">
                                      <input type="text" id="timepicker0" name="weekdays[closing_time][]" value="@if(isset($data->closing_time)){{$data->closing_time}}@else 00:00 @endif" required="" width="276" />
                                    </div>
                                </div>
                            </div>
                              
                            <div class="col-xs-4">
                              <input type="button" class="btn btn-success" value="Add +" id="add">
                            </div>
                          </div>
                        </div>
                        
                            @endif
                            <div class='everyday_dynamic'></div>
                            @endif
                          

                        <!--====   End Other  time  /* edit  ====-->

                        <!--=========   WeekEnd Days time   /* Add  ===========-->
                        <div class="form-group">
                            <label for="password-confirm">{{trans('constants.res_timing_weekend')}}<span style="color: red;">*</span></label>
                        </div>

                         @if(!isset($data))
                         <div class="row  everyday ">
                          <div id="dynamic_field1">
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <label for="hours_opening">{{trans('constants.res_opens')}}<span style="color: red;">*</span></label>
                                    <div class="input-group clockpicker">
                                      <input type="text" class="timepicker"  id="timepickerr" name="weekenddays[opening_time][]" value="@if(isset($data->weekend_opening_time)){{$data->weekend_opening_time}}@else 00:00 @endif" required="" width="276" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <label for="hours_closing">{{trans('constants.res_close')}}<span style="color: red;">*</span></label>

                                    <div class="input-group clockpicker">

                                      <input type="text" id="timepickerr0" name="weekenddays[closing_time][]" value="@if(isset($data->weekend_closing_time)){{$data->weekend_closing_time}}@else 00:00 @endif" required="" width="276" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4">
                              <input type="button" class="btn btn-success" value="Add +" id="add1">
                            </div>
                              </div>
                          </div>
                          <div class='everyday_dynamic1'></div>
                          @endif

                           <!--=========  End  WeekEnd Days time   /* Add  ===========-->

                            @if(isset($weekenddays))
                              @if(count($weekenddays) !=0)
                                <?php $l = 1; ?>
                                @foreach($weekenddays as $we)
                                  <?php if($l ==1){ ?>
                                  <div class="row  everyday ">
                                    <div class="col-xs-4">
                                        <div class="form-group">
                                            <label for="hours_opening">{{trans('constants.res_opens')}}<span style="color: red;">*</span></label>

                                            <div class="input-group clockpicker">
                                              
                                              <input type="text" id="timepickerr" name="weekenddays[opening_time][]" value="@if(isset($we->opening_time)){{$we->opening_time}}@else 00:00 @endif" required="" width="276" />
                                               
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-4">
                                        <div class="form-group">
                                            <label for="hours_closing">{{trans('constants.res_close')}}<span style="color: red;">*</span></label>

                                            <div class="input-group clockpicker">

                                              <input type="text" id="timepickerr0" name="weekenddays[closing_time][]" value="@if(isset($we->closing_time)){{$we->closing_time}}@else 00:00 @endif" required="" width="276" />
                                              
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-4">
                                      <input type="button" class="btn btn-success" value="Add +" id="add1">
                                    </div>
                                    </div>
                                  <?php } $l++ ?>
                                @endforeach


                            <div class="row everyday ">
                              <div id="dynamic_field1">
                                
                                <?php $j = 1; ?>
                                @foreach($weekenddays as $we)
                                  <?php if($j !=1){ ?>
                                  <div id="rowtest<?php echo $j ?>" class="dynamic-added">
                                    <div class="col-xs-4">
                                          <input type="text" id="timepickerr<?php echo $j; ?>" name="weekenddays[opening_time][]" value="@if(isset($we->opening_time)){{$we->opening_time}}@else 00:00 @endif" required="" />
                                    </div>
                                    <div class="col-xs-4">
                                      <input type="text" id="timepickerr-c<?php echo $j; ?>" name="weekenddays[closing_time][]" value="@if(isset($we->closing_time)){{$we->closing_time}}@else 00:00 @endif" required="" />
                                    </div>
                                    <div class=" col-xs-4">
                                    <button type="button" name="remove" id="test<?php echo $j ?>" class="btn btn-danger btn_remove_weekend">X</button>
                                  </div>
                                  </div>
                                  <?php } $j++; ?>
                                @endforeach
                              </div>
                            </div>
                            @else
                            <div class="row  everyday ">
                              <div id="dynamic_field1">
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <label for="hours_opening">{{trans('constants.res_opens')}}<span style="color: red;">*</span></label>
                                        <div class="input-group clockpicker">
                                          <input type="text" id="timepickerr" name="weekenddays[opening_time][]" value="@if(isset($data->weekend_opening_time)){{$data->weekend_opening_time}}@else 00:00 @endif" required="" width="276" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <label for="hours_closing">{{trans('constants.res_close')}}<span style="color: red;">*</span></label>

                                        <div class="input-group clockpicker">

                                          <input type="text" id="timepickerr0" name="weekenddays[closing_time][]" value="@if(isset($data->weekend_closing_time)){{$data->weekend_closing_time}}@else 00:00 @endif" required="" width="276" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                  <input type="button" class="btn btn-success" value="Add +" id="add1">
                                </div>
                                  </div>
                              </div>
                          @endif
                          <div class='everyday_dynamic1'></div>
                        @endif
                              <!-- </div>
                            </div> -->

                           <div class="form-group row">
                             <label class="col-md-3 label-control" for="projectinput4">{{trans('constants.restaurant')}} {{trans('constants.image')}}<span style="color: red;">*</span></label>
                            <div class="col-md-9">
                              @if(isset($data->image))
                                <img id="blah" src="{{SPACES_BASE_URL.$data->image}}" alt="your image"  style="max-width:180px;"><br>
                              @endif
                               <input type='file' name="image" onchange="readURL(this);"  style="padding:10px;background:000;" @if(!isset($data->image)) required="" @endif>
                          </div>
                         </div>
                        
                          <h4>Offer Settings</h4>
                           
                          <div class="form-group">
                            <label for="percentage">Discount Type </label>
                              <select class="c-select form-control" id="status" name="discount_type">
                                <option value="1" @isset($data->discount_type) @if($data->discount_type==1) selected @endif @endisset>Flat Offer</option>
                                <option value="2" @isset($data->discount_type) @if($data->discount_type==2) selected @endif @endisset>Percentage Offer</option>
                              </select>
                             </div>
                             <div class="form-group">
                               <label for="percentage">Target Amount </label>
                                 <input type="text" class="form-control floating-label" name="target_amount" value="@if(isset($data->target_amount)){{$data->target_amount}}@else 0 @endif" >
                             </div>

                              <div class="form-group">
                               <label for="percentage">Offer Amount </label>
                                 <input type="text" class="form-control floating-label" name="offer_amount" value="@if(isset($data->offer_amount)){{$data->offer_amount}}@else 0 @endif">
                             </div>
                             
                          </div>


                          <div class="col-md-6">

                             <div class="form-group">
                               <label for="amount">Role </label>
                                  <select class="c-select form-control" id="role" name="role" required> 
                                  <option value="main" @if(isset($data->role)) @if($data->role == "main") selected @endif @endif>main</option>
                                  <option value="sub" @if(isset($data->role)) @if($data->role == "sub") selected @endif @endif>sub</option>
                              </select>
                             </div>

                            <div class="form-group">
                              <label for="status">Select Parent Restaurant <span style="color: red;">*</span></label>
                              <select class="c-select form-control select2" id="parent" name="parent">
                                <option value="0">Select Parent Restaurant</option>
                              @foreach($parent_restaurants as $p_res)  
                                <option value="{{$p_res->id}}" @if(isset($data->parent)) @if($data->parent == $p_res->id) selected @endif @endif>{{$p_res->restaurant_name}}</option>
                              @endforeach
                              </select>
                            </div>
                         
                            <div class="form-group">
                               <label for="amount">Packaging charge </label>
                                 <input type="text" class="form-control" id="amount" name="packaging_charge" value="@if(isset($data->packaging_charge)){{$data->packaging_charge}}@else 0 @endif" >
                             </div>
                             <div class="form-group">
                               <label for="min_order_value">Minimum order value <span style="color: red;">*</span></label>
                                 <input type="text" class="form-control" id="min_order_value" name="min_order_value" value="@if(isset($data->min_order_value)){{$data->min_order_value}}@else 0 @endif" required>
                             </div>

                             <div class="form-group">
                              <label for="status">Select Suburb <span style="color: red;">*</span></label>
                              <select class="c-select form-control select2" /*onchange="getcity_area()"*/ id="city" multiple="multiple" name="city[]" required>
                                @php
                                  if(isset($data->city)){
                                  $city_data = json_decode($data->city);
                                  if($city_data=='' || $city_data==0) $city_data=array();
                                  }else{
                                    $city_data = array();
                                  }
                                @endphp
                              @foreach($city as $c)  
                                <option value="{{$c->id}}" @if(isset($data->city)) @if(in_array($c->id,$city_data)) selected @endif @endif>{{$c->city}}</option>
                              @endforeach
                              </select>
                            </div>

                            <!-- <div class="form-group">
                              <label for="status">Apt/Suite<span style="color: red;">*</span></label>
                              <select class="c-select form-control" required id="area" name="area">
                               @foreach($area as $a)
                                <option value="{{$a->id}}" @isset($data->area) @if($data->area==$a->id) selected @endif @endisset>{{$a->area}}</option>
                              @endforeach
                              </select>
                            </div> -->
                          
                             <div class="form-group">
                              <label for="description">Description </label>
                              <textarea name="shop_description" id="description" rows="6" class="form-control" placeholder="Enter Description">@if(isset($data->shop_description)){{$data->shop_description}}@endif</textarea>
                             
                            </div>

                             <div class="form-group">
                               <label for="percentage">{{trans('constants.estimate_delivery_time')}} ({{trans('constants.mins')}}) <span style="color: red;">*</span></label>
                                 <input type="text" class="form-control" id="percentage" required value="@if(isset($data->estimated_delivery_time)){{$data->estimated_delivery_time}}@endif" name="estimated_delivery_time" placeholder="15-25">
                             </div>
                             <div class="form-group">
                              <label for="percentage">Order Delivery Type </label>
                                <select class="c-select form-control" id="status" name="deliverytype">
                                  <option value="1" @isset($data->deliverytype) @if($data->deliverytype==1) selected @endif @endisset>Restaurant Delivery Only</option>
                                  <option value="2" @isset($data->deliverytype) @if($data->deliverytype==2) selected @endif @endisset>Fast bee Rider only</option>
                                  <option value="3" @isset($data->deliverytype) @if($data->deliverytype==3) selected @endif @endisset>Both Delivery Options</option>
                                </select>
                             </div>
                            <!--  <div class="form-group">
                               <label for="fssai_license">ABN Number<span style="color: red;">*</span></label>
                                 <input type="text" class="form-control" id="fssai_license" required value="@if(isset($data->fssai_license)){{$data->fssai_license}}@endif" name="fssai_license" placeholder="ABN Number">
                             </div> -->

                             <div class="form-group">
                               <label for="resturant_website">Restaurant Website</label>
                                 <input type="text" class="form-control" id="resturant_website" value="@if(isset($data->resturant_website)){{$data->resturant_website}}@endif" name="resturant_website" placeholder="Restaurant Website">
                             </div>
                            
                              <div class="form-group">
                              <label for="description">Restaurant Address <span style="color: red;">*</span></label>
                                <input id="searchMapInput" name="address" class="form-control" type="text" placeholder="Enter a address" value="@if(isset($data->address)){{$data->address}}@endif" required="">
                                <input type="hidden" id="latitude" name="latitude" value="@if(isset($data->lat)) {{ $data->lat }} @else {{ env('DEFAULT_LAT')}} @endif">
                                <input type="hidden" id="longitude" name="longitude" value="@if(isset($data->lng)) {{ $data->lng }} @else {{ env('DEFAULT_LNG')}} @endif">
                                <span id="address_err" class="text-danger"></span>
                              </div>
                              <div class="form-group">
                                <div id="map"></div>
                              </div>

                        <h4>{{trans('constants.restaurant')}} {{trans('constants.comission_setting')}}</h4>
                        <div class="form-group">
                          <label for="percentage">Do you want to apply unique admin commission?</label>
                          <label class="switch">
                              <input type="checkbox" onchange="checkadmincommission(this)" @if(isset($data->admin_commision) && $data->admin_commision!=0) checked @endif>                        
                              <span class="slider round"></span>
                          </label>
                        </div>
                        <div class="form-group" id="admin_div" @if(isset($data->admin_commision) && $data->admin_commision!=0) style="display:block" @else style="display:none" @endif>
                          <label for="percentage">Admin Commission with Delivery % </label>
                            <input type="text" class="form-control floating-label" name="admin_commision" value="@if(isset($data->admin_commision)){{$data->admin_commision}}@else 0 @endif">
                        </div>
                        <div class="form-group" id="admin_div1" @if(isset($data->admin_commision_without_delivery) && $data->admin_commision_without_delivery!=0) style="display:block" @else style="display:none" @endif>
                          <label for="percentage">Admin Commission without Delivery % </label>
                            <input type="text" class="form-control floating-label" name="admin_commision_without_delivery" value="@if(isset($data->admin_commision_without_delivery)){{$data->admin_commision_without_delivery}}@else 0 @endif">
                        </div>

                        <!-- <div class="form-group">
                          <label for="percentage">{{trans('constants.restaurant')}} {{trans('constants.delivery_charge')}} </label>
                            <input type="text" class="form-control floating-label" name="restaurant_delivery_charge" value="@if(isset($data->restaurant_delivery_charge)){{$data->restaurant_delivery_charge}}@else 0 @endif">
                        </div> -->
                        <!-- <div class="form-group">
                          <label for="percentage">Do you want to apply unique driver commission?</label>
                          <label class="switch">
                              <input type="checkbox" onchange="checkdrivercommission(this)" @if(isset($data->driver_commision) && $data->driver_commision!=0) checked @endif>                        
                              <span class="slider round"></span>
                          </label>
                        </div>
                        <div class="form-group" id="driver_div" @if(isset($data->driver_commision) && $data->driver_commision!=0) style="display:block" @else style="display:none" @endif>
                          <label for="percentage">{{trans('constants.driver_commission')}} % </label>
                            <input type="text" class="form-control floating-label" id="driver_commision" name="driver_commision" value="@if(isset($data->driver_commision)){{$data->driver_commision}}@else 0 @endif">
                        </div> -->

                      </div>
                        </div>

                        <h3>Delivery Charge Settings</h3>
                        <div class="form-group">
                          <label for="percentage">Do you want to apply unique delivery charge?</label>
                          <label class="switch">
                              <input type="checkbox" onchange="checkdeliverycharge(this)" @if(isset($data->restaurant_delivery_charge) && $data->restaurant_delivery_charge!=0) checked @endif>                        
                              <span class="slider round"></span>
                          </label>
                        </div>
                        <div class="row" id="deliverycharge_div" @if(isset($data->restaurant_delivery_charge) && $data->restaurant_delivery_charge!=0) style="display:block" @else style="display:none" @endif>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="restaurant_delivery_charge">{{trans('constants.default_delivery_amt')}}</label>
                              <input type="text" class="form-control" name="restaurant_delivery_charge" id="number"  value="@if(old('restaurant_delivery_charge')) {{ old('restaurant_delivery_charge') }} @elseif(isset($data)) {{$data->restaurant_delivery_charge}} @endif">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label  for="min_dist_delivery_price">{{trans('constants.min_distance_baseprice')}}</label>
                              <input type="text" class="form-control" name="min_dist_delivery_price" id="min_dist_delivery_price" value="@if(old('min_dist_delivery_price')) {{ old('min_dist_delivery_price') }} @elseif(isset($data)) {{$data->min_dist_delivery_price}} @endif" >
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="extra_fee_deliveryamount">{{trans('constants.extra_fee_amt')}}</label>
                              <input type="text" name="extra_fee_deliveryamount" class="form-control" id="extra_fee_deliveryamount" value="@if(old('extra_fee_deliveryamount')) {{ old('extra_fee_deliveryamount') }} @elseif(isset($data)) {{$data->extra_fee_deliveryamount}} @endif">
                            </div>
                          </div>
                        </div>
                        
                        <h3>Driver Commission Settings</h3>
                        <div class="form-group">
                          <label for="percentage">Do you want to apply unique driver commission?</label>
                          <label class="switch">
                              <input type="checkbox" onchange="checkdrivercommission(this)" @if(isset($data->driver_base_price) && $data->driver_base_price!=0) checked @endif>                        
                              <span class="slider round"></span>
                          </label>
                        </div>
                        <div class="row" id="driver_div" @if(isset($data->driver_base_price) && $data->driver_base_price!=0) style="display:block" @else style="display:none" @endif>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="driver_base_price">{{trans('constants.default_delivery_amt')}}</label>
                              <input type="text" class="form-control" name="driver_base_price" id="number"  value="@if(old('driver_base_price')) {{ old('driver_base_price') }} @elseif(isset($data)) {{$data->driver_base_price}} @endif">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label  for="min_dist_base_price">{{trans('constants.min_distance_baseprice')}}</label>
                              <input type="text" class="form-control" name="min_dist_base_price" id="min_dist_base_price" value="@if(old('min_dist_base_price')) {{ old('min_dist_base_price') }} @elseif(isset($data)) {{$data->min_dist_base_price}} @endif" >
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label for="extra_fee_amount">{{trans('constants.extra_fee_amt')}}</label>
                              <input type="text" name="extra_fee_amount" class="form-control" id="extra_fee_amount" value="@if(old('extra_fee_amount')) {{ old('extra_fee_amount') }} @elseif(isset($data)) {{$data->extra_fee_amount}} @endif">
                            </div>
                          </div>
                        </div>
                        <h3>{{trans('constants.bank_details')}}</h3>
                        <div class="row">
                          <div class="col-md-4">
                             <div class="form-group">
                              <label for="name">Account Name<span style="color: red;"></span></label>
                              <input id="name" type="text" class="form-control" name="account_name" value="@if(isset($data->RestaurantBankDetails->account_name)){{$data->RestaurantBankDetails->account_name}}@endif" >
                            </div>
                             </div>
                             <div class="col-md-4">
                             <div class="form-group">
                              <label for="name">BSB<span style="color: red;"></span></label>
                              <input id="name" type="text" class="form-control" name="account_address" value="@if(isset($data->RestaurantBankDetails->account_address)){{$data->RestaurantBankDetails->account_address}}@endif" >
                            </div>
                             </div>
                             <div class="col-md-4">
                             <div class="form-group">
                              <label for="name">Account Number<span style="color: red;"></span></label>
                              <input id="name" type="text" class="form-control" name="account_no" value="@if(isset($data->RestaurantBankDetails->account_no)){{$data->RestaurantBankDetails->account_no}}@endif" >
                            </div>
                             </div>
                             
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                             <div class="form-group">
                              <label for="name">Bank Name<span style="color: red;"></span></label>
                              <input id="name" type="text" class="form-control" name="bank_name"  value="@if(isset($data->RestaurantBankDetails->bank_name)){{$data->RestaurantBankDetails->bank_name}}@endif" >
                            </div>
                             </div>
                             <div class="col-md-4">
                             <div class="form-group">
                              <label for="name">Branch Name</label>
                              <input id="name" type="text" class="form-control" name="branch_name" value="@if(isset($data->RestaurantBankDetails->branch_name)){{$data->RestaurantBankDetails->branch_name}}@endif" >
                            </div>
                             </div>
                             <div class="col-md-4">
                             <div class="form-group">
                              <label for="name">Branch Address </label>
                              <input id="name" type="text" class="form-control" name="branch_address"  value="@if(isset($data->RestaurantBankDetails->branch_address)){{$data->RestaurantBankDetails->branch_address}}@endif" >
                            </div>
                             </div>
                             
                        </div>                        

                        
                        @php //dd($data->Document); @endphp
                        @if(count($document)>0)
                          <h4>{{trans('constants.doc_upload')}}</h4>
                          @foreach($document->chunk(2) as $chunk)
                            <div class="row">
                              @foreach($chunk as $item)
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label for="description">{{$item->document_name}}</label>
                                    <input type="file" name="document[{{$item->id}}][document]" class="form-control">
                                    @if($item->expiry_date_needed==1)
                                      <label for="description">{{trans('constants.expiry_date')}}</label>
                                      <input type="text" name="document[{{$item->id}}][date]" class="form-control pickadate-selectors picker__input picker__input--active" @isset($data->Document) @foreach($data->Document as $val) @if($val->id==$item->id) value="@if($val->pivot->expiry_date!='0000-00-00') {{ date('d F, Y',strtotime($val->pivot->expiry_date))}} @endif" @endif @endforeach @endisset>
                                    @endif
                                  </div>
                                </div>
                              @endforeach
                            </div>
                          @endforeach
                        @endif
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-actions">
                              <button type="button" class="btn btn-warning mr-1" style="padding: 10px 15px;">
                                <i class="ft-x"></i> Cancel
                                </button>
                              <button class="btn btn-primary mr-1" style="padding: 10px 15px;" onclick="return javascript:form_validation();">
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

          <div class="modal animated slideInRight text-left" id="add_cuisine_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
              <h4 class="modal-title" id="myModalLabel76">Add Cuisines</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form>
                <div class="modal-body">
                    <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                    <div class="form-group">
                      <label for="eventName2">Name</label>
                      <input type="text" class="form-control" name="cuisine_name" id="cuisine_name" required="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="close-model" class="btn btn-warning mr-1 close-modal" data-dismiss="modal" style="padding: 10px 15px;">
                        <i class="ft-x"></i> Cancel
                        </button>
                      <button type="button" id="cuisines_add" class="btn btn-primary mr-1" style="padding: 10px 15px;">
                    <i class="ft-check-square"></i> Add
                      </button>
                </div>
              </form>
          </div>
        </div>
      </div>

@endsection

@section('script') 

<script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>
<script type="text/javascript" src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="http://tarruda.github.com/bootstrap-datetimepicker/assets/js/bootstrap-datetimepicker.pt-BR.js"></script>

<script>
        
        @if(isset($weekdays))
          @if(count($weekdays) !=0)
            var n={{count($weekdays)}};  
          @else
            var n=1;  
          @endif
        @else
          var n=1;  
        @endif

        @if(isset($weekenddays))
          @if(count($weekenddays) !=0)
          var m={{count($weekenddays)}};  
          @else
          var m=1;  
          @endif
        @else
          var m=1;  
        @endif

        let j = n + 1;
        for (let index = 1; index < 20; index++) {
          
          var content = '<div id="disp'+j+'" style="display: none;" class="row everyday">';
              content += '<div class="col-xs-4">';
              content += '<div class="form-group">';
              content += '<label for="hours_opening"></label>';
              content += '<div class="input-group clockpicker">';
              content += '<input type="text" id="timepicker'+j+'" name="weekdays[opening_time][]" value=" 00:00 " required="" width="276" >';
              content += '</div></div></div><div class="col-xs-4"><div class="form-group"><label for="hours_closing"></label>';
              content += '<div class="input-group clockpicker">';
              content += '<input type="text" id="timepicker-c'+j+'" name="weekdays[closing_time][]" value=" 00:00 " required="" width="276" >';
              content += '</div></div></div><div class="col-xs-4">';
              content += '<button type="button" name="remove" id="'+j+'" class="btn btn-danger btn_remove_weekday">X</button>';
              content += '</div>';
          $('.everyday_dynamic').append(content);
          j++;
        }

        let k = m + 1;
        for (let index = 1; index < 20; index++) {
          
          var content = '<div id="disp-c'+k+'" style="display: none;" class="row everyday">';
              content += '<div class="col-xs-4">';
              content += '<div class="form-group">';
              content += '<label for="hours_opening"></label>';
              content += '<div class="input-group clockpicker">';
              content += '<input type="text" id="timepickerr'+k+'" name="weekenddays[opening_time][]" value=" 00:00 " required="" width="276" >';
              content += '</div></div></div><div class="col-xs-4"><div class="form-group"><label for="hours_closing"></label>';
              content += '<div class="input-group clockpicker">';
              content += '<input type="text" id="timepickerr-c'+k+'" name="weekenddays[closing_time][]" value=" 00:00 " required="" width="276" >';
              content += '</div></div></div><div class="col-xs-4">';
              content += '<button type="button" name="remove" id="'+k+'" class="btn btn-danger btn_remove_weekend">X</button>';
              content += '</div>';
          $('.everyday_dynamic1').append(content);
          k++;
        }

        $('#timepickerr0').timepicker({
            uiLibrary: 'bootstrap4'
        });
        $('#timepickerr').timepicker({
            uiLibrary: 'bootstrap4'
        });
        //picker for weekdays
        $('#timepicker').timepicker({
            uiLibrary: 'bootstrap4'
        });
        $('#timepicker0').timepicker({
            uiLibrary: 'bootstrap4'
        });
        

      $("#add").click(function(){  
          n++;  

          // var content = '<div class="row everyday">';
          // content += '<div class="col-xs-4">';
          // content += '<div class="form-group">';
          // content += '<label for="hours_opening">Resturant Opens<span style="color: red;">*</span></label>';
          // content += '<div class="input-group clockpicker">';
          // content += '<input type="text" id="timepicker'+n+'" name="weekdays[opening_time][]" value=" 00:00 " required="" width="276" >';
          // content += '</div></div></div><div class="col-xs-4"><div class="form-group"><label for="hours_closing">Resturant Closes<span style="color: red;">*</span></label>';
          // content += '<div class="input-group clockpicker">';
          // content += '<input type="text" id="timepicker-c'+n+'" name="weekdays[closing_time][]" value=" 00:00 " required="" width="276" >';
          // content += '</div></div></div><div class="col-xs-4">';
          // content += '<button type="button" name="remove" id="'+n+'" class="btn btn-danger btn_remove">X</button>';
          // content += '</div>';
          // $('.everyday_dynamic').append(content);

          var x = document.getElementById("disp"+n);
          x.style.display = "block";
          //$('.everyday_dynamic').append('<div class="row  everyday"><div id="row'+n+'" class="dynamic-added"><div class="col-xs-4"><div class="form-group"><label for="hours_opening">Resturant Opens<span style="color: red;">*</span></label><div class="input-group clockpicker"><div role="wrapper" class="gj-timepicker gj-timepicker-bootstrap gj-unselectable input-group" style="width: 276px;"><input type="text" id="timepicker'+n+'" name="weekdays[opening_time][]" value=" 00:00 " required="" width="276" data-type="timepicker" data-guid="583fd82b-364a-4736-31dd-429265de34cc" data-timepicker="true" class="form-control border" role="input"><span class="input-group-append" role="right-icon"><button class="btn btn-outline-secondary border-left-0" type="button"><i class="gj-icon clock"></i></button></span></div></div></div></div><div class="col-xs-4"><div class="form-group"><label for="hours_closing">Resturant Closes<span style="color: red;">*</span></label><div class="input-group clockpicker"><div role="wrapper" class="gj-timepicker gj-timepicker-bootstrap gj-unselectable input-group" style="width: 276px;"><input type="text" id="timepicker-c'+n+'" name="weekdays[closing_time][]" value=" 00:00 " required="" width="276" data-type="timepicker" data-guid="a3855a6a-102f-c263-3422-0fd64973e225" data-timepicker="true" class="form-control border" role="input"><span class="input-group-append" role="right-icon"><button class="btn btn-outline-secondary border-left-0" type="button"><i class="gj-icon clock"></i></button></span></div></div></div></div><div class=" col-xs-4"><button type="button" name="remove" id="'+n+'" class="btn btn-danger btn_remove">X</button></div></div></div>');  
          
      });
      $('#timepicker2').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c2').timepicker({
          uiLibrary: 'bootstrap4'
      });
          
      $('#timepicker3').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c3').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepicker4').timepicker({
              uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c4').timepicker({
              uiLibrary: 'bootstrap4'
      });

      $('#timepicker5').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c5').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepicker6').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c6').timepicker({
          uiLibrary: 'bootstrap4'
      }); 

      $('#timepicker7').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c7').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepicker8').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c8').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepicker9').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c9').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepicker10').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepicker-c10').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepickerr2').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c2').timepicker({
          uiLibrary: 'bootstrap4'
      });
          
      $('#timepickerr3').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c3').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepickerr4').timepicker({
              uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c4').timepicker({
              uiLibrary: 'bootstrap4'
      });

      $('#timepickerr5').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c5').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepickerr6').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c6').timepicker({
          uiLibrary: 'bootstrap4'
      }); 

      $('#timepickerr7').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c7').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepickerr8').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c8').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepickerr9').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c9').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $('#timepickerr10').timepicker({
          uiLibrary: 'bootstrap4'
      });
      $('#timepickerr-c10').timepicker({
          uiLibrary: 'bootstrap4'
      });

      $(document).on('click', '.btn_remove_weekday', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id).remove();  
           $('#disp'+button_id).remove();  
      });  
      $(document).on('click', '.btn_remove_weekend', function(){  
           var button_id = $(this).attr("id");   
           $('#disp-c'+button_id).remove();  
           var button_id = $(this).attr('test'+"id");   
           $('#rowtest'+button_id).remove();  
      });  



      $('#add1').click(function(){  
          m++;  
          var x = document.getElementById("disp-c"+m);
          x.style.display = "block";
          // $('.everyday_dynamic1').append('<div class="row  everyday"><div id="rowtest'+m+'" class="dynamic-added1"><div class="col-xs-4"><div class="form-group"><label for="hours_opening">Resturant Opens<span style="color: red;">*</span></label><div class="input-group clockpicker"><div role="wrapper" class="gj-timepicker gj-timepicker-bootstrap gj-unselectable input-group" style="width: 276px;"><input type="text" id="timepicker'+m+'" name="weekdays[opening_time][]" value=" 00:00 " required="" width="276" data-type="timepicker" data-guid="583fd82b-364a-4736-31dd-429265de34cc" data-timepicker="true" class="form-control border" role="input"><span class="input-group-append" role="right-icon"><button class="btn btn-outline-secondary border-left-0" type="button"><i class="gj-icon clock"></i></button></span></div></div></div></div><div class="col-xs-4"><div class="form-group"><label for="hours_closing">Resturant Closes<span style="color: red;">*</span></label><div class="input-group clockpicker"><div role="wrapper" class="gj-timepicker gj-timepicker-bootstrap gj-unselectable input-group" style="width: 276px;"><input type="text" id="timepicker'+m+'" name="weekdays[closing_time][]" value=" 00:00 " required="" width="276" data-type="timepicker" data-guid="a3855a6a-102f-c263-3422-0fd64973e225" data-timepicker="true" class="form-control border" role="input"><span class="input-group-append" role="right-icon"><button class="btn btn-outline-secondary border-left-0" type="button"><i class="gj-icon clock"></i></button></span></div></div></div></div><div class=" col-xs-4"><button type="button" name="remove" id="test'+m+'" class="btn btn-danger btn_remove">X</button></div></div></div>');  
          // $('#timepicker'+m).timepicker({
          //     uiLibrary: 'bootstrap4'
          // });
      });  

    </script>
<script type="text/javascript">
      $(function() {
    $('#datetimepicker3').datetimepicker({
      pickDate: false
    });
  });

$(document).ready(function() {
  var city_id = $('#city').val();
  $.ajax({
    url : "{{url('/')}}/admin/getcity_area/"+city_id,
    method : "get",
    success : function (data)
    {
    console.log(data.area);
      if(data.area != '') 
      {
        var area='';
        $.each( data.area, function( key, value ) {
          area += '<option value="'+value.id+'">'+value.area+'</option>';
        });
        $('#area').html(area);
      }
      else
      {
          $('#area').html("");
      }
    }

  });

  $('#cuisines_add').click(function() {
      var name = $("#cuisine_name").val();
      var _token = $("#_token").val();
      var link = document.getElementById('close-model');
      if(name=="")
      {
        alert("Cuisine Name is required!");
      }else
      {
        $.ajax({
          url : "{{url('/')}}/admin/add_cuisine_ajax",
          method : "post",
          data : {cuisine_name:name, _token:_token},
          success : function (data)
          {
            if(data.status ==true) 
            {
              //console.log($cuisines);
                cuisines = '<option value="'+data.details.id+'">'+data.details.name+'</option>';
                $('#cuisines').append(cuisines);
                link.click();
            }
            else
            {
                alert("Cuisines already exist!");
                link.click();
            }
          }

        });
      }
  });

  @if(isset($data))
    @if(in_array(3, $delivery_type))
      $('.dining_count').show();
      @else
      $('.dining_count').hide();
    @endif
  @else
    $('.dining_count').hide();
  @endif

});
  
  function getcity_area()
  {
    var city_id = $('#city').val();
    $.ajax({
      url : "{{url('/')}}/admin/getcity_area/"+city_id,
      method : "get",
      success : function (data)
      {
      console.log(data.area);
        if(data.area != '') 
        {
          var area='';
          $.each( data.area, function( key, value ) {
            area += '<option value="'+value.id+'">'+value.area+'</option>';
          });
          $('#area').html(area);
        }
        else
        {
            $('#area').html("");
        }
      }

    });
  }

  function checkadmincommission(obj)
  {
    if ($(obj).is(":checked"))
    {
      $('#admin_div').show();
      $('#admin_div1').show();
    }else{
      $('#admin_div').hide();
      $('#admin_div1').hide();
      $('#admin_commision').val('0');
    }
  }

  function checkdrivercommission(obj)
  {
    if ($(obj).is(":checked"))
    {
      $('#driver_div').show();
    }else{
      $('#driver_div').hide();
      $('#driver_commision').val('0');
    }
  }

  function checkdeliverycharge(obj)
  {
    if ($(obj).is(":checked"))
    {
      $('#deliverycharge_div').show();
    }else{
      $('#deliverycharge_div').hide();
      $('#driver_base_price').val('0');
      $('$min_dist_base_price').val('0');
      $('$extra_fee_amount').val('0');
    }
  }
  
  function funchecktype()
  {
    if($("#dining").is(":checked")){
      $('.dining_count').show();
    }else{
      $('#dining_count').val('0');
      $('.dining_count').hide();
    }
  }
</script>

  
<script src="{{URL::asset('public/app-assets/vendors/js/forms/select/select2.full.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('public/app-assets/js/scripts/forms/select/form-select2.js')}}" type="text/javascript"></script>
      

<script>
  function form_validation(){

    var home_delivery = document.getElementById('home_delivery').checked;
      var pickup = document.getElementById('pickup').checked;
      var dining = document.getElementById('dining').checked;
      var phone = document.getElementById('phone').value;

      if(!home_delivery && !pickup && !dining)
      {
        $('#delivery_type_error').fadeIn().html('Please select any one of the delivery type').delay(3000).fadeOut('slow');
        return false;
      }else if(isNaN(phone))
      {
        $('#phone_error').fadeIn().html('Please enter only numbers').delay(3000).fadeOut('slow');
        return false;
      }else
      {
        return true;
      }

      // if(iserror == 0)
      // {
      //     document.getElementById("wizard").submit();
      // }
  }
</script>
<script>
function initMap() {
    var lati = document.getElementById('latitude').value;
    var long = document.getElementById('longitude').value;
    var myLatlng = new google.maps.LatLng(Number(lati),Number(long));
    var geocoder = new google.maps.Geocoder();
    var map = new google.maps.Map(document.getElementById('map'), {
      center: {lat: Number(lati), lng: Number(long)},
      zoom: 13
    });
    //{{ ( (isset($data->mobile_number)) ? $data->mobile_number : '') }}
    var input = document.getElementById('searchMapInput');
   // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
   
    var autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.bindTo('bounds', map);
  
    var infowindow = new google.maps.InfoWindow();
     var marker = new google.maps.Marker({
          position: myLatlng,
          map: map,
          draggable:true
         
        });

    autocomplete.addListener('place_changed', function() {
        //infowindow.close();
        marker.setVisible(true);
        var place = autocomplete.getPlace();
    
        /* If the place has a geometry, then present it on a map. */
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);
        }

        marker.setPosition(place.geometry.location);
        marker.setVisible(true);
      
        var address = '';
        if (place.address_components) {
            address = [
              (place.address_components[0] && place.address_components[0].short_name || ''),
              (place.address_components[1] && place.address_components[1].short_name || ''),
              (place.address_components[2] && place.address_components[2].short_name || '')
            ].join(' ');
        }
      
        document.getElementById('latitude').value = place.geometry.location.lat();
        document.getElementById('longitude').value = place.geometry.location.lng();
        infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
        infowindow.open(map, marker);
        
        //check rerstaurant address comes within area selected
        //fun_check_restaurant(place.geometry.location.lat(), place.geometry.location.lng());

        /* Location details */
    });
        // draggabled address /* Start

        google.maps.event.addListener(marker, 'dragend', 
        function(marker){
        var latLng = marker.latLng; 
        currentLatitude = latLng.lat();
        currentLongitude = latLng.lng();

            geocoder.geocode({'latLng': latLng }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                    document.getElementById('searchMapInput').value = results[0].formatted_address;
                    document.getElementById('latitude').value = currentLatitude;
                    document.getElementById('longitude').value = currentLongitude;
                    infowindow.setContent('<div>' + results[0].formatted_address + '<br>');
                    infowindow.open(map, marker);
                    }
                }
            });
        }); 

        // draggabled address /* End
  
}

function fun_check_restaurant(lat=0, lng=0)
{
  var area_id = $('#area').val();
  if(lat==0 && lng==0){
    lat = $('#latitude').val();
    lng = $('#longitude').val();
  }
  if(area_id!='' && area_id!=null)
  {
    $.ajax({
      url : "{{url('/')}}/admin/check_restaurant_address",
      method : "post",
      data : {"_token": "{{ csrf_token() }}","area_id":area_id,"lat":lat,"lng":lng},
      success : function (data)
      {
        console.log(data);
        if(!data)
        {
          $("#address_err").html("Given address not within specified range!");
          $("#searchMapInput").val("");
        }else{
          $("#address_err").html("");
        }
      }

    });
  }else{
    $("#address_err").html("Please select Area!");
    $("#searchMapInput").val("");
  }
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{GOOGLE_API_KEY}}&libraries=places&callback=initMap" async defer></script>

@endsection     
 