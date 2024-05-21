@extends('layout.master')

@section('title')

{{APP_NAME}}

@endsection

@section('content')
<style>
     .checked {
         color: orange;
        }
       ul {
         list-style-type: none;
        }
        .link_clr{
          color: white;
        }
        .height-150 {
    height: 100px !important;
    width: 100px;
}
</style>

 
    <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
          <h3 class="content-header-title mb-0 d-inline-block">Driver Details</h3>
        </div>
      </div>
      <div class="content-body">
       <div class="card">
         <div class="card-header">
            <div class="row">
                <div class="col-xl-6 col-lg-6 col-12">
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Driver Name </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{$data->name}}</span> </dd>
                </div>
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Contact </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{$data->phone}}</span> </dd>
                </div>
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Email </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{$data->email}}</span> </dd>
                </div>
                @if(session()->get('role')!=2)
               <!--  <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Address Line1 </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{$data->Deliverypartner_detail->address_line_1}}</span> </dd>
                </div>
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Address Line2 </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{$data->Deliverypartner_detail->address_line_2}}</span> </dd>
                </div> -->
                <!-- <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Country </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->Country)?$data->Deliverypartner_detail->Country->country:""}}</span> </dd>
                </div>
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">State </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->State)?$data->Deliverypartner_detail->State->state:""}}</span> </dd>
                </div>
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">City </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->Citylist)?$data->Deliverypartner_detail->Citylist->city:""}}</span> </dd>
                </div>
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Zipcode </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->zip_code)?$data->Deliverypartner_detail->zip_code:""}}</span> </dd>
                </div>
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">About </dt>
                    <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->about)?$data->Deliverypartner_detail->about:""}}</span> </dd>
                </div> -->
                @endif
                <div class="row m-1">
                    <dt class="col-sm-3 order-txt p-0">Status </dt>
                    <dd class="col-sm-9 order-txt "><span>:<button class="btn btn-danger">
                    @php

                    switch ((int) $data->status) {
                        case 1:
                        echo 'Active';
                        break;
                        case 2:
                        echo 'In Active ';
                        break;
                        
                        
                        default:
                        echo 'NULL';
                        break;
                    }
                    @endphp </button></span> </dd>
                </div>
                </div>
            </div>
          </div>
        </div>
       </div>    
    @if(session()->get('role')!=2)
      @if($data->delivery_mode!=1)
    <!--   <div class="content-body">
       <div class="card"> 
        <div class="card-header">
         <h3>Document and Vehicle Details </h3>
         <br>
            <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">Drivers Licence </dt>                
                <dd class="col-sm-9 order-txt ">:
                    <img id="blah" src="{{SPACES_BASE_URL.$data->license}}" alt="your image" width="180px" height="100px"><br>
                </dd>
            </div>
            <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">License Expiry Date </dt>
                <dd class="col-sm-9 order-txt ">:
                  @if($data->expiry_date!='') {{date('Y-m-d',strtotime($data->expiry_date))}} @endif
                </dd>
            </div>             
            <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">Registration Number</dt>
                <dd class="col-sm-9 order-txt "><span>: {{isset($data->Vehicle->vehicle_name)?$data->Vehicle->vehicle_name:""}}</span> </dd>
            </div>
            <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">Registration Expiry Date</dt>
                <dd class="col-sm-9 order-txt ">: @if(isset($data->Vehicle->registration_expiry_date)) {{date('Y-m-d',strtotime($data->Vehicle->registration_expiry_date))}} @endif</dd>
            </div>
            
             <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">{{trans('constants.insurance_image')}}</dt>
                <dd class="col-sm-9 order-txt "><span>: <img src="{{SPACES_BASE_URL.$data->Vehicle->insurance_image}}" alt="your image" width="180px" height="100px"></span> </dd>
            </div>
            <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">{{trans('constants.vehicle_no')}}</dt>
                <dd class="col-sm-9 order-txt "><span>: @if(isset($data->Vehicle->vehicle_no) && $data->Vehicle->vehicle_no!='') {{$data->Vehicle->vehicle_no}} @endif</span> </dd>
            </div>
            <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">{{trans('constants.insurance')}} {{trans('constants.expiry_date')}}</dt>                
                <dd class="col-sm-9 order-txt "><span>: @if(isset($data->Vehicle->insurance_expiry_date) && $data->Vehicle->insurance_expiry_date!='') {{date('Y-m-d',strtotime($data->Vehicle->insurance_expiry_date))}} @endif</span>
                </dd>
            </div>   
            <div class="row m-1">
                <dt class="col-sm-3 order-txt p-0">{{trans('constants.rc_image')}}</dt>
                <dd class="col-sm-9 order-txt "><span>: 
                    <img id="blah" src="{{SPACES_BASE_URL.$data->Vehicle->rc_image}}" alt="your image"  width="180px" height="100px"><br></span> 
                </dd>
            </div>
                       
       </div>
        </div>
      </div> -->
      @endif
    @endif
    @if(session()->get('role')!=2)
<!--       <div class="content-body">
       <div class="card"> 
        <div class="card-header">
         <h3>Driver Bank Details</h3><br>
         <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Account Name </dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->account_name)?$data->Deliverypartner_detail->account_name:""}}</span> </dd>
        </div>
        <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Account No</dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->account_no)?$data->Deliverypartner_detail->account_no:""}} </span> </dd>
        </div>    
        @if(session()->get('role')!=2)      
         <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Account Address </dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->account_address)?$data->Deliverypartner_detail->account_address:""}}</span> </dd>
        </div>
        @endif
        <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Bank Name</dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->bank_name)?$data->Deliverypartner_detail->bank_name:""}} </span> </dd>
        </div>

         <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Branch Name </dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->branch_name)?$data->Deliverypartner_detail->branch_name:""}}</span> </dd>
        </div>
        <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Branch Address </dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->branch_address)?$data->Deliverypartner_detail->branch_address:""}}</span> </dd>
        </div>
        @if(session()->get('role')!=2)
        <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Swift Code </dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->swift_code)?$data->Deliverypartner_detail->swift_code:""}}</span> </dd>
        </div>
        <div class="row m-1">
            <dt class="col-sm-3 order-txt p-0">Routing No </dt>
            <dd class="col-sm-9 order-txt "><span>: {{isset($data->Deliverypartner_detail->routing_no)?$data->Deliverypartner_detail->routing_no:""}}</span> </dd>
        </div>
        @endif
       </div>
        </div>
      </div> -->
      @endif
       
        <!-- // Basic form layout section end -->
      </div>
   <!-- // Basic form layout section end -->
      
 

   @endsection     
