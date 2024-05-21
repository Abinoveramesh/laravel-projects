@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')
<link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
<div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">CHANGE PASSWORD</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard" class="colorname">DASHBOARD</a>
                </li>
                <li class="breadcrumb-item"><a href="#" class="colorname">CHANGE PASSWORD</a>
                </li>
              </ol>
            </div>
          </div>
        </div>
        
      </div>
               <div class="card">
                <div class="card-content collapse show">
                  <div class="card-body">
                    <form action="{{url('/')}}/admin/update_password" id="myForm" method="post" class="icons-tab-steps wizard-notification" enctype="multipart/form-data">
                       <input type="hidden" name="_token" value="{{csrf_token()}}">
                       
                      <fieldset>
                        <div class="row">

                              
                            <div class="form-group col-md-12">
                              <label for="old_pswd">Old Password <span style="color: red;">*</span></label>
                              <input type="password" class="form-control" name="old_pswd" required  id="old_pswd">
                              <span><i class="fa fa-eye float-right" id="eye_open" style="margin-top:-25px; margin-right:15px"></i>
                             <i class="fa fa-eye-slash float-right" id="eye_close" style="margin-top:-25px; margin-right:15px"></i></span>
                            </div>
                        </div>
                        <div class="row">
                             <div class="form-group col-md-12">
                              <label for="new_pswd">New Password <span style="color: red;">*</span></label>
                              <input type="password" class="form-control" name="new_pswd" id="password" required  id="new_pswd">
                              <span><i class="fa fa-eye float-right" id="eye_open1" style="margin-top:-25px; margin-right:15px"></i>
                             <i class="fa fa-eye-slash float-right" id="eye_close1" style="margin-top:-25px; margin-right:15px"></i></span>
                            </div>
                        </div>
                        <div class="row">
                             <div class="form-group col-md-12">
                              <label for="confirm_pswd">Confirm Password <span style="color: red;">*</span></label>
                              <input type="password" class="form-control" id="confirm_password" name="confirm_pswd" required>
                              <span><i class="fa fa-eye float-right" id="eye_open2" style="margin-top:-25px; margin-right:15px"></i>
                              <i class="fa fa-eye-slash float-right" id="eye_close2" style="margin-top:-25px; margin-right:15px"></i></span>
                              <span class="error" id="message" style="color: red"></span>
                            </div>
                        </div>
                        <div class="row">
                        	<div class="form-group col-md-2">
                        		<button class="btn mr-1 btncolorname" style="padding: 10px 15px;" id="submit" onclick="javascript:return validate();">Submit</button>
                        	</div>
                        </div>
                           
                        </div>
                      </fieldset>
                    </form>
                  </div>
                </div>
              </div>

@endsection

@section('script')
<script type="text/javascript">
  function validate() {
  if($('#confirm_password').val() == ""){
    $('#message').fadeIn().html('Confirm Password feild is required').delay(3000).fadeOut('slow');
    return false;
  }
    if ($('#password').val() == $('#confirm_password').val()) {
    
     return true;
    
    } else 
    $('#message').fadeIn().html('Password and Confirm Password does not match').delay(3000).fadeOut('slow');
   return false;
    
    
  }
 
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  $(document).ready(function() {
    $("#eye_open").hide();
    $("#eye_close").click(function(){
        $("#eye_close").hide();
        $("#eye_open").show();
        $("#old_pswd").attr('type','text');
    });
    $("#eye_open").click(function(){
        $("#eye_open").hide();
        $("#eye_close").show();
        $("#old_pswd").attr('type','password');
    });
    $("#eye_open1").hide();
    $("#eye_close1").click(function(){
        $("#eye_close1").hide();
        $("#eye_open1").show();
        $("#password").attr('type','text');
    });
    $("#eye_open1").click(function(){
        $("#eye_open1").hide();
        $("#eye_close1").show();
        $("#password").attr('type','password');
    });
    $("#eye_open2").hide();
    $("#eye_close2").click(function(){
        $("#eye_close2").hide();
        $("#eye_open2").show();
        $("#confirm_password").attr('type','text');
    });
    $("#eye_open2").click(function(){
        $("#eye_open2").hide();
        $("#eye_close2").show();
        $("#confirm_password").attr('type','password');
    });
});
</script>
@endsection