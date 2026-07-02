<?php
session_start();
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SolarMart | Renewable Energy Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<section id="header">
    <a href="index.php" class="logo-wrap">
        <div class="logo-mark"></div>
        <span>SolarMart</span>
    </a>

    <div>
        <ul id="navbar">
            <li><a class="active" href="index.php">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>

            <?php if (isset($_SESSION['user_id'])) { ?>
                <li id="lg-bag">
                    <a href="cart.php">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-count">0</span>
                    </a>
                </li>

                <li class="profile-menu-wrap">
                    <button type="button" class="profile-icon profile-toggle" aria-label="Open profile" aria-expanded="false">
                        <i class="fa-solid fa-user"></i>
                    </button>

                    <div class="profile-livebar">
                        <div class="profile-livebar-head">
                            <div class="profile-avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
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
            <?php } else { ?>
                <li><a href="login.php">Login</a></li>

                <li id="lg-bag">
                    <a href="cart.php">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="cart-count">0</span>
                    </a>
                </li>
            <?php } ?>

            <a href="#" id="close"><i class="fa fa-times"></i></a>
        </ul>
    </div>

    <div id="mobile">
        <a href="cart.php">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-count">0</span>
        </a>

        <?php if (isset($_SESSION['user_id'])) { ?>
            <button type="button" class="profile-icon profile-toggle mobile-profile" aria-label="Open profile" aria-expanded="false">
                <i class="fa-solid fa-user"></i>
            </button>
        <?php } ?>

        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>
<?php if (isset($_SESSION['user_id'])) { ?>
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
<?php } ?>

<section id="picture1">
    <div class="hero-copy">
        <span class="badge">
            <i class="fa-solid fa-sun"></i> Clean power for every home
        </span>

        <h4>Solar products and installation essentials</h4>
        <h1>Power your future with <span>sunshine</span></h1>

        <p>
            Explore panels, inverters, batteries, lights and complete solar kits.
        </p>

        <div class="hero-actions">
            <a class="primary-btn" href="shop.php">Shop Solar Products</a>
            <a class="outline-btn" href="about.php">Learn More</a>
        </div>
    </div>

    <div class="hero-visual">
        <div class="sun-orb"></div>
        <div class="panel-grid">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
        </div>
    </div>
</section>

<section class="section-p1 stats-strip">
    <div class="stat-card">
        <strong>25+</strong>
        <p>Solar product types</p>
    </div>

    <div class="stat-card">
        <strong>13%</strong>
        <p>VAT calculated at checkout</p>
    </div>

    <div class="stat-card">
        <strong>24/7</strong>
        <p>Customer support</p>
    </div>
</section>

<section id="feature" class="section-p1">
    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-truck-fast"></i></div>
        <h6>Fast Delivery</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-mobile-screen"></i></div>
        <h6>Online Order</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-piggy-bank"></i></div>
        <h6>Save Money</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-screwdriver-wrench"></i></div>
        <h6>Install Support</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-leaf"></i></div>
        <h6>Eco Friendly</h6>
    </div>

    <div class="fe-box">
        <div class="fe-icon"><i class="fa fa-headset"></i></div>
        <h6>24/7 Support</h6>
    </div>
</section>

<section id="product1" class="section-p1">
    <h2>Featured Solar Products</h2>
    <p>Reliable renewable energy products for homes, farms and businesses.</p>
    <div class="pro-container" id="product-list"></div>
</section>

<section id="banner" class="section-m1">
    <h4>Solar Services</h4>
    <h2>Get clean energy with <span>smart products</span></h2>
    <a class="normal" href="shop.php">Shop Now</a>
</section>

<section id="newsletter" class="section-p1 section-m1">
    <div class="newstext">
        <h4>Sign Up For Solar Updates</h4>
        <p>Get email updates about new solar products and <span>special offers</span></p>
    </div>

    <form class="form" data-demo data-message="Thanks for subscribing!">
        <input type="email" placeholder="Your email address" required>
        <button class="normal">Sign Up</button>
    </form>
</section>

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
                <i class="fab fa-facebook-f"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-linkedin"></i>
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

        <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="profile.php">My Profile</a>
            <a href="cart.php">View Cart</a>
            <a href="login.php?logout=true">Logout</a>
        <?php } else { ?>
            <a href="login.php">Sign In</a>
            <a href="cart.php">View Cart</a>
        <?php } ?>

        <a href="shop.php">Wishlist</a>
        <a href="contact.php">Help</a>
    </div>

    <div class="copyright">
        <p>© 2026 SolarMart. All rights reserved.</p>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>