<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Check if user is a seller or agent
if($_SESSION['user_type'] != 'seller' && $_SESSION['user_type'] != 'agent') {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

// Database connection
$host = "localhost";
$username = "uklz9ew3hrop3";
$password = "zyrbspyjlzjb";
$database = "dbbxwfvewdhinh";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip = trim($_POST['zip']);
    $bedrooms = trim($_POST['bedrooms']);
    $bathrooms = trim($_POST['bathrooms']);
    $square_feet = trim($_POST['square_feet']);
    $property_type = trim($_POST['property_type']);
    $year_built = trim($_POST['year_built']);
    
    // Validate inputs
    if (empty($title) || empty($description) || empty($price) || empty($address) || empty($city) || empty($state) || empty($zip) || empty($bedrooms) || empty($bathrooms) || empty($square_feet) || empty($property_type)) {
        $error = "All fields are required";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Price must be a positive number";
    } elseif (!is_numeric($bedrooms) || $bedrooms <= 0) {
        $error = "Bedrooms must be a positive number";
    } elseif (!is_numeric($bathrooms) || $bathrooms <= 0) {
        $error = "Bathrooms must be a positive number";
    } elseif (!is_numeric($square_feet) || $square_feet <= 0) {
        $error = "Square feet must be a positive number";
    } else {
        // Handle image upload
        $image_url = "https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80";
        
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
            $filename = $_FILES['image']['name'];
            $filetype = $_FILES['image']['type'];
            $filesize = $_FILES['image']['size'];
            
            // Verify file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if(!array_key_exists($ext, $allowed)) {
                $error = "Error: Please select a valid file format.";
            }
            
            // Verify file size - 5MB maximum
            $maxsize = 5 * 1024 * 1024;
            if($filesize > $maxsize) {
                $error = "Error: File size is larger than the allowed limit.";
            }
            
            // Verify MIME type of the file
            if(in_array($filetype, $allowed)) {
                // Check whether file exists before uploading it
                if(file_exists("uploads/" . $filename)) {
                    $filename = uniqid() . "-" . $filename;
                }
                
                // Upload file
                if(move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $filename)) {
                    $image_url = "uploads/" . $filename;
                } else {
                    $error = "Error: There was a problem uploading your file. Please try again.";
                }
            } else {
                $error = "Error: There was a problem uploading your file. Please try again.";
            }
        }
        
        if(empty($error)) {
            // Insert property
            $user_id = $_SESSION['user_id'];
            $full_address = $address . ", " . $city . ", " . $state . " " . $zip;
            
            $stmt = $conn->prepare("INSERT INTO properties (user_id, title, description, price, address, city, state, zip, bedrooms, bathrooms, square_feet, property_type, year_built, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issdssssiiisss", $user_id, $title, $description, $price, $address, $city, $state, $zip, $bedrooms, $bathrooms, $square_feet, $property_type, $year_built, $image_url);
            
            if ($stmt->execute()) {
                $property_id = $conn->insert_id;
                $success = "Property added successfully!";
                
                // Redirect to property details page after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'property-details.php?id=" . $property_id . "';
                    }, 2000);
                </script>";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - ZillowClone</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #2a2a33;
            line-height: 1.6;
        }
        
        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
            height: 70px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #0061e4;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo-icon {
            margin-right: 5px;
            font-size: 32px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin: 0 15px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #2a2a33;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #0061e4;
        }
        
        /* Form Container */
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #2a2a33;
            font-size: 24px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2a2a33;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #0061e4;
        }
        
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
            transition: border-color 0.3s;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #0061e4;
        }
        
        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 16px;
            resize: vertical;
            min-height: 150px;
            transition: border-color 0.3s;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #0061e4;
        }
        
        .form-button {
            width: 100%;
            padding: 12px 15px;
            background-color: #0061e4;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .form-button:hover {
            background-color: #0052cc;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2a2a33;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .form-file-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">
                <span class="logo-icon">Z</span>illowClone
            </a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="search.php">Search</a></li>
            </ul>
        </nav>
    </header>
    
    <!-- Add Property Form -->
    <div class="form-container">
        <h2 class="form-title">Add New Property</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <!-- Basic Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">Basic Information</h3>
                
                <div class="form-group full-width">
                    <label for="title" class="form-label">Property Title</label>
                    <input type="text" id="title" name="title" class="form-input" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-textarea" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" id="price" name="price" class="form-input" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="property_type" class="form-label">Property Type</label>
                        <select id="property_type" name="property_type" class="form-select" required>
                            <option value="">Select Property Type</option>
                            <option value="house" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'house') ? 'selected' : ''; ?>>House</option>
                            <option value="apartment" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                            <option value="condo" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'condo') ? 'selected' : ''; ?>>Condo</option>
                            <option value="townhouse" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'townhouse') ? 'selected' : ''; ?>>Townhouse</option>
                            <option value="land" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'land') ? 'selected' : ''; ?>>Land</option>
                            <option value="commercial" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Location Section -->
            <div class="form-section">
                <h3 class="form-section-title">Location</h3>
                
                <div class="form-group full-width">
                    <label for="address" class="form-label">Street Address</label>
                    <input type="text" id="address" name="address" class="form-input" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="city" class="form-label">City</label>
                        <input type="text" id="city" name="city" class="form-input" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="state" class="form-label">State</label>
                        <input type="text" id="state" name="state" class="form-input" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="zip" class="form-label">ZIP Code</label>
                        <input type="text" id="zip" name="zip" class="form-input" value="<?php echo isset($_POST['zip']) ? htmlspecialchars($_POST['zip']) : ''; ?>" required>
                    </div>
                </div>
            </div>
            
            <!-- Details Section -->
            <div class="form-section">
                <h3 class="form-section-title">Property Details</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="bedrooms" class="form-label">Bedrooms</label>
                        <input type="number" id="bedrooms" name="bedrooms" class="form-input" value="<?php echo isset($_POST['bedrooms']) ? htmlspecialchars($_POST['bedrooms']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bathrooms" class="form-label">Bathrooms</label>
                        <input type="number" id="bathrooms" name="bathrooms" class="form-input" step="0.5" value="<?php echo isset($_POST['bathrooms']) ? htmlspecialchars($_POST['bathrooms']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="square_feet" class="form-label">Square Feet</label>
                        <input type="number" id="square_feet" name="square_feet" class="form-input" value="<?php echo isset($_POST['square_feet']) ? htmlspecialchars($_POST['square_feet']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="year_built" class="form-label">Year Built</label>
                        <input type="number" id="year_built" name="year_built" class="form-input" value="<?php echo isset($_POST['year_built']) ? htmlspecialchars($_POST['year_built']) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Images Section -->
            <div class="form-section">
                <h3 class="form-section-title">Images</h3>
                
                <div class="form-group full-width">
                    <label for="image" class="form-label">Property Image</label>
                    <input type="file" id="image" name="image" class="form-file-input" accept="image/*">
                    <p style="margin-top: 5px; color: #596b82; font-size: 14px;">Upload a main image for your property. Max size: 5MB. Supported formats: JPG, JPEG, PNG, GIF.</p>
                </div>
            </div>
            
            <button type="submit" class="form-button">Add Property</button>
        </form>
    </div>
    
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(event) {
                const price = document.getElementById('price').value;
                const bedrooms = document.getElementById('bedrooms').value;
                const bathrooms = document.getElementById('bathrooms').value;
                const squareFeet = document.getElementById('square_feet').value;
                
                if (price <= 0) {
                    event.preventDefault();
                    alert('Price must be a positive number');
                }
                
                if (bedrooms <= 0) {
                    event.preventDefault();
                    alert('Bedrooms must be a positive number');
                }
                
                if (bathrooms <= 0) {
                    event.preventDefault();
                    alert('Bathrooms must be a positive number');
                }
                
                if (squareFeet <= 0) {
                    event.preventDefault();
                    alert('Square feet must be a positive number');
                }
            });
        });
    </script>
</body>
</html>
