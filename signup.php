<?php
// Include the database connection file
include('db.php');

// Initialize variables for success and error messages
$success = '';
$error = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
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

    // Validate passwords
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    }

    // Check if email is already registered
    if (empty($error)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email is already registered. Please <a href='login.php'>login</a> or use a different email.";
            $stmt->close();
        } else {
            $stmt->close();
            // Proceed to insert the new user
            // Store the plain text password (Note: Highly insecure)
            $storedPassword = $password;

            // Prepare SQL query using prepared statements
            $insertStmt = $conn->prepare("INSERT INTO users (username, email, password, phone, address, usertype) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insertStmt === false) {
                die("Prepare failed: " . htmlspecialchars($conn->error));
            }

            // Bind parameters
            $insertStmt->bind_param("ssssss", $username, $email, $storedPassword, $phone, $address, $usertype);

            // Execute the query
            if ($insertStmt->execute()) {
                $success = "New account created successfully! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Error: " . htmlspecialchars($insertStmt->error);
            }

            // Close the insert statement
            $insertStmt->close();
        }
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
    <title>Signup Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (Optional for Icons) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- MDB UI Kit (Optional for Material Design) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="loginSignup.css">
    <style>
        /* Additional custom styles */
        body {
            background-color: #f8f9fa;
        }
        .container-fluid.custom-section-bg {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* Ensures full viewport height */
            padding: 2rem; /* Adds padding for better spacing on smaller screens */
            box-sizing: border-box; /* Includes padding in the element's total width and height */
        }
        .form-container {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px; /* Increased max-width for a wider layout */
            box-sizing: border-box; /* Ensures padding is included within the width */
        }
        .submit-btn, .back-btn {
            width: 100%;
        }
        .alert {
            margin-top: 1rem;
        }

        /* Optional: Adjust form labels and inputs for better spacing */
        .form-label {
            font-weight: 500;
        }
        .form-control, .form-select {
            height: calc(3.5rem + 2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid custom-section-bg">
        <div class="form-container">
            <h2 class="mb-4 text-center">Create an Account</h2>

            <!-- Display Success Message -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Display Error Message -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="signup.php" id="signupForm">
                <!-- Row for Username and Email -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <!-- Row for Password and Confirm Password -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div class="col-md-6">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control form-control-lg" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                    </div>
                </div>

                <!-- Row for Phone Number and Address -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control form-control-lg" id="phone" name="phone" placeholder="Enter your phone number">
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label">Address</label>
                        <select class="form-select form-select-lg" id="address" name="address" required>
                            <option value="" disabled selected>Select Your Address</option>
                            <option value="Dhaka">Dhaka</option>
                            <option value="Sylhet">Sylhet</option>
                            <option value="Chittagong">Chittagong</option>
                            <option value="Khulna">Khulna</option>
                            <option value="Borishal">Borishal</option>
                            <option value="Rajshahi">Rajshahi</option>
                        </select>
                    </div>
                </div>

                <!-- Row for User Type -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
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
                    <!-- Optional: Add another field here if needed, e.g., Company Name for certain user types -->
                    <!--
                    <div class="col-md-6">
                        <label for="company" class="form-label">Company Name</label>
                        <input type="text" class="form-control form-control-lg" id="company" name="company" placeholder="Enter your company name">
                    </div>
                    -->
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-lg submit-btn">Sign Up</button>
            </form>

            <!-- Back to Login Button -->
            <button class="btn btn-secondary btn-lg back-btn mt-3" onclick="goToLogin()">Back to Login</button>
        </div>
    </div>

    <script>
        // JavaScript to handle the "Back to Login" button action
        function goToLogin() {
            window.location.href = 'login.php'; // Ensure this points to your login page
        }

        // Optional: Form validation for the signup
        document.getElementById('signupForm').addEventListener('submit', function (event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                event.preventDefault(); // Prevent form submission if passwords don't match
            }
        });
    </script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- MDB JS (Optional for Material Design) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.0.0/mdb.min.js"></script>
</body>
</html>
