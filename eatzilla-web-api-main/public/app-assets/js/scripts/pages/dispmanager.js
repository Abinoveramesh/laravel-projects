$("input:checkbox").change(function(e) {
    if(!$(this).is(':checked')){
      var all_option_id = $(this).attr('name').replace('[', '').replace(']', '');
      $('#'+all_option_id).prop('checked', false);
    }
  }); 