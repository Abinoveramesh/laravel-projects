@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')
  <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{strtoUpper(trans('constants.cms'))}}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard" class="brand-font-link-color">{{strtoUpper(trans('constants.dashboard'))}}</a>
                </li><li class="breadcrumb-item"><a href="#" class="brand-font-link-color">{{strtoUpper(trans('constants.cms'))}} {{strtoUpper(trans('constants.manage'))}}</a>
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
                      @if(session()->get('role')!=1)
                        <li> <button class="btn btn-primary btn-sm"><i class="ft-plus white"></i><a style=" color: white !important;" href="{{url('/')}}/admin/add_addons"> {{trans('constants.add')}} {{trans('constants.addon')}}</a></button></li>
                      @endif
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
                            <th>{{trans('constants.title')}} {{trans('constants.name')}}</th>
                            <th>{{trans('constants.action')}}</th>
                            </thead>
                        <tbody>
                          @php $i=1; @endphp
                          @foreach($data as $value)
                            <tr>
                              <td>{{$i}}</td>
                              <td>{{$value->title}}</td>
                              <td>
                                <a href="{{url('/')}}/admin/page/{{$value->page_name}}" class="button btn btn-icon success-button-style mr-1 link_clr"><i class="ft-edit"></i></a>
                              </td>
                          
                            </tr>
                          @php $i=$i+1; @endphp
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
  
    @endsection     
 