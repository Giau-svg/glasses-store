<!-- Bootstrap core JavaScript-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>

<!-- Custom scripts -->
<script>
// Toggle the side navigation
$(document).ready(function() {
    // Toggle sidebar
    $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
        e.preventDefault();
        $("body").toggleClass("sidebar-toggled");
        
        // Animate sidebar transition
        if ($("body").hasClass("sidebar-toggled")) {
            $(".sidebar-wrapper").css("width", "0");
            $("#content-wrapper").css("margin-left", "0");
            $(".topnav").css("left", "0");
        } else {
            $(".sidebar-wrapper").css("width", "250px");
            $("#content-wrapper").css("margin-left", "250px");
            $(".topnav").css("left", "250px");
        }
    });

    // Handle responsive behavior
    function checkWidth() {
        var windowWidth = $(window).width();
        if (windowWidth < 768) {
            $("body").addClass("sidebar-toggled");
            $(".sidebar-wrapper").css("width", "0");
            $("#content-wrapper").css("margin-left", "0");
            $(".topnav").css("left", "0");
        } else {
            if (!$("body").hasClass("sidebar-toggled")) {
                $(".sidebar-wrapper").css("width", "250px");
                $("#content-wrapper").css("margin-left", "250px");
                $(".topnav").css("left", "250px");
            }
        }
    }

    // Initial check
    checkWidth();
    
    // Check on resize
    $(window).resize(function() {
        checkWidth();
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar-wrapper').on('mousewheel DOMMouseScroll wheel', function(e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function() {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function(e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });
    
    // Bootstrap components initialization
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
});
</script> 