function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#blah').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$('#image').bind('change', function() {
    var filterType = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
    var a=(this.files[0].size);
    var filetype = (this.files[0].type);
    $(".error").html('');
    if(!filterType.test(filetype)){
        $('#image').val('');
        $(".error").html('Please select a valid image');
    }else if(a > 150000) {
        $('#image').val('');
        $(".error").html('File Sholud be Less than 150KB')
    }
});