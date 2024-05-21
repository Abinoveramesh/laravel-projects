@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')
 <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{ strtoUpper(trans('constants.site_setting')) }}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard" class="brand-font-link-color">{{strtoUpper(trans('constants.dashboard'))}}</a></li>
                <li class="breadcrumb-item"><a href="#" class="brand-font-link-color">{{ strtoUpper(trans('constants.site_setting')) }}</a>
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
                  <h4 class="card-title">&nbsp;</h4>
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
                    <form action="{{url('/')}}/admin/update-setting" class="icons-tab-steps wizard-notification" method="post" enctype="multipart/form-data">
                       <input type="hidden" name="_token" value="{{csrf_token()}}">
                       <input type="hidden" name="type" value="site">
                     <fieldset>
                      <div class="row">
                       <div class="col-md-12">  
                        @if(isset($data['app_name']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.app_name') }} </label>                          
                           <div class="col-md-10">
                               <input type="text" name="app_name" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['app_name'])){{$data['app_name']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['site_logo']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.site_logo') }} </label>                          
                           <div class="col-md-10">
                               <img src="@if(isset($data['site_logo'])){{SPACES_BASE_URL}}{{$data['site_logo']}}@endif" id="site_logo"  style="margin-bottom:20px;"  width="200em">
                               <input type="file" onchange="loadFile(event)" name="site_logo" class="form-control">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['site_favicon']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.site_favicon') }} </label>                          
                           <div class="col-md-10">
                               <img src="@if(isset($data['site_favicon'])){{SPACES_BASE_URL}}{{$data['site_favicon']}} @endif"  style="margin-bottom:20px;"  id="site_favicon" width="100em">
                               <input type="file"  name="site_favicon" class="form-control">
                               

                          </div>
                       </div>
                       @endif
                       @if(isset($data['site_email']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.site_email') }} </label>                          
                           <div class="col-md-10">
                               <input type="text" name="site_email" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['site_email'])){{$data['site_email']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['site_contact']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.site_contact') }} </label>                          
                           <div class="col-md-10">
                               <input type="text" name="site_contact" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['site_contact'])){{$data['site_contact']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['menu_color']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.menu_color') }} </label>                          
                           <div class="col-md-10">
                               <input type="text" name="menu_color" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['menu_color'])){{$data['menu_color']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['highlight_color']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.highlight_color') }} </label>
                           <div class="col-md-10">
                               <input type="text" name="highlight_color" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['highlight_color'])){{$data['highlight_color']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['default_radius']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.default_radius') }} </label>                          
                           <div class="col-md-10">
                               <input type="text" name="default_radius" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['default_radius'])){{$data['default_radius']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['default_unit']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.default_unit') }} </label>
                           <div class="col-md-10">
                               <input type="text" name="default_unit" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['default_unit'])){{$data['default_unit']}}@endif">
                          </div>
                       </div>
                       @endif
                      
                       @if(isset($data['order_prefix']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.order_prefix') }} </label>
                           <div class="col-md-10">
                               <input type="text" name="order_prefix" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['order_prefix'])){{$data['order_prefix']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['email_enable']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.email_enable') }} </label>                          
                           <div class="col-md-10">
                            <label class="radio-inline">
                                <input type="radio" value="0" name="email_enable"  @if($data['email_enable']==0) checked="" @endif required="" >No
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="1" name="email_enable"   @if($data['email_enable']==1) checked="" @endif required="" >Yes
                            </label>
                          </div>
                       </div>
                       @endif
                       @if(isset($data['sms_enable']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.sms_enable') }} </label>                          
                           <div class="col-md-10">
                            <label class="radio-inline">
                                <input type="radio" value="0" name="sms_enable" @if($data['sms_enable']==0) checked="" @endif>No
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="1" name="sms_enable" @if($data['sms_enable']==1) checked="" @endif >Yes
                            </label>

                          </div>
                       </div>
                       @endif
                       @if(isset($data['time_zone']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.time_zone') }} </label>                          
                           <div class="col-md-10">
                              <select name="time_zone" id="" class="form-control" required="">
                               <option value="Asia/Kolkata" @if($data['time_zone']=='Asia/Kolkata') selected="" @endif> {{$data['time_zone']}} </option>
                              </select>
                          </div>
                       </div>
                       @endif
                       @if(isset($data['country_currency']))
                       <div class="form-group row">                        
                           <label class="col-md-2">Country Currency</label>                          
                           <div class="col-md-10">
                              <select name="country_currency" id="" class="form-control" required="">
                              <option value selected="selected" disabled="disabled"></option>
                                    <option value="{{ $data['country_currency'] }}" selected = "{{$data['country_currency']}}">{{$data['country_currency']}}</option>
                              </select>
                          </div>
                       </div>
                       @endif
                       @if(isset($data['provider_timeout']))
                         <div class="form-group row">                        
                           <label class="col-md-2">Provider Timeout (In Seconds) </label>
                           <div class="col-md-10">
                               <input type="text" name="provider_timeout" class="form-control" placeholder="Title of the message" required="" value="@if(isset($data['provider_timeout'])){{$data['provider_timeout']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['idel_time']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.idel_time') }} (In Minutes)</label>
                           <div class="col-md-10">
                               <input type="text" name="idel_time" class="form-control" placeholder="{{__('idel_time')}}" required="" value="@if(isset($data['idel_time'])){{$data['idel_time']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['loyalty_point']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.loyalty_point') }} (Per {{DEFAULT_CURRENCY}})</label>
                           <div class="col-md-10">
                               <input type="text" name="loyalty_point" class="form-control" placeholder="{{__('loyalty_point')}}" required="" value="@if(isset($data['loyalty_point'])){{$data['loyalty_point']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['Maximum_loyalty_points']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.Maximum_loyalty_points') }}</label>
                           <div class="col-md-10">
                               <input type="text" name="Maximum_loyalty_points" class="form-control" placeholder="{{__('Maximum_loyalty_points')}}" required="" value="@if(isset($data['Maximum_loyalty_points'])){{$data['Maximum_loyalty_points']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['loyalty_amount']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.loyalty_amount') }}</label>
                           <div class="col-md-10">
                               <input type="text" name="loyalty_amount" class="form-control" placeholder="{{__('loyalty_amount')}}" required="" value="@if(isset($data['loyalty_amount'])){{$data['loyalty_amount']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['user_ios_delete_key']))
                       <div class="form-group row">                        
                           <label class="col-md-2">User IOS Delete Account</label>                          
                           <div class="col-md-10">
                              <select name="user_ios_delete_key" id="" class="form-control">
                              <option value="1" @if($data['user_ios_delete_key']==1)selected @endif>TRUE</option>
                               <option value="0" @if($data['user_ios_delete_key']==0)selected @endif>FALSE</option>
                              </select>
                          </div>
                       </div>
                       @endif
                       @if(isset($data['rider_ios_delete_key']))
                       <div class="form-group row">                        
                           <label class="col-md-2">Rider IOS Delete Account</label>                          
                           <div class="col-md-10">
                              <select name="rider_ios_delete_key" id="" class="form-control">
                               <option value="1" @if($data['rider_ios_delete_key']==1)selected @endif>TRUE</option>
                               <option value="0" @if($data['rider_ios_delete_key']==0)selected @endif>FALSE</option>
                              </select>
                          </div>
                       </div>
                       @endif
                       @if(isset($data['admin_commission']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.admin_commission') }} %</label>
                           <div class="col-md-10">
                               <input type="number" name="admin_commission" class="form-control" placeholder="Enter Admin Commission in %" required="" value="@if(isset($data['admin_commission'])){{$data['admin_commission']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['admin_gst']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.admin_gst') }} %</label>
                           <div class="col-md-10">
                               <input type="number" name="admin_gst" class="form-control" placeholder="Enter Admin GST in %" required="" value="@if(isset($data['admin_gst'])){{$data['admin_gst']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['restaurant_commission']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.gst') }} %</label>
                           <div class="col-md-10">
                               <input type="number" name="restaurant_commission" class="form-control" placeholder="Enter Restaurant Commission in %" required="" value="@if(isset($data['restaurant_commission'])){{$data['restaurant_commission']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['user_toll_number']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.user_toll_number') }}</label>
                           <div class="col-md-10">
                               <input type="number" name="user_toll_number" class="form-control" placeholder="Enter User Toll Number in %" required="" value="@if(isset($data['user_toll_number'])){{$data['user_toll_number']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['rider_toll_number']))
                         <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.rider_toll_number') }}</label>
                           <div class="col-md-10">
                               <input type="number" name="rider_toll_number" class="form-control" placeholder="Enter Rider Toll Number in %" required="" value="@if(isset($data['rider_toll_number'])){{$data['rider_toll_number']}}@endif">
                          </div>
                       </div>
                       @endif
                       @if(isset($data['ccavenue_payment']))
                       <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.ccavenue_payment') }}</label>                          
                           <div class="col-md-10">
                              <select name="ccavenue_payment" id="ccavenue_payment" class="form-control">
                               <option value="1" @if($data['ccavenue_payment']==1)selected @endif>TEST</option>
                               <option value="2" @if($data['ccavenue_payment']==2)selected @endif>LIVE</option>
                              </select>
                          </div>
                       </div>
                       @endif
                       @if(isset($data['ccavenue_refund']))
                       <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.ccavenue_refund') }}</label>                          
                           <div class="col-md-10">
                              <select name="ccavenue_refund" id="ccavenue_refund" class="form-control">
                               <option value="1" @if($data['ccavenue_refund']==1)selected @endif>TEST</option>
                               <option value="2" @if($data['ccavenue_refund']==2)selected @endif>LIVE</option>
                              </select>
                          </div>
                       </div>
                       @endif
                       @if(isset($data['b2biz_payment']))
                       <div class="form-group row">                        
                           <label class="col-md-2">{{ trans('constants.b2biz_payment') }}</label>                          
                           <div class="col-md-10">
                              <select name="b2biz_payment" id="b2biz_payment" class="form-control">
                               <option value="1" @if($data['b2biz_payment']==1)selected @endif>TEST</option>
                               <option value="2" @if($data['b2biz_payment']==2)selected @endif>LIVE</option>
                              </select>
                          </div>
                       </div>
                       @endif
                       <div class="form-group row">
                        <label class="col-md-2"></label>
                          <div class="col-md-10">
                            <button type="submit"  class="btn success-button-style mr-1" style="padding: 10px 15px;">Update Settings</button> &nbsp;
                             <!-- <button type="button" class="btn btn-success mr-1" style="padding: 10px 15px;">Schedule Push</button> -->
                        </div>
                       </div>
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
   

<!-- <script type="text/javascript">  
  var loadFile = function(event) {
  var output = document.getElementById('site_logo');
  output.src = URL.createObjectURL(event.target.files[0]);
};
var _URL = window.URL || window.webkitURL;

$("#site_logo").change(function(e) {
    var file, img;


    if ((file = this.files[0])) {
        img = new Image();
        img.onload = function() {
        if(this.width <= 360 && this.height <= 640){
        
            alert(this.width + " " + this.height);
        }else{
          alert('invalid file size');
        }
        };
        img.onerror = function() {
            alert( "not a valid file: " + file.type);
        };
        img.src = _URL.createObjectURL(file);


    }

});
</script> -->

<script type="text/javascript">  
  var loadFile1 = function(event) {
  var fileUpload = document.getElementById("fileUpload");
 
    //Check whether the file is valid Image.
    var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(.jpg|.png|.gif)$");
    if (regex.test(fileUpload.value.toLowerCase())) {
 
        //Check whether HTML5 is supported.
        if (typeof (fileUpload.files) != "undefined") {
            //Initiate the FileReader object.
            var reader = new FileReader();
            //Read the contents of Image File.
            reader.readAsDataURL(fileUpload.files[0]);
            reader.onload = function (e) {
                //Initiate the JavaScript Image object.
                var image = new Image();
 
                //Set the Base64 string return from FileReader as source.
                image.src = e.target.result;
                       
                //Validate the File Height and Width.
                image.onload = function () {
                    var height = this.height;
                    var width = this.width;
                    if (height > 100 || width > 100) {
                        alert("Height and Width must not exceed 100px.");
                        return false;
                    }
                    alert("Uploaded image has valid Height and Width.");
                    return true;
                };
 
            }
        } else {
            alert("This browser does not support HTML5.");
            return false;
        }
    } else {
        alert("Please select a valid Image file.");
        return false;
    }

}
</script>

<!-- <script type="text/javascript">
function Upload() {
    //Get reference of FileUpload.
    var fileUpload = document.getElementById("fileUpload");
 
    //Check whether the file is valid Image.
    var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+(.jpg|.png|.gif)$");
    if (regex.test(fileUpload.value.toLowerCase())) {
 
        //Check whether HTML5 is supported.
        if (typeof (fileUpload.files) != "undefined") {
            //Initiate the FileReader object.
            var reader = new FileReader();
            //Read the contents of Image File.
            reader.readAsDataURL(fileUpload.files[0]);
            reader.onload = function (e) {
                //Initiate the JavaScript Image object.
                var image = new Image();
 
                //Set the Base64 string return from FileReader as source.
                image.src = e.target.result;
                       
                //Validate the File Height and Width.
                image.onload = function () {
                    var height = this.height;
                    var width = this.width;
                    if (height > 100 || width > 100) {
                        alert("Height and Width must not exceed 100px.");
                        return false;
                    }
                    alert("Uploaded image has valid Height and Width.");
                    return true;
                };
 
            }
        } else {
            alert("This browser does not support HTML5.");
            return false;
        }
    } else {
        alert("Please select a valid Image file.");
        return false;
    }
}
</script> -->

    @endsection     
 