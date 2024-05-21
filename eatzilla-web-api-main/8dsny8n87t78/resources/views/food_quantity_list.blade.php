@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')
@php $qtyAccess = explode(",",auth()->user()->AccessPrivilages->food_quantity); @endphp
  <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{strtoUpper(trans('constants.food_qty'))}}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard">{{strtoUpper(trans('constants.dashboard'))}}</a>
                </li><li class="breadcrumb-item"><a href="#">{{strtoUpper(trans('constants.food_qty'))}} {{strtoUpper(trans('constants.list'))}}</a>
                </li>
              </ol>
            </div>
          </div>
        </div>
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
                  <h4 class="card-title"></h4>
                  <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>

                  <div class="heading-elements">
                     <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                      <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                      <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                     <li @if(auth()->user()->role==3 && !in_array(2,(array)$qtyAccess)) style="display:none" @endif> <button class="btn btn-primary btn-sm"><i class="ft-plus white"></i><a style=" color: white !important;" href="{{url('/')}}/admin/add-food-quantity"> {{trans('constants.add')}} {{trans('constants.food_qty')}}</a></button></li>
                      </ul>
                      </div>
                      
                    </div>
                </div>


               <div class="card-content collapse show">
                  <div class="card-body card-dashboard">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered zero-configuration">
                      <thead> 
                          <tr>
                            <th>{{trans('constants.sno')}}</th>
                            <th>{{trans('constants.name')}}</th>
                            <th>{{trans('constants.action')}}</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php $i=1; @endphp
                          @foreach($food_quantity_list as $value)
                            <tr>
                              <td>{{$i}}</td>
                              <td>{{$value->name}}</td>
                              <td>
                                <a href="{{url('/')}}/admin/edit-food-quantity/{{$value->id}}" class="button btn btn-icon btn-success mr-1 link_clr" @if(auth()->user()->role==3 && !in_array(3,(array)$qtyAccess)) style="display:none" @endif><i class="ft-edit"></i></a>
                                <button type="button" class="btn btn-icon btn-success mr-1 link_clr" data-id="1" data-toggle="modal"  data-target="#{{$value->id}}" @if(auth()->user()->role==3 && !in_array(4,(array)$qtyAccess)) style="display:none" @endif><i class="ft-delete"></i></button>
                              </td>
                            </tr>
                            <!----------  Delete Banner Model --------------------->

                            <div class="modal animated slideInRight text-left" id="{{$value->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                              <div class="modal-content">
                                <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel76">Delete {{__('constants.food_qty')}}</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                  </button>
                                </div>
                                <form method="post" action="{{url('/')}}/admin/delete_food_quantity">
                                  <div class="modal-body">
                                    
                                      <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        <input type="hidden" name="id" value="{{$value->id}}">
                                        <div class="form-group">
                                          <label for="eventName2">Are you sure to delete {{__('constants.food_qty')}} : {{$value->name}} ?
                                            <br>Some Food Items are based on this {{__('constants.food_qty')}} will also be deleted. 
                                            <br>If you need those Food list, kindly remove the {{__('constants.food_qty')}} from that list before delete!
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
                          <!----------  Delete Banner Model Ends--------------------->
                          @php $i=$i+1; @endphp
                          @endforeach
                        </tbody>
                   
                        <tfoot>
                          <tr>
                             <th>{{trans('constants.sno')}}</th>
                            <th>{{trans('constants.name')}}</th>
                            <th>{{trans('constants.action')}}</th>
                          </tr>
                         </tfoot>
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
  
    @endsection     
 