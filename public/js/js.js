const languageBtn = document.querySelector('.mobile-item__language')
const languageOptions = document.querySelector('.mobile__language')
var user = document.querySelector('.header__user')
var userMenu = document.querySelector('.user__info')

if (user && userMenu) {
  function showMenu() {
    userMenu.classList.toggle('show-user');
  }
  user.addEventListener('click', showMenu);
}

if (languageBtn && languageOptions) {
  function showMobileLanguages() {
    languageOptions.classList.toggle('show-language');
  }
  languageBtn.addEventListener('click', showMobileLanguages);
}

$(document).ready(function() {
    console.log("jQuery ready! Bắt đầu cập nhật số giỏ hàng...");

    // Cập nhật số giỏ hàng ngay khi load bất kỳ trang nào
    $.get('get_cart_count.php', function(count){
        console.log("Kết quả get_cart_count.php:", count);
        $('#cart-count').text(count);
        if ($('#cart-count').length) {
            console.log("Đã tìm thấy #cart-count và cập nhật thành công!");
        } else {
            console.warn("Không tìm thấy #cart-count trên trang!");
        }
    }).fail(function() {
        console.error("Lỗi khi gọi get_cart_count.php!");
    });

    // Khi thêm vào giỏ hàng
    $(".btn-add-to-cart").click(function() {
        let id = $(this).data('id');
        console.log("Thêm vào giỏ hàng, id:", id);
        $.ajax({
            url: 'add_to_cart.php',
            type: 'GET',
            data: {id},
        })
        .done(function(response){
            console.log("Kết quả add_to_cart.php:", response);
            if(response == 1){
                // Cập nhật lại số giỏ hàng
                $.get('get_cart_count.php', function(count){
                    console.log("Sau khi thêm, số giỏ hàng:", count);
                    $('#cart-count').text(count);
                });
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Thêm giỏ hàng thành công',
                    timer: 1500,
                    showConfirmButton: false
                });
            }else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: response
                });
            }
        });
    });

    $("#project").autocomplete({
        minLength: 2,
        source: function(request, response) {
            if (request.term.trim() === '') {
                response([]);
                return;
            }
            $.getJSON('get_search.php', { term: request.term }, response);
        },
    });
});
