<?php
session_start(); 

// Error reporting to show all errors for debugging (set to 0 in production)
error_reporting(0);

// Include the database configuration
require_once('include/config.php');

if (isset($_POST['submit'])) {
    // Collect form data
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $Password = $_POST['password'];
    $pass = md5($Password);
    $RepeatPassword = $_POST['RepeatPassword'];

    // Validate all fields server-side
    $errors = [];
    
    if (empty($fname)) {
        $errors[] = "First Name is required.";
    }
    if (empty($lname)) {
        $errors[] = "Last Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } elseif (!is_numeric($mobile) || strlen($mobile) != 10) {
        $errors[] = "Invalid mobile number. It should be exactly 10 digits.";
    }
    if (empty($state)) {
        $errors[] = "State is required.";
    }
    if (empty($city)) {
        $errors[] = "City is required.";
    }
    if (empty($Password) || empty($RepeatPassword)) {
        $errors[] = "Password and Confirm Password cannot be empty.";
    } elseif ($Password != $RepeatPassword) {
        $errors[] = "Password and Confirm Password do not match.";
    }
    
    // Check if the email or mobile already exists in the database
    if (empty($errors)) {
        $usermatch = $dbh->prepare("SELECT mobile, email FROM tbluser WHERE email = :usreml OR mobile = :mblenmbr");
        $usermatch->execute(array(':usreml' => $email, ':mblenmbr' => $mobile));
        $row = $usermatch->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if ($row['email'] == $email) {
                $errors[] = "Email is already registered.";
            }
            if ($row['mobile'] == $mobile) {
                $errors[] = "Mobile number is already registered.";
            }
        }
    }

    // If there are no errors, proceed to insert the user data
    if (empty($errors)) {
        $sql = "INSERT INTO tbluser (fname, lname, email, mobile, state, city, password) VALUES (:fname, :lname, :email, :mobile, :state, :city, :Password)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':mobile', $mobile, PDO::PARAM_STR);
        $query->bindParam(':state', $state, PDO::PARAM_STR);
        $query->bindParam(':city', $city, PDO::PARAM_STR);
        $query->bindParam(':Password', $pass, PDO::PARAM_STR);

        // Execute the query
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if ($lastInsertId > 0) {
            echo "<script>alert('Registration successful. Please login');</script>";
            echo "<script>window.location.href='login.php';</script>";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>GYMs Engine</title>
    <meta charset="UTF-8">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/nice-select.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>
    <link rel="stylesheet" href="css/style.css"/>
    
    <!-- JavaScript for Client-Side Validation -->
    <script type="text/javascript">
        function validateForm() {
            var fname = document.getElementById('fname').value;
            var lname = document.getElementById('lname').value;
            var email = document.getElementById('email').value;
            var mobile = document.getElementById('mobile').value;
            var password = document.getElementById('password').value;
            var repeatPassword = document.getElementById('RepeatPassword').value;
            var errors = [];
            
            if (fname === "") {
                errors.push("First Name is required.");
            }
            if (lname === "") {
                errors.push("Last Name is required.");
            }
            if (email === "") {
                errors.push("Email is required.");
            } else if (!/\S+@\S+\.\S+/.test(email)) {
                errors.push("Invalid email format.");
            }
            if (mobile === "") {
                errors.push("Mobile number is required.");
            } else if (!/^\d{10}$/.test(mobile)) {
                errors.push("Mobile number must be 10 digits.");
            }
            if (password === "" || repeatPassword === "") {
                errors.push("Password and Confirm Password cannot be empty.");
            } else if (password !== repeatPassword) {
                errors.push("Password and Confirm Password do not match.");
            }
            
            if (errors.length > 0) {
                alert(errors.join("\n"));
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <!-- Page Preloder -->
    <?php include 'include/header.php';?>

    <!-- Page top Section -->
    <section class="page-top-section set-bg" data-setbg="img/page-top-bg.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-white">
                    <h2>Registration</h2>
                </div>
            </div>
        </div>
    </section>

    <!-- Registration Form -->
    <section class="contact-page-section spad overflow-hidden">
        <div class="container">
            <div class="row">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <?php if (!empty($errors)) { ?>
                        <div class="errorWrap">
                            <strong>ERROR</strong>: <?php echo implode("<br>", $errors); ?>
                        </div>
                    <?php } ?>
                    <form class="singup-form contact-form" method="post" onsubmit="return validateForm()">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="fname" id="fname" placeholder="First Name" autocomplete="off" value="<?php echo $fname;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="lname" id="lname" placeholder="Last Name" autocomplete="off" value="<?php echo $lname;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="email" id="email" placeholder="Your Email" autocomplete="off" value="<?php echo $email;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="mobile" id="mobile" maxlength="10" placeholder="Mobile Number" autocomplete="off" value="<?php echo $mobile;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="state" id="state" placeholder="Your State" autocomplete="off" value="<?php echo $state;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="city" id="city" placeholder="Your City" autocomplete="off" value="<?php echo $city;?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="password" id="password" placeholder="Password" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <input type="password" name="RepeatPassword" id="RepeatPassword" placeholder="Confirm Password" autocomplete="off" required>
                            </div>
                            <div class="col-md-4">
                                <input type="submit" id="submit" name="submit" value="Register Now" class="site-btn sb-gradient">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-2"></div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'include/footer.php';?>
    <div class="back-to-top"><img src="img/icons/up-arrow.png" alt=""></div>

    <!-- Scripts -->
    <script src="js/vendor/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.slicknav.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
