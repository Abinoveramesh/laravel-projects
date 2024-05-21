<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
  <!-- <link rel="stylesheet" type="text/css" href="https://foodie.deliveryventure.com/assets/admin/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://foodie.deliveryventure.com/assets/admin/fonts/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://foodie.deliveryventure.com/assets/admin/plugins/clockpicker/dist/bootstrap-clockpicker.min.css"> -->

  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta name="description" content="{{APP_NAME}} admin is super flexible, powerful, responsive bootstrap 4 admin panel.">
  <meta name="keywords" content="{{APP_NAME}} admin is super flexible, powerful, responsive bootstrap 4 admin panel.">
  <!-- <meta name="author" content="PIXINVENT"> -->
  <title>{{APP_NAME}}</title>
  <link rel="icon" href="{{URL::asset(SPACES_BASE_URL.SITE_FAVICON)}}">
  <!-- <title>@yield('title')</title> -->
  <link rel="apple-touch-icon" href="{{URL::asset(SPACES_BASE_URL.SITE_FAVICON)}}">
  <link rel="shortcut icon" type="image/x-icon" href="{{URL::asset(SPACES_BASE_URL.SITE_FAVICON)}}">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Quicksand:300,400,500,700"
  rel="stylesheet">
  <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css"
  rel="stylesheet">
  <!-- BEGIN VENDOR CSS-->
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/css/vendors.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/vendors/css/forms/selects/select2.min.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/vendors/css/pickers/daterange/daterangepicker.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/vendors/css/pickers/pickadate/pickadate.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/vendors/css/tables/datatable/datatables.min.css')}}">
  <!-- END VENDOR CSS-->
  <!-- BEGIN MODERN CSS-->
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/css/app.css')}}">
  <!-- END MODERN CSS-->
  <!-- BEGIN Page Level CSS-->
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/css/core/menu/menu-types/vertical-menu-modern.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/css/core/colors/palette-gradient.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/vendors/css/charts/jquery-jvectormap-2.0.3.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/vendors/css/charts/morris.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/fonts/simple-line-icons/style.css')}}">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/css/plugins/pickers/daterange/daterange.css')}}">
  <!-- END Page Level CSS-->
  <!-- BEGIN Custom CSS-->
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/assets/css/style.css')}}">
  <!-- END Custom CSS-->
  
  <!-- BEGIN Page Level CSS-->
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/css/view-orders.css')}}">
  <!-- END Page Level CSS-->

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="{{URL::asset('public/app-assets/vendors/css/forms/toggle/switchery.min.css')}}">
  
  <script type="text/javascript"
     src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js">
    </script> 

   <!--  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"> -->

</head>
<style type="text/css">
  .main-menu.menu-dark .navigation {
    background: {{MENU_COLOR}} !important;
}
.main-menu.menu-dark .navigation > li.open > a {
    background: {{HIGHLIGHT_COLOR}} !important;
}
.main-menu.menu-dark .navigation > li > ul {
    background: {{MENU_COLOR}}  !important;
}
.navbar-semi-dark .navbar-header {
    background: {{MENU_COLOR}}  !important;
}
.main-menu.menu-dark {
    background: {{MENU_COLOR}}  !important;
}
.error_message {
  color: red;
}
.breadcrumb {
  color : #0819c4;
}
.colorname {
  color: #0819c4 !important; 
}
.btncolorname{
  background-color:#0819c4;
  color:#FFFFFF;
}
.deletebtncolor{
  background-color:red;
  color:red;
}
</style>
<audio id="myAudio">
    <source src="{{url('/')}}/public/sound/notification.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
  </audio>

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/1.3.5/socket.io.js"></script>
<body class="vertical-layout vertical-menu-modern 2-columns   menu-expanded fixed-navbar"
data-open="click" data-menu="vertical-menu-modern" data-col="2-columns">
  <!-- fixed-top-->
  <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-semi-dark navbar-shadow">
    <div class="navbar-wrapper">
      <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
          <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#"><i class="ft-menu font-large-1"></i></a></li>
          <li class="nav-item mr-auto">
            <a class="navbar-brand" href="#">
              <img class="brand-logo" alt="{{APP_NAME}}" src="{{URL::asset(SPACES_BASE_URL.SITE_FAVICON)}}">
              <h3 class="brand-text">{{APP_NAME}}</h3>
            </a>
          </li>
          <li class="nav-item d-none d-md-block float-right"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="toggle-icon ft-toggle-right font-medium-3 white" data-ticon="ft-toggle-right"></i></a></li>
          <li class="nav-item d-md-none">
            <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i class="la la-ellipsis-v"></i></a>
          </li>
        </ul>
      </div>
      <div class="navbar-container content">
        <div class="collapse navbar-collapse" id="navbar-mobile">
          <ul class="nav navbar-nav mr-auto float-left">
            
          </ul>
          <ul class="nav navbar-nav float-right">
            <li class="dropdown dropdown-user nav-item">
              <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
                <span class="mr-1">{{trans('constants.welcome_title')}}
                  <span class="user-name text-bold-700">{{ucfirst(session()->get('user_name'))}}</span>
                </span>
                <span class="avatar avatar-online">
                  <img src="{{URL::asset('public/app-assets/images/portrait/small/avatar-s-19.png')}}" alt="avatar"><i></i></span>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                <!-- <a class="dropdown-item" href="#"><i class="ft-user"></i> Edit Profile</a> -->
                <!-- <a class="dropdown-item" href="#"><i class="ft-mail"></i> My Inbox</a>
                <a class="dropdown-item" href="#"><i class="ft-check-square"></i> Task</a>
                <a class="dropdown-item" href="#"><i class="ft-message-square"></i> Chats</a> -->
                <!-- <div class="dropdown-divider"></div> -->
                <a class="dropdown-item" href="{{url('/')}}/admin/change_password"><i class="fa fa-key" aria-hidden="true"></i>Change Password</a>
                <a class="dropdown-item" href="{{url('/')}}/admin/logout"><i class="ft-power"></i> {{trans('constants.logout_txt')}}</a>
              </div>
            </li>
          <!--   <li class="dropdown dropdown-language nav-item"><a class="dropdown-toggle nav-link" id="dropdown-flag" href="#" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false"><i class="flag-icon flag-icon-gb"></i><span class="selected-language"></span></a>
              <div class="dropdown-menu" aria-labelledby="dropdown-flag"><a class="dropdown-item" href="#"><i class="flag-icon flag-icon-gb"></i> English</a>
                <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-fr"></i> French</a>
                <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-cn"></i> Chinese</a>
                <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-de"></i> German</a>
              </div>
            </li> -->
 <!--            <li class="dropdown dropdown-notification nav-item">
              <a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon ft-bell"></i>
                <span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow">5</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                <li class="dropdown-menu-header">
                  <h6 class="dropdown-header m-0">
                    <span class="grey darken-2">Notifications</span>
                  </h6>
                  <span class="notification-tag badge badge-default badge-danger float-right m-0">5 New</span>
                </li>
                <li class="scrollable-container media-list w-100">
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-plus-square icon-bg-circle bg-cyan"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">You have new order!</h6>
                        <p class="notification-text font-small-3 text-muted">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">30 minutes ago</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-download-cloud icon-bg-circle bg-red bg-darken-1"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading red darken-1">99% Server load</h6>
                        <p class="notification-text font-small-3 text-muted">Aliquam tincidunt mauris eu risus.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Five hour ago</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-alert-triangle icon-bg-circle bg-yellow bg-darken-3"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading yellow darken-3">Warning notifixation</h6>
                        <p class="notification-text font-small-3 text-muted">Vestibulum auctor dapibus neque.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Today</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-check-circle icon-bg-circle bg-cyan"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">Complete the task</h6>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last week</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left align-self-center"><i class="ft-file icon-bg-circle bg-teal"></i></div>
                      <div class="media-body">
                        <h6 class="media-heading">Generate monthly report</h6>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Last month</time>
                        </small>
                      </div>
                    </div>
                  </a>
                </li>
                <li class="dropdown-menu-footer"><a class="dropdown-item text-muted text-center" href="javascript:void(0)">Read all notifications</a></li>
              </ul>
            </li> -->
 <!--            <li class="dropdown dropdown-notification nav-item">
              <a class="nav-link nav-link-label" href="#" data-toggle="dropdown"><i class="ficon ft-mail">             </i></a>
              <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                <li class="dropdown-menu-header">
                  <h6 class="dropdown-header m-0">
                    <span class="grey darken-2">Messages</span>
                  </h6>
                  <span class="notification-tag badge badge-default badge-warning float-right m-0">4 New</span>
                </li>
                <li class="scrollable-container media-list w-100">
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left">
                        <span class="avatar avatar-sm avatar-online rounded-circle">
                          <img src="{{URL::asset('public/app-assets/images/portrait/small/avatar-s-19.png')}}" alt="avatar"><i></i></span>
                      </div>
                      <div class="media-body">
                        <h6 class="media-heading">Margaret Govan</h6>
                        <p class="notification-text font-small-3 text-muted">I like your portfolio, let's start.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Today</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left">
                        <span class="avatar avatar-sm avatar-busy rounded-circle">
                          <img src="{{URL::asset('public/app-assets/images/portrait/small/avatar-s-2.png')}}" alt="avatar"><i></i></span>
                      </div>
                      <div class="media-body">
                        <h6 class="media-heading">Bret Lezama</h6>
                        <p class="notification-text font-small-3 text-muted">I have seen your work, there is</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Tuesday</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left">
                        <span class="avatar avatar-sm avatar-online rounded-circle">
                          <img src="{{URL::asset('public/app-assets/images/portrait/small/avatar-s-3.png')}}" alt="avatar"><i></i></span>
                      </div>
                      <div class="media-body">
                        <h6 class="media-heading">Carie Berra</h6>
                        <p class="notification-text font-small-3 text-muted">Can we have call in this week ?</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">Friday</time>
                        </small>
                      </div>
                    </div>
                  </a>
                  <a href="javascript:void(0)">
                    <div class="media">
                      <div class="media-left">
                        <span class="avatar avatar-sm avatar-away rounded-circle">
                          <img src="{{URL::asset('public/app-assets/images/portrait/small/avatar-s-6.png')}}" alt="avatar"><i></i></span>
                      </div>
                      <div class="media-body">
                        <h6 class="media-heading">Eric Alsobrook</h6>
                        <p class="notification-text font-small-3 text-muted">We have project party this saturday.</p>
                        <small>
                          <time class="media-meta text-muted" datetime="2015-06-11T18:29:20+08:00">last month</time>
                        </small>
                      </div>
                    </div>
                  </a>
                </li>
                <li class="dropdown-menu-footer"><a class="dropdown-item text-muted text-center" href="javascript:void(0)">Read all messages</a></li>
              </ul>
            </li> -->
          </ul>
        </div>
      </div>
    </div>
  </nav>
  <div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">
     @include('includes.navBar')
 </div>
 <div class="app-content content">
    <div class="row">
      <div class="col-md-1">
      </div>
      <div class="col-md-10">
          @if($errors->any())
            <p class="alert alert-danger" style="margin-top: 20px;">{{ $errors->first() }}</p>
          @endif
          @if(Session::has('error'))
                <p class="alert alert-danger" style="margin-top: 20px;">{{ Session::get('error') }}</p>
            @endif

            @if(Session::has('success'))
                <p class="alert alert-success" style="margin-top: 20px;">{{ Session::get('success') }}</p>
            @endif
      </div>
      <div class="col-md-5"><br>
        <!--FOR NEW ORDER NOTIFICATION ALERT  -->
         <div class="card" id="notification">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-danger rounded-left">
                            <i class="fa fa-user-circle-o font-small-3 text-white"></i>
                        </div>
                        <div class="py-1 px-2 media-body">
                            <h5 class="danger">New Order Received. To view click <a href="{{url('/')}}/admin/orders/new">Here</a> <a onclick="myFunction()" style="float: right"> x </a></h5>
                           
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" id="driver_notification">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-danger rounded-left">
                            <i class="fa fa-user-circle-o font-small-3 text-white"></i>
                        </div>
                        <div class="py-1 px-2 media-body">
                            <h5 class="danger">New Driver Registered. To view click <a href="{{url('/')}}/admin/pending_drivers">Here</a> <a onclick="myFunction()" style="float: right"> x </a></h5>
                           
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" id="restaurant_notification">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-danger rounded-left">
                            <i class="fa fa-user-circle-o font-small-3 text-white"></i>
                        </div>
                        <div class="py-1 px-2 media-body">
                            <h5 class="danger">New Restaurant Registered. To view click <a href="{{url('/')}}/admin/pending_restaurant">Here</a> <a onclick="myFunction()" style="float: right"> x </a></h5>
                           
                        </div>
                    </div>
                </div>
            </div>
             <div class="card" id="driver-notification">
                <div class="card-content">
                    <div class="media align-items-stretch">
                        <div class="p-2 text-center bg-danger rounded-left">
                            <i class="fa fa-user-circle-o font-small-3 text-white"></i>
                        </div>
                        <div class="py-1 px-2 media-body">
                            <h5 class="danger">No Drivers available for order :<a href="{{url('/')}}/admin/orders/new" id="order_id"></a> <a onclick="myFunction()" style="float:right"> x </a></h5>
                           
                        </div>
                    </div>
                </div>
            </div>
             <!--FOR NEW ORDER NOTIFICATION ALERT ENDS  -->
      </div>
    </div>
    
  <!--FOR NEW ORDER NOTIFICATION ALERT  -->
<script type="text/javascript">
    document.getElementById('notification').hidden = true;
    document.getElementById('driver_notification').hidden = true;
    document.getElementById('restaurant_notification').hidden = true;
    document.getElementById('driver-notification').hidden = true;
</script>
 <!--FOR NEW ORDER NOTIFICATION ALERT ENDS -->
     @yield('content')
</div>
@if(Session::get('role') == 2)
{
<!-- Start of LiveChat (www.livechatinc.com) code -->
<script>
    window.__lc = window.__lc || {};
    window.__lc.license = 11974932;
    ;(function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechatinc.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
</script>
<noscript><a href="https://www.livechatinc.com/chat-with/11974932/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechatinc.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>
<!-- End of LiveChat code -->
}
@endif
@include('includes.footer')

@yield('script')

<script>
  console.log(window.location.hostname);
  if(window.location.hostname == "161.35.142.163/food-web/truely-food-restaurant/")
  {
    window.location = 'https://161.35.142.163/food-web/truely-food-restaurant/';
  }
</script>
<!--FOR NEW ORDER NOTIFICATION ALERT  -->
<script>
function myFunction() {
  document.getElementById('notification').hidden = true;
  document.getElementById('driver_notification').hidden = true;
  document.getElementById('restaurant_notification').hidden = true;
  document.getElementById('driver-notification').hidden = true;
  play_wav();
}

$.ajax({
  type: "GET",
  url: "{{url('/')}}/admin/get_orders_count",
  success: function(data){
    console.log(data);
    if(data.new_orders!=0) $('#new_order').html(data.new_orders);
    if(data.processing_orders!=0) $('#processing_order').html(data.processing_orders);
    if(data.pickup_orders!=0) $('#order_pickup').html(data.pickup_orders);
    if(data.delivered_orders!=0) $('#deliverd_order').html(data.delivered_orders);
    if(data.cancelled_orders!=0) $('#cancelled_order').html(data.cancelled_orders);
    if(data.pickuporder!=0) $('#pickup_order').html(data.pickuporder);
    if(data.diningorder!=0) $('#dining_order').html(data.diningorder);
  }
});

var isStop = false;
var myaudio = document.getElementById("myAudio"); 
var refreshIntervalId;

   function WebSocketTest() { 

      var URL_SERVER = "https://api-admin.truely.info:8081/"; 
      var socket = io(URL_SERVER,{"path":"","transports":["websocket"]});   
      socket.on('connect', function() {
        if (socket) {
          console.log("Connected.");
          socket.on('message', function(evt){
              console.log("income message: ", evt);
              try {
                isStop = false;
                var received_msg = JSON.parse(evt);
                console.log("Message is received..."+evt.data);
                let id = received_msg.msg;
                console.log("Message is received..."+evt.data);
                if (id == {{Session::get('userid')}}) {
                  // call the alarm function
                  // document.getElementById('mySound').play();
                  // var audio = new Audio('{{url('/')}}/public/sound/to-the-point.mp3');
                  // var playPromise = audio.play();
                  if(myaudio.pause){
                    refreshIntervalId = setInterval(function(){ 
                      if(!isStop){
                        console.log(myaudio);
                        // document.querySelector('audio').play();
                          //myaudio.play(); 
                          console.log("playing");
                      }
                  }, 200);
                  }
                  document.getElementById('notification').hidden = false;
                }
                if ({{Session::get('role')}}==1) {
                  // call the alarm function
                  // document.getElementById('mySound').play();
                  // var audio = new Audio('{{url('/')}}/public/sound/to-the-point.mp3');
                  // var playPromise = audio.play();
                  if(myaudio.pause){
                    refreshIntervalId = setInterval(function(){ 
                      if(!isStop){
                        // document.querySelector('audio').play();
                        // myaudio.play(); 
                            console.log("playing");
                      }
                  }, 200);
                  }
                  document.getElementById('notification').hidden = false;
                }
              } catch (error) {
                  console.log(error);
              }
          });

                        socket.on('driver-message', function(evt){
              console.log("income message: ", evt);
              try {
                isStop = false;
                var received_msg = JSON.parse(evt);
                console.log("Message is received..."+evt.data);
                let id = received_msg.msg;
                console.log("Message is received..."+evt.data);
                if (id == {{Session::get('userid')}}) {
                  // call the alarm function
                  // document.getElementById('mySound').play();
                  // var audio = new Audio('{{url('/')}}/public/sound/to-the-point.mp3');
                  // var playPromise = audio.play();
                  if(myaudio.pause){
                    refreshIntervalId = setInterval(function(){ 
                      if(!isStop){
                        console.log(myaudio);
                        // document.querySelector('audio').play();
                          //myaudio.play(); 
                          console.log("playing");
                      }
                  }, 200);
                  }
                  document.getElementById('driver-notification').hidden = false;
                  document.getElementById("order_id").textContent += received_msg.order_id;
                }
                if ({{Session::get('role')}}==1) {
                  // call the alarm function
                  // document.getElementById('mySound').play();
                  // var audio = new Audio('{{url('/')}}/public/sound/to-the-point.mp3');
                  // var playPromise = audio.play();
                  if(myaudio.pause){
                    refreshIntervalId = setInterval(function(){ 
                      if(!isStop){
                        // document.querySelector('audio').play();
                        // myaudio.play(); 
                            console.log("playing");
                      }
                  }, 200);
                  }
                  document.getElementById('driver-notification').hidden = false;
                }
              } catch (error) {
                  console.log(error);
              }
          });

        }else{
          console.log("Not Connected.");
        }
      });

      /* if ("WebSocket" in window) { */
          /* console.log("WebSocket is supported by your Browser!"); */
          
          // Let us open a web socket
          /* var ws = new WebSocket("ws://{{$_SERVER['HTTP_HOST']}}:8081");
  
          ws.onopen = function() {
            console.log('Socket connected successfully!..');
          }; */
          
          /* ws.onmessage = function (evt) { 
            try {
              isStop = false;
              var received_msg = JSON.parse(evt.data);
              console.log("Message is received..."+evt.data);
              let id = received_msg.msg;
              console.log("Message is received..."+evt.data);
              if (id == {{Session::get('userid')}}) {
                // call the alarm function
                // document.getElementById('mySound').play();
                // var audio = new Audio('{{url('/')}}/public/sound/to-the-point.mp3');
                // var playPromise = audio.play();
                if(myaudio.pause){
                  refreshIntervalId = setInterval(function(){ 
                    if(!isStop){
                      console.log(myaudio);
                      document.querySelector('audio').play();
                        //myaudio.play(); 
                        console.log("playing");
                    }
                }, 200);
                }
                document.getElementById('notification').hidden = false;
              }
              if ({{Session::get('role')}}==1) {
                // call the alarm function
                // document.getElementById('mySound').play();
                // var audio = new Audio('{{url('/')}}/public/sound/to-the-point.mp3');
                // var playPromise = audio.play();
                if(myaudio.pause){
                  refreshIntervalId = setInterval(function(){ 
                    if(!isStop){
                      document.querySelector('audio').play();
                       // myaudio.play(); 
                          console.log("playing");
                    }
                }, 200);
                }
                document.getElementById('notification').hidden = false;
              }
            } catch (error) {
                
            }
          }; */
  
          /* ws.onclose = function() { 
            console.log("Connection is closed..."); 
          };

      } else {
          console.log("WebSocket NOT supported by your Browser!");
      } */
    }

    $( document ).ready(function() {

      WebSocketTest();

    });

  //let audio,refreshIntervalId;

  function play_wav()
  {
    console.log("myaudio",myaudio);
    if(myaudio){
      isStop = true;
      clearInterval(refreshIntervalId);
      // myaudio.pause();
      console.log("paused");
      return;
    } 
  }

  // For alerting restaurant for new orders after 5 minutes if not accepted

    function notify_restaurant_for_new_orders()
{
  if ({{Session::get('role')}}!=1) {


        id = {{Session::get('userid')}};
      
        var url = "{{url('/')}}/admin/notify_restaurant_for_new_orders/"+id;

      $.ajax({
          type: "GET",
          url: url,
          success: function(data){
            console.log("response");
            var res = JSON.parse(data);
            console.log(res.status);
            if(res.status == "true")
            {
              if(myaudio.pause){
                      refreshIntervalId = setInterval(function(){ 
                        if(!isStop){
                          // document.querySelector('audio').play();
                          // myaudio.play(); 
                              console.log("playing");
                        }
                    }, 200);
                    }
                    document.getElementById('notification').hidden = false;
            }
          }
      });
  }
}

setInterval(notify_restaurant_for_new_orders,50000);


  function update_availability(obj, id=0)
{
  if(id==0)
    id = {{Session::get('userid')}};
  
  if ($(obj).is(":checked"))
    var url = "{{url('/')}}/admin/update_open_status/1/"+id;
  else
    var url = "{{url('/')}}/admin/update_open_status/2/"+id;

  $.ajax({
      type: "GET",
      url: url,
      success: function(data){
        console.log(data);
        alert("Status updated succesfully");
      }
  });
}

  function update_busy_availability(obj)
{
  var id = {{Session::get('userid')}};
  if ($(obj).is(":checked"))
    var url = "{{url('/')}}/admin/update_restaurant_busy_status/1/"+id;
  else
    var url = "{{url('/')}}/admin/update_restaurant_busy_status/0/"+id;

  $.ajax({
      type: "GET",
      url: url,
      success: function(data){
        console.log(data);
        alert("Status updated succesfully");
      }
  });
}

</script>
 <!--FOR NEW ORDER NOTIFICATION ALERT ENDS -->