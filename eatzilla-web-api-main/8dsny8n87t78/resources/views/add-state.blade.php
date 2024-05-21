@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')

<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
      <h3 class="content-header-title mb-0 d-inline-block"> CITY</h3>
      <div class="row breadcrumbs-top d-inline-block">
        <div class="breadcrumb-wrapper col-12">
          <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{url('/')}}/admin/state_list" class="brand-font-link-color">{{ strtoUpper(trans('constants.state')) }} {{ strtoUpper(trans('constants.list')) }}</a></li>
            <li class="breadcrumb-item"><a href="#" class="brand-font-link-color">{{ strtoUpper(trans('constants.add')) }} {{ strtoUpper(trans('constants.state')) }}</a>
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <div class="content-body">
    <!-- Basic form layout section start -->
    <section id="horizontal-form-layouts">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title" id="horz-layout-basic"></i> Add City</h4>
              <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
              <div class="heading-elements">
                <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                  <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                  <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                </ul>
              </div>
            </div>
            <div class="card-content collpase show">
              <div class="card-body">
                <form action="{{url('/')}}/admin/save_state" class="icons-tab-steps wizard-notification" method="post">
                     <input type="hidden" name="_token" value="{{csrf_token()}}">
                     <input type="hidden" name="id" value="@if(isset($data['id'])){{$data['id']}}@endif">
                  <fieldset>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="SenderEmail">{{trans('constants.country')}} :</label>
                          <input type="text" readonly class="form-control" name="country_id" value="India" required="">
                        </div> 
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="SenderEmail">{{trans('constants.state_name')}} :</label>
                          <select class="form-control" name="new_state_id" required="">
                            <!-- <option value="" disabled="">Select Conutry</option> -->
                            @foreach($newState as $state)
                              @if(isset($data->new_state_id))
                                @if($data->new_state_id == $state->id)  
                                <option value="{{$state->id}}" selected="">{{$state->state}}</option>
                                @else
                                <option value="{{$state->id}}">{{$state->state}}</option>
                                @endif
                                @else 
                                <option value="{{$state->id}}">{{$state->state}}</option>
                                @endif
                             @endforeach
                          </select>
                        </div> 
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="SenderEmail">City :</label>
                          <input type="text" class="form-control" name="state" value="@if(isset($data->state)){{$data->state}}@endif" required="">
                        </div> 
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="SenderEmail">City Code :</label>
                          <input type="text" onchange="cityCode(event)" id="city_code" class="form-control" name="city_code" value="@if(isset($data->city_code)){{$data->city_code}}@endif" required="">
                          <p class="error" style="color:red"></p>
                        </div> 
                      </div>
                    </div>
                  </fieldset>
                  <div class="form-actions center">
                    <a href="{{url('/')}}/admin/state_list" class="btn cancel-button-style mr-1" style="padding: 10px 15px;">
                              <i class="ft-x"></i> Cancel
                          </a>
                   <button type="submit" class="btn success-button-style mr-1" style="padding: 10px 15px;">
                     <i class="ft-check-square"></i> Save
                   </button>
                 </div>
               </form>
             </div>
           </div>
         </div>
       </div>
     </div>
   </section>
   <!-- // Basic form layout section end -->
 </div>
 <script>
    $('#city_code').keypress(function (e) {
      var regex = new RegExp("^[a-zA-Z]+$");
      var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (regex.test(str)) {
        $('.error').hide()
        return true;
      }else {
        e.preventDefault();
        $('.error').show();
        $('.error').text('Please Enter Alphabate Letters Only');
        return false;
      }
    });
 </script>
 @endsection     
 