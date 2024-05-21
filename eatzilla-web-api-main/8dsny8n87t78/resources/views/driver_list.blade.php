@extends('layout.master')

@section('title')

{{APP_NAME}}
@endsection

@section('content')
<style>

#myImg {
  border-radius: 5px;
  cursor: pointer;
  transition: 0.3s;
}

#myImg:hover {opacity: 0.7;}

/* The Modal (background) */
.modal_img {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  padding-top: 100px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
  padding-left: 30em;
}
.modal-content {
  width: 80%!important;
  margin-bottom: 2em;
 
}

/* Modal Content (image) */
.modal_img-content {
  margin: auto;
  display: block;
  width: 80%;
  max-width: 700px;
}

/* Caption of Modal Image */
#caption {
  margin: auto;
  display: block;
  width: 80%;
  max-width: 700px;
  text-align: center;
  color: #ccc;
  padding: 10px 0;
  height: 150px;
}

/* Add Animation */
.modal_img-content, #caption {  
  -webkit-animation-name: zoom;
  -webkit-animation-duration: 0.6s;
  animation-name: zoom;
  animation-duration: 0.6s;
}

@-webkit-keyframes zoom {
  from {-webkit-transform:scale(0)} 
  to {-webkit-transform:scale(1)}
}

@keyframes zoom {
  from {transform:scale(0)} 
  to {transform:scale(1)}
}

/* The Close Button */
.close_modal {
  position: absolute !important;
  right: 35px !important;
  color: #f1f1f1 !important;
  font-size: 40px !important;
  font-weight: bold !important;
  transition: 0.3s !important;
}

.close_modal:hover,
.close_modal:focus {
  color: #fff !important;
  text-decoration: none;
  cursor: pointer;
}

/* 100% Image Width on Smaller Screens */
@media only screen and (max-width: 700px){
  .modal_img-content {
    width: 100%;
  }
}
</style>
 <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">DELIVERY PEOPLE</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard">{{strtoUpper(trans('constants.dashboard'))}}</a>
                <li class="breadcrumb-item"><a href="#">DELIVERY PEOPLE LIST</a>
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
<!--        <section id="minimal-statistics-bg">
          <div class="row">
            
            
            <div class="col-xl-4 col-lg-6 col-12">
              <div class="card bg-info">
                <div class="card-content">
                  <div class="card-body pb-1">
                    <div class="row text-white">
                      <div class="col-6">
                        <h1><i class="la la-line-chart background-round text-white"></i></h1>
                        <h4 class="pt-1 m-0 text-white">{{$all_drivers}}<i class="la la-arrow-up"></i>
                      </div>
                      <div class="col-6 text-right">
                        <h3 class="text-white mb-2">All Drivers</h3>
                        
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-12">
              <div class="card bg-success">
                <div class="card-content">
                  <div class="card-body pb-1">
                    <div class="row text-white">
                      <div class="col-6">
                        <h1><i class="la la-cart-arrow-down background-round text-white"></i></h1>
                        <h4 class="pt-1 m-0 text-white">{{$active_drivers}}<i class="la la-arrow-up"></i></h4>
                      </div>
                      <div class="col-6 text-right">
                        <h3 class="text-white mb-2">Active Drivers</h3>
                        
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-12">
              <div class="card bg-danger">
                <div class="card-content">
                  <div class="card-body pb-1">
                    <div class="row text-white">
                      <div class="col-6">
                        <h1><i class="la la-cart-plus background-round text-white"></i></h1>
                        <h4 class="pt-1 m-0 text-white">{{$in_active_drivers}} <i class="la la-arrow-down"></i></h4>
                      </div>
                      <div class="col-6 text-right">
                        <h3 class="text-white mb-2">In Active Drivers</h3>
                        
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            !-- <div class="col-xl-3 col-lg-6 col-12">
              <div class="card bg-warning">
                <div class="card-content">
                  <div class="card-body pb-1">
                    <div class="row text-white">
                      <div class="col-6">
                        <h1><i class="la la-money background-round text-white"></i></h1>
                        <h4 class="pt-1 m-0 text-white">82% <i class="la la-arrow-up"></i>
                      </div>
                      <div class="col-6 text-right">
                        <h3 class="text-white mb-2">Monthly Earnings</h3>
                        
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div> --
          </div>
        </section> -->

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
                     <li>  <button class="btn btn-primary btn-sm"><i class="ft-plus white"></i><a style=" color: white !important;" href="{{URL('/')}}/admin/add_new_driver"> Add Delivery People</a></button></li>
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
                            <th>Sl.No</th>
                            <th>Partner Id</th>
                            <th>Name</th>
                            <!-- <th>Email</th> -->
                            <!-- <th>Service Zone</th> -->
                            <th>Contact Details</th>
                            <th>Availability Status</th>
                            <th>Image</th>
                            <th>Rating</th>
{{--                            <th>Documents</th>--}}
                            <th>Vehicle Type</th>
                            <th>Vehicle No</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $i=1; ?>
                          @foreach($data as $d)
                          <tr>
                            <td>
                              {{$i}}
                            </td>
                            <td>{{$d->partner_id}}
                            <td>{{$d->name}}</td>
                            <!-- <td>{{$d->email}}</td> -->
                            <!-- <td>@isset($d->Deliverypartner_detail->Citylist) {{$d->Deliverypartner_detail->Citylist->city}} @endisset</td> -->
                            <td>{{$d->phone}}</td>
                            <td>
                              @php
                                  $status = App\Model\AvailableProviders::where('provider_id',(string)$d->id)->where('status','1')->first();
                              @endphp
                              @if($status) <b class="text-success"> Online <b> @else <b class="text-danger"> Offline <b> @endif
                            </td>
                            <td><img src="{{SPACES_BASE_URL.$d->profile_pic}}" width="100px" alt=""></td>
                            <td>
                            @if(isset($d->Foodrequest))
                              @php 
                                $total = 0; 
                                $total_count = 0; 
                                $rating = 0; 
                              @endphp

                              @foreach($d->Foodrequest as $d1)
                                @if(isset($d1['OrderRatings']))
                                    @php
                                    $total += $d1['OrderRatings']['delivery_boy_rating'];
                                    $total_count = $total_count+1;
                                    @endphp
                                @endif
                              @endforeach

                            @if($total_count != 0 && $total != 0)
                              <!-- ($total / ($total_count-1)); -->
                              @php 
                                $rating = $total / ($total_count);
                              @endphp
                                
                              <!--isset orderrating checked rating-->

                              @for($x=1;$x<=(float)$rating;$x++) 
                              <i class="fa fa-star" style="color: #e6910d;font-size: 17px;"></i> 
                              @endfor

                              @if(strpos((float)$d->rating,'.'))
                              <i class="fa fa-star-half-o" style="color: #e6910d;font-size: 17px;"></i>
                              @php $x++; @endphp
                              @endif

                              @while ($x<=5)
                              <i class="fa fa-star-o" style="color: #e6910d;font-size: 17px;"></i>
                              @php $x++; @endphp
                              @endwhile 

                              <!-- -->

                            @else <!--isset orderrating false-->
                              @for($l=1;$l<=5; $l++)
                                 <span class="fa fa-star-o" style="color: #e6910d;font-size: 17px;"></span>
                              @endfor
                            @endif <!-- -->
                            @else <!--isset request false-->
                              @for($m=1;$m<=5; $m++)
                               <span class="fa fa-star-o" style="color: #e6910d;font-size: 17px;"></span>
                              @endfor
                            @endif <!-- -->
                            </td>
{{--                            <td>--}}
{{--                              @if(!empty($d->license)) --}}
{{--                                <li><a onclick='openmodal("{{SPACES_BASE_URL}}{{$d->license}}")'>License</a></li><br>--}}
{{--                              @endif--}}
{{--                              @if(!empty($d->Vehicle->insurance_image)) --}}
{{--                              <li><a onclick='openmodal("{{SPACES_BASE_URL}}{{$d->Vehicle->insurance_image}}")'>Registration</a></li><br>--}}
{{--                              @endif--}}
{{--                              @if(!empty($d->Vehicle->rc_image)) --}}
{{--                              <li><a onclick='openmodal("{{SPACES_BASE_URL}}{{$d->Vehicle->rc_image}}")'>CTP</a></li><br>--}}
{{--                              @endif--}}
{{--                              --}}
{{--                            </td>--}}

                              <td><?php
                                  switch ((int)$d->delivery_mode) {
                                      case 1:
                                          echo 'Cycle';
                                          break;
                                      case 2:
                                          echo 'Bike';
                                          break;
                                      case 3:
                                          echo 'Car';
                                          break;

                                      default:
                                          echo 'Cycle ';
                                          break;
                                  }
                                  ?>
                              </td>
                              <td>@isset($d->Deliverypartner_detail->vehicle_name) {{$d->Deliverypartner_detail->vehicle_name}} @endisset</td>
                            <td>
                              <button class="btn btn-primary btn-sm"><i class="ft-map white"></i><a style=" color: white !important;" href="{{URL('/')}}/admin/track_driver/{{$d->id}}"> Track</a></button>
                            </td>
                            <td>
                            <!-- <button class="table-btn btn btn-icon btn-danger" form="resource-settle-55">Unsettle</button> -->
                            <li><a href="{{url('/')}}/admin/edit_delivery_boy_details/{{$d->id}}" class="button btn btn-icon btn-success mr-1 link_clr"><i class="ft-edit"></i></a>
                              <a href="{{url('/')}}/admin/view_driver_details/{{$d->id}}" class="button btn btn-icon btn-success mr-1 link_clr"><i class="ft-eye"></i></a>
                              <button type="button" class="btn btn-icon btn-success mr-1 link_clr" data-id="1" data-toggle="modal"  data-target="#{{$d->id}}"><i class="ft-delete"></i></button>
                            </li>
                             </td>
                          </tr>

                                    <!---  Delete Delivery Partner Model -->

                                <div class="modal animated slideInRight text-left" id="{{$d->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
                                       <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                         <div class="modal-header">
                                          <h4 class="modal-title" id="myModalLabel76">Delete Delivery Partner</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                         </div>
                                         <form method="post" action="{{url('/')}}/admin/delete_delivery_partner">
                                            <div class="modal-body">
                                              
                                                <input type="hidden" name="_token" value="{{csrf_token()}}">
                                                  <input type="hidden" name="id" value="{{$d->id}}">
                                                 <div class="form-group">
                                                    <label for="eventName2">Are you sure to delete delivery partner : {{$d->partner_id}}</label>
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
                                   <!--  Delete Delivery Partner Model Ends-->
                          <?php $i++; ?>
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
    <div id="myModal" class="modal_img">
      <span class="close_modal">&times;</span>
      <img class="modal-content" style="width:50%" id="img01">
    </div>
    @endsection     
 
 @section('script')
 <script>
  var modal = document.getElementById("myModal");
  var modalImg = document.getElementById("img01");
  function openmodal(url){
    modal.style.display = "block";
    modalImg.src = url;
  }
  // Get the <span> element that closes the modal
  var span = document.getElementsByClassName("close_modal")[0];

  // When the user clicks on <span> (x), close the modal
  span.onclick = function() { 
    modal.style.display = "none";
  }
 </script>
 @endsection