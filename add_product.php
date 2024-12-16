<?php
session_start();
include 'db.php';  // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['userid'])) {
    echo '<div class="alert alert-danger">You must be logged in to add a product.</div>';
    exit();
}

$farmer_id = $_SESSION['userid'];  // Get the logged-in user's ID

// Check if the user is already registered as a producer
$stmt = $conn->prepare("SELECT producer_id FROM producers WHERE producer_id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    // If producer doesn't exist, automatically register the user as a producer
    $stmt = $conn->prepare("INSERT INTO producers (producer_id) VALUES (?)");
    $stmt->bind_param("i", $farmer_id);
    
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">You have been successfully registered as a producer.</div>';
    } else {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($conn->error) . '</div>';
        exit();
    }
}

// Proceed with adding the product if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $good_name = $_POST['good_name'];
    $good_type = $_POST['good_type'];
    $price_per_unit = $_POST['price_per_unit'];
    $production_date = $_POST['production_date'];
    $shelf_time = $_POST['shelf_time'];
    $temperature_threshold = $_POST['temperature_threshold'];
    $batch_number = $_POST['batch_number'];

    // Insert the product into the database
    $stmt = $conn->prepare("INSERT INTO perishable_goods (good_name, good_type, price_per_unit, production_date, shelf_time, temperature_threshold, batch_number, producer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsdssi", $good_name, $good_type, $price_per_unit, $production_date, $shelf_time, $temperature_threshold, $batch_number, $farmer_id);

    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Product added successfully.</div>';
    } else {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($conn->error) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farmer Dashboard - Add Product</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styles */
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 15px 25px;
            display: block;
            color: #ffffff;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center text-white">Farmer Dashboard</h4>
        <a href="#addProduct" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="addProduct">Add New Product</a>
        <a href="#viewProducts" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="viewProducts">View Products</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Welcome to Your Dashboard</h2>
        <hr>

        <!-- Add New Product Section -->
        <div id="addProduct" class="collapse show">
            <h3>Add New Product</h3>
            <form method="POST" action="add_product.php">
                <div class="mb-3">
                    <label for="good_name" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="good_name" name="good_name" required>
                </div>
                <div class="mb-3">
                    <label for="good_type" class="form-label">Product Type</label>
                    <select class="form-select" id="good_type" name="good_type" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Dairy">Dairy</option>
                        <option value="Meat">Meat</option>
                        <!-- Add more types as needed -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="price_per_unit" class="form-label">Price Per Unit</label>
                    <input type="number" step="0.01" class="form-control" id="price_per_unit" name="price_per_unit" required>
                </div>
                <div class="mb-3">
                    <label for="production_date" class="form-label">Production Date</label>
                    <input type="date" class="form-control" id="production_date" name="production_date" required>
                </div>
                <div class="mb-3">
                    <label for="shelf_time" class="form-label">Shelf Time (days)</label>
                    <input type="number" class="form-control" id="shelf_time" name="shelf_time" required>
                </div>
                <div class="mb-3">
                    <label for="temperature_threshold" class="form-label">Temperature Threshold (&deg;C)</label>
                    <input type="number" step="0.1" class="form-control" id="temperature_threshold" name="temperature_threshold" required>
                </div>
                <div class="mb-3">
                    <label for="batch_number" class="form-label">Batch Number</label>
                    <input type="text" class="form-control" id="batch_number" name="batch_number" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>

        <hr>

        <!-- View Products Section -->
        <div id="viewProducts" class="collapse">
            <h3>Your Products</h3>
            <?php
            // Fetch products belonging to the farmer
            $stmt = $conn->prepare("SELECT goods_id, good_name, good_type, price_per_unit, production_date, shelf_time, temperature_threshold, batch_number FROM perishable_goods WHERE producer_id = ?");
            if ($stmt === false) {
                echo '<div class="alert alert-danger">Prepare failed: ' . htmlspecialchars($conn->error) . '</div>';
            } else {
                $stmt->bind_param("i", $farmer_id);
                $stmt->execute();
                $result = $stmt->get_result();
            ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Goods ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Price Per Unit</th>
                            <th>Production Date</th>
                            <th>Shelf Time (days)</th>
                            <th>Temperature Threshold (&deg;C)</th>
                            <th>Batch Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['goods_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['good_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['good_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['price_per_unit']); ?></td>
                                <td><?php echo htmlspecialchars($row['production_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['shelf_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['temperature_threshold']); ?></td>
                                <td><?php echo htmlspecialchars($row['batch_number']); ?></td>
                                <td>
                                    <a href="edit_product.php?goods_id=<?php echo $row['goods_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete_product.php?goods_id=<?php echo $row['goods_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php
                $stmt->close();
            }
            ?>
        </div>

    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
