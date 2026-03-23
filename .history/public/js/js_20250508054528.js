const languageBtn = document.querySelector('.mobile-item__language')
const languageOptions = document.querySelector('.mobile__language')
var user = document.querySelector('.header__user')
var userMenu = document.querySelector('.user__info')

function showMenu() {
  userMenu.classList.toggle('show-user');
}
user.addEventListener('click', showMenu);

function showMobileLanguages() {
  languageOptions.classList.toggle('show-language');
}
languageBtn.addEventListener('click', showMobileLanguages);

$(document).ready(function() {
    $(".btn-add-to-cart").click(function() {
        let id = $(this).data('id');
        $.ajax({
          url: 'add_to_cart.php',
          type: 'GET',
          data: {id},
        })
        .done(function(response){
          if(response == 1){
            // Gọi AJAX lấy số lượng mới
            $.get('get_cart_count.php', function(count){
              $('#cart-count').text(count);
            });
            alert('Thêm giỏ hàng thành công');
          }else {
            alert(response);  
          }
        });
    });
});

$(document).ready(function(){
  $.get('get_cart_count.php', function(count){
    $('#cart-count').text(count);
  });
});