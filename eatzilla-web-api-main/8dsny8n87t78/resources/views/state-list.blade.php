@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')
@php $cityAccess = explode(",",auth()->user()->AccessPrivilages->city_management); @endphp
<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
      <h3 class="content-header-title mb-0 d-inline-block"> {{strtoUpper(trans('constants.city_name'))}}  {{strtoUpper(trans('constants.list'))}}</h3>
      <div class="row breadcrumbs-top d-inline-block">
        <div class="breadcrumb-wrapper col-12">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{url('/')}}/admin/city_list" class="brand-font-link-color">{{ strtoUpper(trans('constants.city')) }} {{ strtoUpper(trans('constants.list')) }}</a>
                </li>
            <li class="breadcrumb-item">
              <a href="#" class="brand-font-link-color">{{trans('constants.city_name')}} {{strtoLower(trans('constants.list'))}} </a>
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
                <h4 class="card-title"> {{trans('constants.city_name')}} {{strtoLower(trans('constants.list'))}}</h4>
                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>

                <div class="heading-elements">
                 <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                  <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                  <li><a data-action="expand"><i class="ft-maximize"></i></a></li>

                     <li @if(auth()->user()->role==3 && !in_array(12,(array)$cityAccess)) style="display:none" @endif> <button class="btn btn-sm success-button-style"><i class="ft-plus white"></i><a style=" color: white !important;" href="{{url('/')}}/admin/add_state"> Add City</a></button></li>
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
                      <th>SI.No</th>
                      <th>{{trans('constants.city_name')}}</th>
                      <th>{{trans('constants.country')}}</th>
                      <th>{{trans('constants.state_name')}}</th>
                      <th>{{trans('constants.city_code')}}</th>
                      <th @if(auth()->user()->role==3 && !in_array(13,(array)$cityAccess)) style="display:none" @endif>{{trans('constants.action')}}</th>   
                    </tr>
                  </thead>
                  <tbody>
                    @php $i = 1; @endphp
                    @forelse($data as $d)
                    <tr>
                      <td>@php echo $i++; @endphp</td> 
                      <td>{{$d->state}}</td>   
                      <td>@if(!empty($d->Country)) {{$d->Country->country}} @endif</td>
                      <td>@if(!empty($d->NewState)) {{$d->NewState->state}} @endif</td>
                      <td>{{$d->city_code}}</td>   
                      <td @if(auth()->user()->role==3 && !in_array(13,(array)$cityAccess)) style="display:none" @endif>
                        <button class="btn success-button-style btn-sm"><a style=" color: white !important;" href="{{URL('/')}}/admin/edit_state/{{$d->id}}"><i class="ft-edit white"></i></a></button>
                        <button class="btn btn-sm cancel-button-style"><a style=" color: white !important;" href="{{URL('/')}}/admin/delete_state/{{$d->id}}"><i class="ft-delete brand-font-link-color"></i></a></button>
                      </td>
                    </tr>
                    @empty
                    {{trans('constants.no_data')}}
                    @endforelse
                  </div>
                </div>

                @endsection     
