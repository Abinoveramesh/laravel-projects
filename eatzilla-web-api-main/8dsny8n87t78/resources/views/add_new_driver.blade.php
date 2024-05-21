@extends('layout.master')

@section('title')
{{APP_NAME}}
@endsection

@section('content')
<div class="content-wrapper">
  <div class="content-body">
    <section id="icon-tabs">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">CREATE DRIVER</h4>
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
            <hr>

            <div class="card-content collapse show">
              <div class="card-body">
                <form role="form" action="{{url('/')}}/admin/add_driver" class="icons-tab-steps wizard-notification" method="post" enctype="multipart/form-data" id="add_driver">
                  <input type="hidden" name="_token" value="{{csrf_token()}}">
                  @if(isset($insert1))
                  <input type="hidden" name="id" id="id" value="{{($insert1->id ?$insert1->id:'')}}"  >
                  @endif

                  <fieldset>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="name">Driver Name<span style="color: red;">*</span></label>
                          <input id="name" type="text" class="form-control" name="driver_name" value="@if(isset($insert1->name)){{$insert1->name}}@endif" required="" autofocus="">
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="name">Email</label>
                          <input id="name" type="email" class="form-control" name="email" value="@if(isset($insert1->email)){{$insert1->email}}@endif" autofocus="" required="">
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label  for="projectinput4">Status<span style="color: red;">*</span></label>
                          <select name="status" id="" class="form-control" required="">
                            <option value="1" @if(isset($insert1->status) && $insert1->status==1) selected @endif>Active</option>
                            <option value="2" @if(isset($insert1->status) && $insert1->status==2) selected @endif>In Active</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-1">
                        <div class="form-group">
                          <label for="name">Phone<span style="color: red;">*</span></label>
                            <select id="country_code" class="form-control" name="country_code" style="width:auto;" required="">
                            @foreach($country as $c)
                            <option value="{{$c->country_code}}" @isset($insert1->country_code) @if($insert1->country_code==$c->country_code) selected @endif @endisset>{{$c->country_code}}</option>
                            @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-3">
                        <div class="form-group">
                          <label for="name">&nbsp;</label>
                          <input id="name" type="text" class="form-control" name="phone_no" value="@if(isset($insert1->phone)){{$insert1->phone}}@endif" required="" autofocus="">
                          </div>
                        </div>
                         <div class="col-md-4">
                        <div class="form-group">
                          <label for="name">Mode of delivery<span style="color: red;">*</span></label>
                          <select name="delivery_mode" id="delivery_mode" class="form-control" required="" onchange="funchkmode(this.value)">
                            <option value="">--Select Mode of Delivery--</option>
                            <option value="1" @if(isset($insert1)) @if($insert1->delivery_mode==1) selected @endif @endif>Cycle</option>
                            <option value="2" @if(isset($insert1)) @if($insert1->delivery_mode==2) selected @endif @endif>Bike</option>
                            <option value="3" @if(isset($insert1)) @if($insert1->delivery_mode==3) selected @endif @endif>Car</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="vehicle_no">Vehicle No.</label>
                          <input id="vehicle_no" type="text" class="form-control" name="vehicle_no" value="@if(isset($insert1->Deliverypartner_detail->vehicle_name)){{$insert1->Deliverypartner_detail->vehicle_name}}@endif" autofocus="">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group row">
                          <label class="col-md-3 label-control" for="projectinput4">{{trans('constants.driver')}} {{trans('constants.image')}}</label>
                          <div class="col-md-9">
                            @if(isset($insert1->profile_pic))
                              <img id="blah" src="{{SPACES_BASE_URL.$insert1->profile_pic}}"
                                   alt="your image" style="max-width:180px;"><br>
                            @endif
                            <input type='file' name="profile_pic" onchange="readURL(this);"
                                   style="padding:10px;background:#FFF;"
                                   @if(!isset($insert1->profile_pic)) required="" @endif>
                          </div>
                        </div>
                      </div>
                    </div>
                    @if(!isset($insert1))
                    <h3>Security Settings</h3>
                    <div class="row">
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="password">Password<span style="color: red;">*</span></label>
                          <input id="password" type="password" class="form-control" name="password"  value="@if(isset($insert1->password)){{$insert1->password}}@endif" required="" autofocus="">
                        </div>
                        <span class="error_message" id="password_error"></span>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="confirm_password">Confirm Password<span style="color: red;">*</span></label>
                          <input id="confirm_password" type="password" class="form-control" value="" required="" autofocus="">
                        </div>
                      </div>
                    </div>
                    @endif

                    </div>
                    <div class="form-actions">
                      <button type="button" class="btn btn-warning mr-1" style="padding: 10px 15px;">
                       <i class="ft-x"></i> Cancel
                      </button>
                      <button onclick="javascript:return form_validation();" class="btn btn-primary mr-1" style="padding: 10px 15px;">
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
  </div>
</div>


@endsection

@section('script')

<script>

// function funchkmode(value)
// {
//   if(value==1){
//     $("#mode_div").hide();
//     $("#mode_show").show();
//   }else{
//     $("#mode_div").show();
//     $("#mode_show").hide();
//   }
// }


  $('#license_expiry').pickadate({
    min: new Date(),
    selectYears: true,
    selectMonths: true
  });

  function form_validation()
  {
    if(!$('#id').val())
    {
      var password = document.getElementById('password').value;
      var confirm_password = document.getElementById('confirm_password').value;
      if(password != confirm_password)
      {
        $('#password_error').fadeIn().html('Password and Confirm Password does not match').delay(3000).fadeOut('slow');
        return false;
      }else
      {
        document.getElementById("add_driver").submit();
      }
    }else
    {
      document.getElementById("add_driver").submit();
    }

  }








  function getprovience(id)
  {
    if(id==1)
      var provienceid = $('#country').val();
    else
     var provienceid = $("#state_province").val();

    $.ajax({
      url : "{{url('/')}}/admin/getprovience/"+provienceid+"/"+id,
      method : "get",
      success : function (data)
      {
        if(id==1){
          var state='<option value="">--select state--</option>';
          if(data.state != '')
          {
            $.each( data.state, function( key, value ) {
              state += '<option value="'+value.id+'">'+value.state+'</option>';
            });
          }
          $('#state_province').html(state);

        }else{
          var city='<option value="">--select city--</option>';
          if(data.city != '')
          {
            $.each( data.city, function( key, value ) {
              city += '<option value="'+value.id+'">'+value.city+'</option>';
            });
          }
          $('#city').html(city);
        }
      }

    });
  }


  function getcity_area()
  {
    var city_id = $('#city').val();
    $.ajax({
      url : "{{url('/')}}/admin/getcity_area/"+city_id,
      method : "get",
      success : function (data)
      {
      console.log(data.area);
        if(data.area != '')
        {
          var area='<option value="">--Select Area--</option>';
          $.each( data.area, function( key, value ) {
            area += '<option value="'+value.id+'">'+value.area+'</option>';
          });
          $('#area').html(area);
        }
        else
        {
            $('#area').html("");
        }
      }

    });
  }
</script>

@endsection