@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')

<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
      <h3 class="content-header-title mb-0 d-inline-block"> {{strtoUpper(trans('constants.delivery'))}} {{strtoUpper(trans('constants.instruction'))}}</h3>
      <div class="row breadcrumbs-top d-inline-block">
        <div class="breadcrumb-wrapper col-12">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href=" " class="brand-font-link-color">{{!empty($data->id)?strtoUpper(trans('constants.edit')):strtoUpper(trans('constants.add'))}} {{strtoUpper(trans('constants.delivery'))}} {{strtoUpper(trans('constants.instruction'))}}</a>
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
                <form action="{{url('/')}}/admin/create_delivery_instruction" class="icons-tab-steps wizard-notification" method="post" enctype="multipart/form-data">
                  <fieldset>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <input type="hidden" name="_token" id="_token" value="{{csrf_token()}}">
                          <input type="hidden" class="form-control" id="id" name="id" value="@if(!empty($data->id)){{$data->id}}@endif">  
                          <label for="instruction">{{trans('constants.instruction')}} :</label>
                          <input type="text" class="form-control" id="instruction" name="instruction" value='{{!empty($data->instruction)?$data->instruction:""}}' pattern="[a-zA-Z0-9 ]{1,100}">
                        </div> 
                        <div class="form-group">
                          <label for="image"> {{trans('constants.image')}}:</label><br>
                          @if(!empty($data->image))
                            <img id="blah" src="@if(isset($data)){{SPACES_BASE_URL}}{{$data->image}}@else http://placehold.it/180 @endif" alt="your image" / style="max-width:180px;"><br>
                          @else
                            <img id="blah" src="http://placehold.it/180" alt="your image" style="max-width:180px;"><br>
                          @endif
                          <input type='file' name="image" id="image" onchange="readURL(this);" / style="padding:10px;background:000;">
                          <br><p class="error" style="color:red"></p>
                        </div> 
                      </div>
                    </div>
                  </fieldset>
                  <div class="form-actions center">
                    <button type="button" class="btn cancel-button-style mr-1" style="padding: 10px 15px;">
                     <i class="ft-x"></i> Cancel
                   </button>
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
 @endsection     
 @section('script')
 <script src="{{URL::asset('public/app-assets/js/scripts/pages/delivery-instruction.js')}}" type="text/javascript"></script>  
 @endsection('script')