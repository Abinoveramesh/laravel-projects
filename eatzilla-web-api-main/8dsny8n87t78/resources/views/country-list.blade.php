@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')
@php $cityAccess = explode(",",auth()->user()->AccessPrivilages->city_management); @endphp
<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
      <h3 class="content-header-title mb-0 d-inline-block"> {{strtoUpper(trans('constants.country'))}}  {{strtoUpper(trans('constants.list'))}}</h3>
      <div class="row breadcrumbs-top d-inline-block">
        <div class="breadcrumb-wrapper col-12">
          <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="{{url('/')}}/admin/city_list" class="colorname">{{ strtoUpper(trans('constants.city')) }} {{ strtoUpper(trans('constants.list')) }}</a>
                </li>
            <li class="breadcrumb-item">
              <a href="#" class="colorname">{{trans('constants.country')}} {{strtoLower(trans('constants.list'))}} </a>
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
                <h4 class="card-title"> {{trans('constants.country')}} {{strtoLower(trans('constants.list'))}}</h4>
                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>

                <div class="heading-elements">
                 <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                  <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                  <li><a data-action="expand"><i class="ft-maximize"></i></a></li>

                     <li @if(auth()->user()->role==3 && !in_array(8,(array)$cityAccess)) style="display:none" @endif> <button class="btn btn-sm btncolorname"><i class="ft-plus white"></i><a style=" color: white !important;" href="{{url('/')}}/admin/add_country"> {{trans('constants.add')}} {{trans('constants.country')}}</a></button></li>
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
                      <th>{{trans('constants.country')}}</th>   
                      <th>{{trans('constants.country')}} {{trans('constants.code')}}</th>   
                      <th>{{trans('constants.currency')}} {{trans('constants.code')}}</th>   
                      <th>{{trans('constants.currency')}} {{trans('constants.symbol')}}</th> 
                      <th>{{trans('constants.action')}}</th>   
                    </tr>
                  </thead>
                  <tbody>
                    @php $i = 1; @endphp
                    @forelse($data as $d)
                    <tr>
                      <td>@php echo $i++; @endphp</td>   
                      <td>{{$d->country}}</td>   
                      <td>{{$d->country_code}}</td>   
                      <td>{{$d->currency_code}}</td>   
                      <td>{{$d->currency_symbol}}</td>   
                      <td>
                        <button class="btn btn-sm btncolorname" @if(auth()->user()->role==3 && !in_array(9,(array)$cityAccess)) style="display:none" @endif><a style=" color: white !important;" href="{{URL('/')}}/admin/edit_country/{{$d->id}}"><i class="ft-edit white"></i></a></button>
                       @if($d->is_default == 0 )
                        <button class="btn btn-info btn-sm" @if(auth()->user()->role==3 && !in_array(10,(array)$cityAccess)) style="display:none" @endif><a style=" color: white !important;" href="{{URL('/')}}/admin/default_country/{{$d->id}}">Make default</a></button>
                      @else
                        <button class="btn btn-warning btn-sm">default</button>
                      @endif</td>   
                    </tr>
                    @empty
                    {{trans('constants.no_data')}}
                    @endforelse
                  </div>
                </div>

                @endsection     
