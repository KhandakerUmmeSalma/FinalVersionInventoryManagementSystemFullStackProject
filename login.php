<?php
session_start();
include 'db.php'; // Ensure this is the correct path to your db.php

// Initialize variables for error messages
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user input
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $usertype = htmlspecialchars(trim($_POST['usertype']));

    // Validate user type
    $validUserTypes = [
        'farmer',
        'loan_provider',
        'insurance_provider',
        'investment_provider',
        'grant_provider',
        'admin',
        'advisor',
        'customer',
        'food_safety_officer',
        'delivery_person',
        'storage_unit_staff',
        'producer'
    ];

    if (!in_array($usertype, $validUserTypes)) {
        $error = "Invalid user type selected.";
    }

    // Proceed if no errors
    if (empty($error)) {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, email, password, usertype FROM users WHERE email = ?");
        if ($stmt === false) {
            die('<div class="alert alert-danger" role="alert">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>');
        }

        // Bind parameters
        $stmt->bind_param("s", $email);

        // Execute the statement
        $stmt->execute();

        // Store the result
        $stmt->store_result();

        // Check if the user exists
        if ($stmt->num_rows == 1) {
            // Bind the result variables
            $stmt->bind_result($id, $emailDB, $storedPassword, $dbUserType);
            $stmt->fetch();

            // Compare the entered password with the stored password
            if ($password === $storedPassword) {
                // Check if the user type matches
                if ($usertype !== $dbUserType) {
                    $error = "Incorrect user type.";
                } else {
                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['userid'] = $id;
                    $_SESSION['email'] = $emailDB;
                    $_SESSION['usertype'] = $usertype;

                    // Redirect based on user type
                    switch ($usertype) {
                        case 'farmer':
                            header("Location: farmer.php");
                            break;
                        case 'loan_provider':
                            header("Location: loanProvider.php");
                            break;
                        case 'insurance_provider':
                            header("Location: insuranceProvider.php");
                            break;
                        case 'investment_provider':
                            header("Location: investor.php");
                            break;
                        case 'grant_provider':
                            header("Location: grantProvider.php");
                            break;
                        case 'admin':
                            header("Location: Admin_E/admin_dashboard.php");
                            break;
                        case 'advisor':
                            header("Location: advisor.php");
                            break;
                        case 'customer':
                            header("Location: customer.php"); // Ensure this page exists
                            break;
                        case 'food_safety_officer':
                            header("Location: foodSafetyOfficer.php"); // Ensure this page exists
                            break;
                        case 'delivery_person':
                            header("Location: deliveryPerson.php"); // Ensure this page exists
                            break;
                        case 'storage_unit_staff':
                            header("Location: storageUnitStaff.php"); // Ensure this page exists
                            break;
                        case 'producer':
                            header("Location: producer.php"); // Ensure this page exists
                            break;
                        default:
                            $error = "Invalid user type.";
                            break;
                    }
                    exit();
                }
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid credentials.";
        }

        // Close the statement
        $stmt->close();
    }

    // Close the database connection
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- MDB UI Kit -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="loginCss.css">
    <style>
        /* Additional custom styles */
        body {
            background-color: #f8f9fa;
        }
        .container.custom-section-bg {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        .submit-btn, .back-btn {
            width: 100%;
        }
        .alert {
            margin-top: 1rem;
        }
        .icon-color {
            color: #007bff; /* Adjust icon color as needed */
        }
    </style>
</head>
<body>
    <section class="vh-100 custom-section-bg">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-8">
                    <div class="card custom-card-radius shadow-lg">
                        <div class="row g-0">
                            <!-- Left Image Section -->
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="assets/loginPicture.png" alt="login form" class="img-fluid custom-image-radius" />
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">

                                    <form method="POST" action="login.php" id="loginForm">
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <i class="fa-solid fa-warehouse fa-2x me-3 icon-color"></i>
                                            <span class="h1 fw-bold mb-0">FreshPick</span>
                                        </div>

                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Sign into your account</h5>

                                        <!-- Display Error Message -->
                                        <?php if (!empty($error)): ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?php echo $error; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Email Field -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email" required>
                                        </div>

                                        <!-- Password Field -->
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                                        </div>

                                        <!-- User Type Selection -->
                                        <div class="mb-4">
                                            <label for="usertype" class="form-label">User Type</label>
                                            <select class="form-select form-select-lg" id="usertype" name="usertype" required>
                                                <option value="" disabled selected>Select Your User Type</option>
                                                <option value="farmer">Farmer</option>
                                                <option value="loan_provider">Loan Provider</option>
                                                <option value="insurance_provider">Insurance Provider</option>
                                                <option value="investment_provider">Investment Provider</option>
                                                <option value="grant_provider">Grant Provider</option>
                                                <option value="admin">Admin</option>
                                                <option value="advisor">Advisor</option>
                                                <option value="customer">Customer</option>
                                                <option value="food_safety_officer">Food Safety Officer</option>
                                                <option value="delivery_person">Delivery-person</option>
                                                <option value="storage_unit_staff">Storage Unit Staff</option>
                                                <option value="producer">Producer</option>
                                                <!-- Add other user types as needed -->
                                            </select>
                                        </div>

                                        <!-- Login Button -->
                                        <div class="pt-1 mb-4">
                                            <button class="btn btn-primary btn-lg submit-btn" type="submit">Login</button>
                                        </div>

                                        <a class="small text-muted" href="#!">Forgot password?</a>
                                        <p class="mb-5 pb-lg-2" style="color: #393f81;">
                                            Don't have an account? <a href="signup.php" style="color: #393f81;">Register here</a>
                                        </p>
                                        <a href="#!" class="small text-muted">Terms of use.</a>
                                        <a href="#!" class="small text-muted">Privacy policy</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- MDB JS (Optional for Material Design) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.js"></script>
</body>
</html>
