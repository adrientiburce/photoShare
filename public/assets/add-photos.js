function addPhotos(url, callback){

    var count = 0;

    $(".photo").click(function(){
        if ($(this).hasClass("selected")) {
            $(this).removeClass("selected");
            if (count > 0) count --;
        } else {
            $(this).addClass("selected");
            count ++;
        }
        majNbPhotos(count);
    });

    $("#create-album").click(function(){
        var IDs = [];
        var Photos = [];
        $(".photo.selected").each(function(){
            IDs.push($(this).attr("data-id"))
            Photos.push($(this));
        });
        console.log(IDs);
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: {photos : IDs},
            success: function(response){
                if (response["success"]){
                    callback(Photos, response);
                }
            }
        });
    });

    function majNbPhotos(count){
        var span = $('#photos-selected');
        var btn = $('#create-album');
        switch(count){
            case 0:
                span.html("Aucune photo sélectionnée");
                btn.prop('disabled', true);
                break;
            case 1:
                span.html("1 photo sélectionnée");
                btn.prop('disabled', false);
                break;
            default:
                span.html(count + " photos sélectionnées");
                btn.prop('disabled', false);
        }
    }
}