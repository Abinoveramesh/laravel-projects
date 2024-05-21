@extends('layout.master')

@section('title')

{{APP_NAME}}
@endsection

@section('content')

   <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{strtoUpper($title)}} {{strtoUpper(trans('constants.order'))}} {{strtoUpper(trans('constants.list'))}}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/order_dashboard">{{strtoUpper(trans('constants.order'))}} {{strtoUpper(trans('constants.dashboard'))}}</a>
                </li>
                <li class="breadcrumb-item"><a href="#">{{strtoUpper($title)}} {{strtoUpper(trans('constants.order'))}} {{strtoUpper(trans('constants.list'))}}</a>
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
                  <h4 class="card-title"></h4>
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
                            <th>{{trans('constants.order_id')}}</th>
                            <th>{{trans('constants.customer')}} {{trans('constants.name')}}</th>
                            <th>{{trans('constants.delivery_people')}}</th>
                            <th>{{trans('constants.restaurant')}}</th>
                            <th>{{trans('constants.address')}}</th>
                            <th>{{trans('constants.total')}}</th>
                            <th>{{trans('constants.order_det')}}</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @php $i=1; @endphp
                          @foreach($data as $val)
                          <tr>
                            <td>{{$i}}</td>
                            <td>{{$val->order_id}}</td>
                            <td>@isset($val->Users) {{$val->Users->name}} <br>{{$val->Users->phone}} @endisset</td>
                            <td>@isset($val->Deliverypartners) {{$val->Deliverypartners->name}} @endisset</td>
                            <td>@isset($val->Restaurants) {{$val->Restaurants->restaurant_name}}<br>
                            {{$val->Restaurants->phone}} @endisset</td>
                            <td>{{$val->delivery_address}}</td>
                            <td>{{DEFAULT_CURRENCY_SYMBOL}} {{$val->bill_amount}}</td>
                            <td>   
                               <a href="{{url('/')}}/admin/view_order/{{$val->id}}" class="btn btn-success">{{trans('constants.view')}} {{trans('constants.order')}}</a>
                          </td>
                          <td>
                            @if($val->status==0)
                              @if(session()->get('role')==1)
                                <a href="#">{{trans('constants.wait_for_accept')}}</a>
                              @else
                                <button onclick="assign_driver_list({{$val->id}})" class="btn btn-info">Approve</button>
                                <a href="{{url('/')}}/admin/cancel_request/{{$val->id}}" class="btn btn-info">{{trans('constants.cancel')}}</a>
                              @endif
                            @endif
                            @if($val->status==1)
                              @if(session()->get('role')==1)
                                <button onclick="driver_assign({{$val->id}});" class="btn btn-info">Assign</button>
                              @else
                                 <button onclick="ready_for_pickup({{$val->id}});" class="btn btn-info">Ready for Pickup</button>
                                <!-- <button onclick="assign_driver_list({{$val->id}});" class="btn btn-info">Assign</button> -->

                              @endif
                                <a href="{{url('/')}}/admin/cancel_request/{{$val->id}}" class="btn btn-info">{{trans('constants.cancel')}}</a>
                            @endif
                            @if($val->status==2)
                            <a href="#">{{trans('constants.food_prepare')}}</a>
                            @endif
                            @if($val->status==3)
                              @if(session()->get('role')==2)
                                @if($val->delivery_boy_id==99)
                                      <button onclick="complete_without_driver({{$val->id}});" class="btn btn-info">Complete</button>
                                @else
                                      <a href="#">{{trans('constants.deliveryboy_assigned')}}</a>
                                @endif
                              @else
                              <a href="#">{{trans('constants.deliveryboy_assigned')}}</a>
                              @endif
                            @endif
                            @if($val->status==4)
                            <a href="#">{{trans('constants.order_pickup')}}</a>
                            @endif
                            @if($val->status==5)
                            <a href="#">{{trans('constants.onthe_way')}}</a>
                            @endif
                            @if($val->status==6)
                            <a href="#">{{trans('constants.pending_pay')}}</a>
                            @endif
                            @if($val->status==7)
                            <a href="#">{{trans('constants.complete')}}</a>
                            @endif
                            @if($val->status == 10)
                            <a href="#">{{trans('constants.cancelled')}}</a>
                            @endif
                          </td>
                     </tr>
                     <?php $i++; ?>
                     @endforeach

                      </tbody>
                     </table>
                </div>
                <br><br>
                <div id="manual_driver_div">
                <div id="assign_driver_list">
                
                </div>
                <div id="ready_for_pickup"></div>
                <div id="complete_pickup"></div>
           <!--   <div class="card-block">
               <h3>Total Earning:- </h3>
                     <div class="row m-1">
                        <dt class="col-sm-3 order-txt p-0">Total Earning</dt>
                        <dd class="col-sm-9 order-txt "><span>: ₹58067.00</span></dd>
                    </div>
                    <div class="row m-1">
                        <dt class="col-sm-3 order-txt p-0">Commision from Food Items</dt>
                        <dd class="col-sm-9 order-txt "><span>: ₹2519.00</span> </dd>
                    </div>
                    <div class="row m-1">
                        <dt class="col-sm-3 order-txt p-0">Commision from Delivery Charge</dt>
                        <dd class="col-sm-9 order-txt "><span>: ₹53.50</span> </dd>
                    </div>
                    <div class="row m-1">
                        <dt class="col-sm-3 order-txt p-0">Total Commision </dt>
                        <dd class="col-sm-9 order-txt "><span>: ₹2572.50</span> </dd>
                    </div>
                </div> -->
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
 
@section('script')
<script>

/**
* Function for to assign the drivers manuallly
* @param 
*/
function driver_assign(id){
  var role1 = "{{session()->get('role')}}";
  if(role1==2){
    var role="restaurant";
  }else{
    var role="admin";
  }
  
  $.ajax({
    type: "GET",
    url:"{{url('/')}}/admin/manual_driver_assign/"+id+"/"+role,
    success:function(data)
    {
      console.log(data);
            //alert(id);
            //alert("success");
            //pagination();
          // $.each(data1,function(index,value)){
            var html = '<div class="modal animated slideInRight text-left" id="'+id+'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="myModalLabel76">Manual Driver Assign</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div class="form-group"><div>'; 
            //var id = id;
            //  $.each(data)
            // {
            //   html += '<label>prem</label></div></div></div></div></div>';
            // }

            if(data!="")
            {
              data.forEach(function(item) {
               // alert(item.id);
               html += '<a href="{{url("/")}}/admin/assign_driver/'+item.id+'/'+id+'" > '+item.name+'('+item.partner_id+') &nbsp; </a>';
             });
            }
            else
            {
              html += "No Providers Available";
            }

            html += '</div></div></div></div></div>';
          // }
          $("#manual_driver_div").html(html);
          $('#'+id).modal();
        }
        
      });
  }
  function assign_driver_list(id){
    var status = 1;
    var restaurant_id = "{{session()->get('userid')}}";
    $.ajax({
      type: "GET",
      url:"{{url('/')}}/admin/assign_driver_list/"+restaurant_id,
      success:function(datas)
      {
        var html = '<div class="modal animated slideInRight text-left" id="manual_driver_div_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="myModalLabel76">Manual Driver Assign</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div class="form-group"><div>'; 
        html += '<a href="{{url("/")}}/admin/assign_without_driver/'+id+'/'+status+'" ><button class="btn btn-info">Approve Order Without Assigning Rider</button></a><br>&nbsp;<h6><b>Restaurant Drivers</b></h6>';
        if(datas.restaurant_driver_list!="")
        {
          console.log(datas.restaurant_driver_list);
          datas.restaurant_driver_list.forEach(function(item) {
           // alert(item.id);
           html += '<a href="{{url("/")}}/admin/accept_assign_driver/'+item.id+'/'+id+'" > '+item.name+'('+item.partner_id+') </a><br>';
         });
        }
        else
        {
          html += "No Providers Available";
        }
        html += '<br><h6><b>Notlob Drivers</b>&nbsp&nbsp;';
        if(datas.admin_driver_list!="")
        {
          html += '<a href="{{url("/")}}/admin/assign_notlob_drivers/'+id+'" > Send Request </a>';
        }
        html += '</h6>';
        if(datas.admin_driver_list!="")
        {
          console.log(datas.admin_driver_list);
          datas.admin_driver_list.forEach(function(item) {
            // html += '<a href="{{url("/")}}/admin/accept_assign_driver/'+item.id+'/'+id+'" > '+item.name+'('+item.partner_id+') </a><br>';
            html += '<p> '+item.name+'('+item.partner_id+') </p><br>';
          });
        }else
        {
          html += "No Providers Available";
        }
        html += '</div></div></div></div></div>';
        $("#manual_driver_div").html(html);
        $('#manual_driver_div_modal').modal('show');
      }
    });
  }
  function ready_for_pickup(id){
    var status = 3;
    var html = '<div class="modal animated slideInRight text-left" id="ready_for_pickup_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="myModalLabel76">Ready For Pickup</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div class="form-group"><div>'; 
        html += '<p>This will put the order to ready to pick-up state for the rider and also inform our client. Are you sure you want to continue?</p><br><button style="margin-right:10px;" class="btn btn-info"><a href="{{url("/")}}/admin/assign_without_driver/'+id+'/'+status+'" style="color:white !important;">OK</a></button><button type="button" class="btn btn-danger" data-dismiss="modal" style="margin-left:10px;">Cancel</button>';
       
        html += '</div></div></div></div></div></div></div>';
        $("#ready_for_pickup").html(html);
        $('#ready_for_pickup_modal').modal('show');
  }

  function complete_without_driver(id){
    var status = 7;
    var html = '<div class="modal animated slideInRight text-left" id="complete_pickup_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="myModalLabel76">Complete Order</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div class="form-group"><div>'; 
        html += '<p>This will complete the order. Only choose this option if the order is delivered. Are you sure you want to continue?</p><br><button style="margin-right:10px;" class="btn btn-info"><a href="{{url("/")}}/admin/assign_without_driver/'+id+'/'+status+'" style="color:white !important;">OK</a></button><button type="button" class="btn btn-danger" data-dismiss="modal" style="margin-left:10px;">Cancel</button>';
       
        html += '</div></div></div></div></div></div></div>';
        $("#complete_pickup").html(html);
        $('#complete_pickup_modal').modal('show');
  }
</script>
@endsection