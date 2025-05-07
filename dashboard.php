<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
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

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_type = $_SESSION['user_type'];

// Get saved properties
$saved_properties = [];
$stmt = $conn->prepare("SELECT p.* FROM saved_properties sp JOIN properties p ON sp.property_id = p.id WHERE sp.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $saved_properties[] = $row;
}

// Get user's listings if they are a seller or agent
$user_listings = [];
if ($user_type == 'seller' || $user_type == 'agent') {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_listings[] = $row;
    }
}

// Get recent inquiries if they are a seller or agent
$inquiries = [];
if ($user_type == 'seller' || $user_type == 'agent') {
    $stmt = $conn->prepare("
        SELECT i.*, p.title as property_title, u.name as inquirer_name 
        FROM inquiries i 
        JOIN properties p ON i.property_id = p.id 
        JOIN users u ON i.user_id = u.id 
        WHERE p.user_id = ? 
        ORDER BY i.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $inquiries[] = $row;
    }
}

// Get user's inquiries if they are a buyer
$user_inquiries = [];
if ($user_type == 'buyer') {
    $stmt = $conn->prepare("
        SELECT i.*, p.title as property_title, u.name as owner_name 
        FROM inquiries i 
        JOIN properties p ON i.property_id = p.id 
        JOIN users u ON p.user_id = u.id 
        WHERE i.user_id = ? 
        ORDER BY i.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_inquiries[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ZillowClone</title>
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
        
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .user-menu-btn {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #2a2a33;
        }
        
        .user-menu-btn:hover {
            color: #0061e4;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #0061e4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-weight: bold;
        }
        
        .user-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 200px;
            z-index: 1000;
            display: none;
        }
        
        .user-menu-dropdown.active {
            display: block;
        }
        
        .user-menu-dropdown ul {
            list-style: none;
        }
        
        .user-menu-dropdown ul li {
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .user-menu-dropdown ul li:last-child {
            border-bottom: none;
        }
        
        .user-menu-dropdown ul li a {
            text-decoration: none;
            color: #2a2a33;
            display: block;
        }
        
        .user-menu-dropdown ul li a:hover {
            color: #0061e4;
        }
        
        /* Dashboard Layout */
        .dashboard {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-title {
            font-size: 28px;
            color: #2a2a33;
        }
        
        .dashboard-actions {
            display: flex;
            gap: 10px;
        }
        
        .dashboard-btn {
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
        
        .dashboard-btn:hover {
            background-color: #0052cc;
        }
        
        .dashboard-btn.secondary {
            background-color: white;
            color: #0061e4;
            border: 1px solid #0061e4;
        }
        
        .dashboard-btn.secondary:hover {
            background-color: #f0f6ff;
        }
        
        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .dashboard-card-header {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-card-title {
            font-size: 18px;
            font-weight: 500;
            color: #2a2a33;
        }
        
        .dashboard-card-content {
            padding: 15px;
        }
        
        /* Dashboard Sections */
        .dashboard-section {
            margin-bottom: 40px;
        }
        
        .dashboard-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .dashboard-section-title {
            font-size: 22px;
            color: #2a2a33;
        }
        
        /* Property Cards */
        .property-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .property-image {
            height: 180px;
            background-size: cover;
            background-position: center;
        }
        
        .property-details {
            padding: 15px;
        }
        
        .property-price {
            font-size: 20px;
            font-weight: bold;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .property-address {
            color: #596b82;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .property-features {
            display: flex;
            justify-content: space-between;
            color: #596b82;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .property-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .property-btn {
            padding: 6px 12px;
            background-color: #0061e4;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
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
        
        /* Inquiries Table */
        .inquiries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .inquiries-table th,
        .inquiries-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .inquiries-table th {
            background-color: #f8f9fa;
            font-weight: 500;
            color: #2a2a33;
        }
        
        .inquiries-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .inquiries-table .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .inquiries-table .status.new {
            background-color: #e3f2fd;
            color: #0061e4;
        }
        
        .inquiries-table .status.replied {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom-color: #0061e4;
            color: #0061e4;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #596b82;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dce0e0;
        }
        
        .empty-state-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #2a2a33;
        }
        
        .empty-state-description {
            margin-bottom: 20px;
        }
        
        .empty-state-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #0061e4;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .empty-state-btn:hover {
            background-color: #0052cc;
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
                <?php if($user_type == 'seller' || $user_type == 'agent'): ?>
                    <li><a href="add-property.php">Add Property</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="user-menu">
                <button class="user-menu-btn" id="userMenuBtn">
                    <div class="user-avatar"><?php echo substr($user_name, 0, 1); ?></div>
                    <span><?php echo $user_name; ?></span>
                </button>
                
                <div class="user-menu-dropdown" id="userMenuDropdown">
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="saved-properties.php">Saved Properties</a></li>
                        <?php if($user_type == 'seller' || $user_type == 'agent'): ?>
                            <li><a href="my-listings.php">My Listings</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Dashboard -->
    <div class="dashboard">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Welcome, <?php echo $user_name; ?></h1>
            
            <div class="dashboard-actions">
                <?php if($user_type == 'seller' || $user_type == 'agent'): ?>
                    <a href="add-property.php" class="dashboard-btn">Add New Property</a>
                <?php endif; ?>
                <a href="search.php" class="dashboard-btn secondary">Search Properties</a>
            </div>
        </div>
        
        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">Saved Properties</h3>
                </div>
                <div class="dashboard-card-content">
                    <h2><?php echo count($saved_properties); ?></h2>
                </div>
            </div>
            
            <?php if($user_type == 'seller' || $user_type == 'agent'): ?>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">My Listings</h3>
                    </div>
                    <div class="dashboard-card-content">
                        <h2><?php echo count($user_listings); ?></h2>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Inquiries</h3>
                    </div>
                    <div class="dashboard-card-content">
                        <h2><?php echo count($inquiries); ?></h2>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if($user_type == 'buyer'): ?>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">My Inquiries</h3>
                    </div>
                    <div class="dashboard-card-content">
                        <h2><?php echo count($user_inquiries); ?></h2>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Saved Properties Section -->
        <div class="dashboard-section">
            <div class="dashboard-section-header">
                <h2 class="dashboard-section-title">Saved Properties</h2>
                <a href="saved-properties.php" class="dashboard-btn secondary">View All</a>
            </div>
            
            <?php if(count($saved_properties) > 0): ?>
                <div class="dashboard-grid">
                    <?php foreach(array_slice($saved_properties, 0, 3) as $property): ?>
                        <div class="property-card">
                            <div class="property-image" style="background-image: url('<?php echo $property['image_url']; ?>');"></div>
                            <div class="property-details">
                                <div class="property-price">$<?php echo number_format($property['price']); ?></div>
                                <div class="property-address"><?php echo $property['address']; ?></div>
                                <div class="property-features">
                                    <span><?php echo $property['bedrooms']; ?> bds</span>
                                    <span><?php echo $property['bathrooms']; ?> ba</span>
                                    <span><?php echo number_format($property['square_feet']); ?> sqft</span>
                                </div>
                                <div class="property-actions">
                                    <a href="property-details.php?id=<?php echo $property['id']; ?>" class="property-btn">View Details</a>
                                    <button class="property-btn secondary" onclick="removeSavedProperty(<?php echo $property['id']; ?>)">Remove</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">♡</div>
                    <h3 class="empty-state-title">No saved properties yet</h3>
                    <p class="empty-state-description">Start browsing and save properties you're interested in.</p>
                    <a href="search.php" class="empty-state-btn">Browse Properties</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if($user_type == 'seller' || $user_type == 'agent'): ?>
            <!-- My Listings Section -->
            <div class="dashboard-section">
                <div class="dashboard-section-header">
                    <h2 class="dashboard-section-title">My Listings</h2>
                    <a href="my-listings.php" class="dashboard-btn secondary">View All</a>
                </div>
                
                <?php if(count($user_listings) > 0): ?>
                    <div class="dashboard-grid">
                        <?php foreach(array_slice($user_listings, 0, 3) as $listing): ?>
                            <div class="property-card">
                                <div class="property-image" style="background-image: url('<?php echo $listing['image_url']; ?>');"></div>
                                <div class="property-details">
                                    <div class="property-price">$<?php echo number_format($listing['price']); ?></div>
                                    <div class="property-address"><?php echo $listing['address']; ?></div>
                                    <div class="property-features">
                                        <span><?php echo $listing['bedrooms']; ?> bds</span>
                                        <span><?php echo $listing['bathrooms']; ?> ba</span>
                                        <span><?php echo number_format($listing['square_feet']); ?> sqft</span>
                                    </div>
                                    <div class="property-actions">
                                        <a href="edit-property.php?id=<?php echo $listing['id']; ?>" class="property-btn">Edit</a>
                                        <button class="property-btn secondary" onclick="deleteProperty(<?php echo $listing['id']; ?>)">Delete</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🏠</div>
                        <h3 class="empty-state-title">No listings yet</h3>
                        <p class="empty-state-description">Start adding your properties to showcase them to potential buyers.</p>
                        <a href="add-property.php" class="empty-state-btn">Add Property</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Inquiries Section -->
            <div class="dashboard-section">
                <div class="dashboard-section-header">
                    <h2 class="dashboard-section-title">Recent Inquiries</h2>
                </div>
                
                <?php if(count($inquiries) > 0): ?>
                    <div class="tabs">
                        <div class="tab active" data-tab="all">All</div>
                        <div class="tab" data-tab="new">New</div>
                        <div class="tab" data-tab="replied">Replied</div>
                    </div>
                    
                    <div class="tab-content active" id="all-tab">
                        <table class="inquiries-table">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>From</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($inquiries, 0, 5) as $inquiry): ?>
                                    <tr>
                                        <td><?php echo $inquiry['property_title']; ?></td>
                                        <td><?php echo $inquiry['inquirer_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                        <td>
                                            <span class="status <?php echo $inquiry['status']; ?>">
                                                <?php echo ucfirst($inquiry['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view-inquiry.php?id=<?php echo $inquiry['id']; ?>" class="property-btn">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-content" id="new-tab">
                        <table class="inquiries-table">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>From</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $new_inquiries = array_filter($inquiries, function($inquiry) {
                                    return $inquiry['status'] == 'new';
                                });
                                
                                foreach(array_slice($new_inquiries, 0, 5) as $inquiry): 
                                ?>
                                    <tr>
                                        <td><?php echo $inquiry['property_title']; ?></td>
                                        <td><?php echo $inquiry['inquirer_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                        <td>
                                            <span class="status new">New</span>
                                        </td>
                                        <td>
                                            <a href="view-inquiry.php?id=<?php echo $inquiry['id']; ?>" class="property-btn">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="tab-content" id="replied-tab">
                        <table class="inquiries-table">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>From</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $replied_inquiries = array_filter($inquiries, function($inquiry) {
                                    return $inquiry['status'] == 'replied';
                                });
                                
                                foreach(array_slice($replied_inquiries, 0, 5) as $inquiry): 
                                ?>
                                    <tr>
                                        <td><?php echo $inquiry['property_title']; ?></td>
                                        <td><?php echo $inquiry['inquirer_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                        <td>
                                            <span class="status replied">Replied</span>
                                        </td>
                                        <td>
                                            <a href="view-inquiry.php?id=<?php echo $inquiry['id']; ?>" class="property-btn">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📩</div>
                        <h3 class="empty-state-title">No inquiries yet</h3>
                        <p class="empty-state-description">When buyers inquire about your properties, you'll see them here.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if($user_type == 'buyer'): ?>
            <!-- My Inquiries Section -->
            <div class="dashboard-section">
                <div class="dashboard-section-header">
                    <h2 class="dashboard-section-title">My Inquiries</h2>
                </div>
                
                <?php if(count($user_inquiries) > 0): ?>
                    <table class="inquiries-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Owner</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($user_inquiries as $inquiry): ?>
                                <tr>
                                    <td><?php echo $inquiry['property_title']; ?></td>
                                    <td><?php echo $inquiry['owner_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                    <td>
                                        <span class="status <?php echo $inquiry['status']; ?>">
                                            <?php echo ucfirst($inquiry['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view-inquiry.php?id=<?php echo $inquiry['id']; ?>" class="property-btn">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📩</div>
                        <h3 class="empty-state-title">No inquiries yet</h3>
                        <p class="empty-state-description">When you inquire about properties, you'll see them here.</p>
                        <a href="search.php" class="empty-state-btn">Browse Properties</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // User menu dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userMenuDropdown = document.getElementById('userMenuDropdown');
            
            userMenuBtn.addEventListener('click', function() {
                userMenuDropdown.classList.toggle('active');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userMenuBtn.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                    userMenuDropdown.classList.remove('active');
                }
            });
            
            // Tabs functionality
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab contents
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab') + '-tab';
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
        
        // Remove saved property
        function removeSavedProperty(propertyId) {
            if (confirm('Are you sure you want to remove this property from your saved list?')) {
                // Send AJAX request to remove property
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'remove-saved-property.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        // Reload the page to reflect changes
                        window.location.reload();
                    }
                };
                xhr.send('property_id=' + propertyId);
            }
        }
        
        // Delete property
        function deleteProperty(propertyId) {
            if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
                // Send AJAX request to delete property
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete-property.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        // Reload the page to reflect changes
                        window.location.reload();
                    }
                };
                xhr.send('property_id=' + propertyId);
            }
        }
    </script>
</body>
</html>
