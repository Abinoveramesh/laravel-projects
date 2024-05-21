@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')
<style>
@media (min-width: 768px) and (max-width: 1024.98px){
.col-md-1.checkbox-1 {
    flex: 0px;
    max-width: 25%;
}
.col-md-2.small-font {
    max-width: 25%;
}
.col-md-5.checkbox-2 {
    max-width: 25%;
}
.col-md-4.make-default {
    max-width: 25%;
}
} 
@media (min-width: 425px) and (max-width: 767.98px){
 .col-md-1.checkbox-1 {
    max-width: 50%;
}
.col-md-2.small-font {
    max-width: 50%;
}
.col-md-5.checkbox-2 {
    max-width: 50%;
}
.col-md-4.make-default {
   max-width: 50%; 
   padding: 10px;
 }
} 
@media (min-width: 375px) and (max-width: 424.98px){
 .col-md-1.checkbox-1 {
    max-width: 50%;
}
.col-md-2.small-font {
    max-width: 50%;
}
.col-md-5.checkbox-2 {
    max-width: 50%;
}
.col-md-4.make-default {
   max-width: 50%; 
   padding: 10px;
 }
} 
@media (min-width: 320px) and (max-width: 374.98px){
 .col-md-1.checkbox-1 {
    max-width: 50%;
}
.col-md-2.small-font {
    max-width: 50%;
}
.col-md-5.checkbox-2 {
    max-width: 50%;
}
.col-md-4.make-default {
   max-width: 50%; 
   padding: 10px;
 }
} 
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

</style>

<div class="content-wrapper">
  <div class="content-header row">
    <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
      <h3 class="content-header-title mb-0 d-inline-block">Product</h3>
      <div class="row breadcrumbs-top d-inline-block">
        <div class="breadcrumb-wrapper col-12">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Add Product</a>
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
<div class="content-body">
    <!-- Basic form layout section start -->
 <div class="card">
  <div class="card-header">
    <!-- <h4 class="card-title" id="horz-layout-basic"></i> {{trans('constants.add')}} {{trans('constants.country')}}</h4> -->
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
    <form action="{{url('/')}}/admin/add_choice_category" class="icons-tab-steps wizard-notification" method="post" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="{{csrf_token()}}">
      <fieldset>
      <div class="row">
        <div class="col-md-6">
          @if(session()->get('role')==1 || session()->get('role')==3)
            <div class="form-group">
                <label for="eventName2">Restaurant<span style="color: red;">*</span></label>
                <select name="restaurant_name" id="restaurant_id" onchange="getrestaurant_based_detail()" class="form-control" required="">
                  @foreach($restaurant as $res)
                    @if(isset($res->restaurant_name))
                      <option value="{{$res->id}}">{{$res->restaurant_name}}</option>
                  @endif
                  @endforeach
              </select> 
            </div>
          @else
            <input type="hidden" name="restaurant_name" value="{{session()->get('userid')}}" id="restaurant_id">
          @endif

          <div class="form-group">
            <label for="eventName2">Name <span style="color: red;">*</span></label>
            <input type="text" class="form-control" required name="name" id="Name">
          </div>
           <div class="form-group">
            <label for="lastName2">Description </label>
            <textarea  id="Description" name="description" rows="4" class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label for="eventLocation2">Category <span style="color: red;">*</span></label>
            <select class="c-select form-control select2" id="Category" name="category[]" required multiple="multiple">
              @foreach($category as $cat)
              <option value="{{$cat->id}}">{{$cat->category_name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <h3><label>Pricing </label></h3>
            <hr>
          </div>
          <div class="form-group">
            <label for="eventName2">Price <span style="color: red;">*</span></label>
            <input type="text" name="price" required class="form-control" id="price">
          </div> 
          <div class="form-group">
            <label for="eventName2">Food Image</label><br>
            <input type="file" name="image" id="image" onchange="GetFileSize()">
          </div> 
           <div class="form-group">
            <label for="eventName2">Status<span style="color: red;">*</span></label>
            <label class="switch">
            <input type="checkbox" name="status" value="1" checked="">                        
            <span class="slider round"></span></label>
         </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
         
                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label for="SenderEmail"><b>  Add Choice Category  :</b></label>
                        </div> 
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <span id="button_span"><input type="hidden" id="count_element" value="0"><button class="btn btn-primary mr-1" type="button" onclick="category_Add()">Add</button></span>
                        </div> 
                      </div> 
                      <div class="col-md-3"></div>
                    </div>
                    <div class="row"><div id="category_div"></div></div>
                     

                 
                  <div class="form-actions center">
                    <button type="button" class="btn btn-warning mr-1" style="padding: 10px 15px;">
                     <i class="ft-x"></i> Cancel
                   </button>
                   <button type="submit" class="btn btn-primary mr-1" style="padding: 10px 15px;">
                     <i class="ft-check-square"></i> Save
                   </button>
                 </div>
                </fieldset>
               </form>
             </div>
           </div>
         </div>
       </div>
     </div>
   </section>
   <!-- // Basic form layout section end -->
 </div>
<script src="{{URL::asset('public/app-assets/vendors/js/forms/select/select2.full.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('public/app-assets/js/scripts/forms/select/form-select2.js')}}" type="text/javascript"></script>
 <script>
  function category_Add(){
    // alert("sfhg");
    var div_count = $("#count_element").val();
    // alert(div_count);
    var i = 1;
    var incre_div_count = +div_count + +i;
    // alert(incre_div_count);
    $("#count_element").val(incre_div_count);
    var element = '<div id="category_child_div_'+incre_div_count+'"><div class="row" ><div class="col-md-3"><div class="form-group"><label>category name</label><input type="hidden" name="category_choice_id['+incre_div_count+']" value="'+incre_div_count+'"><input class="form-control" type="text" id="category_name_'+incre_div_count+'" name="category_name['+incre_div_count+']" required=""></div></div><div class="col-md-3"><div class="form-group"><label>max</label><input class="form-control" type="text" id="max_'+incre_div_count+'" name="max['+incre_div_count+']" required=""></div></div><div class="col-md-3"><div class="form-group"><label>min</label><input type="text" class="form-control" id="max'+incre_div_count+'" name="min['+incre_div_count+']" required=""></div></div><div class="col-md-1"><button type="button" class="btn btn-primary mr-1" id="'+incre_div_count+'" onclick="add_choice('+incre_div_count+')" >+</button></div><div class="col-md-1"><button type="button" name="remove" id="'+incre_div_count+'" class="btn btn-danger btn_remove button_div_remove">X</button></div></div>&nbsp;<div style="margin-left:25px;" id="choice_div_'+incre_div_count+'"></div></div><hr><input type="hidden" id="choice_'+incre_div_count+'" value="0">';
    $("#category_div").append(element);
  }   

  $(document).on('click', '.button_div_remove', function(){  
          var button_id = $(this).attr("id");   
          $('#category_child_div_'+button_id).remove();  
    });

  function add_choice(id){
    // alert("sjgfd");
    var div_count_child = $("#choice_"+id).val();
    var i = 1;
    var incre_div_count_child = +div_count_child + +i;
    $("#choice_"+id).val(incre_div_count_child);
    var element = '<div class="row" id="category_choice_div_'+id+'_'+incre_div_count_child+'"><div class="col-md-3"><div class="form-group"><label>Choice name</label><input type="hidden" value="'+id+'" name="choice_name_id['+id+'][]"><input type="text" class="form-control" id="category_name_'+id+'" name="choice_name['+id+'][]" required=""></div></div><div class="col-md-3"><div class="form-group"><label>Price</label><input class="form-control" type="text" id="price_'+id+'" name="price_choice['+id+'][]" required=""></div></div><div class="col-md-1"><button type="button" name="remove" id="'+id+'_'+incre_div_count_child+'" class="btn btn-danger btn_remove button_child_div_remove">X</button></div></div>';
    $("#choice_div_"+id).append(element);
  }

  $(document).on('click', '.button_child_div_remove', function(){  
          var button_id = $(this).attr("id");   
          $('#category_choice_div_'+button_id).remove();  
    });

  function getrestaurant_based_detail()
  {
    var restaurant_id = $('#restaurant_id').val();
    $.ajax({
      url : "{{url('/')}}/admin/getrestaurant_based_detail/"+restaurant_id,
      method : "get",
      success : function (data)
      {
      console.log(data);
        if(data != '') 
        {
          if(data != "null"){
            var category='';
            // console.log(data.category);
            $.each( data, function( key, value ) {
              category += '<option value="'+value.id+'">'+value.category_name+'</option>';
            });
            $('#Category').html(category);
          }else{
            $('#Category').html("");
          }
        }
        else
        {
          $('#Category').html("");
        } 
          
      }

    });
  }

  $(document).ready(function(){
      getrestaurant_based_detail();
  });

  
 </script>
 @endsection     
 