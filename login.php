<?php
session_start();
include "db.php";

if(isset($_GET['logout'])){

    session_destroy();

    header("Location: login.php");

    exit();
}

$error = "";
$registerError = "";
$registerSuccess = "";
$showSignup = false;

/* LOGIN */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_age'] = $user['age'];
            $_SESSION['user_gender'] = $user['gender'];
            $_SESSION['user_location'] = $user['location'];
            $_SESSION['user_address'] = $user['address'];

            header("Location: index.php");
            exit();

        } else {

            $error = "Incorrect password";

        }

    } else {

        $error = "Email not found";

    }
}

/* REGISTER */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {

    $showSignup = true;

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {

        $registerError = "Email already registered";

    } else {

        $sql = "INSERT INTO users(
                    fullname,
                    email,
                    phone,
                    age,
                    gender,
                    location,
                    address,
                    password
                )
                VALUES(
                    '$fullname',
                    '$email',
                    '$phone',
                    '$age',
                    '$gender',
                    '$location',
                    '$address',
                    '$hashedPassword'
                )";

        if (mysqli_query($conn, $sql)) {

            $registerSuccess = "Account created successfully. You can login now.";

            $showSignup = false;

        } else {

            $registerError = "Something went wrong. Please try again.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | SolarMart</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="style.css">
</head>

<body>

<section id="header">

    <a href="index.php" class="logo-wrap">
        <div class="logo-mark"></div>
        <span>SolarMart</span>
    </a>

    <ul id="navbar">

        <li><a href="index.php">Home</a></li>

        <li><a href="shop.php">Shop</a></li>

        <li>
            <a href="cart.php">
                Cart <span class="cart-count">0</span>
            </a>
        </li>

        <a href="#" id="close">
            <i class="fa fa-times"></i>
        </a>

    </ul>

    <div id="mobile">

        <a href="cart.php">
            <i class="fa-solid fa-bag-shopping"></i>
            <span class="cart-count">0</span>
        </a>

        <i id="bar" class="fas fa-outdent"></i>

    </div>

</section>

<section class="page-header">
    <h1>Customer Login</h1>
    <p>Login to access your SolarMart account.</p>
</section>

<section class="section-p1 login-section">

    <div class="login-box">

        <!-- LOGIN FORM -->

        <form
            id="loginForm"
            class="card form-stack login-card <?php echo $showSignup ? 'hidden' : ''; ?>"
            method="POST"
        >

            <h3>Customer Login</h3>

            <?php if ($error != "") { ?>
                <div class="login-alert">
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['reset_success'])) { ?>

                <div class="success-alert">

                    <?php
                        echo $_SESSION['reset_success'];
                        unset($_SESSION['reset_success']);
                    ?>

                </div>

            <?php } ?>

            <?php if ($registerSuccess != "") { ?>

                <div class="success-alert">
                    <?php echo $registerSuccess; ?>
                </div>

            <?php } ?>

            <input
                class="form-input"
                type="email"
                name="email"
                required
                placeholder="Email address"
            >

            <div class="password-box">

                <input
                    class="form-input"
                    type="password"
                    id="loginPassword"
                    name="password"
                    required
                    placeholder="Password"
                >

                <i
                    class="fa fa-eye"
                    onclick="togglePassword('loginPassword', this)"
                ></i>

            </div>

            <button
                class="primary-btn"
                type="submit"
                name="login"
            >
                Login
            </button>

            <a href="forgot_password.php" class="forgot-link">
                Forgot Password?
            </a>

            <p>Don't have an account?</p>

            <button
                type="button"
                class="outline-btn"
                onclick="showSignup()"
            >
                Sign Up
            </button>

        </form>

        <!-- SIGNUP FORM -->

        <form
            id="signupForm"
            class="card form-stack login-card <?php echo $showSignup ? '' : 'hidden'; ?>"
            method="POST"
        >

            <h3>Create Account</h3>

            <?php if ($registerError != "") { ?>

                <div class="login-alert">
                    <?php echo $registerError; ?>
                </div>

            <?php } ?>

            <input
                class="form-input"
                type="text"
                name="fullname"
                required
                placeholder="Full name"
            >

            <input
                class="form-input"
                type="email"
                name="email"
                required
                placeholder="Email address"
            >

            <input
                class="form-input"
                type="text"
                name="phone"
                required
                placeholder="Phone number"
            >

            <input
                class="form-input"
                type="number"
                name="age"
                required
                placeholder="Age"
            >

            <select class="form-input" name="gender" required>

                <option value="">Select gender</option>

                <option value="Male">Male</option>

                <option value="Female">Female</option>

                <option value="Other">Other</option>

            </select>

            <input
                class="form-input"
                type="text"
                name="location"
                required
                placeholder="City / Location"
            >

            <textarea
                class="form-input"
                name="address"
                required
                placeholder="Full delivery address"
            ></textarea>

            <div class="password-box">

                <input
                    class="form-input"
                    type="password"
                    id="signupPassword"
                    name="password"
                    required
                    placeholder="Password"
                >

                <i
                    class="fa fa-eye"
                    onclick="togglePassword('signupPassword', this)"
                ></i>

            </div>

            <button
                class="primary-btn"
                type="submit"
                name="register"
            >
                Create Account
            </button>

            <p>Already have an account?</p>

            <button
                type="button"
                class="outline-btn"
                onclick="showLogin()"
            >
                Back to Login
            </button>

        </form>

    </div>

</section>

<script src="script.js"></script>

<script>

function togglePassword(inputId, icon) {

    const input = document.getElementById(inputId);

    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("fa-eye");

        icon.classList.add("fa-eye-slash");

    } else {

        input.type = "password";

        icon.classList.remove("fa-eye-slash");

        icon.classList.add("fa-eye");

    }
}

function showSignup() {

    document.getElementById("loginForm").classList.add("hidden");

    document.getElementById("signupForm").classList.remove("hidden");
}

function showLogin() {

    document.getElementById("signupForm").classList.add("hidden");

    document.getElementById("loginForm").classList.remove("hidden");
}

</script>

</body>
</html>