@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')
<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
      <h3 class="content-header-title mb-0 d-inline-block"> {{strtoUpper(trans('constants.instruction'))}} {{strtoUpper(trans('constants.list'))}}</h3>
      <div class="row breadcrumbs-top d-inline-block">
        <div class="breadcrumb-wrapper col-12">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard" class="brand-font-link-color">{{strtoUpper(trans('constants.dashboard'))}}</a>
            <li class="breadcrumb-item">
              <a href="#" class="brand-font-link-color">{{strtoUpper(trans('constants.instruction'))}} {{strtoUpper(trans('constants.list'))}} </a>
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
                <h4 class="card-title"> &nbsp;</h4>
                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>

                <div class="heading-elements">
                 <ul class="list-inline mb-0" style="margin-right:20px;">
                  <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                  <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                  <li><a data-action="expand"><i class="ft-maximize"></i></a></li>

                     <li> <button class="btn success-button-style btn-sm" style="margin-left:5px;"><i class="ft-plus white"></i><a style=" color: white !important;" href="{{url('/')}}/admin/add_delivery_instruction"> {{trans('constants.add')}} {{trans('constants.delivery')}} {{trans('constants.instruction')}}</a></button></li>
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
                      <th>{{trans('constants.instruction')}}</th>  
                      <th>{{trans('constants.action')}}</th>   
                    </tr>
                  </thead>
                  <tbody>
                    @php $i=1; @endphp
                        @foreach($data as $value)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{$value->instruction}}</td>
                                <td>
                                    <button type="button" class="btn btn-icon cancel-button-style mr-1 link_clr" data-id="1" data-toggle="modal"  data-target="#delete{{$value->id}}" @if(auth()->user()->role==3 && !in_array(1,(array)$usersAccess)) style="display:none" @endif><i class="ft-delete"></i></button>
                                    <a href="{{url('/')}}/admin/edit_delivery_instruction/{{$value->id}}" class="button btn btn-icon success-button-style mr-1 link_clr"><i class="ft-edit"></i></a>
                                </td>                        
                            </tr>
                          <!----------  Delete User Partner Model --------------------->
                          <div class="modal animated slideInRight text-left" id="delete{{$value->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <h4 class="modal-title" id="myModalLabel76">{{trans('constants.delete')}} {{trans('constants.instruction')}}</h4>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                              <span aria-hidden="true">&times;</span>
                                          </button>
                                      </div>
                                      <form method="post" action="{{url('/')}}/admin/delete_delivery_instruction">
                                          <div class="modal-body">
                                              <input type="hidden" name="_token" value="{{csrf_token()}}">
                                              <input type="hidden" name="id" value="{{$value->id}}">
                                              <div class="form-group">
                                                  <label for="eventName2">{{trans('constants.sure_delete_msg')}} {{trans('constants.this')}} {{trans('constants.instruction')}} : {{$value->name}}</label>
                                              </div>
                                          </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn cancel-button-style mr-1" data-dismiss="modal" style="padding: 10px 15px;">
                                                  <i class="ft-x"></i> {{trans('constants.cancel')}}
                                              </button>
                                              <button type="submit" class="btn success-button-style mr-1" style="padding: 10px 15px;">
                                                  <i class="ft-check-square"></i> {{trans('constants.delete')}}
                                              </button>
                                          </div>
                                      </form>
                                  </div>
                              </div>
                            </div>
                            <!----------  Delete User Model Ends--------------------->
                            @php $i++; @endphp
                        @endforeach
                    </div>
                </div>

                @endsection     
