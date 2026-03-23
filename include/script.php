<!-- Bootstrap core JavaScript-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script>
// Toggle the side navigation
$(document).ready(function() {
    $("#sidebarToggleTop").on('click', function(e) {
        $(".sidebar").toggleClass("toggled");
        if ($(".sidebar").hasClass("toggled")) {
            $('#content-wrapper').css('margin-left', '0');
        } else {
            $('#content-wrapper').css('margin-left', '250px');
        }
    });

    // Close any open menu accordions when window is resized
    $(window).resize(function() {
        if ($(window).width() < 768) {
            $('.sidebar').addClass('toggled');
            $('#content-wrapper').css('margin-left', '0');
        } else {
            $('.sidebar').removeClass('toggled');
            $('#content-wrapper').css('margin-left', '250px');
        }
    });

    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
});
</script> 