@extends('layout.master')

@section('title')
{{APP_NAME}}

@endsection

@section('content')
<style>
    .form-control:focus {
    border-color: #E3E3E3;
    box-shadow: none;
    }
    .no-cursor-change {
    cursor: default !important;
    }
</style>
<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
      <h3 class="content-header-title mb-0 d-inline-block">{{strtoUpper($type)}} {{strtoUpper(trans('constants.payout'))}}</h3>
      <div class="row breadcrumbs-top d-inline-block">
        <div class="breadcrumb-wrapper col-12">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="{{url('/')}}/admin/dashboard" class="brand-font-link-color">{{strtoUpper(trans('constants.dashboard'))}}</a>
            </li>
            <li class="breadcrumb-item">
              <a href="#" class="brand-font-link-color">{{strtoUpper($type)}} {{strtoUpper(trans('constants.payout'))}}</a>
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
                <h4 class="card-title">&nbsp;</h4>
                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>

                <div class="heading-elements">
                 <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                  <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                  <li><a data-action="expand"><i class="ft-maximize"></i></a></li>

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
                      <th>{{Ucfirst($type)}}</th>   
                      <th>{{trans('constants.total')}}</th>   
                      <th>{{trans('constants.transaction_id')}}</th>  
                      <th>{{trans('constants.description')}}</th> 
                      <th>Transaction Date</th> 
                      <th>{{trans('constants.status')}}</th>   
                    </tr>
                  </thead>
                  <tbody>
                    @php $i = 1; @endphp
                    @forelse($data as $d)
                    <tr>
                      <td>@php echo $i++; @endphp</td>
                      @php 
                      if($d->Deliverypartners){
                        $name = $d->Deliverypartners->name;
                      }
                      elseif($d->Restaurants){
                        $name = $d->Restaurants->restaurant_name;
                      }
                      else{
                        $name = '';
                      }
                      @endphp
                      <td>{{($name)}}</td>
                      <td>{{DEFAULT_CURRENCY_SYMBOL}} {{$d['payout_amount']}}</td>
                      <td>{{$d['transaction_id']}}</td>
                      <td>{{$d['description']}}</td>
                      <td>{{$d['trans_datetime']}}</td>
                      @if(!empty($d['trans_status']))
                      <td><a href="#" data-toggle="modal" data-target="#payout_status" class ="payout" url="1" merchant_ref_no="{{$d->merchant_ref_no}}" payout_trans_id="{{$d->payout_trans_id}}" payout_id="{{$d->id}}"><span>View</span></a></td>   
                      @else
                      <td><span>{{$d['trans_status']}}</span></td>   
                      @endif
                    </tr>
                    @empty
                      {{trans('constants.no_data')}}
                    @endforelse
                  </div>
                </div>
                    <div class="modal fade" id="payout_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index: 99999;">
                      <div class="modal-dialog" role="document">
                          <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">PAYMENT STATUS</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" id="popup-content">
                    
                                </div>
                                <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                          </div>
                      </div>
                  </div>
            <script>               
              $(document).on("click", ".payout", function () {
                var payout_id = $(this).attr('payout_id');
                var merchant_ref_no = $(this).attr('merchant_ref_no');
                var payout_trans_id = $(this).attr('payout_trans_id');
                $.ajax({
                  url: "{{url('/')}}/admin/payout_status",
                  type: "GET",
                  cache: false,
                  dataType: "json",
                  data: {
                    payout_id: payout_id,
                    merchant_ref_no:merchant_ref_no,
                    payout_trans_id:payout_trans_id
                  },
                  success : function (data)
                   {
                     console.log(data);
                     var response = data;
                     var popupConent = '<div class="refund">'+
                    '<div class="row clearfix">'+
                        '<div class="col-lg-4 col-md-4 col-sm-4 form-control-label">'+
                            '<label for="start_date">Benifitor Name</label>'+
                        '</div>'+
                       '<div class="col-lg-8 col-md-8 col-sm-8">'+
                            '<div class="form-group">'+
                                '<input type="text" id="reference_no" class="form-control prevent_typing" value="'+data.restaurants.restaurant_name+'">'+
                            '</div>'+
                        '</div>'+
                   ' </div>'+
                    '<div class="row clearfix">'+
                        '<div class="col-lg-4 col-md-4 col-sm-4 form-control-label">'+
                            '<label for="fbinsta">Payment Mode</label>'+
                       ' </div>'+
                        '<div class="col-lg-8 col-md-8 col-sm-8">'+
                            '<div class="form-group">'+
                                '<input type="text" id="request_id" class="form-control prevent_typing" value="Cash">'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="row clearfix">'+
                        '<div class="col-lg-4 col-md-4 col-sm-4 form-control-label">'+
                            '<label for="fbinsta">Pay Status</label>'+
                        '</div>'+
                        '<div class="col-lg-8 col-md-8 col-sm-8">'+
                            '<div class="form-group">'+
                                '<input type="text" id="refund_amt" name="refund_amt" class="form-control prevent_typing" value="SUCCESS">'+
                            '</div>'+
                        '</div>'+
                    '</div>';
                  
                    $('#popup-content').html(popupConent);
                    $('.prevent_typing').on('keydown', function(e) {
                    e.preventDefault(); // Cancels the keydown event
                    });
                        $('.prevent_typing').css('caret-color', 'transparent');
                        $('.prevent_typing').hover(function() {
                            $(this).addClass("no-cursor-change");
                        }, function() {
                        $(this).removeClass("no-cursor-change");
                    });
              }                     
              });
                });
            </script>
            
@endsection
