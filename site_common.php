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

            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>

            <li id="lg-bag">
                <a href="cart.php">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="cart-count">0</span>
                </a>
            </li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="profile-menu-wrap">
                    <button type="button" class="profile-icon profile-toggle" aria-label="Open profile" aria-expanded="false">
                        <i class="fa-solid fa-user"></i>
                    </button>
                    <div class="profile-livebar">
                        <div class="profile-livebar-head">
                            <div class="profile-avatar"><i class="fa-solid fa-user"></i></div>
                            <div>
                                <h4><?php echo e($_SESSION['user_name'] ?? 'Customer'); ?></h4>
                                <p><?php echo e($_SESSION['user_email'] ?? ''); ?></p>
                            </div>
                        </div>
                        <div class="profile-livebar-info">
                            <p><i class="fa-solid fa-phone"></i> <?php echo e($_SESSION['user_phone'] ?? 'Not added'); ?></p>
                            <p><i class="fa-solid fa-location-dot"></i> <?php echo e($_SESSION['user_location'] ?? 'Not added'); ?></p>
                            <p><i class="fa-solid fa-house"></i> <?php echo e($_SESSION['user_address'] ?? 'Not added'); ?></p>
                        </div>
                        <div class="profile-livebar-actions">
                            <a href="profile.php" class="outline-btn">Full Profile</a>
                            <a href="login.php?logout=true" class="primary-btn">Logout</a>
                        </div>
                    </div>
                </li>
            <?php endif; ?>

            <a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
    </div>

    <div id="mobile">
        <a href="cart.php">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-count">0</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button type="button" class="profile-icon profile-toggle mobile-profile" aria-label="Open profile" aria-expanded="false">
                <i class="fa-solid fa-user"></i>
            </button>
        <?php endif; ?>
        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="profile-livebar mobile-profile-panel">
    <div class="profile-livebar-head">
        <div class="profile-avatar"><i class="fa-solid fa-user"></i></div>
        <div>
            <h4><?php echo e($_SESSION['user_name'] ?? 'Customer'); ?></h4>
            <p><?php echo e($_SESSION['user_email'] ?? ''); ?></p>
        </div>
    </div>
    <div class="profile-livebar-info">
        <p><i class="fa-solid fa-phone"></i> <?php echo e($_SESSION['user_phone'] ?? 'Not added'); ?></p>
        <p><i class="fa-solid fa-location-dot"></i> <?php echo e($_SESSION['user_location'] ?? 'Not added'); ?></p>
        <p><i class="fa-solid fa-house"></i> <?php echo e($_SESSION['user_address'] ?? 'Not added'); ?></p>
    </div>
    <div class="profile-livebar-actions">
        <a href="profile.php" class="outline-btn">Full Profile</a>
        <a href="login.php?logout=true" class="primary-btn">Logout</a>
    </div>
</div>
<?php endif; ?>
<?php
}

function renderFooter() {
?>
<footer class="section-p1">
    <div class="col">
        <a href="index.php" class="logo-wrap">
            <div class="logo-mark"></div>
            <span>SolarMart</span>
        </a>

        <h4>Contact</h4>
        <p><strong>Address:</strong> Kathmandu, Nepal</p>
        <p><strong>Phone:</strong> +977-9800000000</p>
        <p><strong>Hours:</strong> 9:00 - 18:00, Sun-Fri</p>

        <div class="follow">
            <h4>Follow Us</h4>
            <div class="icon">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </div>

    <div class="col">
        <h4>About</h4>
        <a href="about.php">About Us</a>
        <a href="contact.php">Delivery Information</a>
        <a href="contact.php">Privacy Policy</a>
        <a href="contact.php">Terms & Conditions</a>
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
        <a href="shop.php">Shop</a>
        <a href="contact.php">Help</a>
    </div>

    <div class="copyright">
        <p>© 2026 SolarMart. All rights reserved.</p>
    </div>
</footer>
<?php
}
?>