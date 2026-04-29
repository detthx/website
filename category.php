 <?php
session_start(); 

include("conn.php");
// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
  $email = $_POST['email'] ?? '';
  $firstName = $_POST['firstName'] ?? '';
  $lastName = $_POST['lastName'] ?? '';
  $birthday = $_POST['birthday'] ?? '';
  $username = $_POST['regUsername'] ?? '';
  $password = $_POST['regPassword'] ?? '';

  $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
  $stmt->execute([$username, $email]);
  if ($stmt->fetch()) {
    $registerError = "Username or email already taken.";
  } else {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, first_name, last_name, birthday, username, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$email, $firstName, $lastName, $birthday, $username, $passwordHash]);
    $registerSuccess = "Registration successful. Please login.";
  }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $loginUsername = $_POST['loginUsername'] ?? '';
  $loginPassword = $_POST['loginPassword'] ?? '';
if ($loginUsername == "admin" && $loginPassword=="admin"){
  ?>
<script>
  alert("welcome Admin")
  window.location.href="admin/index.php"
</script>
  <?php
  
}else{
  
  if ($loginUsername && $loginPassword) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$loginUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($loginPassword, $user['password_hash'])) {
      $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'first_name' => $user['first_name']
      ];
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
    } else {
      $loginError = "Invalid username or password.";
    }
  } else {
    $loginError = "Please enter username and password.";
  }
}
} 

$loggedIn = isset($_SESSION['user']);
$username = $loggedIn ? htmlspecialchars($_SESSION['user']['username']) : '';
$firstName = $loggedIn ? htmlspecialchars($_SESSION['user']['first_name']) : '';
if(isset($_SESSION['user']['id'])){
  $user = $_SESSION['user']['id'];
}
  $stmt1 = $pdo->query("SELECT * from cart inner join products on cart.p_id = products.id where u_id = '$user'");
  $cart_count = $stmt1->rowCount();

  if(isset($_GET['category'])){
    $cat = $_GET['category'];
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Shop Homepage</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .slider-container { overflow: hidden; }
    .slider-track { display: flex; transition: transform 0.5s ease; }
    .slider-image { min-width: 100%; flex-shrink: 0; }
  </style>
  <style>
    
    .container {
        position: absolute;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
      padding: 40px;
      margin-top:700px;
      height:400px;
      overflow:auto;
      scrollbar-width:none;
    }


    .product-card {
      position: relative;
      width: 200px;
      height: 260px;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      cursor: pointer;
      background-color: #fff;
    }

    .product-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .product-info {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      padding: 10px;
      background: rgba(0, 0, 0, 0.6);
      color: #fff;
      transform: translateY(100%);
      transition: transform 0.3s ease;
    }

    .product-card:hover .product-info {
      transform: translateY(0);
    }

    .product-name {
      font-size: 1rem;
      font-weight: bold;
    }

    .product-price,
    .product-stock {
      font-size: 0.85rem;
    }
    .scene {
      width: 300px;
      height: 200px;
      perspective: 1000px;
    }

    .cube {
      width: 100%;
      height: 100%;
      position: relative;
      transform-style: preserve-3d;
      animation: rotateCube 8s infinite linear;
    }

    .cube-face {
      position: absolute;
      width: 100%;
      height: 100%;
      backface-visibility: hidden;
    }

    .front  { transform: rotateY(0deg) translateZ(150px); }
    .right  { transform: rotateY(90deg) translateZ(150px); }
    .back   { transform: rotateY(180deg) translateZ(150px); }
    .left   { transform: rotateY(-90deg) translateZ(150px); }

    @keyframes rotateCube {
      0%   { transform: rotateY(0deg); }
      25%  { transform: rotateY(-90deg); }
      50%  { transform: rotateY(-180deg); }
      75%  { transform: rotateY(-270deg); }
      100% { transform: rotateY(-360deg); }
    }
  </style>

  <script>
    function toggleCart() {
      document.getElementById("cartModal").classList.toggle("hidden");
    }
    function toggleLogin() {
      document.getElementById("registerModal").classList.add("hidden");
      document.getElementById("loginModal").classList.toggle("hidden");
    }
    function toggleRegister() {
      document.getElementById("loginModal").classList.add("hidden");
      document.getElementById("registerModal").classList.toggle("hidden");
    }
    let currentIndex = 0;
    function slideImages() {
      const track = document.getElementById("sliderTrack");
      const totalImages = track.children.length;
      currentIndex = (currentIndex + 1) % totalImages;
      track.style.transform = 'translateX(-${currentIndex * 100}%)';
    }
    window.onload = () => {
      setInterval(slideImages, 3000);
    }
  </script>
</head>
<body class="bg-gray-100">
<?php if (!empty($loginError)) : ?>
  <script>
    Swal.fire({ icon: 'error', title: 'Login Failed', text: '<?= htmlspecialchars($loginError) ?>' });
    document.addEventListener('DOMContentLoaded', () => document.getElementById("loginModal").classList.remove("hidden"));
  </script>
<?php endif; ?>

<?php if (!empty($registerError)) : ?>
  <script>
    Swal.fire({ icon: 'error', title: 'Registration Failed', text: '<?= htmlspecialchars($registerError) ?>' });
    document.addEventListener('DOMContentLoaded', () => document.getElementById("registerModal").classList.remove("hidden"));
  </script>
<?php endif; ?>

<?php if (!empty($registerSuccess)) : ?>
  <script>
    Swal.fire({ icon: 'success', title: 'Registration Successful', text: '<?= htmlspecialchars($registerSuccess) ?>' });
    document.addEventListener('DOMContentLoaded', () => document.getElementById("loginModal").classList.remove("hidden"));
  </script>
<?php endif; ?>

<nav class="flex items-center justify-between bg-white shadow px-6 py-4">
  <div class="flex items-center space-x-3">
    <img src="images/logo7.png" alt="Logo" class="h-10 w-20" />
  </div>
    <!-- Search Form -->
  <form method="GET" class="">
    <div class="flex space-x-4 items-center">
      <input type="text" name="search" placeholder="Search for products
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
             class="px-4 py-2 rounded border border-gray-300 w-1/2"
             style="width:200px"
             >
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
    </div>
  </form>
  <div class="flex items-center space-x-6">
    <a href="index.php" class="hover:text-blue-600">Home</a>
    <div class="relative group">
  <button class="hover:text-blue-600 inline-flex items-center">Products
    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M19 9l-7 7-7-7" />
    </svg>
  </button>
  <div class="absolute left-0 mt-2 w-40 bg-white shadow-lg rounded-md opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200 z-50">
    <a href="category.php?category=For Men" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"> For Men</a>
    <a href="category.php?category=For Women" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">For Women</a>
    
  </div>
</div>
    <a href="about.php" class="hover:text-blue-600">About</a>
    <?php if ($loggedIn): ?>
      
      <a href="logout.php" class="hover:text-red-600 font-semibold">Logout</a>
      <span class="font-semibold">Welcome, <?= $firstName ?>!</span>
    <?php else: ?>
      <button onclick="toggleLogin()" class="hover:text-blue-600">Login</button>
    <?php endif; ?>
    <button onclick="toggleCart()" class="relative">
      <i class="fas fa-shopping-cart text-xl"></i>
      <span class="absolute -top-3 -right-3 text-xs bg-red-500 text-white rounded-full h-4 w-4 flex items-center justify-center"><?= $cart_count?></span>
    </button>
  </div>
</nav>


<main class="flex p-6 items-center justify-center">
   <div class="scene">
    <div class="cube">
      <div class="cube-face front">
        <img src="images/11.png" class="w-full h-full object-cover" />
      </div>
      <div class="cube-face right">
        <img src="images/12.png" class="w-full h-full object-cover" />
      </div>
      <div class="cube-face back">
        <img src="images/13.png" class="w-full h-full object-cover" />
      </div>
      <div class="cube-face left">
        <img src="images/14.png" class="w-full h-full object-cover" />
      </div>
    </div>
  </div>

 <!-- PRODUCT LISTING -->
<div class="container grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 p-6">
  <?php
    $category = $_GET['category'] ?? '';
    $query = "SELECT * FROM products";
    $params = [];

    if ($category) {
        $query .= " WHERE category = :category";
        $params[':category'] = $category;
    }

    $stmtprod = $pdo->prepare($query);
    $stmtprod->execute($params);
    $products = $stmtprod->fetchAll();
  if ($products) {
        foreach ($products as $product): ?>
          <a href="productlink.php?id=<?= $product['id'] ?>" class="block">
            <div class="bg-gray-800 text-white rounded-xl shadow-lg hover:shadow-xl transition-shadow p-5 flex flex-col h-full">
              <img src="admin/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-48 object-cover rounded-lg mb-4" />
              
              <div class="flex-grow">
                <h3 class="text-lg font-bold text-white mb-1 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="text-green-400 font-semibold text-xl mb-2">₱ <?= number_format($product['price'], 2) ?></p>
                <p class="text-sm text-gray-400 mb-4">Stock: <?= $product['quantity'] ?></p>
              </div>

              <button
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md font-semibold transition-colors"
                aria-label="View details of <?= htmlspecialchars($product['name']) ?>"
              >
                View
              </button>
            </div>
          </a>
    <?php endforeach;
    } else {
        echo '<p class="text-center text-gray-300 col-span-full">No products found.</p>';
    }
    ?>
  </div>
</div>

  



  
</main>

<!-- Cart Modal -->
<div id="cartModal" class="fixed top-0 right-0 w-80 h-full bg-white shadow-lg p-4 hidden z-50">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold">Your Cart</h2>
    <button onclick="toggleCart()" class="text-gray-600 hover:text-gray-900 text-xl"><i class="fas fa-times"></i></button>
  </div>
  <?php 
  
  $cart_prods = $stmt1->fetchAll(2);
  foreach($cart_prods as $cart_prod){

?>
<div class="cart-item flex items-center justify-between mb-3">
    <img src="admin/<?=$cart_prod['image']?>" class="w-12 h-12 rounded"/>
    <div class="flex-1 px-3">
      <p class="font-medium"><?=$cart_prod['name']?></p>
      <p class="text-sm text-gray-600">₱ <?=number_format($cart_prod['price'],2)?></p>
    </div>
    <a href="delete_cart.php?c_id=<?=$cart_prod['c_id']?>&p_id=<?=$cart_prod['id']?>">
    <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
    </a>
  </div>

<?php

  
  }
  
  
  ?>
  </div>
  

<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 <?= isset($loginError) ? '' : 'hidden' ?> z-50">
  <div class="bg-white rounded shadow-lg p-6 w-96 relative">
    <button onclick="toggleLogin()" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900 text-xl"><i class="fas fa-times"></i></button>
    <h2 class="text-2xl font-semibold mb-4">Login</h2>
    <form method="post" action="">
      <input type="hidden" name="login" value="1" />
      <label class="block mb-2 font-medium" for="loginUsername">Username</label>
      <input id="loginUsername" name="loginUsername" type="text" class="w-full mb-4 px-3 py-2 border rounded" required />
      <label class="block mb-2 font-medium" for="loginPassword">Password</label>
      <input id="loginPassword" name="loginPassword" type="password" class="w-full mb-4 px-3 py-2 border rounded" required />
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Login</button>
    </form>
    <p class="mt-4 text-center text-sm">Don't have an account?
      <button onclick="toggleRegister()" class="text-blue-600 hover:underline font-semibold">Register here</button>
    </p>
  </div>
</div>

<!-- Register Modal -->
<div id="registerModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 <?= isset($registerError) || isset($registerSuccess) ? '' : 'hidden' ?> z-50">
  <div class="bg-white rounded shadow-lg w-96 p-6 relative">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">Register</h2>
      <button onclick="toggleRegister()" class="text-gray-600 hover:text-gray-900 text-xl"><i class="fas fa-times"></i></button>
    </div>
    <form method="post" action="">
      <input type="hidden" name="register" value="1" />
      <label class="block mb-1 font-medium" for="email">Email</label>
      <input type="email" id="email" name="email" class="w-full mb-3 p-2 border rounded" required />
      <label class="block mb-1 font-medium" for="firstName">First Name</label>
      <input type="text" id="firstName" name="firstName" class="w-full mb-3 p-2 border rounded" required />
      <label class="block mb-1 font-medium" for="lastName">Last Name</label>
      <input type="text" id="lastName" name="lastName" class="w-full mb-3 p-2 border rounded" required />
      <label class="block mb-1 font-medium" for="birthday">Birthday</label>
      <input type="date" id="birthday" name="birthday" class="w-full mb-3 p-2 border rounded" required />
      <label class="block mb-1 font-medium" for="regUsername">Username</label>
      <input type="text" id="regUsername" name="regUsername" class="w-full mb-3 p-2 border rounded" required />
      <label class="block mb-1 font-medium" for="regPassword">Password</label>
      <input type="password" id="regPassword" name="regPassword" class="w-full mb-3 p-2 border rounded" required />
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Register</button>
    </form>
    <p class="mt-3 text-center text-sm text-gray-600">
      Already have an account? 
      <a href="#" onclick="toggleLogin();" class="text-blue-600 hover:underline cursor-pointer">Login here</a>
    </p>
  </div>
</div>



</body>
</html
