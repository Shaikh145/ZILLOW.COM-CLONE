<?php
session_start();

// Database connection
$host = "localhost";
$username = "uklz9ew3hrop3";
$password = "zyrbspyjlzjb";
$database = "dbbxwfvewdhinh";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if property ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

$property_id = $_GET['id'];

// Get property details
$stmt = $conn->prepare("
    SELECT p.*, u.name as owner_name, u.email as owner_email 
    FROM properties p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

$property = $result->fetch_assoc();

// Check if property is saved by the user
$is_saved = false;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT id FROM saved_properties WHERE user_id = ? AND property_id = ?");
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $is_saved = ($result->num_rows > 0);
}

// Process inquiry form
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inquiry_submit'])) {
    if(!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href = 'login.php';</script>";
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $message = trim($_POST['message']);
    
    if (empty($message)) {
        $error = "Message is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO inquiries (user_id, property_id, message, status, created_at) VALUES (?, ?, ?, 'new', NOW())");
        $stmt->bind_param("iis", $user_id, $property_id, $message);
        
        if ($stmt->execute()) {
            $success = "Your inquiry has been sent successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}

// Get similar properties
$stmt = $conn->prepare("
    SELECT * FROM properties 
    WHERE property_type = ? AND id != ? 
    ORDER BY RAND() 
    LIMIT 3
");
$stmt->bind_param("si", $property['property_type'], $property_id);
$stmt->execute();
$similar_result = $stmt->get_result();

$similar_properties = [];
while ($row = $similar_result->fetch_assoc()) {
    $similar_properties[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $property['title']; ?> - ZillowClone</title>
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
        
        /* Property Details Container */
        .property-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Property Header */
        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .property-title {
            font-size: 28px;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .property-address {
            color: #596b82;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .property-price {
            font-size: 28px;
            font-weight: bold;
            color: #0061e4;
            margin-bottom: 10px;
        }
        
        .property-actions {
            display: flex;
            gap: 10px;
        }
        
        .property-btn {
            padding: 8px 16px;
            background-color: #0061e4;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .property-btn:hover {
            background-color: #0052cc;
        }
        
        .property-btn.secondary {
            background-color: white;
            color: #0061e4;
            border: 1px solid #0061e4;
        }
        
        .property-btn.secondary:hover {
            background-color: #f0f6ff;
        }
        
        .property-btn.saved {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }
        
        /* Property Gallery */
        .property-gallery {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .property-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }
        
        /* Property Details */
        .property-details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .property-details-left {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .property-details-right {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .property-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .property-card-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2a2a33;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        /* Property Features */
        .property-features {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .property-feature {
            text-align: center;
        }
        
        .property-feature-value {
            font-size: 24px;
            font-weight: bold;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .property-feature-label {
            color: #596b82;
            font-size: 14px;
        }
        
        /* Property Description */
        .property-section {
            margin-bottom: 30px;
        }
        
        .property-section-title {
            font-size: 20px;
            margin-bottom: 15px;
            color: #2a2a33;
        }
        
        .property-description {
            color: #596b82;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        
        /* Property Details List */
        .property-details-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .property-detail-item {
            display: flex;
            align-items: center;
        }
        
        .property-detail-label {
            font-weight: 500;
            margin-right: 10px;
            color: #2a2a33;
        }
        
        .property-detail-value {
            color: #596b82;
        }
        
        /* Contact Form */
        .contact-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
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
        
        .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 16px;
            resize: vertical;
            min-height: 120px;
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
            margin-bottom: 15px;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        /* Agent Card */
        .agent-card {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .agent-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #0061e4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
            font-weight: bold;
        }
        
        .agent-info {
            flex: 1;
        }
        
        .agent-name {
            font-size: 18px;
            font-weight: 500;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .agent-contact {
            color: #596b82;
            font-size: 14px;
        }
        
        /* Similar Properties */
        .similar-properties {
            margin-top: 50px;
        }
        
        .similar-properties-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #2a2a33;
        }
        
        .similar-properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .similar-property-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .similar-property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .similar-property-image {
            height: 180px;
            background-size: cover;
            background-position: center;
        }
        
        .similar-property-details {
            padding: 15px;
        }
        
        .similar-property-price {
            font-size: 20px;
            font-weight: bold;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .similar-property-address {
            color: #596b82;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .similar-property-features {
            display: flex;
            justify-content: space-between;
            color: #596b82;
            font-size: 14px;
            margin-top: 10px;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .property-details-grid {
                grid-template-columns: 1fr;
            }
            
            .property-details-list {
                grid-template-columns: 1fr;
            }
            
            .property-image {
                height: 300px;
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
                <li><a href="search.php">Search</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="login.php">Sign In</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <!-- Property Details -->
    <div class="property-container">
        <div class="property-header">
            <div>
                <h1 class="property-title"><?php echo $property['title']; ?></h1>
                <p class="property-address"><?php echo $property['address'] . ', ' . $property['city'] . ', ' . $property['state'] . ' ' . $property['zip']; ?></p>
                <div class="property-price">$<?php echo number_format($property['price']); ?></div>
            </div>
            
            <div class="property-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($is_saved): ?>
                        <button class="property-btn saved" id="savePropertyBtn" data-property-id="<?php echo $property_id; ?>" data-saved="true">
                            ♥ Saved
                        </button>
                    <?php else: ?>
                        <button class="property-btn secondary" id="savePropertyBtn" data-property-id="<?php echo $property_id; ?>" data-saved="false">
                            ♡ Save
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="property-btn secondary">♡ Save</a>
                <?php endif; ?>
                
                <button class="property-btn" onclick="window.print()">Print</button>
                <button class="property-btn secondary" onclick="shareProperty()">Share</button>
            </div>
        </div>
        
        <!-- Property Gallery -->
        <div class="property-gallery">
            <img src="<?php echo $property['image_url']; ?>" alt="<?php echo $property['title']; ?>" class="property-image">
        </div>
        
        <!-- Property Details Grid -->
        <div class="property-details-grid">
            <!-- Left Column -->
            <div class="property-details-left">
                <!-- Property Features -->
                <div class="property-features">
                    <div class="property-feature">
                        <div class="property-feature-value"><?php echo $property['bedrooms']; ?></div>
                        <div class="property-feature-label">Bedrooms</div>
                    </div>
                    
                    <div class="property-feature">
                        <div class="property-feature-value"><?php echo $property['bathrooms']; ?></div>
                        <div class="property-feature-label">Bathrooms</div>
                    </div>
                    
                    <div class="property-feature">
                        <div class="property-feature-value"><?php echo number_format($property['square_feet']); ?></div>
                        <div class="property-feature-label">Square Feet</div>
                    </div>
                    
                    <div class="property-feature">
                        <div class="property-feature-value"><?php echo $property['year_built'] ? $property['year_built'] : 'N/A'; ?></div>
                        <div class="property-feature-label">Year Built</div>
                    </div>
                </div>
                
                <!-- Property Description -->
                <div class="property-section">
                    <h2 class="property-section-title">Description</h2>
                    <div class="property-description">
                        <?php echo nl2br($property['description']); ?>
                    </div>
                </div>
                
                <!-- Property Details -->
                <div class="property-section">
                    <h2 class="property-section-title">Property Details</h2>
                    <div class="property-details-list">
                        <div class="property-detail-item">
                            <span class="property-detail-label">Property Type:</span>
                            <span class="property-detail-value"><?php echo ucfirst($property['property_type']); ?></span>
                        </div>
                        
                        <div class="property-detail-item">
                            <span class="property-detail-label">Year Built:</span>
                            <span class="property-detail-value"><?php echo $property['year_built'] ? $property['year_built'] : 'N/A'; ?></span>
                        </div>
                        
                        <div class="property-detail-item">
                            <span class="property-detail-label">Bedrooms:</span>
                            <span class="property-detail-value"><?php echo $property['bedrooms']; ?></span>
                        </div>
                        
                        <div class="property-detail-item">
                            <span class="property-detail-label">Bathrooms:</span>
                            <span class="property-detail-value"><?php echo $property['bathrooms']; ?></span>
                        </div>
                        
                        <div class="property-detail-item">
                            <span class="property-detail-label">Square Feet:</span>
                            <span class="property-detail-value"><?php echo number_format($property['square_feet']); ?></span>
                        </div>
                        
                        <div class="property-detail-item">
                            <span class="property-detail-label">Price per Sq Ft:</span>
                            <span class="property-detail-value">$<?php echo number_format($property['price'] / $property['square_feet'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="property-details-right">
                <!-- Contact Agent Card -->
                <div class="property-card">
                    <h3 class="property-card-title">Contact <?php echo $property['user_type'] == 'agent' ? 'Agent' : 'Owner'; ?></h3>
                    
                    <div class="agent-card">
                        <div class="agent-avatar"><?php echo substr($property['owner_name'], 0, 1); ?></div>
                        <div class="agent-info">
                            <div class="agent-name"><?php echo $property['owner_name']; ?></div>
                            <div class="agent-contact"><?php echo $property['owner_email']; ?></div>
                        </div>
                    </div>
                    
                    <?php if(!empty($error)): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="success-message"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $property_id; ?>" method="post" class="contact-form">
                        <div class="form-group">
                            <label for="message" class="form-label">Message</label>
                            <textarea id="message" name="message" class="form-textarea" placeholder="I am interested in this property and would like to schedule a viewing." required></textarea>
                        </div>
                        
                        <button type="submit" name="inquiry_submit" class="form-button">Send Message</button>
                    </form>
                </div>
                
                <!-- Mortgage Calculator Card -->
                <div class="property-card">
                    <h3 class="property-card-title">Mortgage Calculator</h3>
                    
                    <div class="form-group">
                        <label for="down_payment" class="form-label">Down Payment (%)</label>
                        <input type="number" id="down_payment" class="form-input" value="20" min="0" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                        <input type="number" id="interest_rate" class="form-input" value="3.5" min="0" max="20" step="0.1">
                    </div>
                    
                    <div class="form-group">
                        <label for="loan_term" class="form-label">Loan Term (years)</label>
                        <input type="number" id="loan_term" class="form-input" value="30" min="1" max="50">
                    </div>
                    
                    <button type="button" id="calculate_mortgage" class="form-button">Calculate</button>
                    
                    <div id="mortgage_result" style="margin-top: 15px; text-align: center; display: none;">
                        <div style="font-size: 24px; font-weight: bold; color: #0061e4; margin-bottom: 5px;">$<span id="monthly_payment">0</span></div>
                        <div style="color: #596b82; font-size: 14px;">Estimated monthly payment</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Similar Properties -->
        <?php if(count($similar_properties) > 0): ?>
            <div class="similar-properties">
                <h2 class="similar-properties-title">Similar Properties</h2>
                
                <div class="similar-properties-grid">
                    <?php foreach($similar_properties as $similar): ?>
                        <a href="property-details.php?id=<?php echo $similar['id']; ?>" class="similar-property-card" style="text-decoration: none; color: inherit;">
                            <div class="similar-property-image" style="background-image: url('<?php echo $similar['image_url']; ?>');"></div>
                            <div class="similar-property-details">
                                <div class="similar-property-price">$<?php echo number_format($similar['price']); ?></div>
                                <div class="similar-property-address"><?php echo $similar['address'] . ', ' . $similar['city']; ?></div>
                                <div class="similar-property-features">
                                    <span><?php echo $similar['bedrooms']; ?> bds</span>
                                    <span><?php echo $similar['bathrooms']; ?> ba</span>
                                    <span><?php echo number_format($similar['square_feet']); ?> sqft</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Save property functionality
        document.addEventListener('DOMContentLoaded', function() {
            const savePropertyBtn = document.getElementById('savePropertyBtn');
            
            if (savePropertyBtn) {
                savePropertyBtn.addEventListener('click', function() {
                    const propertyId = this.getAttribute('data-property-id');
                    const isSaved = this.getAttribute('data-saved') === 'true';
                    
                    // Send AJAX request to save/unsave property
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', isSaved ? 'unsave-property.php' : 'save-property.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (this.status === 200) {
                            // Update button state
                            if (isSaved) {
                                savePropertyBtn.setAttribute('data-saved', 'false');
                                savePropertyBtn.classList.remove('saved');
                                savePropertyBtn.classList.add('secondary');
                                savePropertyBtn.innerHTML = '♡ Save';
                            } else {
                                savePropertyBtn.setAttribute('data-saved', 'true');
                                savePropertyBtn.classList.remove('secondary');
                                savePropertyBtn.classList.add('saved');
                                savePropertyBtn.innerHTML = '♥ Saved';
                            }
                        }
                    };
                    xhr.send('property_id=' + propertyId);
                });
            }
            
            // Mortgage calculator
            const calculateMortgageBtn = document.getElementById('calculate_mortgage');
            
            if (calculateMortgageBtn) {
                calculateMortgageBtn.addEventListener('click', function() {
                    const propertyPrice = <?php echo $property['price']; ?>;
                    const downPaymentPercent = parseFloat(document.getElementById('down_payment').value);
                    const interestRate = parseFloat(document.getElementById('interest_rate').value);
                    const loanTerm = parseInt(document.getElementById('loan_term').value);
                    
                    // Calculate mortgage
                    const downPayment = propertyPrice * (downPaymentPercent / 100);
                    const loanAmount = propertyPrice - downPayment;
                    const monthlyInterest = interestRate / 100 / 12;
                    const numberOfPayments = loanTerm * 12;
                    
                    let monthlyPayment = 0;
                    
                    if (interestRate > 0) {
                        monthlyPayment = loanAmount * (monthlyInterest * Math.pow(1 + monthlyInterest, numberOfPayments)) / (Math.pow(1 + monthlyInterest, numberOfPayments) - 1);
                    } else {
                        monthlyPayment = loanAmount / numberOfPayments;
                    }
                    
                    // Display result
                    document.getElementById('monthly_payment').textContent = monthlyPayment.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    document.getElementById('mortgage_result').style.display = 'block';
                });
            }
        });
        
        // Share property functionality
        function shareProperty() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo $property['title']; ?>',
                    text: 'Check out this property: <?php echo $property['title']; ?>',
                    url: window.location.href
                })
                .catch(error => console.log('Error sharing:', error));
            } else {
                // Fallback for browsers that don't support Web Share API
                prompt('Copy this link to share:', window.location.href);
            }
        }
    </script>
</body>
</html>
