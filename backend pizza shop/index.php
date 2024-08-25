<?php

include 'config.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = 'username or email already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'registered successfully, login now please!';
      }
   }

}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'cart quantity updated!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:index.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = 'please login first!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'already added to cart';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'added to cart!';
      }

   }

}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = 'please login first!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'flat no.'.$_POST['flat'].', '.$_POST['street'].' - '.$_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'order placed successfully!';
      }else{
         $message[] = 'your cart empty!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Complete Responsive Pizza Shop Website Design</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>


   
<!-- header section starts  -->

<header class="header">

   <section class="flex">

      <a href="#home" class="logo">Pizza.</a>

      <nav class="navbar">
         <a href="#home">home</a>
         <a href="#about">about</a>
         <a href="#menu">menu</a>
         <a href="#order">order</a>
         <a href="#faq">faq</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="order-btn" class="fas fa-box"></div>
         <div id="cart-btn" class="fas fa-shopping-cart"><span>(4)</span></div>
      </div>

   </section>

</header>

<!-- header section ends -->

<div class="user-account">

   <section>

      <div id="close-account"><span>close</span></div>

      <div class="user">
         <p><span>you are not logged in now!</span></p>
      </div>

      <div class="display-orders">
         <p>pizza-1 <span>( $3/- x 2 )</span></p>
         <p>pizza 03 <span>( $2/- x 1 )</span></p>
         <p>pizza 06 <span>( $4/- x 4 )</span></p>
         <p>pizza 07 <span>( $2/- x 1 )</span></p>
      </div>

      <div class="flex">

         <form action="" method="post">
            <h3>login now</h3>
            <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
            <input type="submit" value="login now" name="login" class="btn">
         </form>

         <form action="" method="post">
            <h3>register now</h3>
            <input type="text" name="name" required class="box" placeholder="enter your name" maxlength="20">
            <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
            <input type="password" name="cpass" required class="box" placeholder="confirm your password" maxlength="20">
            <input type="submit" value="register now" name="register" class="btn">
         </form>

      </div>

   </section>

</div>

<div class="my-orders">

   <section>

      <div id="close-orders"><span>close</span></div>

      <h3 class="title"> my orders </h3>

      <div class="box">
         <p>placed on : <span>06/04/2022</span> </p>
         <p>name : <span>shaikh anas</span> </p>
         <p>number : <span>1234567890</span></p>
         <p>address : <span>flat no. 123, bulding no. 2, jogeshwari, mumbai, india - 400104</span></p>
         <p>payment method : <span>cash on delivery</span></p>
         <p>your orders : <span>pizza 01 $3/- x 2, pizza 03 $2/- x 1, pizza 06 $4/- x 4, pizza 07, $2/- x 1</span></p>
         <p>total price : <span>$11/-</span></p>
         <p>payment status : <span style="color: var(--red);">pending</span> </p>
      </div>

      <div class="box">
         <p>placed on : <span>06/04/2022</span> </p>
         <p>name : <span>shaikh anas</span> </p>
         <p>number : <span>1234567890</span></p>
         <p>address : <span>flat no. 123, bulding no. 2, jogeshwari, mumbai, india - 400104</span></p>
         <p>payment method : <span>cash on delivery</span></p>
         <p>your orders : <span>pizza 01 $3/- x 2, pizza 03 $2/- x 1, pizza 06 $4/- x 4, pizza 07, $2/- x 1</span></p>
         <p>total price : <span>$11/-</span></p>
         <p>payment status : <span style="color: var(--red);">pending</span> </p>
      </div>

   </section>

</div>

<div class="shopping-cart">

   <section>

      <div id="close-cart"><span>close</span></div>

      <div class="box">
         <a href="#" class="fas fa-times"></a>
         <img src="images/pizza-1.jpg" alt="">
         <div class="content">
            <p>pizza-1 <span>( $3/- x 2 )</span></p>
            <form action="" method="post">
               <input type="number" class="qty" name="qty" min="1" value="2" max="100">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </form>
         </div>
      </div>

      <div class="box">
         <a href="#" class="fas fa-times"></a>
         <img src="images/pizza-3.jpg" alt="">
         <div class="content">
            <p>pizza-3 <span>( $2/- x 1 )</span></p>
            <form action="" method="post">
               <input type="number" class="qty" name="qty" min="1" value="1" max="100">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </form>
         </div>
      </div>

      <div class="box">
         <a href="#" class="fas fa-times"></a>
         <img src="images/pizza-6.jpg" alt="">
         <div class="content">
            <p>pizza-6 <span>( $4/- x 2 )</span></p>
            <form action="" method="post">
               <input type="number" class="qty" name="qty" min="1" value="2" max="100">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </form>
         </div>
      </div>

      <div class="box">
         <a href="#" class="fas fa-times"></a>
         <img src="images/pizza-7.jpg" alt="">
         <div class="content">
            <p>pizza-7 <span>( $2/- x 1 )</span></p>
            <form action="" method="post">
               <input type="number" class="qty" name="qty" min="1" value="1" max="100">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </form>
         </div>
      </div>

      <a href="#order" class="btn">order now</a>

   </section>

</div>

<div class="home-bg">

   <section class="home" id="home">

      <div class="slide-container">

         <div class="slide active">
            <div class="image">
               <img src="images/home-img-1.png" alt="">
            </div>
            <div class="content">
               <h3>homemade Pepperoni Pizza</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-2.png" alt="">
            </div>
            <div class="content">
               <h3>Pizza With Mushrooms</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-3.png" alt="">
            </div>
            <div class="content">
               <h3>Mascarpone And Mushrooms</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

      </div>

   </section>

</div>

<!-- about section starts  -->

<section class="about" id="about">

   <h1 class="heading">about us</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/about-1.svg" alt="">
         <h3>made with love</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">our menu</a>
      </div>

      <div class="box">
         <img src="images/about-2.svg" alt="">
         <h3>30 minutes delivery</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">our menu</a>
      </div>

      <div class="box">
         <img src="images/about-3.svg" alt="">
         <h3>share with freinds</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">our menu</a>
      </div>

   </div>

</section>

<!-- about section ends -->

<!-- menu section starts  -->

<section id="menu" class="menu">

   <h1 class="heading">our menu</h1>

   <div class="box-container">

      <div class="box">
         <div class="price">$<span>2</span>/-</div>
         <img src="images/pizza-1.jpg" alt="">
         <div class="name">pizza 1</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>4</span>/-</div>
         <img src="images/pizza-2.jpg" alt="">
         <div class="name">pizza 2</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>2</span>/-</div>
         <img src="images/pizza-3.jpg" alt="">
         <div class="name">pizza 3</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>3</span>/-</div>
         <img src="images/pizza-4.jpg" alt="">
         <div class="name">pizza 4</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>2</span>/-</div>
         <img src="images/pizza-5.jpg" alt="">
         <div class="name">pizza 5</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>4</span>/-</div>
         <img src="images/pizza-6.jpg" alt="">
         <div class="name">pizza 6</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>2</span>/-</div>
         <img src="images/pizza-7.jpg" alt="">
         <div class="name">pizza 7</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>3</span>/-</div>
         <img src="images/pizza-8.jpg" alt="">
         <div class="name">pizza 8</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

      <div class="box">
         <div class="price">$<span>4</span>/-</div>
         <img src="images/pizza-9.jpg" alt="">
         <div class="name">pizza 9</div>
         <form action="" method="post">
            <input type="number" min="1" max="100" value="1" class="qty" name="qty">
            <input type="submit" value="add to cart" name="add_to_cart" class="btn">
         </form>
      </div>

   </div>

</section>

<!-- menu section ends -->

<!-- order section starts  -->

<section class="order" id="order">

   <h1 class="heading">order now</h1>

   <form action="" method="post">

      <div class="display-orders">
         <p>pizza-1 <span>( $3/- x 2 )</span></p>
         <p>pizza 03 <span>( $2/- x 1 )</span></p>
         <p>pizza 06 <span>( $4/- x 4 )</span></p>
         <p>pizza 07 <span>( $2/- x 1 )</span></p>
      </div>

      <div class="flex">
         <div class="inputBox">
            <span>your name :</span>
            <input type="text" name="name" class="box" required placeholder="enter your name" maxlength="20">
         </div>
         <div class="inputBox">
            <span>your number :</span>
            <input type="number" name="number" class="box" required placeholder="enter your number" min="0">
         </div>
         <div class="inputBox">
            <span>payment method</span>
            <select name="method" class="box">
               <option value="cash on delivery">cash on delivery</option>
               <option value="credit card">credit card</option>
               <option value="paytm">paytm</option>
               <option value="paypal">paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>address line 01 :</span>
            <input type="text" name="flat" class="box" required placeholder="e.g. flat no." maxlength="50">
         </div>
         <div class="inputBox">
            <span>address line 02 :</span>
            <input type="text" name="street" class="box" required placeholder="e.g. street name." maxlength="50">
         </div>
         <div class="inputBox">
            <span>pin code :</span>
            <input type="number" name="pin_code" class="box" required placeholder="e.g. 123456" min="0">
         </div>
      </div>

      <input type="submit" value="order now" class="btn" name="order">

   </form>

</section>

<!-- order section ends -->

<!-- faq section starts  -->

<section class="faq" id="faq">

   <h1 class="heading">FAQ</h1>

   <div class="accordion-container">

      <div class="accordion active">
         <div class="accordion-heading">
            <span>how does it work?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>how long does it take for delivery?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>can I order for huge parties?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>how much protein it contains?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>


      <div class="accordion">
         <div class="accordion-heading">
            <span>is it cooked with oil?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

   </div>

</section>

<!-- faq section ends -->

<!-- footer section starts  -->

<section class="footer">

   <div class="box-container">

      <div class="box">
         <i class="fas fa-phone"></i>
         <h3>phone number</h3>
         <p>+123-456-7890</p>
         <p>+111-222-3333</p>
      </div>

      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>our address</h3>
         <p>mumbai, india - 400104</p>
      </div>

      <div class="box">
         <i class="fas fa-clock"></i>
         <h3>opening hours</h3>
         <p>00:09am to 00:10pm</p>
      </div>

      <div class="box">
         <i class="fas fa-envelope"></i>
         <h3>email address</h3>
         <p>shaikhanas@gmail.com</p>
         <p>anasbhai@gmail.com</p>
      </div>

   </div>

   <div class="credit">
      &copy; copyright @ 2022 by <span>mr. web designer</span> | all rights reserved!
   </div>

</section>

<!-- footer section ends -->


<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>