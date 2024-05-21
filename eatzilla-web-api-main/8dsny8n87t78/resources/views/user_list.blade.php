@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')
    <?php  $usersAccess = explode(",",auth()->user()->AccessPrivilages->users);  ?>
  <div class="content-wrapper">
      <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
          <h3 class="content-header-title mb-0 d-inline-block">{{ strtoUpper(trans('constants.user'))}} {{ strtoUpper(trans('constants.list'))}}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard" class="brand-font-link-color">{{ strtoUpper(trans('constants.dashboard'))}}</a>
                </li>
                <li class="breadcrumb-item"><a href="#" class="brand-font-link-color">{{ strtoUpper(trans('constants.user'))}} {{ strtoUpper(trans('constants.list'))}}</a>
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
                    <div class="row">
                      <div class="col-1">
                        <p>City Filter :</p>
                      </div>
                      <div class="col-2">
                        <select class="form-control form-control-sm" name="city" id="city">
                          <option value="all">All City</option>
                          @foreach($city as $c)
                          <option value="{{$c->id}}" @if($c->id == $city_id) selected @endif>{{$c->state}}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
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
                    <table id="user_list" class="table table-striped table-bordered zero-configuration">
                      <thead> 


                          <tr>
                            <th>SI.No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <!-- <th>Image</th> -->
                            <th>Customer Details</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $i=1; ?>
                          @foreach($user_detail as $key)
                          <tr>
                            <td>{{$i}}</td>
                            <td>{{$key->name}}</td>
                            <td>{{$key->email}}</td>
                            <!-- <td><img src="{{$key->profile_image}}" width="100px" alt=""></td> -->
                            <td>{{$key->phone}}</td>
                              <td>
                                  <button type="button" class="btn btn-icon success-button-style mr-1 link_clr" data-id="1" data-toggle="modal"  data-target="#delete{{$key->id}}" @if(auth()->user()->role==3 && !in_array(1,(array)$usersAccess)) style="display:none" @endif><i class="ft-delete"></i></button>
                              </td>
                          </tr>
                          <?php $i++; ?>

                          <!----------  Delete User Partner Model --------------------->
                          <div class="modal animated slideInRight text-left" id="delete{{$key->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel76" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <h4 class="modal-title" id="myModalLabel76">Delete User</h4>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                              <span aria-hidden="true">&times;</span>
                                          </button>
                                      </div>
                                      <form method="post" action="{{url('/')}}/admin/delete_user">
                                          <div class="modal-body">

                                              <input type="hidden" name="_token" value="{{csrf_token()}}">
                                              <input type="hidden" name="id" value="{{$key->id}}">
                                              <div class="form-group">
                                                  <label for="eventName2">Are you sure want to delete : {{$key->name}}</label>
                                              </div>

                                          </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn cancel-button-style mr-1" data-dismiss="modal" style="padding: 10px 15px;">
                                                  <i class="ft-x"></i> Cancel
                                              </button>
                                              <button type="submit" class="btn success-button-style mr-1" style="padding: 10px 15px;">
                                                  <i class="ft-check-square"></i> Delete
                                              </button>
                                          </div>
                                      </form>
                                  </div>
                              </div>
                          </div>
                          <!----------  Delete User Model Ends--------------------->
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
    <script>
      $(document).ready(function(){               
          var table = $('#user_list').DataTable({
              dom: "lBfrtip",
              buttons: [
            {
                extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'A3',
                    title: 'user_list', 
                    customize: function (doc) { doc.defaultStyle.fontSize =7.2;  doc.styles.tableHeader.fontSize = 10; }
    
                },
                'excel','csv','print','copy',
                ],                  
          });         
        });
        $("#city").change(function(){
          var city_id = $(this).val()
          var url = '{{url('/')}}/admin/user_list?city_id='+city_id
          window.location.href = url
        })
    </script>

    @endsection     
 