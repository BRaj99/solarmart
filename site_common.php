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
    <a href="index.php" class="logo-wrap">
        <div class="logo-mark"></div>
        <span>SolarMart</span>
    </a>

    <div>
        <ul id="navbar">
            <li><a<?php echo navActive('home', $current); ?> href="index.php">Home</a></li>
            <li><a<?php echo navActive('shop', $current); ?> href="shop.php">Shop</a></li>
            <li><a<?php echo navActive('blog', $current); ?> href="blog.php">Blog</a></li>
            <li><a<?php echo navActive('about', $current); ?> href="about.php">About</a></li>
            <li><a<?php echo navActive('contact', $current); ?> href="contact.php">Contact</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="login.php?logout=true">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>

            <li id="lg-bag">
                <a href="cart.php">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-count">0</span>
                </a>
            </li>

            <a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
    </div>

    <div id="mobile">
        <a href="cart.php">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-count">0</span>
        </a>
        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>
<?php
}

function renderFooter() {
?>
<footer class="site-footer section-p1">
    <div class="footer-main">
        <div class="footer-brand">
            <a href="index.php" class="logo-wrap">
                <div class="logo-mark"></div>
                <span>SolarMart</span>
            </a>
            <p>SolarMart supplies solar panels, batteries, inverters, lights, kits and accessories for homes and businesses in Nepal.</p>
            <div class="footer-social">
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                <a href="#" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="blog.php">Blog</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
        </div>

        <div class="footer-col">
            <h4>Customer</h4>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">My Profile</a>
                <a href="cart.php">Cart</a>
                <a href="login.php?logout=true">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="cart.php">Cart</a>
            <?php endif; ?>
        </div>

        <div class="footer-col footer-contact">
            <h4>Contact</h4>
            <p><i class="fa-solid fa-location-dot"></i> Kathmandu, Nepal</p>
            <p><i class="fa-solid fa-phone"></i> +977-9800000000</p>
            <p><i class="fa-solid fa-envelope"></i> info@solarmart.com</p>
            <p><i class="fa-solid fa-clock"></i> 9:00 AM - 6:00 PM, Sun-Fri</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 SolarMart. All rights reserved.</p>
        <p>Clean energy products for everyday power needs.</p>
    </div>
</footer>
<?php
}
?>