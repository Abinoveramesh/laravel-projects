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
        /* .link_clr{
          color: white;
        }
        a:hover{
          color: white !important;
        } */
            .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

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
          <h3 class="content-header-title mb-0 d-inline-block">Delivery Location List</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{url('/')}}/admin/dashboard">{{strtoUpper(trans('constants.dashboard'))}}</a>
                <li class="breadcrumb-item"><a href="#">Delivery Location List</a>
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
                    <table id="restaurant_list" class="table table-striped table-bordered zero-configuration">
                      <thead> 
                        <tr>
                          <th>SI.No</th>
                          <th>Suburb</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                         <?php $i = 1;  ?>
                          @foreach($city as $d)
                          <tr>
                            <td>{{$i}}</td>
                            <td>{{$d->city}}</td>
                            <td><button class="btn btn-primary btn-sm"><a href="{{URL('/')}}/admin/view_delivery_location/{{$d->id}}" style="color:white"><i class="ft-eye"></i></a></button></td>
                          </tr>
                          <?php $i = $i+1; ?>
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
    </div>
  </div>
@endsection     

@section('script')
 <script>
    $(document).ready(function(){               
        var table = $('#restaurant_list').DataTable({
            dom: "lBfrtip",
            destroy: true,
            buttons: [
            {
              extend: 'pdfHtml5',
                  orientation: 'landscape',
                  pageSize: 'A3',   
                  title: 'restaurant_list',               
                  customize: function (doc) { doc.defaultStyle.fontSize =7.2;  doc.styles.tableHeader.fontSize = 10; }
              },
              'excel','csv','print','copy',
              ],                  
        });         
      });
  </script>
 @endsection