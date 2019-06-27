$(document).ready(function() {
    


    // pour éviter que le navigateur n'affiche l'image
    $("html").on("dragover", function(e) {
       e.preventDefault();
       e.stopPropagation();
       $(".upload-area").css({"background-color": ' #fafdff', 'border-color': '#68a2d4', 'border-style' : 'dotted'})
       $("#drop-info").text("Glissez ici !");
    });

    $("html").on("drop", function(e) { e.preventDefault(); e.stopPropagation(); });

    $('.upload-area').on('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(".upload-area").css({"background-color": ' #dce8f5', 'border-color': '#68a2d4', 'border-style' : 'dotted'})
        $("#drop-info").text("Déposez");
    });

    $('html').on('dragleave', function(e) {
        e.stopPropagation();
        e.preventDefault();
        console.log("Leave");
        $(".upload-area").css({"background-color": 'white', 'border-color': 'transparent', 'border-style' : 'solid'});
        $("#drop-info").text("Glissez ici !");
    })

    // Drop
    $('.upload-area').on('drop', function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(".upload-area").css({"background-color": 'white', 'border-color': 'transparent', 'border-style' : 'solid'});

        $("#drop-info").text("Téléversement en cours");

        var file = e.originalEvent.dataTransfer.files;
        var fd = new FormData();

        fd.append('file', file[0]);
        uploadData(fd);
    });

    // ou au clic
    $("#upload-btn").click(function(){
        $("#file").click(); //on clique sur le file input caché
    });

    // file selected
    $("#file").change(function(){
        var fd = new FormData();

        var files = $('#file')[0].files[0];

        fd.append('file',files);

        uploadData(fd);
    });
});

function uploadData(fd){
    console.log("Upload");
    console.log(fd);
    $.ajax({
        type: 'POST',
        url: '{{ path('photo_upload') }}',
        data: fd,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response){
            console.log(response);
            addThumbnail(response);
        }
    });
}

// Added thumbnail
function addThumbnail(data){
    $("#drop-info").text("Glissez ici !");
    var src = data.src;

    // Creating an thumbnail
    var thumbnail = $('<div class="thumbnail">')
        .append('<img src="../uploads/img/' +src+'" width="100%" height="78%">')
        .append('<span class="size">' + "title" + '<span>');
    $("#uploadfile").append(thumbnail);

}