    <style type="text/css">

        .switch {
        position: relative;
        display: inline-block;
        float: right;
        width: 60px;
        height: 34px;
        }

        .switch input {
        opacity: 0;
        width: 0;
        height: 0;
        }

        .slider-avail {
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

        .slider-avail:before {
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

        input:checked + .slider-avail {
        background-color: #4a9c2d;
        }

        input:focus + .slider-avail {
        box-shadow: 0 0 1px #4a9c2d;
        }

        input:checked + .slider-avail:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
        }

        /* Rounded slider-avails */
        .slider-avail.round {
        border-radius: 34px;
        }

        .slider-avail.round:before {
        border-radius: 50%;
        }
        .available_status {
            font-weight: 700;
            border-right: 0px !important;
            background: #2c303b !important;
        }
        .main-menu.menu-dark .navigation > li.open > a.available_status{
            background: #2c303b !important;
        }
    </style>
   <div class="main-menu-content">
      <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

                <li class="navigation-header"><span>General</span><i data-toggle="tooltip" data-placement="right" data-original-title="General" class=" ft-minus"></i>
                </li>
                 @if(Session::get('role') == 2)
                <li class="nav-item" >
                    <a class="available_status">
                        <span data-i18n="" class="menu-title">Available Status</span>
                        <label class="switch">
                            <input type="checkbox" name="status" @if(isset(auth()->guard('restaurant')->user()->is_open)){{(auth()->guard('restaurant')->user()->status==1)?"checked":"" }} @else checked @endif onclick="update_availability(this)" >
                            <span class="slider-avail round"></span>
                        </label>
                    </a>
                </li>
                @endif
                 @if(Session::get('role') == 2)
                <li class="nav-item" >
                    <a class="available_status">
                        <span data-i18n="" class="menu-title">Busy Status</span>
                        <label class="switch">
                            <input type="checkbox" name="status" @if(isset(auth()->guard('restaurant')->user()->is_open)){{(auth()->guard('restaurant')->user()->is_busy==1)?"checked":"" }} @else checked @endif onclick="update_busy_availability(this)" >
                            <span class="slider-avail round"></span>
                        </label>
                    </a>
                </li>
                @endif
                @if(Session::get('role') != 2)
                    <li {{{ (Request::is('admin/dashboard') ? 'class=active' : '') }}} class="nav-item"
                        @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->dashboard)) style="display:none" @endif
                        >
                        <a href="{{URL('/')}}/admin/dashboard"><i class="ft-home"></i><span data-i18n="" class="menu-title">Super Admin Dashboard</span></a>
                    </li>
                    <li {{{ (Request::is('admin/availability_map') ? 'class=active' : '') }}}
                        @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->availability_map)) style="display:none" @endif
                        >
                        <a href="{{URL('/')}}/admin/availability_map" class="menu-item"><i class="ft-map"></i><span data-i18n="" class="menu-title">Availability Map</span></a>
                    </li>
                @endif
                @if(Session::get('role') == 2)
                <li {{{ (Request::is('admin/dashboard') ? 'class=active' : '') }}} class="nav-item" >
                    <a href="{{URL('/')}}/admin/dashboard"><i class="ft-home"></i><span data-i18n="" class="menu-title">Restaurant Dashboard</span></a>
                </li>
                @endif

{{--                <li {{{ (Request::is('admin/order_dashboard') ? 'class=active' : '') }}}--}}
{{--                    {{{ (Request::is('admin/orders/new') ? 'class=active' : '') }}} {{{ (Request::is('admin/orders/availability_map') ? 'class=active' : '') }}}--}}
{{--                    {{{ (Request::is('admin/orders/processing') ? 'class=active' : '') }}} {{{ (Request::is('admin/orders/pickup') ? 'class=active' : '') }}}{{{ (Request::is('admin/orders/delivered') ? 'class=active' : '') }}}{{{ (Request::is('admin/orders/cancelled') ? 'class=active' : '') }}} {{{ (Request::is('admin/pickup-orders') ? 'class=active' : '') }}} {{{ (Request::is('admin/dining-orders') ? 'class=active' : '') }}} class="nav-item has-sub"--}}
{{--                    @if(isset(auth()->user()->role) && auth()->user()->role==3 && isset(auth()->user()->AccessPrivilages->order_management) && empty(auth()->user()->AccessPrivilages->order_management)) style="display:none" @endif >--}}
{{--                    <a href="#"><i class="ft-menu"></i><span data-i18n="" class="menu-title">{{trans('constants.order_mng')}}</span></a>--}}
{{--                    @php--}}
{{--                        if(isset(auth()->user()->AccessPrivilages->order_management))--}}
{{--                            $orderAccess = explode(",",auth()->user()->AccessPrivilages->order_management);--}}
{{--                        else--}}
{{--                            $orderAccess = array();--}}
{{--                    @endphp--}}
{{--                    <ul class="menu-content">--}}
{{--                        <li {{{ (Request::is('admin/order_dashboard') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(1,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/order_dashboard" class="menu-item">{{trans('constants.order')}} {{trans('constants.dashboard')}}</a></li>--}}
{{--                        <li {{{ (Request::is('admin/orders/new') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(2,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/orders/new" class="menu-item">{{trans('constants.new')}} {{trans('constants.order')}}</a><span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow" id="new_order"></span></li>--}}
{{--                        <li {{{ (Request::is('admin/orders/processing') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(3,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/orders/processing" class="menu-item">{{trans('constants.process')}} {{trans('constants.order')}}</a><span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow" id="processing_order"></span></li>--}}
{{--                        <li {{{ (Request::is('admin/orders/pickup') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(4,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/orders/pickup" class="menu-item"> {{trans('constants.order')}} {{trans('constants.pickup')}}</a><span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow" id="order_pickup"></span></li>--}}
{{--                        <li {{{ (Request::is('admin/orders/delivered') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(5,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/orders/delivered" class="menu-item"> {{trans('constants.delivered')}} {{trans('constants.order')}}</a><span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow" id="deliverd_order"></span></li>--}}
{{--                        <li {{{ (Request::is('admin/orders/cancelled') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(6,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/orders/cancelled" class="menu-item"> {{trans('constants.cancelled')}} {{trans('constants.order')}}</a><span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow" id="cancelled_order"></span></li>--}}
{{--                        <li {{{ (Request::is('admin/pickup-orders') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(7,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/pickup-orders" class="menu-item"> {{trans('constants.pickup')}} {{trans('constants.order')}}</a><span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow" id="pickup_order"></span></li>--}}
{{--                       <li {{{ (Request::is('admin/dining-orders') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(8,(array)$orderAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/dining-orders" class="menu-item"> {{trans('constants.dining')}} {{trans('constants.order')}}</a><span class="badge badge-pill badge-default badge-danger badge-default badge-up badge-glow" id="dining_order"></span></li>--}}
{{--                    </ul>--}}
{{--                </li>--}}

                 @if(Session::get('role') != 2)
                <li {{{ (Request::is('admin/city_list') ? 'class=active' : '') }}}
                    {{{ (Request::is('admin/add_city') ? 'class=active' : '') }}}
                    {{{ (Request::is('admin/country_list') ? 'class=active' : '') }}}{{{ (Request::is('admin/state_list') ? 'class=active' : '') }}}class="nav-item has-sub" @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->city_management)) style="display:none" @endif><a href="#"><i class="ft-users"></i><span data-i18n="" class="menu-title">{{__('constants.city')}} Management</span></a>
                    <ul class="menu-content">
                        @php
                            if(isset(auth()->user()->AccessPrivilages->city_management))
                                $cityAccess = explode(",",auth()->user()->AccessPrivilages->city_management);
                            else
                                $cityAccess = array();
                        @endphp
                        <li {{{ (Request::is('admin/city_list') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(1,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/city_list" class="menu-item">{{__('constants.city')}} List</a></li>

                        <li {{{ (Request::is('admin/add_city') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(2,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/add_city" class="menu-item">Add {{__('constants.city')}}</a></li>

                        <!-- <li {{{ (Request::is('admin/country_list') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(7,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/country_list" class="menu-item">{{ trans('constants.country') }} {{ trans('constants.list') }}</a></li> -->

                        <li {{{ (Request::is('admin/state_list') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(11,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/state_list" class="menu-item">{{ trans('constants.city_name') }} {{ trans('constants.list') }}</a></li>
                        
                        <li {{{ (Request::is('admin/new_state_list') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(11,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/new_state_list" class="menu-item">{{ trans('constants.state_name') }} {{ trans('constants.list') }}</a></li>
                    </ul>
                </li>
                @if(Session::get('role') != 2)
                <li {{{ (Request::is('admin/zones_list') ? 'class=active' : '') }}}
                    {{{ (Request::is('admin/add_zone') ? 'class=active' : '') }}}
                    {{{ (Request::is('admin/zone_country_list') ? 'class=active' : '') }}}{{{ (Request::is('admin/zone_state_list') ? 'class=active' : '') }}}class="nav-item has-sub" @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->city_management)) style="display:none" @endif><a href="#"><i class="ft-users"></i><span data-i18n="" class="menu-title">{{__('constants.zone')}} Management</span></a>
                    <ul class="menu-content">

                            @php
                                if(isset(auth()->user()->AccessPrivilages->city_management))
                                    $cityAccess = explode(",",auth()->user()->AccessPrivilages->city_management);
                                else
                                    $cityAccess = array();
                            @endphp

                         
                            <li {{{ (Request::is('admin/zones_list') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(1,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/zones_list" class="menu-item">{{__('constants.zone')}} List</a></li>

                        <li {{{ (Request::is('admin/add_zone') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(2,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/add_zone" class="menu-item">Add {{__('constants.zone')}}</a></li>

                        <!-- <li {{{ (Request::is('admin/zone_country_list') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(7,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/zone_country_list" class="menu-item">{{ trans('constants.country') }} {{ trans('constants.list') }}</a></li> -->

                        <!-- <li {{{ (Request::is('admin/zone_state_list') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(11,(array)$cityAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/zone_state_list" class="menu-item">{{ trans('constants.city_name') }} {{ trans('constants.list') }}</a></li> -->
                           
                        </ul>
                    </li>
                @endif
                @if(Session::get('role') == 1)
                    <!-- <li {{{ (Request::is('admin/disp_managerlist') ? 'class=active' : '') }}}
                        {{{ (Request::is('admin/add_dispmanager') ? 'class=active' : '') }}}
                         class="nav-item has-sub"><a href="#"><i class="ft-user"></i><span data-i18n="" class="menu-title">Sub Admin Management</span></a>
                        <ul class="menu-content">
                            <li {{{ (Request::is('admin/disp_managerlist') ? 'class=active' : '') }}}><a href="{{URL('/')}}/admin/disp_managerlist" class="menu-item">Sub Admin List</a></li>
                            <li {{{ (Request::is('admin/add_dispmanager') ? 'class=active' : '') }}}><a href="{{URL('/')}}/admin/add_dispmanager" class="menu-item">Add Sub Admin</a></li>
                        </ul>
                    </li> -->
                @endif
                @php
                    if(!empty(auth()->user()->AccessPrivilages->promocode)){
                        $promoAccess = explode(",",auth()->user()->AccessPrivilages->promocode);
                    }else{
                        $promoAccess = array();
                    }
                @endphp
                <!-- <li {{{ (Request::is('admin/custumpush') ? 'class=active' : '') }}} class="nav-item" @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(5,(array)$promoAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/custumpush"><i class="ft-book"></i><span data-i18n="" class="menu-title">{{ trans('constants.custom_push') }}</span></a></li> -->
                <li {{{ (Request::is('admin/user_list') ? 'class=active' : '') }}} @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->users)) style="display:none" @endif>
                    <a href="{{URL('/')}}/admin/user_list" class="menu-item"><i class="ft-users"></i><span data-i18n="" class="menu-title">{{trans('constants.user')}} {{trans('constants.manage')}}</span></a>
                </li>
                <li {{{ (Request::is('admin/payout/restaurant') ? 'class=active' : '') }}} {{{ (Request::is('admin/payout/driver') ? 'class=active' : '') }}}
                {{{ (Request::is('admin/payout_history/restaurant') ? 'class=active' : '') }}} {{{ (Request::is('admin/payout_history/driver') ? 'class=active' : '') }}} class="nav-item has-sub" @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->payouts)) style="display:none" @endif><a href="#"><i class="ft-book"></i><span data-i18n="" class="menu-title">{{trans('constants.payout')}}</span></a>
                    <ul class="menu-content">
                    @php $payoutAccess = explode(",",auth()->user()->AccessPrivilages->payouts); @endphp
                      <li {{{ (Request::is('admin/payout/restaurant') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(1,(array)$payoutAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/payout/restaurant" class="menu-item">{{trans('constants.restaurant')}} {{trans('constants.payout')}} </a></li>
                      <!-- <li {{{ (Request::is('admin/payout/driver') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(3,(array)$payoutAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/payout/driver" class="menu-item">{{trans('constants.driver')}} {{trans('constants.payout')}} </a></li> -->
                      <li {{{ (Request::is('admin/payout_history/restaurant') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(5,(array)$payoutAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/payout_history/restaurant" class="menu-item">{{trans('constants.restaurant')}} {{trans('constants.transaction_history')}} </a></li>
                      <!-- <li {{{ (Request::is('admin/payout_history/driver') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(6,(array)$payoutAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/payout_history/driver" class="menu-item">{{trans('constants.driver')}} {{trans('constants.transaction_history')}} </a></li> -->
                    </ul>
               </li>
                @endif
                @if(Session::get('role') != 2)
                <li {{{ (Request::is('admin/cms_list') ? 'class=active' : '') }}}  @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->cms)) style="display:none" @endif><a href="{{URL('/')}}/admin/cms_list"><i class="ft-book"></i><span data-i18n="" class="menu-title">CMS Management</span></a>
                </li>
                <li {{{ (Request::is('admin/settings/site') ? 'class=active' : '') }}}
                    {{{ (Request::is('admin/settings/google') ? 'class=active' : '') }}}{{{ (Request::is('admin/settings/email') ? 'class=active' : '') }}}{{{ (Request::is('admin/email_template_list') ? 'class=active' : '') }}}class="nav-item has-sub" @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->settings)) style="display:none" @endif><a href="#"><i class="ft-settings"></i><span data-i18n="" class="menu-title">{{trans('constants.setting')}}</span></a>
                    <ul class="menu-content">
                        @php $settingsAccess = explode(",",auth()->user()->AccessPrivilages->settings); @endphp
                      <li {{{ (Request::is('admin/settings/site') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(1,(array)$settingsAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/settings/site" class="menu-item">{{trans('constants.site_setting')}}</a></li>
                      <li {{{ (Request::is('admin/settings/google') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(2,(array)$settingsAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/settings/google" class="menu-item">{{trans('constants.google_setting')}}</a></li>
                      <li {{{ (Request::is('admin/instruction_setting') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(5,(array)$settingsAccess)) style="display:none" @endif><a href="{{URL('/')}}/admin/instruction_setting" class="menu-item">{{trans('constants.instruction_setting')}}</a></li>
                    </ul>
               </li>
               @endif
                @if(Session::get('role') != 2)
                <li {{{ (Request::is('admin/admin_restaurant_report') ? 'class=active' : '') }}}{{{ (Request::is('admin/delivery_boy_reports') ? 'class=active' : '') }}}{{{ (Request::is('admin/restaurant_report') ? 'class=active' : '') }}} class="nav-item has-sub" @if(auth()->user()->role==3 && empty(auth()->user()->AccessPrivilages->reports)) style="display:none" @endif><a href="#"><i class="ft-mail"></i><span data-i18n="" class="menu-title">Reports</span></a>
                    <ul class="menu-content">
                        @php $reportsAccess = explode(",",auth()->user()->AccessPrivilages->reports); @endphp
                        <li {{{ (Request::is('admin/admin_restaurant_report') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(1,(array)$reportsAccess)) style="display:none" @endif><a href="{{url('/')}}/admin/admin_restaurant_report" class="menu-item">Restaurant Report</a></li>

                        <li {{{ (Request::is('admin/delivery_boy_reports') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(3,(array)$reportsAccess)) style="display:none" @endif><a href="{{url('/')}}/admin/delivery_boy_reports" class="menu-item">Delivery Boy Report</a></li>
                        <li {{{ (Request::is('admin/restaurant_report') ? 'class=active' : '') }}} @if(isset(auth()->user()->role) && auth()->user()->role==3 && !in_array(5,(array)$reportsAccess)) style="display:none" @endif><a href="{{url('/')}}/admin/restaurant_report" class="menu-item"><span data-i18n="" class="menu-title">Order Reports</span></a></li>

                    </ul>
                </li>
                @endif
            </ul>
    </div>


  
  