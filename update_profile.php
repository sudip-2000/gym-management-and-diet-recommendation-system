<?php
session_start();
include 'include/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['uid'])) {
    echo "<script>alert('Please log in first.');</script>";
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$uid = $_SESSION['uid'];

// Fetch current user data
$userQuery = "SELECT age, weight, height, fitness_goal FROM tbluser WHERE id = :uid";
$userStmt = $dbh->prepare($userQuery);
$userStmt->bindParam(':uid', $uid, PDO::PARAM_INT);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission to update user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $fitness_goal = $_POST['fitness_goal'];

    if (!empty($age) && !empty($weight) && !empty($height) && !empty($fitness_goal)) {
        try {
            $updateQuery = "UPDATE tbluser SET age = :age, weight = :weight, height = :height, fitness_goal = :fitness_goal WHERE id = :uid";
            $updateStmt = $dbh->prepare($updateQuery);
            $updateStmt->bindParam(':age', $age, PDO::PARAM_INT);
            $updateStmt->bindParam(':weight', $weight, PDO::PARAM_STR);
            $updateStmt->bindParam(':height', $height, PDO::PARAM_STR);
            $updateStmt->bindParam(':fitness_goal', $fitness_goal, PDO::PARAM_STR);
            $updateStmt->bindParam(':uid', $uid, PDO::PARAM_INT);

            if ($updateStmt->execute()) {
                echo "<script>alert('Profile updated successfully!');</script>";
                echo "<script>window.location.href='diet_recommendations.php';</script>";
            } else {
                echo "<script>alert('Error updating profile.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('An error occurred. Please try again later.');</script>";
            error_log("Database Error: " . $e->getMessage());
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/style.css">

    
    <link rel="stylesheet" href="css/font-awesome.min.css"/>
    <link rel="stylesheet" href="css/owl.carousel.min.css"/>
    <link rel="stylesheet" href="css/nice-select.css"/>
    <link rel="stylesheet" href="css/magnific-popup.css"/>
    <link rel="stylesheet" href="css/slicknav.min.css"/>
    <link rel="stylesheet" href="css/animate.css"/>
    

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
        }

        .form-section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }

        .profile-form {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        .profile-form h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .profile-form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        .profile-form input, .profile-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .profile-form .error-message {
            color: red;
            font-size: 12px;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .profile-form button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .profile-form button:hover {
            background-color: #45a049;
        }

        .profile-form button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const ageInput = document.getElementById('age');
            const weightInput = document.getElementById('weight');
            const heightInput = document.getElementById('height');
            const submitButton = document.querySelector('button[type="submit"]');

            const ageError = document.getElementById('ageError');
            const weightError = document.getElementById('weightError');
            const heightError = document.getElementById('heightError');

            // Validate age
            ageInput.addEventListener('input', () => {
                const ageValue = parseInt(ageInput.value, 10);
                if (ageValue < 18 || ageValue > 100 || isNaN(ageValue)) {
                    ageError.textContent = 'Age must be between 18 and 100.';
                } else {
                    ageError.textContent = '';
                }
                validateForm();
            });

            // Validate weight
            weightInput.addEventListener('input', () => {
                const weightValue = parseFloat(weightInput.value);
                if (weightValue <= 0 || isNaN(weightValue)) {
                    weightError.textContent = 'Weight must be a positive value.';
                } else {
                    weightError.textContent = '';
                }
                validateForm();
            });

            // Validate height
            heightInput.addEventListener('input', () => {
                const heightValue = parseFloat(heightInput.value);
                if (heightValue <= 0 || heightValue > 250 || isNaN(heightValue)) {
                    heightError.textContent = 'Height must be between 1 and 250 cm.';
                } else {
                    heightError.textContent = '';
                }
                validateForm();
            });

            // Enable submit button only if all fields are valid
            const validateForm = () => {
                if (!ageError.textContent && !weightError.textContent && !heightError.textContent &&
                    ageInput.value && weightInput.value && heightInput.value) {
                    submitButton.disabled = false;
                } else {
                    submitButton.disabled = true;
                }
            };

            validateForm();  // Initialize form validation state
        });
    </script>
</head>
<body>
    <?php include 'include/header.php'; ?>

        <!-- Page top Section -->
        <section class="page-top-section set-bg" data-setbg="img/search-bg.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 m-auto text-white">
                    <h2>Profile Update Form</h2>
                </div>
            </div>
        </div>
    </section>

    <section class="form-section">
        <form method="POST" class="profile-form">
            <h2>Update Your Profile</h2>
            
            <label for="age">Age:</label>
            <input type="number" id="age" name="age" value="<?php echo htmlentities($user['age']); ?>" required>
            <div id="ageError" class="error-message"></div>
            
            <label for="weight">Weight (kg):</label>
            <input type="number" id="weight" name="weight" value="<?php echo htmlentities($user['weight']); ?>" required>
            <div id="weightError" class="error-message"></div>
            
            <label for="height">Height (cm):</label>
            <input type="number" id="height" name="height" value="<?php echo htmlentities($user['height']); ?>" required>
            <div id="heightError" class="error-message"></div>
            
            <label for="fitness_goal">Fitness Goal:</label>
            <select id="fitness_goal" name="fitness_goal" required>
                <option value="" disabled>Select a goal</option>
                <option value="weight_loss" <?php if ($user['fitness_goal'] == 'weight_loss') echo 'selected'; ?>>Weight Loss</option>
                <option value="muscle_gain" <?php if ($user['fitness_goal'] == 'muscle_gain') echo 'selected'; ?>>Muscle Gain</option>
                <option value="maintain_fitness" <?php if ($user['fitness_goal'] == 'maintain_fitness') echo 'selected'; ?>>Maintain Fitness</option>
            </select>
            
            <button type="submit" name="update" disabled>Update Profile</button>
        </form>
    </section>

    <?php include 'include/footer.php'; ?>
</body>
</html>
