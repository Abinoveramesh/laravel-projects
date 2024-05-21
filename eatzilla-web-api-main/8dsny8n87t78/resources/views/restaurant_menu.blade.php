@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')
 <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">MENU LIST</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard" class="colorname">{{strtoUpper(trans('constants.dashboard'))}}</a>
                </li>
                <li class="breadcrumb-item"><a href="#" class="colorname">MENU LIST</a>
                </li>
               
              </ol>
            </div>
          </div>
        </div>
        <!-- <div class="content-header-right col-md-6 col-12">
          <div class="dropdown float-md-right">
            <button class="btn btn-danger dropdown-toggle round btn-glow px-2" id="dropdownBreadcrumbButton"
            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button>
            <div class="dropdown-menu" aria-labelledby="dropdownBreadcrumbButton"><a class="dropdown-item" href="#"><i class="la la-calendar-check-o"></i> Calender</a>
              <a class="dropdown-item" href="#"><i class="la la-cart-plus"></i> Cart</a>
              <a class="dropdown-item" href="#"><i class="la la-life-ring"></i> Support</a>
              <div class="dropdown-divider"></div><a class="dropdown-item" href="#"><i class="la la-cog"></i> Settings</a>
            </div>
          </div>
        </div> -->
      </div>
      <div class="content-body">
        <!-- Basic form layout section start -->


         <section id="configuration">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-head">
                  <div class="card-header">
                  <h4 class="card-title" style="height:50px;color:red;">
                </h4>
                  <h4 class="card-title">&nbsp;</h4>
                  <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>

                  <div class="heading-elements">
                     <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                      <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                      <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                    <li>  <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add_res_menu"><i class="ft-plus white"></i>Add Restaurant Menu</button>
                     </li>
                   </ul>
                  </div>   
                </div>
                </div>

                <div class="modal animated slideInRight text-left" id="add_res_menu" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
                       <div class="modal-dialog" role="document">
                        <div class="modal-content">
                         <div class="modal-header">
                          <h4 class="modal-title" id="myModalLabel76">Add Restaurant Menu</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                         </div>
                         <form method="post" action="{{url('/')}}/admin/add_restaurant_menu">
                            <div class="modal-body">
                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                @if(session()->get('role')==1)
                                  <div class="form-group">
                                      <label for="eventName2">Restaurant<span style="color: red;">*</span></label>
                                      <select name="restaurant_id" id="restaurant_id" onchange="getrestaurant_based_detail()" class="form-control" required="">
                                        @foreach($restaurant as $res)
                                          @if(isset($res->restaurant_name))
                                            <option value="{{$res->id}}">{{$res->restaurant_name}}</option>
                                        @endif
                                        @endforeach
                                    </select> 
                                  </div>
                                @else
                                  <input type="hidden" name="restaurant_id" value="{{session()->get('userid')}}" id="restaurant_id">
                                @endif
                                 <div class="form-group">
                                    <label for="eventName2">Menu Name:</label>
                                    <input type="text" class="form-control" name="menu_name" required="">
                                  </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-warning mr-1" data-dismiss="modal" style="padding: 10px 15px;">
                                   <i class="ft-x"></i> Cancel
                                    </button>
                                  <button type="submit" class="btn btn-primary mr-1" style="padding: 10px 15px;">
                                <i class="ft-check-square"></i> Add
                                 </button>
                            </div>
                          </form>
                      </div>
                    </div>
                  </div>

                <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered zero-configuration">
                      <thead> 
                          <tr>
                            <th>SI</th>
                            <th>Name</th>
                            @if(session()->get('role')==1)
                              <th>Restaurant Name</th>
                            @endif
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $i=1; ?>
                          @foreach($data as $d)
                          <tr>
                            <td>{{$i}}</td>
                            <td>{{$d->menu_name}}</td>
                            @if(session()->get('role')==1)
                              <td> @isset($d->Restaurant->restaurant_name) {{$d->Restaurant->restaurant_name}} @endisset</td>
                            @endif
                            <td><button type="button" class="button btn btn-icon btn-success mr-1" data-id="1" data-toggle="modal"  
                                data-target="#{{$d->id}}"><i class="ft-edit"></i></button>
                                 <button type="button" class="btn btn-icon btn-success mr-1 link_clr" data-id="1" data-toggle="modal"  data-target="#delete{{$d->id}}"><i class="ft-delete"></i></button></li>
                            </td>
                          </tr>
                          <?php $i++; ?>
                           <!----------  Edit Cuisine Partner Model --------------------->
                        <div class="modal animated slideInRight text-left" id="{{$d->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
                               <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                 <div class="modal-header">
                                  <h4 class="modal-title" id="myModalLabel76">Edit Restaurant Menu</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                 </div>
                                 <form method="post" action="{{url('/')}}/admin/update_restaurant_menu">
                                    <div class="modal-body">
                                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        <input type="hidden" name="id" value="{{$d->id}}">
                                        @if(session()->get('role')==1)
                                          <div class="form-group">
                                              <label for="eventName2">Restaurant<span style="color: red;">*</span></label>
                                              <select name="restaurant_id" id="restaurant_id" class="form-control" required="">
                                                @foreach($restaurant as $res)
                                                  @if(isset($res->restaurant_name))
                                                    <option value="{{$res->id}}" @if($res->id==$d->restaurant_id) selected @endif>{{$res->restaurant_name}}</option>
                                                  @endif
                                                @endforeach
                                            </select> 
                                          </div>
                                        @else
                                          <input type="hidden" name="restaurant_id" value="{{session()->get('userid')}}" id="restaurant_id">
                                        @endif
                                        <div class="form-group">
                                          <label for="eventName2">Menu Name:</label>
                                          <input type="text" class="form-control" id="cuisine_name" name="menu_name" value="{{$d->menu_name}}">
                                        </div>
                                    
                                    </div>
                                     <div class="modal-footer">
                                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal" style="padding: 10px 15px;">
                                           <i class="ft-x"></i> Cancel
                                            </button>
                                          <button type="submit" class="btn btn-primary mr-1" style="padding: 10px 15px;">
                                        <i class="ft-check-square"></i> Update
                                         </button>
                                    </div>
                                  </form>
                              </div>
                            </div>
                          </div>
                           <!----------  Edit Cuisine Model Ends--------------------->
                            <!----------  Delete Cuisine Partner Model --------------------->

                                  <div class="modal animated slideInRight text-left" id="delete{{$d->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
                                       <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                         <div class="modal-header">
                                          <h4 class="modal-title" id="myModalLabel76">Delete Menu</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                         </div>
                                         <form method="post" action="{{url('/')}}/admin/delete_restaurant_menu/{{$d->id}}">
                                            <div class="modal-body">
                                              
                                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                                  <input type="hidden" name="id" value="{{$d->id}}">
                                                 <div class="form-group">
                                                    <label for="eventName2">Are you sure to delete  : {{$d->menu_name}}
                                                    <br>Some Food Items are based on this menu will also be deleted.
                                                    </label>
                                                  </div>
                                            
                                            </div>
                                             <div class="modal-footer">
                                                <button type="button" class="btn btn-warning mr-1" data-dismiss="modal" style="padding: 10px 15px;">
                                                   <i class="ft-x"></i> Cancel
                                                    </button>
                                                  <button type="submit" class="btn btn-primary mr-1" style="padding: 10px 15px;">
                                                <i class="ft-check-square"></i> Delete
                                                 </button>
                                            </div>
                                          </form>
                                      </div>
                                    </div>
                                  </div>
                                   <!----------  Delete Cuisine Model Ends--------------------->
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- // Basic form layout section end -->
      </div>
    </div>
 
    @endsection     
 