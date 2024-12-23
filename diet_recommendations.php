<?php
session_start();
include 'include/config.php';

// Redirect user to login if not logged in
if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Please log in first.');</script>";
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$uid = $_SESSION['uid'];

// Fetch user data
$userQuery = "SELECT age, weight, height, fitness_goal FROM tbluser WHERE id = :uid";
$userStmt = $dbh->prepare($userQuery);
$userStmt->bindParam(':uid', $uid, PDO::PARAM_INT);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Check if profile is complete
if (!$user || empty($user['age']) || empty($user['weight']) || empty($user['height']) || empty($user['fitness_goal'])) {
    echo "<script>alert('Please complete your profile to get diet recommendations.');</script>";
    echo "<script>window.location.href='update_profile.php';</script>";
    exit();
}

// Calculate BMI
$user['bmi'] = $user['weight'] / pow($user['height'] / 100, 2);

// Fetch diet recommendations with weighted score
function getDietRecommendations($dbh, $user) {
    $dietQuery = "
    SELECT * ,
           ABS(:bmi - target_bmi) AS bmi_diff,
           (min_age <= :age AND max_age >= :age) AS age_match,
           (min_weight <= :weight AND max_weight >= :weight) AS weight_match,
           (min_height <= :height AND max_height >= :height) AS height_match,
           (CASE WHEN fitness_goal = :fitness_goal THEN 1 ELSE 0 END) AS goal_match,
           (
               4 * ((min_age <= :age AND max_age >= :age) +
                    (min_weight <= :weight AND max_weight >= :weight) +
                    (min_height <= :height AND max_height >= :height))
               + 6 * (CASE WHEN fitness_goal = :fitness_goal THEN 1 ELSE 0 END)
               - 2 * ABS(:bmi - target_bmi)
           ) AS weighted_score
    FROM tbladddiet
    WHERE (min_age <= :age AND max_age >= :age)
      AND (min_weight <= :weight AND max_weight >= :weight)
      AND (min_height <= :height AND max_height >= :height)
    ORDER BY RAND()  -- Randomize results
    LIMIT 3;  -- Fetch top 3 diet plans randomly
";

    $dietStmt = $dbh->prepare($dietQuery);
    $dietStmt->bindParam(':age', $user['age'], PDO::PARAM_INT);
    $dietStmt->bindParam(':weight', $user['weight'], PDO::PARAM_STR);
    $dietStmt->bindParam(':height', $user['height'], PDO::PARAM_STR);
    $dietStmt->bindParam(':bmi', $user['bmi'], PDO::PARAM_STR);
    $dietStmt->bindParam(':fitness_goal', $user['fitness_goal'], PDO::PARAM_STR);
    $dietStmt->execute();
    
    return $dietStmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch all matching diet plans
}

$dietRecommendations = getDietRecommendations($dbh, $user);

// Handle AJAX request for refreshing recommendations
if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    echo json_encode(getDietRecommendations($dbh, $user));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diet Recommendations</title>

    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/nice-select.css"/>
    <link rel="stylesheet" href="css/magnific-popup.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>
    <link rel="stylesheet" href="css/animate.css"/>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .next-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .next-button:hover {
            background-color: #0056b3;
        }
        .update-profile-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .update-profile-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'include/header1.php'; ?>

    <!-- Page top Section -->
    <section class="page-top-section set-bg" data-setbg="img/search-bg.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-white">
                    <h2>Diet Recommendations</h2>
                    <a href="update_profile.php" class="update-profile-btn">Update Profile</a>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing-section spad">
        <div class="container">
            <div class="section-title text-center">
                <h2>Diet Recommendations with Recipes</h2>
                <h3 class="text-center text-gray">Get Hooked on Healthy Eating</h3>
            </div>
            <div class="row">
                <div class="col-lg-12 col-sm-6" style="text-align: center;">
                    <ul id="diet-list">
                        <?php if (empty($dietRecommendations)): ?>
                            <p>No suitable diet plans found. Please contact an administrator for further assistance.</p>
                        <?php else: ?>
                            <?php foreach ($dietRecommendations as $diet): ?>
                                <li>
                                    <h3><?php echo htmlentities($diet['diet_name']); ?></h3>
                                    <p><?php echo htmlentities($diet['description']); ?></p>
                                    <p><strong>Recipe:</strong> <?php echo htmlentities($diet['recipe']); ?></p>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <button class="next-button" id="next-btn">Next</button>
                </div>
            </div>
        </div>
    </section>

    <?php include 'include/footer.php'; ?>

    <script>
        $(document).ready(function () {
            $('#next-btn').on('click', function () {
                $.ajax({
                    url: 'diet_recommendations.php',
                    type: 'POST',
                    data: { ajax: 'true' }, // Trigger AJAX to get new recommendations
                    dataType: 'json',
                    success: function (data) {
                        const dietList = $('#diet-list');
                        dietList.empty();  // Clear the previous list
                        if (data.length === 0) {
                            dietList.append('<p>No suitable diet plans found. Please contact an administrator for further assistance.</p>');
                        } else {
                            data.forEach(function (diet) {
                                dietList.append(`
                                    <li>
                                        <h3>${diet.diet_name}</h3>
                                        <p>${diet.description}</p>
                                        <p><strong>Recipe:</strong> ${diet.recipe}</p>
                                    </li>
                                `);
                            });
                        }
                    },
                    error: function () {
                        alert('Error fetching new recommendations.');
                    }
                });
            });
        });
    </script>
</body>
</html>
