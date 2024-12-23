<?php
session_start();
include 'include/config.php';

if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Please log in first.');</script>";
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

if (!isset($_GET['diet_id'])) {
    echo "<script>alert('Invalid diet ID.');</script>";
    echo "<script>window.location.href='diet_recommendations.php';</script>";
    exit();
}

$dietId = $_GET['diet_id'];

try {
    $query = "SELECT * FROM tbladddiet WHERE diet_id = :diet_id";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':diet_id', $dietId, PDO::PARAM_INT);
    $stmt->execute();
    $diet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$diet) {
        echo "<script>alert('Diet not found.');</script>";
        echo "<script>window.location.href='diet_recommendations.php';</script>";
        exit();
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo "<script>alert('An error occurred. Please try again later.');</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlentities($diet['diet_name']); ?> - Recipe</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            margin: 30px auto;
            max-width: 800px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .diet-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .diet-header h2 {
            font-size: 2.5rem;
            color: #4CAF50;
        }

        .diet-details {
            line-height: 1.8;
        }

        .diet-details h3 {
            font-size: 1.5rem;
            margin-top: 20px;
            color: #555;
        }
    </style>
</head>
<body>
    <?php include 'include/header.php'; ?>

    <section class="container">
        <div class="diet-header">
            <h2><?php echo htmlentities($diet['diet_name']); ?></h2>
        </div>
        <div class="diet-details">
            <p><strong>Description:</strong> <?php echo nl2br(htmlentities($diet['description'])); ?></p>
            <h3>Recipe</h3>
            <p><?php echo nl2br(htmlentities($diet['recipe'])); ?></p>
        </div>
        <div class="text-center">
            <a href="diet_recommendations.php" class="btn btn-secondary">Back to Recommendations</a>
        </div>
    </section>

    <?php include 'include/footer.php'; ?>
</body>
</html>
