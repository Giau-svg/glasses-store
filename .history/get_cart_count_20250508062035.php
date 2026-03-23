<?php
  session_start();
  var_dump($_SESSION); // Thêm dòng này để xem session thực tế
  $count = 0;
  if (isset($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $item) {
          $count += $item['quantity'];
      }
  }
  if (isset($_SESSION['carts'])) {
      foreach ($_SESSION['carts'] as $item) {
          $count += $item['quantity'];
      }
  }
  echo $count;