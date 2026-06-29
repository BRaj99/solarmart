<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function navActive($page, $current) {
    return $page === $current ? ' class="active"' : '';
}
function renderHeader($current = '') {
?>
<section id="header">
    <a href="index.php" class="logo-wrap"><div class="logo-mark"></div><span>SolarMart</span></a>
    <div>
        <ul id="navbar">
            <li><a<?php echo navActive('home', $current); ?> href="index.php">Home</a></li>
            <li><a<?php echo navActive('shop', $current); ?> href="shop.php">Shop</a></li>
            <li><a<?php echo navActive('blog', $current); ?> href="blog.php">Blog</a></li>
            <li><a<?php echo navActive('about', $current); ?> href="about.php">About</a></li>
            <li><a<?php echo navActive('contact', $current); ?> href="contact.php">Contact</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a<?php echo navActive('profile', $current); ?> href="profile.php"><i class="fa-solid fa-user"></i> My Account</a></li>
                <li><a href="login.php?logout=true">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
            <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a></li>
            <a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
    </div>
    <div id="mobile"><a href="cart.php"><i class="fa-solid fa-bag-shopping"></i><span class="cart-count">0</span></a><i id="bar" class="fas fa-outdent"></i></div>
</section>
<?php
}
function renderFooter() {
?>
<footer class="section-p1">
    <div class="col">
        <a href="index.php" class="logo-wrap"><div class="logo-mark"></div><span>SolarMart</span></a>
        <h4>Contact</h4>
        <p><strong>Address:</strong> Kathmandu, Nepal</p>
        <p><strong>Phone:</strong> +977-9800000000</p>
        <p><strong>Hours:</strong> 9:00 - 18:00, Sun-Fri</p>
    </div>
    <div class="col">
        <h4>Pages</h4>
        <a href="shop.php">Shop</a>
        <a href="blog.php">Blog</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact / Help</a>
    </div>
    <div class="col">
        <h4>My Account</h4>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">My Profile</a>
            <a href="cart.php">View Cart</a>
            <a href="login.php?logout=true">Logout</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
            <a href="cart.php">View Cart</a>
        <?php endif; ?>
    </div>
    <div class="copyright"><p>© 2026 SolarMart. PHP/MySQL ecommerce website.</p></div>
</footer>
<?php } ?>
