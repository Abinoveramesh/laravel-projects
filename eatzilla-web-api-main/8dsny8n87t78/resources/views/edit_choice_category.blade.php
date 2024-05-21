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
          <h3 class="content-header-title mb-0 d-inline-block">{{strtoUpper(trans('constants.edit'))}} {{strtoUpper(trans('constants.product'))}}</h3>
          <div class="row breadcrumbs-top d-inline-block">
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/')}}/admin/product_list">{{strtoUpper(trans('constants.product'))}} {{strtoUpper(trans('constants.list'))}}</a>
                </li>
                <li class="breadcrumb-item"><a href="">{{strtoUpper(trans('constants.edit'))}} {{strtoUpper(trans('constants.product'))}}</a>
                </li>
              </ol>
            </div>
          </div>
        </div>
        
      </div>
      <div class="content-body">
        <section id="icon-tabs">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title"></h4>
                  <a class="heading-elements-toggle"><i class="la la-ellipsis-h font-medium-3"></i></a>
                  <div class="heading-elements">
                    <ul class="list-inline mb-0">
                      <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                      <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                      <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                      <li><a data-action="close"><i class="ft-x"></i></a></li>
                    </ul>
                  </div>
                </div>

                <div class="card-content collapse show">
                  <div class="card-body">
                    <form action="{{url('/')}}/admin/update_choice_category" method="post" class="icons-tab-steps wizard-notification" enctype="multipart/form-data">
                       <input type="hidden" name="_token" value="{{csrf_token()}}">
                       <input type="hidden" name="id" value="{{$product_list->id}}" id="product_id">
                    <fieldset>
                        <div class="row">
                          <div class="col-md-6">
                            @if(session()->get('role')==1 || session()->get('role')==3)
                                <div class="form-group">
                                    <label for="eventName2">Restaurant<span style="color: red;">*</span></label>
                                    <select name="restaurant_name" id="restaurant_id" onchange="getrestaurant_based_detail()" class="form-control" required="">
                                      @foreach($restaurant as $res)
                                        @if(isset($res->restaurant_name))
                                          <option value="{{$res->id}}" {{ ($product_list->restaurant_id == $res->id)? 'selected':"" }}>{{$res->restaurant_name}}</option>
                                      @endif
                                      @endforeach
                                  </select> 
                                </div>
                              @else
                                <input type="hidden" name="restaurant_name" value="{{session()->get('userid')}}" id="restaurant_id">
                              @endif
                            <div class="form-group">
                              <label for="eventName2">Name <span style="color: red;">*</span></label>
                              <input type="text" class="form-control" name="name" required value="{{$product_list->name}}" id="Name">
                            </div>
                             <div class="form-group">
                              <label for="lastName2">Description </label>
                              <textarea  id="Description" name="description" rows="4" class="form-control">{{$product_list->description}}</textarea>
                             
                            </div>
                             <div class="form-group">
                              <label for="eventLocation2">Category <span style="color: red;">*</span></label>
                              @php
                                  if(isset($product_list->category_id)){
                                  $category_data = json_decode($product_list->category_id);
                                  if($category_data=='' || $category_data==0) $category_data=array();
                                  }else{
                                    $category_data = array();
                                  }
                              @endphp
                              <select class="c-select form-control select2" id="Category" required name="category[]" multiple="multiple">
                                @foreach($category as $cat)
                                <option value="{{$cat->id}}" @if(isset($product_list->category_id)) @if(in_array($cat->id,$category_data)) selected @endif @endif >{{$cat->category_name}}</option>
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
                              <input type="text" name="price" required value="{{$product_list->price}}" class="form-control" id="price">
                            </div> 
                            <div class="form-group">
                             <label  for="projectinput4">Food Image</label>
                            <div>
                              @if(isset($product_list->image)&& $product_list->image!='')
                                <img id="blah" src="{{SPACES_BASE_URL}}@if(isset($product_list->image)&& $product_list->image!=''){{$product_list->image}} @endif" alt="your image" / style="max-width:180px;"><br>
                              @endif
                                <input type='file' name="image" id="image" onchange="GetFileSize();" / style="padding:10px;background:000;">
                          </div>
                         </div>
                         <div class="form-group">
                            <label for="eventName2">Status<span style="color: red;">*</span></label>
                            <label class="switch">
                            <input type="checkbox" name="status" value="1" {{ ($product_list->status==1)?"checked":"" }}> 

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
                                    <span id="button_span"><input type="hidden" id="count_element"  value="0"><button type="button" class="btn btn-primary mr-1" onclick="category_Add()">Add</button></span>
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
                           </div>
                          </div>
                        </div>
                      </fieldset>
                    </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
              </section>
            </div>
          </div>
  <script src="{{URL::asset('public/app-assets/vendors/js/forms/select/select2.full.min.js')}}" type="text/javascript"></script>
  <script src="{{URL::asset('public/app-assets/js/scripts/forms/select/form-select2.js')}}" type="text/javascript"></script>
  <script>
     function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#blah')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
            function readURL1(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#blah1')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }


        // function funchangePrice(id)
        // {
        //   var price = $('#food_quantity_price_'+id).val();
        //   if($('#food_quantity_default_'+id).is(':checked') && price!='')
        //   {
        //     if($("#food_quantity_"+id).is(':checked')){
        //       $('#price').val(price);
        //     }else{
        //       $('#price').val(price);
        //       alert('Food Quantity checkbox is mandatory');
        //       $("#food_quantity_"+id).prop('checked',true)
        //     }
        //   }else{
        //     if(price!=''){
        //       if($("#food_quantity_"+id).is(':checked')){
        //         $('#price').val(price);
        //       }else{
        //         $('#price').val(price);
        //         alert('Food Quantity checkbox is mandatory');
        //         $("#food_quantity_"+id).prop('checked',true)
        //       }
        //     }
        //   }
        // }
        function funchangePrice(id)
        {
          var price = $('#food_quantity_price_'+id).val();
          if(price!=''){
            if($('#food_quantity_default_'+id).is(':checked')){
              if($("#food_quantity_"+id).is(':checked')){
                $('#price').val(price);
              }else{
                alert('Food quantity checkbox is mandtory');
                $("#food_quantity_"+id).prop('checked',true);
              }
            }else{
              if(!$("#food_quantity_"+id).is(':checked')){
              //}else{
                alert('Food quantity checkbox is mandtory');
                $("#food_quantity_"+id).prop('checked',true);
              }
            }
          }else{
            if($('#food_quantity_default_'+id).is(':checked')){
              if($("#food_quantity_"+id).is(':checked')){
                alert('Food quantity price is mandtory');
                $("#food_quantity_price_"+id).val('0');
              }else{
                alert('Food quantity checkbox and price is mandtory');
                $("#food_quantity_"+id).prop('checked',true);
                $("#food_quantity_price_"+id).val('0');
              }
            }
          }
        }

        // function getrestaurant_based_detail()
        // {
        //   var restaurant_id = $('#restaurant_id').val();
        //   $.ajax({
        //     url : "{{url('/')}}/admin/getrestaurant_based_detail/"+restaurant_id,
        //     method : "get",
        //     success : function (data)
        //     {
        //     console.log(data.menu);
        //       if(data.add_ons != '') 
        //       {
        //         var add_ons='';
        //         $.each( data.add_ons, function( key, value ) {
        //           add_ons += '<option value="'+value.id+'">'+value.name+'</option>';
        //         });
        //         $('#add_ons').html(add_ons);
        //       }
        //       else
        //       {
        //           $('#add_ons').html("");
        //       }
        //       if(data.menu != '') 
        //       {
        //         var menu='';
        //         $.each( data.menu, function( key, value ) {
        //           menu += '<option value="'+value.id+'">'+value.menu_name+'</option>';
        //         });
        //         $('#menu').html(menu);
        //       }
        //       else
        //       {
        //           $('#menu').html("");
        //       }
        //     }

        //   });
        // }

    function GetFileSize() {
        var fi = document.getElementById('image');
        if (fi.files.length > 0) {
           
            for (var i = 0; i <= fi.files.length - 1; i++) {

                var fsize = fi.files.item(i).size; 
                var fsize1= Math.round((fsize / 1024));
                if(fsize1>=3000){
                alert("The File shouldn't exceed 3MB");
                var remove = $("#image").val('');
                }
                
            }
        }
    }      
</script>
<script>
  function category_Add(){


    // alert("sfhg");
    var div_count = $("#count_element").val();
    // alert(div_count);
    var i = 1;
    var incre_div_count = +div_count + +i;
    // alert(incre_div_count);
    $("#count_element").val(incre_div_count);
    var element = '<div id="category_child_div_'+incre_div_count+'"><div class="row" ><div class="col-md-3"><div class="form-group"><label>category name</label><input type="hidden" name="category_choice_id['+incre_div_count+']" value="'+incre_div_count+'"><input type="text" class="form-control" id="category_name_'+incre_div_count+'" name="category_name['+incre_div_count+']" required=""></div></div><div class="col-md-3"><div class="form-group"><label>max</label><input type="text" class="form-control" id="max_'+incre_div_count+'" name="max['+incre_div_count+']" required=""></div></div><div class="col-md-3"><div class="form-group"><label>min</label><input type="text" class="form-control" id="max'+incre_div_count+'" name="min['+incre_div_count+']" required=""></div></div><div class="col-md-1"><button type="button" class="btn btn-primary mr-1" id="'+incre_div_count+'" onclick="add_choice('+incre_div_count+')" >+</button></div><div class="col-md-1"><button type="button" name="remove" id="'+incre_div_count+'" class="btn btn-danger btn_remove button_div_remove">X</button></div></div>&nbsp;<div style="margin-left:25px;" id="choice_div_'+incre_div_count+'"></div></div><hr><input type="hidden" id="choice_'+incre_div_count+'" value="0">';
    $("#category_div").append(element);
  }   

  $(document).on('click', '.button_div_remove', function(){  
          var button_id = $(this).attr("id");   
          $('#category_child_div_'+button_id).remove();  
    });

  function add_choice(id,value=0){
    // alert("sjgfd");
    var div_count_child = $("#choice_"+id).val();
    var i = 1;
    var incre_div_count_child = +div_count_child + +i;
    $("#choice_"+id).val(incre_div_count_child);
    if(value!=0){
      var element = '<div class="row" id="category_choice_div_'+id+'_'+incre_div_count_child+'"><div class="col-md-3"><div class="form-group"><label>Choice name</label><input type="hidden" value="'+id+'" name="choice_name_id['+value+'][]"><input type="text" class="form-control" id="category_name_'+id+'" name="choice_name['+value+'][]" required=""></div></div><div class="col-md-3"><div class="form-group"><label>Price</label><input type="text" id="price_'+id+'" class="form-control" name="price_choice['+value+'][]" required=""></div></div><div class="col-md-1"><button type="button" name="remove" id="'+id+'_'+incre_div_count_child+'" class="btn btn-danger btn_remove button_child_div_remove">X</button></div></div>';
      $("#choice_div_"+id).append(element);
    }else{
      var element = '<div class="row" id="category_choice_div_'+id+'_'+incre_div_count_child+'"><div class="col-md-3"><div class="form-group"><label>Choice name</label><input type="hidden" value="'+id+'" name="choice_name_id['+id+'][]"><input type="text" class="form-control" id="category_name_'+id+'" name="choice_name['+id+'][]" required=""></div></div><div class="col-md-3"><div class="form-group"><label>Price</label><input type="text" id="price_'+id+'" class="form-control" name="price_choice['+id+'][]" required=""></div></div><div class="col-md-1"><button type="button" name="remove" id="'+id+'_'+incre_div_count_child+'" class="btn btn-danger btn_remove button_child_div_remove">X</button></div></div>';
      $("#choice_div_"+id).append(element);
    }
    
  }

  $(document).on('click', '.button_child_div_remove', function(){  
    var button_id = $(this).attr("id");   
    $('#category_choice_div_'+button_id).remove();  
  });

  $(document).ready(function(){
    @if(isset($choice_category) && !empty($choice_category))
      @foreach($choice_category as $d)
         var div_count = $("#count_element").val();
          // alert(div_count);
          var i = 1;
          var incre_div_count = +div_count + +i;
          // alert(incre_div_count);
          // alert(incre_div_count);
          var value="{{$d->id}}";
          var choice_category_name="{{$d->name}}";
          var max = "{{$d->max}}";
          var min = "{{$d->min}}";
          // alert(value);
          $("#count_element").val(incre_div_count);
          var element = '<div id="category_child_div_'+incre_div_count+'"><div class="row" ><div class="col-md-3"><div class="form-group"><label>category name</label><input type="hidden" name="category_choice_id_old['+value+'][]" id="category_choice_id_'+incre_div_count+'"><input type="text" class="form-control" id="category_name_'+incre_div_count+'" name="category_name_old['+value+']" required=""></div></div><div class="col-md-3"><div class="form-group"><label>max</label><input class="form-control" type="text" id="max_'+incre_div_count+'" name="max_old['+value+']" required=""></div></div><div class="col-md-3"><div class="form-group"><label>min</label><input type="text" class="form-control" id="min_'+incre_div_count+'" name="min_old['+value+']" required=""></div></div><div class="col-md-1"><button class="btn btn-primary mr-1" type="button" id="'+incre_div_count+'" onclick="add_choice('+incre_div_count+','+value+')" >+</button></div><div class="col-md-1"><button type="button" name="remove" id="'+incre_div_count+'" class="btn btn-danger btn_remove button_div_remove">X</button></div></div>&nbsp;<div style="margin-left:25px;" id="choice_div_'+incre_div_count+'"></div></div><hr><input type="hidden" id="choice_'+incre_div_count+'" value="0">';
          $("#category_div").append(element);
          $("#category_choice_id_"+incre_div_count).val(value);
          $("#category_name_"+incre_div_count).val(choice_category_name);
          $("#max_"+incre_div_count).val(max);
          $("#min_"+incre_div_count).val(min);
          @if(isset($d->choice) && !empty($d->choice))
            @foreach($d->choice as $c)
              var value1 = "{{$c->id}}";
              var choice_name = "{{$c->name}}";
              var choice_price = "{{$c->price}}";
              var div_count_child = $("#choice_"+incre_div_count).val();
              var i = 1;
              var incre_div_count_child = +div_count_child + +i;
              $("#choice_"+incre_div_count).val(incre_div_count_child);
              var element = '<div class="row" id="category_choice_div_'+value+'_'+incre_div_count_child+'"><div class="col-md-3"><div class="form-group"><label>Choice name</label><input type="hidden" name="choice_name_id_old['+value+']['+value1+']" id="choice_name_id_'+value+'_'+value1+'"><input type="text" class="form-control" id="choice_name_'+value+'_'+value1+'" name="choice_name_old['+value+']['+value1+']" required="" ></div></div><div class="col-md-3"><div class="form-group"><label>Price</label><input type="text" class="form-control" id="price_choice_'+value+'_'+value1+'" name="price_choice_old['+value+']['+value1+']" required="" ></div></div><div class="col-md-1"><button type="button" name="remove" id="'+value+'_'+incre_div_count_child+'" class="btn btn-danger btn_remove button_child_div_remove">X</button></div></div>';
              // alert("yhgf");
              $("#choice_div_"+incre_div_count).append(element);
              $("#choice_name_id_"+value+"_"+value1).val(value1);
              $("#choice_name_"+value+"_"+value1).val(choice_name);
              $("#price_choice_"+value+"_"+value1).val(choice_price);
            @endforeach
          @endif
      @endforeach
    @endif
  });

  function getrestaurant_based_detail()
  {
    var restaurant_id = $('#restaurant_id').val();
    $.ajax({
      url : "{{url('/')}}/admin/getrestaurant_based_detail/"+restaurant_id,
      method : "get",
      success : function (data)
      {
      // console.log(data);
        if(data != '') 
        {
          if(data != "null"){
            var category='';
            // category += '<option value="">--Select Category--</option>';
            $.each( data.category, function( key, value ) {
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
  
 </script>
  
<!-- <script src="{{URL::asset('public/app-assets/vendors/js/vendors.min.js')}}" type="text/javascript"></script> -->
<script src="{{URL::asset('public/app-assets/vendors/js/forms/select/select2.full.min.js')}}" type="text/javascript"></script>
<script src="{{URL::asset('public/app-assets/js/scripts/forms/select/form-select2.js')}}" type="text/javascript"></script>

    @endsection     
 