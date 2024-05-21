@extends('layout.master')

@section('title')

    {{APP_NAME}}
@endsection

@section('content')

    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                <h3 class="content-header-title mb-0 d-inline-block">EDIT SUB ADMIN</h3>
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{url('/')}}/admin/disp_managerlist" class="brand-font-link-color">SUB ADMIN LIST</a>
                            </li>
                            <li class="breadcrumb-item"><a href=" " class="brand-font-link-color">EDIT SUB ADMIN</a>
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
                                    <form action="{{url('/')}}/admin/add_dispmanager" id="addform" method="post" class="icons-tab-steps wizard-notification">
                                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        <input type="hidden" name="id" id="admin_id" value="{{$admin_detail->id}}">
                                        <fieldset>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="eventName2">Name <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control required" id="name" name="name" value="{{$admin_detail->name}}"><br>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email">Email <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control required" id="email" name="email" value="{{$admin_detail->email}}"><br>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="password">Password <span style="color: red;">*</span></label>
                                                        <input type="Password" class="form-control required" id="password" name="password" value="{{$admin_detail->org_password}}"><br>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="number">Phone <span style="color: red;">*</span></label>
                                                        <input type="text" class="form-control required" id="number" name="phone" value="{{$admin_detail->phone}}" ><br>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="number">Access Privileges</label>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 main-menu-content">
                                                            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                                                                <li class="active nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(1);" title="Super Admin Dashboard">Super Admin Dashboard</a>
                                                                </li>
                                                                <li class="active nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(21);" title="Availablity Map">Availablity Map</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(2);" title="Order Management">Order Management</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(4);" title="City Management">City Management</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(5);" title="Food Management">Food Management</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(6);" title="Driver Management">Driver Management</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(7);" title="Document Management">Document Management</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(8);" title="Cancellation Reasons">Cancellation Reasons</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(9);" title="Promocodes">Promocodes</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(10);" title="Restaurant Banner">Restaurant Banner</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(11);" title="Popular Brand">Popular Brand</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(12);" title="User Management">User Management</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(13);" title="Cuisines List">Cuisines List</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(14);" title="Addons List">Addons List</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(15);" title="Payout">Payout</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(18);" title="CMS Management">CMS Management</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(19);" title="Settings">Settings</a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a href="javascript:;" onclick="ChangePrivilegeBox(20);" title="Reports">Reports</a>
                                                                </li>
                                                            </ul>
                                                            <span class='error' id="privilage_err" style="display:none;color:red">Privilages Required Confirmation</span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="pm_menu" id="pm_1" style="height:300px;">
                                                                <h4>Super Admin Dashboard</h4>
                                                                @php
                                                                    $dashboard_detail= !empty($access_detail->dashboard)?$access_detail->dashboard:"";
                                                                    if($dashboard_detail != null && !empty($dashboard_detail)){
                                                                      $dashboard_access = explode(',',$dashboard_detail);
                                                                    }else{
                                                                      $dashboard_access = [];
                                                                    }
                                                                @endphp
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="main_dashboard" onchange="doAll('main_dashboard[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Dashboard</span><i>:</i>
                                                                        <label class="stylish_check">View &nbsp;<input type="checkbox" name="main_dashboard[]" value="1" @if(isset($dashboard_access))@if(in_array(1,$dashboard_access)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $availability_map_detail=!empty($access_detail->availability_map)?$access_detail->availability_map:"";
                                                                if($availability_map_detail != null && !empty($availability_map_detail)){
                                                                  $availability_map_detail = explode(',',$availability_map_detail);
                                                                }else{
                                                                  $availability_map_detail = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_21" style="display:none;">
                                                                <h4>Availability Map</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="availability_map" onchange="doAll('availability_map[]',this)" /><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Availability Map</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="availability_map[]" value="1" @if(isset($availability_map_detail)) @if(in_array(1,$availability_map_detail)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $order_management_detail=!empty($access_detail->order_management)?$access_detail->order_management:"";
                                                                if($order_management_detail != null && !empty($order_management_detail)){
                                                                  $order_management_detail_check = explode(',',$order_management_detail);
                                                                }else{
                                                                  $order_management_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_2" style="display:none;">
                                                                <h4>Order Management</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="order_dashboard" onchange="doAll('order_dashboard[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Order Dashboard</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="1"  @if(isset($order_management_detail_check)) @if(in_array(1,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>New Order</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="2" @if(isset($order_management_detail_check)) @if(in_array(2,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Processing Order</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="3" @if(isset($order_management_detail_check)) @if(in_array(3,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Order Pickup</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="4" @if(isset($order_management_detail_check)) @if(in_array(4,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Delivered Order</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="5" @if(isset($order_management_detail_check)) @if(in_array(5,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Cancelled Order</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="6" @if(isset($order_management_detail_check)) @if(in_array(6,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Pickup Order</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="7" @if(isset($order_management_detail_check)) @if(in_array(7,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Dining Order</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="order_dashboard[]" value="8" @if(isset($order_management_detail_check)) @if(in_array(8,$order_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $restaurant_management_detail=!empty($access_detail->restaurant)?$access_detail->restaurant:"";
                                                                if($restaurant_management_detail != null && !empty($restaurant_management_detail)){
                                                                  $restaurant_management_detail_check = explode(',',$restaurant_management_detail);
                                                                }else{
                                                                  $restaurant_management_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_3" style="display:none;">
                                                                <h4>Restaurant Manage</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="restaurant" onchange="doAll('restaurant[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Restaurant</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="restaurant[]" value="1" @if(isset($restaurant_management_detail_check)) @if(in_array(1,$restaurant_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="restaurant[]" value="2" @if(isset($restaurant_management_detail_check)) @if(in_array(2,$restaurant_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="restaurant[]" value="3" @if(isset($restaurant_management_detail_check)) @if(in_array(3,$restaurant_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="restaurant[]" value="4" @if(isset($restaurant_management_detail_check)) @if(in_array(4,$restaurant_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Status Update&nbsp;<input type="checkbox" name="restaurant[]" value="5" @if(isset($restaurant_management_detail_check)) @if(in_array(5,$restaurant_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $city_management_detail=!empty($access_detail->city_management)?$access_detail->city_management:"";
                                                                if($city_management_detail != null && !empty($city_management_detail)){
                                                                  $city_management_detail_check = explode(',',$city_management_detail);
                                                                }else{
                                                                  $city_management_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_4" style="display:none;">
                                                                <h4>City Management</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="city" onchange="doAll('city[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>City</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="city[]" value="1"  @if(isset($city_management_detail_check)) @if(in_array(1,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="city[]" value="2"  @if(isset($city_management_detail_check)) @if(in_array(2,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="city[]" value="3"  @if(isset($city_management_detail_check)) @if(in_array(3,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Area</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="city[]" value="4"  @if(isset($city_management_detail_check)) @if(in_array(4,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="city[]" value="5"  @if(isset($city_management_detail_check)) @if(in_array(5,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="city[]" value="6"  @if(isset($city_management_detail_check)) @if(in_array(6,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Country</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="city[]" value="7"  @if(isset($city_management_detail_check)) @if(in_array(7,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="city[]" value="8"  @if(isset($city_management_detail_check)) @if(in_array(8,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="city[]" value="9"  @if(isset($city_management_detail_check)) @if(in_array(9,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Make Default&nbsp;<input type="checkbox" name="city[]" value="10"  @if(isset($city_management_detail_check)) @if(in_array(10,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>State</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="city[]" value="11"  @if(isset($city_management_detail_check)) @if(in_array(11,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="city[]" value="12"  @if(isset($city_management_detail_check)) @if(in_array(12,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="city[]" value="13"  @if(isset($city_management_detail_check)) @if(in_array(13,$city_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $food_management_detail=!empty($access_detail->food_management)?$access_detail->food_management:"";
                                                                if($food_management_detail != null && !empty($food_management_detail)){
                                                                  $food_management_detail_check = explode(',',$food_management_detail);
                                                                }else{
                                                                  $food_management_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_5" style="display:none;">
                                                                <h4>Food Manage</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="food" onchange="doAll('food[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Food List</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="food[]" value="1" @if(isset($food_management_detail_check)) @if(in_array(1,$food_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $driver_management_detail=!empty($access_detail->driver_management)?$access_detail->driver_management:"";
                                                                if($driver_management_detail != null && !empty($driver_management_detail)){
                                                                  $driver_management_detail_check = explode(',',$driver_management_detail);
                                                                }else{
                                                                  $driver_management_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_6" style="display:none;">
                                                                <h4>Driver Management</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="driver" onchange="doAll('driver[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Driver</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="driver[]" value="1" @if(isset($driver_management_detail_check)) @if(in_array(1,$driver_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="driver[]" value="2" @if(isset($driver_management_detail_check)) @if(in_array(2,$driver_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="driver[]" value="3" @if(isset($driver_management_detail_check)) @if(in_array(3,$driver_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $document_management_detail=!empty($access_detail->document)?$access_detail->document:"";
                                                                if($document_management_detail != null && !empty($document_management_detail)){
                                                                  $document_management_detail_check = explode(',',$document_management_detail);
                                                                }else{
                                                                  $document_management_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_7" style="display:none;">
                                                                <h4>Document Management</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="document" onchange="doAll('document[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Document</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="document[]" value="1" @if(isset($document_management_detail_check)) @if(in_array(1,$document_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="document[]" value="2" @if(isset($document_management_detail_check)) @if(in_array(2,$document_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="document[]" value="3" @if(isset($document_management_detail_check)) @if(in_array(3,$document_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="document[]" value="4" @if(isset($document_management_detail_check)) @if(in_array(4,$document_management_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $cancel_reason_detail=!empty($access_detail->cancel_reason)?$access_detail->cancel_reason:"";
                                                                if($cancel_reason_detail != null && !empty($cancel_reason_detail)){
                                                                  $cancel_reason_detail_check = explode(',',$cancel_reason_detail);
                                                                }else{
                                                                  $cancel_reason_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_8" style="display:none;">
                                                                <h4>Cancellation Reasons</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="resaon" onchange="doAll('resaon[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Reason</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="resaon[]" value="1"  @if(isset($cancel_reason_detail_check)) @if(in_array(1,$cancel_reason_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="resaon[]" value="2"  @if(isset($cancel_reason_detail_check)) @if(in_array(2,$cancel_reason_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="resaon[]" value="3"  @if(isset($cancel_reason_detail_check)) @if(in_array(3,$cancel_reason_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="resaon[]" value="4"  @if(isset($cancel_reason_detail_check)) @if(in_array(4,$cancel_reason_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $promocode_detail=!empty($access_detail->promocode)?$access_detail->promocode:"";
                                                                if($promocode_detail != null && !empty($promocode_detail)){
                                                                  $promocode_detail_check = explode(',',$promocode_detail);
                                                                }else{
                                                                  $promocode_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_9" style="display:none;">
                                                                <h4>Promocodes</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="promocode" onchange="doAll('promocode[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Promocodes</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="promocode[]" value="1" @if(isset($promocode_detail_check)) @if(in_array(1,$promocode_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="promocode[]" value="2" @if(isset($promocode_detail_check)) @if(in_array(2,$promocode_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="promocode[]" value="3" @if(isset($promocode_detail_check)) @if(in_array(3,$promocode_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="promocode[]" value="4" @if(isset($promocode_detail_check)) @if(in_array(4,$promocode_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Custom Push</span><i>:</i>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="promocode[]" value="5" @if(isset($promocode_detail_check)) @if(in_array(5,$promocode_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $banner_detail=!empty($access_detail->banner)?$access_detail->banner:"";
                                                                if($banner_detail != null && !empty($banner_detail)){
                                                                  $banner_detail_check = explode(',',$banner_detail);
                                                                }else{
                                                                  $banner_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_10" style="display:none;">
                                                                <h4>Restaurant Banner</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="banner" onchange="doAll('banner[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="banner[]" value="1" @if(isset($banner_detail_check)) @if(in_array(1,$banner_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="banner[]" value="2" @if(isset($banner_detail_check)) @if(in_array(2,$banner_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="banner[]" value="3" @if(isset($banner_detail_check)) @if(in_array(3,$banner_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="banner[]" value="4" @if(isset($banner_detail_check)) @if(in_array(4,$banner_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $popular_brands_detail=!empty($access_detail->popular_brands)?$access_detail->popular_brands:"";
                                                                if($popular_brands_detail != null && !empty($popular_brands_detail)){
                                                                  $popular_brands_detail_check = explode(',',$popular_brands_detail);
                                                                }else{
                                                                  $popular_brands_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_11" style="display:none;">
                                                                <h4>Popular Brand</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="popular_brand" onchange="doAll('popular_brand[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="popular_brand[]" value="1" @if(isset($popular_brands_detail_check)) @if(in_array(1,$popular_brands_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="popular_brand[]" value="2" @if(isset($popular_brands_detail_check)) @if(in_array(2,$popular_brands_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="popular_brand[]" value="3" @if(isset($popular_brands_detail_check)) @if(in_array(3,$popular_brands_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="popular_brand[]" value="4" @if(isset($popular_brands_detail_check)) @if(in_array(4,$popular_brands_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $users_detail=!empty($access_detail->users)?$access_detail->users:"";
                                                                if($users_detail != null && !empty($users_detail)){
                                                                  $users_detail_check = explode(',',$users_detail);
                                                                }else{
                                                                  $users_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_12" style="display:none;">
                                                                <h4>User Management</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="users" onchange="doAll('users[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="users[]" value="1" @if(isset($users_detail_check)) @if(in_array(1,$users_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $cuisines_detail=!empty($access_detail->cuisines)?$access_detail->cuisines:"";
                                                                if($cuisines_detail != null && !empty($cuisines_detail)){
                                                                  $cuisines_detail_check = explode(',',$cuisines_detail);
                                                                }else{
                                                                  $cuisines_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_13" style="display:none;">
                                                                <h4>Cuisines List</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="cuisines" onchange="doAll('cuisines[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="cuisines[]" value="1" @if(isset($cuisines_detail_check)) @if(in_array(1,$cuisines_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="cuisines[]" value="2" @if(isset($cuisines_detail_check)) @if(in_array(2,$cuisines_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="cuisines[]" value="3" @if(isset($cuisines_detail_check)) @if(in_array(3,$cuisines_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="cuisines[]" value="4" @if(isset($cuisines_detail_check)) @if(in_array(4,$cuisines_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $addons_detail=!empty($access_detail->addons)?$access_detail->addons:"";
                                                                if($addons_detail != null && !empty($addons_detail)){
                                                                  $addons_detail_check = explode(',',$addons_detail);
                                                                }else{
                                                                  $addons_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_14" style="display:none;">
                                                                <h4>Addon</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="addons" onchange="doAll('addons[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="addons[]" value="1" @if(isset($addons_detail_check)) @if(in_array(1,$addons_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $payouts_detail=isset($access_detail->payouts)?$access_detail->payouts:"";
                                                                if($payouts_detail != null && !empty($payouts_detail)){
                                                                  $payouts_detail_check = explode(',',$payouts_detail);
                                                                }else{
                                                                  $payouts_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_15" style="display:none;">
                                                                <h4>Payout</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="payout" onchange="doAll('payout[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Restaurant Payout</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="payout[]" value="1" @if(isset($payouts_detail_check)) @if(in_array(1,$payouts_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Make Payment&nbsp;<input type="checkbox" name="payout[]" value="2" @if(isset($payouts_detail_check)) @if(in_array(2,$payouts_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Driver Payout</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="payout[]" value="3" @if(isset($payouts_detail_check)) @if(in_array(3,$payouts_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Make Payment&nbsp;<input type="checkbox" name="payout[]" value="4" @if(isset($payouts_detail_check)) @if(in_array(4,$payouts_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Restaurant Transaction History</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="payout[]" value="5" @if(isset($payouts_detail_check)) @if(in_array(5,$payouts_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Driver Transaction History</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="payout[]" value="6" @if(isset($payouts_detail_check)) @if(in_array(6,$payouts_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $food_quantity_detail=!empty($access_detail->food_quantity)?$access_detail->food_quantity:"";
                                                                if($food_quantity_detail != null && !empty($food_quantity_detail)){
                                                                  $food_quantity_detail_check = explode(',',$food_quantity_detail);
                                                                }else{
                                                                  $food_quantity_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_16" style="display:none;">
                                                                <h4>Food Quantity</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="food_quantity" onchange="doAll('food_quantity[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="food_quantity[]" value="1" @if(isset($food_quantity_detail_check)) @if(in_array(1,$food_quantity_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="food_quantity[]" value="2" @if(isset($food_quantity_detail_check)) @if(in_array(2,$food_quantity_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="food_quantity[]" value="3" @if(isset($food_quantity_detail_check)) @if(in_array(3,$food_quantity_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="food_quantity[]" value="4" @if(isset($food_quantity_detail_check)) @if(in_array(4,$food_quantity_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $category_detail=!empty($access_detail->category)?$access_detail->category:"";
                                                                if($category_detail != null && !empty($category_detail)){
                                                                  $category_detail_check = explode(',',$category_detail);
                                                                }else{
                                                                  $category_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_17" style="display:none;">
                                                                <h4>Category</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="category" onchange="doAll('category[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="category[]" value="1" @if(isset($category_detail_check)) @if(in_array(1,$category_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Add&nbsp;<input type="checkbox" name="category[]" value="2" @if(isset($category_detail_check)) @if(in_array(2,$category_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="category[]" value="3" @if(isset($category_detail_check)) @if(in_array(3,$category_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Delete&nbsp;<input type="checkbox" name="category[]" value="4" @if(isset($category_detail_check)) @if(in_array(4,$category_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $menu_detail=!empty($access_detail->menu)?$access_detail->menu:"";
                                                                if($menu_detail != null && !empty($menu_detail)){
                                                                  $menu_detail_check = explode(',',$menu_detail);
                                                                }else{
                                                                  $menu_detail_check = [];
                                                                }
                                                            @endphp

                                                            <input type="hidden" name="menu[]" value="0">

                                                            @php
                                                                $cms_detail= !empty($access_detail->cms)?$access_detail->cms:"";
                                                                if($cms_detail != null && !empty($cms_detail)){
                                                                  $cms_detail_check = explode(',',$cms_detail);
                                                                }else{
                                                                  $cms_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_18" style="display:none;">
                                                                <h4>CMS Management</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="cms" onchange="doAll('cms[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>About Us</span><i>:</i>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="cms[]" value="1" @if(isset($cms_detail_check)) @if(in_array(1,$cms_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Faq</span><i>:</i>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="cms[]" value="2" @if(isset($cms_detail_check)) @if(in_array(2,$cms_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Help</span><i>:</i>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="cms[]" value="3" @if(isset($cms_detail_check)) @if(in_array(3,$cms_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $settings_detail= !empty($access_detail->settings)?$access_detail->settings:"";
                                                                if($settings_detail != null && !empty($settings_detail)){
                                                                  $settings_detail_check = explode(',',$settings_detail);
                                                                }else{
                                                                  $settings_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_19" style="display:none;">
                                                                <h4>Settings</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="settings" onchange="doAll('settings[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Site Setting</span><i>:</i>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="settings[]" value="1" @if(isset($settings_detail_check)) @if(in_array(1,$settings_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Google Setting</span><i>:</i>
                                                                        <label class="stylish_check">Edit&nbsp;<input type="checkbox" name="settings[]" value="2" @if(isset($settings_detail_check)) @if(in_array(2,$settings_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            @php
                                                                $reports_detail= !empty($access_detail->reports)?$access_detail->reports:"";
                                                                if($reports_detail != null && !empty($reports_detail)){
                                                                  $reports_detail_check = explode(',',$reports_detail);
                                                                }else{
                                                                  $reports_detail_check = [];
                                                                }
                                                            @endphp
                                                            <div class="pm_menu" id="pm_20" style="display:none;">
                                                                <h4>Reports</h4>
                                                                <div class="check_role_div">All &nbsp;<label class="stylish_check_all"><input type="checkbox" id="report" onchange="doAll('report[]',this)"/><small class="checkmark"></small></label></div>
                                                                <ul class="privilage_opt_bordered">
                                                                    <li>
                                                                        <span>Restaurant Report</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="report[]" value="1" @if(isset($reports_detail_check)) @if(in_array(1,$reports_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Export&nbsp;<input type="checkbox" name="report[]" value="2" @if(isset($reports_detail_check)) @if(in_array(2,$reports_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Delivery Boy Report</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="report[]" value="3" @if(isset($reports_detail_check)) @if(in_array(3,$reports_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Export&nbsp;<input type="checkbox" name="report[]" value="4" @if(isset($reports_detail_check)) @if(in_array(4,$reports_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                    <li>
                                                                        <span>Order Reports</span><i>:</i>
                                                                        <label class="stylish_check">View&nbsp;<input type="checkbox" name="report[]" value="5" @if(isset($reports_detail_check)) @if(in_array(5,$reports_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                        <label class="stylish_check">Export&nbsp;<input type="checkbox" name="report[]" value="6" @if(isset($reports_detail_check)) @if(in_array(6,$reports_detail_check)) checked @endif @endif><small class="checkmark"></small></label>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-actions">
                                                        <a href="{{url('/')}}/admin/disp_managerlist" class="btn cancel-button-style mr-1" style="padding: 10px 15px;">
                                                            <i class="ft-x"></i> Cancel
                                                        </a>
                                                        <button type="button" class="btn success-button-style mr-1 analyze" style="padding: 10px 15px;">
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


@endsection

@section('script')

    <script>
        $(document).ready(function(){
            var delivery_mode = $("#delivery_mode").val();
            if(delivery_mode!=1){
                $(".license_expiry").addClass('required');
                $(".vehicle_name").addClass('required');
                $(".vehicle_no").addClass('required');
                $(".insurance_no").addClass('required');
                $(".rc_no").addClass('required');
                $(".insurance_expiry_date").addClass('required');
                $(".rc_expiry_date").addClass('required');
                $("#mode_div").show();
            }else{
                $(".license_expiry").removeClass('required');
                $(".vehicle_name").removeClass('required');
                $(".vehicle_no").removeClass('required');
                $(".insurance_no").removeClass('required');
                $(".rc_no").removeClass('required');
                $(".insurance_expiry_date").removeClass('required');
                $(".rc_expiry_date").removeClass('required');
                $("#mode_div").hide();
            }
        });
        function ChangePrivilegeBox(type) {
            //alert(type);
            $('.pm_menu').hide();
            $('#pm_'+type).show();
        }

        function doAll(type,checkboxElem) {
            if (checkboxElem.checked) {
                $("input[name='"+type+"']").prop('checked', true);
            } else {
                $("input[name='"+type+"']").prop('checked', false);
            }
        }

        function form_validation()
        {
            var password = document.getElementById('password').value;
            var confirm_password = document.getElementById('confirm_password').value;
            if(password != confirm_password)
            {
                $('#password_error').fadeIn().html('Password and Confirm Password does not match').delay(3000).fadeOut('slow');
                $('#confirm_password').focus();
                return false;
            }
            $('#privilage_err').hide();
            var total_privileages = $('#addform input[type=checkbox]:checked').length;
            if(total_privileages>0)
            {
                return true;
            }else
            {
                $('#privilage_err').show();
                return false;
            }
            // else
            // {
            //   document.getElementById("add_driver").submit();
            // }
        }

    </script>
    <script>
        var counter = 0;
        var phone_validation = 0;
        var email_validation = 0;
        var admin_id = $("#admin_id").val();
        //phone validation for exist checking

        $("#number").focusout(function(e){
            var phone = $(this).val();
            if(phone != ''){
                $.ajax({
                    url : "{{url('/')}}/admin/check_subadmin_phone_exist",
                    method : "get",
                    data : {'phone':phone,'id':admin_id},
                    success : function (response){
                        if(response.status == true){
                            if ($("#number").parent().next(".validation").length == 0)
                            {
                                phone_validation++;
                                $("#number").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>phone number is already exists</div>");
                            }else{
                                phone_validation++;
                                $("#number").parent().next(".validation").remove();
                                $("#number").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>phone number is already exists</div>");
                            }
                        }else{
                            phone_validation = 0;
                            $("#number").parent().next(".validation").remove();
                        }
                    }
                });
            }
        });


        //email validation for format and existing check

        $("#email").focusout(function(e){
            var email = $("#email").val();
            var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
            if(email != ''){
                if(!email.match(pattern)){
                    count++;
                    if ($(this).parent().next(".validation").length == 0)
                    {
                        email_validation++;
                        $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>Invalid email id</div>");
                    }else{
                        email_validation++;
                        $("#email").parent().next(".validation").remove();
                        $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>Invalid email id</div>");
                    }
                }
                else
                {
                    $(this).parent().next(".validation").remove();
                    $.ajax({
                        url : "{{url('/')}}/admin/check_subadmin_email_exist",
                        method : "get",
                        data : {'email':email,'id':admin_id},
                        success : function (response){
                            if(response.status == true){
                                if ($(this).parent().next(".validation").length == 0)
                                {
                                    email_validation++;
                                    $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>email is already exists</div>");
                                }else{
                                    email_validation++;
                                    $("#email").parent().next(".validation").remove();
                                    $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>email is already exists</div>");
                                }
                            }else{
                                email_validation = 0;
                                $(this).parent().next(".validation").remove();
                            }
                        }
                    });
                }
            }
        });


        $(".analyze").click(function(e){
            counter = 0;
            $(".required").each(function() {
                var value = $(this).val();
                if(value == null || value == ""){
                    if ($(this).parent().next(".validation").length == 0) // only add if not added
                    {
                        $(this).parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>This field is required</div>");
                        counter++;
                    }else{
                        $(this).parent().next(".validation").remove();
                        $(this).parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>This field is required</div>");
                        counter++;
                    }
                }else{
                    $(this).parent().next(".validation").remove();
                }
            });
            $('#privilage_err').hide();
            var total_privileages = $('#addform input[type=checkbox]:checked').length;
            if(total_privileages<=0)
            {
                $('#privilage_err').show();
                counter++;
            }

            var email = $("#email").val();
            var pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
            if(email != ''){
                if(!email.match(pattern)){
                    if ($("#email").parent().next(".validation").length == 0)
                    {
                        counter++;
                        $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>Invalid email id</div>");
                    }else{
                        counter++;
                        $("#email").parent().next(".validation").remove();
                        $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>Invalid email id</div>");
                    }
                }
                else
                {
                    $("#email").parent().next(".validation").remove();
                    $.ajax({
                        url : "{{url('/')}}/admin/check_subadmin_email_exist",
                        method : "get",
                        data : {'email':email,'id':admin_id},
                        success : function (response){
                            if(response.status == true){
                                if ($("#email").parent().next(".validation").length == 0)
                                {
                                    counter++;
                                    $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>email is already exists</div>");
                                }else{
                                    counter++;
                                    $("#email").parent().next(".validation").remove();
                                    $("#email").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>email is already exists</div>");
                                }
                            }else{
                                $("#email").parent().next(".validation").remove();
                            }
                        }
                    });
                }
            }

            var phone = $("#number").val();
            if(phone != ''){
                $.ajax({
                    url : "{{url('/')}}/admin/check_subadmin_phone_exist",
                    method : "get",
                    data : {'phone':phone,'id':admin_id},
                    success : function (response){
                        if(response.status == true){
                            if ($("#number").parent().next(".validation").length == 0)
                            {
                                counter++;
                                $("#number").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>phone number is already exists</div>");
                            }else{
                                counter++;
                                $("#number").parent().next(".validation").remove();
                                $("#number").parent().after("<div class='validation' style='position: absolute;margin-top: -2%;color:red;'>phone number is already exists</div>");
                            }
                        }else{
                            $("#number").parent().next(".validation").remove();
                        }
                    }
                });
            }

            if(counter > 0 || phone_validation > 0 || email_validation >0){
                e.preventDefault();
            }else{
                $("#addform").submit();
            }
        });
    </script>
    <script src="{{URL::asset('public/app-assets/js/scripts/pages/dispmanager.js')}}" type="text/javascript"></script>  
@endsection
