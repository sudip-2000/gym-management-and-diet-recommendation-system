<?php 
session_start();
error_reporting(0);
include 'include/config.php';
$uid = $_SESSION['uid'];

if (isset($_POST['submit'])) { 
    $pid = $_POST['pid'];

    // Check if the user has already booked this package
    $sql_check = "SELECT * FROM tblbooking WHERE package_id = :pid AND userid = :uid";
    $query_check = $dbh->prepare($sql_check);
    $query_check->bindParam(':pid', $pid, PDO::PARAM_STR);
    $query_check->bindParam(':uid', $uid, PDO::PARAM_STR);
    $query_check->execute();

    if ($query_check->rowCount() > 0) {
        // If the package is already booked
        echo "<script>alert('You have already booked this package.');</script>";
        echo "<script>window.location.href='index.php'</script>";
    } else {
        // Proceed with the booking
        $sql = "INSERT INTO tblbooking (package_id, userid) VALUES (:pid, :uid)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':pid', $pid, PDO::PARAM_STR);
        $query->bindParam(':uid', $uid, PDO::PARAM_STR);
        $query->execute();

        echo "<script>alert('Package has been booked successfully.');</script>";
        echo "<script>window.location.href='booking-history.php'</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>GYMs Engine</title>
    <meta charset="UTF-8">
    <meta name="description" content="GYMs Engine Yoga HTML Template">
    <meta name="keywords" content="yoga, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/nice-select.css"/>
    <link rel="stylesheet" href="css/magnific-popup.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>
    <link rel="stylesheet" href="css/animate.css"/>

    <!-- Main Stylesheets -->
    <link rel="stylesheet" href="css/style.css"/>
</head>
<body>
    <!-- Header Section -->
    <?php include 'include/header.php'; ?>
    <!-- Header Section end -->

    <!-- Page top Section -->
    <section class="page-top-section set-bg" data-setbg="img/gyms.gif">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-white">
                    <h2>Home</h2>
                    <p>GymsEngine provides a comprehensive suite of tools to help you manage your gym efficiently, from member management to scheduling and reporting.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section spad">
        <div class="container">
            <div class="section-title text-center">
                <img src="img/favicon.ico" alt="">
                <h2>Pricing Plans</h2>
                <p>Push your limits, find your strength!</p>
            </div>
            <div class="row">
                <?php 
                $sql = "SELECT id, category, titlename, PackageType, PackageDuratiobn, Price, uploadphoto, Description, create_date FROM tbladdpackage";
                $query = $dbh->prepare($sql);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);

                if ($query->rowCount() > 0) {
                    foreach ($results as $result) {
                ?>
                <div class="col-lg-3 col-sm-6">
                    <div class="pricing-item beginner">
                        <div class="pi-top">
                            <h4><?php echo $result->titlename; ?></h4>
                        </div>
                        <div class="pi-price">
                            <h3><?php echo htmlentities($result->Price); ?></h3>
                            <p><?php echo $result->PackageDuratiobn; ?></p>
                        </div>
                        <ul>
                            <?php echo $result->Description; ?>
                        </ul>
                        <?php if (strlen($_SESSION['uid']) == 0): ?>
                            <a href="login.php" class="site-btn sb-line-gradient">Booking Now</a>
                        <?php else: ?>
                            <form method='post'>
                                <input type='hidden' name='pid' value='<?php echo htmlentities($result->id); ?>'>
                                <input class='site-btn sb-line-gradient' type='submit' name='submit' value='Book Now' onclick="return confirm('Do you really want to book this package?');"> 
                            </form> 
                        <?php endif; ?>
                    </div>
                </div>
                <?php } } ?>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'include/footer.php'; ?>
    <!-- Footer Section end -->

    <div class="back-to-top"><img src="img/icons/up-arrow.png" alt=""></div>

    <!-- Javascripts -->
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
