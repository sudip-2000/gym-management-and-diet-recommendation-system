<?php
session_start();
error_reporting(0);
include 'include/config.php'; 
if (strlen($_SESSION['adminid']==0)) {
    header('location:logout.php');
} else {
    $pid = $_GET['pid'];
    if (isset($_POST['Submit'])) {
        $category = $_POST['category'];
        $titlename = $_POST['titlename'];
        $package = $_POST['package'];
        $packageduratiobn = $_POST['packageduratiobn'];
        $Price = $_POST['Price'];
        $photo = $_POST['photo']; // Optional field
        $description = $_POST['description'];

        // Validation checks
        if (empty($category) || $category == 'NA') {
            $errormsg = "Category is required.";
        } elseif (empty($titlename)) {
            $errormsg = "Title Name is required.";
        } elseif (empty($package) || $package == 'NA') {
            $errormsg = "Package Type is required.";
        } elseif (empty($packageduratiobn)) {
            $errormsg = "Package Duration is required.";
        } elseif (empty($Price) || !is_numeric($Price) || $Price <= 0) {
            $errormsg = "Please enter a valid price. Price cannot be negative or zero.";
        } elseif (empty($description)) {
            $errormsg = "Description is required.";
        } else {
            // SQL query to update the package
            $sql = "UPDATE tbladdpackage SET category=:category, titlename=:titlename, PackageType=:package,
            packageduratiobn=:packageduratiobn, Price=:Price, description=:description WHERE id=:pid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':category', $category, PDO::PARAM_STR);
            $query->bindParam(':titlename', $titlename, PDO::PARAM_STR);
            $query->bindParam(':package', $package, PDO::PARAM_STR);
            $query->bindParam(':packageduratiobn', $packageduratiobn, PDO::PARAM_STR);
            $query->bindParam(':Price', $Price, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':pid', $pid, PDO::PARAM_STR);
            $query->execute();

            // Message after update
            echo "<script>alert('Record Updated successfully');</script>";
            echo "<script>window.location.href='manage-post.php'</script>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="description" content="Vali is a">
    <title>Admin | Update Package</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="app sidebar-mini rtl">
    <!-- Navbar-->
    <?php include 'include/header.php'; ?>
    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <?php include 'include/sidebar.php'; ?>
    <main class="app-content">
        <h3>Update Package</h3>
        <hr />
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <!-- Success Message-->
                    <?php if ($msg) { ?>
                    <div class="alert alert-success" role="alert">
                        <strong>Well done!</strong> <?php echo htmlentities($msg); ?>
                    </div>
                    <?php } ?>

                    <!-- Error Message-->
                    <?php if ($errormsg) { ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Oh snap!</strong> <?php echo htmlentities($errormsg); ?>
                    </div>
                    <?php } ?>

                    <div class="tile-body">
                        <?php
                        $sql = "SELECT * FROM tbladdpackage AS t1
                            JOIN tblcategory AS t2 ON t1.category=t2.id
                            JOIN tblpackage AS t3 ON t1.PackageType=t3.id WHERE t1.id=:pid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':pid', $pid, PDO::PARAM_STR);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $cnt = 1;
                        if ($query->rowCount() > 0) {
                            foreach ($results as $result) {
                        ?>
                        <form class="row" method="post">
                            <div class="form-group col-md-6">
                                <label class="control-label">Category</label>
                                <select name="category" id="category" class="form-control" onChange="getdistrict(this.value);">
                                    <option value="<?php echo $result->id; ?>"><?php echo $result->category_name; ?></option>
                                    <option value="NA">--select--</option>
                                    <?php 
                                    $stmt = $dbh->prepare("SELECT * FROM tblcategory ORDER BY category_name");
                                    $stmt->execute();
                                    $countriesList = $stmt->fetchAll();
                                    foreach ($countriesList as $country) {
                                        echo "<option value='".$country['id']."'>".$country['category_name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">Package Type</label>
                                <select name="package" id="package" class="form-control">
                                    <option value="<?php echo $result->id; ?>"><?php echo $result->PackageName; ?></option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Title Name</label>
                                <input class="form-control" name="titlename" id="titlename" type="text" placeholder="Enter your Title Name" value="<?php echo $result->titlename; ?>">
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Package Duration</label>
                                <input class="form-control" type="text" name="packageduratiobn" placeholder="Enter Package Duration" value="<?php echo $result->PackageDuratiobn; ?>">
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Price</label>
                                <input class="form-control" type="text" name="Price" id="Price" placeholder="Enter your Price" value="<?php echo $result->Price; ?>">
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Description</label>
                                <textarea name="description" id="description" class="form-control" cols="5" rows="10"><?php echo $result->Description; ?></textarea>
                            </div>

                            <div class="form-group col-md-4 align-self-end">
                                <input type="Submit" name="Submit" id="Submit" class="btn btn-primary" value="Submit">
                            </div>
                        </form>
                        <?php $cnt++; } } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Essential javascripts for application to work-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/plugins/pace.min.js"></script>
    <script src="//js.nicedit.com/nicEdit-latest.js" type="text/javascript"></script>
    <script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</body>
</html>

<script>
// Script for handling category and package dropdown
function getdistrict(val) {
    $.ajax({
        type: "POST",
        url: "ajaxfile.php",
        data: 'category=' + val,
        success: function (data) {
            $("#package").html(data);
        }
    });
}
</script>
<?php } ?>
