<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="pizza, delivery food, fast food, sushi, take away, chinese, italian food">
    <meta name="description" content="">
    <meta name="author" content="Ansonika">
    <title>{{APP_NAME}}</title>
      <!-- FAV ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- BASE CSS -->
    <link href="{{URL::asset('public/login-assets/LOGIN/css/base.css')}}" rel="stylesheet">
    <link rel="shortcut icon" type="image/x-icon" href="{{URL::asset(SPACES_BASE_URL.SITE_FAVICON)}}">
     
</head>
<style type="text/css">
    .btn.btn-submit {
    width: 100%;
    margin-top: 30px;
    color: #191b19;
    padding: 10px;
    background: #fff;
    font-weight: 600;
    outline: 0;
    -webkit-transition: all .2s ease;
    transition: all .2s ease;
    font-size: 16px;
}
.password-pos{
    position: relative;
}
.password-icon-pos{
    position: absolute;
    top: 12%;
    right: 4%;
    transform: translate(-4%, -20%);
}
</style>

<body style="background-image: url('public/uploads/login-background1.jpg') !important;position: relative;background-size: cover;">

    <div id="preloader">
        <div class="sk-spinner sk-spinner-wave" id="status">
            <div class="sk-rect1"></div>
            <div class="sk-rect2"></div>
            <div class="sk-rect3"></div>
            <div class="sk-rect4"></div>
            <div class="sk-rect5"></div>
        </div>
    </div><!-- End Preload -->
    
   
    <!-- End Header =============================================== -->
    
    <!-- SubHeader =============================================== -->
    <section>
     
        <div class="col-md-12" >
           <div class="modal-popup col-md-6 col-md-offset-3" style="margin-top:10px; margin-bottom:10px;">
            <h3><B>WELCOME TO {{strtoUpper(APP_NAME)}} ADMIN</B></h3>
            <h3><B>LOGIN</B></h3>
             <div style="font-size:17px;" >
                  @if(session()->has('success'))
                        <div class="card" style="color:green;"><!-- gainsboro -->
                            <div class="card-content">
                                <strong>{{ Session::get('success')}}</strong>
                            </div>
                        </div>
                    @endif
                    
                    @if(session()->has('error'))
                        <div class="card" style="color:red;"><!-- #f97c7c -->
                            <div class="card-content">
                                <strong>{{ Session::get('error')}}</strong>
                            </div>
                        </div>
                    @endif
                   </div>    
           <form action="{{url('/')}}/admin/login" method="post" class="popup-form" id="myLogin">
               <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <!-- <div class="login_icon"><i class="icon_lock_alt"></i></div> -->
                    <img src="{{URL::asset(SPACES_BASE_URL.SITE_LOGO)}}" width="150">
                    
                    <input type="text" class="form-control form-white" placeholder="Username" style="border-color:black" name="email" required="">
                    <i class="fa fa-user font-large-8 float-right" style="margin-top:-45px;margin-right:15px"></i>

                   <div class="form-group password-pos">
                   <input type="Password" id="password" class="form-control form-white" placeholder="Password" style="border-color:black" name="password" required="">
                   <div class="password-icon-pos">
                   <span><i class="fa fa-eye " id="eye_open" ></i>
                    <i class="fa fa-eye-slash " id="eye_close" ></i></span>
                   </div>
                    <!-- <div class="text-left">
                        <a href="#">Forgot Password?</a>
                    </div> -->
                    <button type="submit" class="btn btn-submit"style="border-color:black">Submit</button>
                </form>
                </div>
        </div><!-- End sub_content -->
    
   
   
    </section><!-- End Header video -->
    <!-- End SubHeader ============================================ -->
<!-- COMMON SCRIPTS -->
<script src="{{URL::asset('public/login-assets/LOGIN/js/jquery-2.2.4.min.js')}}"></script>
<script src="{{URL::asset('public/login-assets/LOGIN/js/common_scripts_min.js')}}"></script>
<script src="{{URL::asset('public/login-assets/LOGIN/js/functions.js')}}"></script>
<script src="{{URL::asset('public/login-assets/LOGIN/assets/validate.js')}}"></script>

<!-- SPECIFIC SCRIPTS -->
<script src="{{URL::asset('public/login-assets/LOGIN/js/video_header.js')}}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
        $("#eye_open").hide();
        $("#eye_close").click(function(){
              
            $("#eye_close").hide();
            $("#eye_open").show();
            $("#password").attr('type','text');
        });
        $("#eye_open").click(function(){
            $("#eye_open").hide();
            $("#eye_close").show();
            $("#password").attr('type','password');
        });

        'use strict';
        HeaderVideo.init({
            container: $('.header-video'),
            header: $('.header-video--media'),
            videoTrigger: $("#video-trigger"),
            autoPlayVideo: true
    });    

});
</script>

</body>

<!-- Mirrored from www.ansonika.com/quickfood/ by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 22 Aug 2018 13:44:46 GMT -->
</html>