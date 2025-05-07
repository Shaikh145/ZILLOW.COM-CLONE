<?php
session_start();
include 'db.php';

// Initialize search parameters
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$min_price = isset($_GET['min_price']) && !empty($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && !empty($_GET['max_price']) ? (int)$_GET['max_price'] : 10000000;
$min_beds = isset($_GET['min_beds']) ? (int)$_GET['min_beds'] : 0;
$min_baths = isset($_GET['min_baths']) ? (int)$_GET['min_baths'] : 0;
$property_type = isset($_GET['property_type']) ? $_GET['property_type'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build SQL query
$sql = "SELECT * FROM properties WHERE status = 'active'";
$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " AND (title LIKE ? OR address LIKE ? OR city LIKE ? OR state LIKE ? OR zip LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sssss";
}

if ($min_price > 0) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
    $types .= "i";
}

if ($max_price < 10000000) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
    $types .= "i";
}

if ($min_beds > 0) {
    $sql .= " AND bedrooms >= ?";
    $params[] = $min_beds;
    $types .= "i";
}

if ($min_baths > 0) {
    $sql .= " AND bathrooms >= ?";
    $params[] = $min_baths;
    $types .= "i";
}

if (!empty($property_type)) {
    $sql .= " AND property_type = ?";
    $params[] = $property_type;
    $types .= "s";
}

// Add sorting
switch ($sort) {
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}

// Execute query
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

// Get property types for filter
$property_types = [];
$stmt = $conn->query("SELECT DISTINCT property_type FROM properties ORDER BY property_type");
while ($row = $stmt->fetch_assoc()) {
    $property_types[] = $row['property_type'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Properties - ZillowClone</title>
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
        
        /* Search Container */
        .search-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Search Header */
        .search-header {
            margin-bottom: 20px;
        }
        
        .search-title {
            font-size: 28px;
            color: #2a2a33;
            margin-bottom: 10px;
        }
        
        .search-form {
            display: flex;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #dce0e0;
            border-radius: 4px 0 0 4px;
            font-size: 16px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #0061e4;
        }
        
        .search-btn {
            padding: 0 20px;
            background-color: #0061e4;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-btn:hover {
            background-color: #0052cc;
        }
        
        /* Search Layout */
        .search-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }
        
        /* Filters */
        .filters {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: fit-content;
        }
        
        .filters-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2a2a33;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .filter-group {
            margin-bottom: 20px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #2a2a33;
        }
        
        .filter-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .filter-input:focus {
            outline: none;
            border-color: #0061e4;
        }
        
        .filter-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            transition: border-color 0.3s;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #0061e4;
        }
        
        .price-range {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .price-range .filter-input {
            flex: 1;
        }
        
        .filter-btn {
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
            margin-top: 10px;
        }
        
        .filter-btn:hover {
            background-color: #0052cc;
        }
        
        .filter-btn.secondary {
            background-color: white;
            color: #0061e4;
            border: 1px solid #0061e4;
        }
        
        .filter-btn.secondary:hover {
            background-color: #f0f6ff;
        }
        
        /* Results */
        .results {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .results-count {
            font-size: 16px;
            color: #596b82;
        }
        
        .sort-select {
            padding: 8px 12px;
            border: 1px solid #dce0e0;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }
        
        .sort-select:focus {
            outline: none;
            border-color: #0061e4;
        }
        
        /* Property Cards */
        .property-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .property-image {
            width: 300px;
            height: 200px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }
        
        .property-details {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .property-price {
            font-size: 24px;
            font-weight: bold;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .property-title {
            font-size: 18px;
            font-weight: 500;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .property-address {
            color: #596b82;
            margin-bottom: 15px;
        }
        
        .property-features {
            display: flex;
            gap: 15px;
            color: #596b82;
            margin-bottom: 15px;
        }
        
        .property-feature {
            display: flex;
            align-items: center;
        }
        
        .property-feature-value {
            font-weight: 500;
            margin-right: 5px;
        }
        
        .property-description {
            color: #596b82;
            margin-bottom: 15px;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .property-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }
        
        .property-date {
            color: #596b82;
            font-size: 14px;
        }
        
        .property-type {
            display: inline-block;
            padding: 4px 8px;
            background-color: #f0f6ff;
            color: #0061e4;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            color: #596b82;
            margin-bottom: 20px;
        }
        
        .empty-state-btn {
            display: inline-block;
            padding: 10px 20px;
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
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }
        
        .pagination-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 4px;
            background-color: white;
            color: #2a2a33;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid #dce0e0;
        }
        
        .pagination-item:hover {
            background-color: #f0f6ff;
            border-color: #0061e4;
        }
        
        .pagination-item.active {
            background-color: #0061e4;
            color: white;
            border-color: #0061e4;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .search-layout {
                grid-template-columns: 1fr;
            }
            
            .property-card {
                flex-direction: column;
            }
            
            .property-image {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-input {
                border-radius: 4px;
                margin-bottom: 10px;
            }
            
            .search-btn {
                border-radius: 4px;
                width: 100%;
                padding: 12px;
            }
            
            .property-features {
                flex-wrap: wrap;
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
                    <?php if($_SESSION['user_type'] == 'seller' || $_SESSION['user_type'] == 'agent'): ?>
                        <li><a href="add-property.php">Add Property</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="login.php">Sign In</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    
    <!-- Search Container -->
    <div class="search-container">
        <div class="search-header">
            <h1 class="search-title">Search Properties</h1>
            
            <form action="search.php" method="get" class="search-form">
                <input type="text" name="q" class="search-input" placeholder="Enter an address, neighborhood, city, or ZIP code" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>
        
        <div class="search-layout">
            <!-- Filters -->
            <div class="filters">
                <h2 class="filters-title">Filters</h2>
                
                <form action="search.php" method="get" id="filterForm">
                    <!-- Keep search query if exists -->
                    <?php if(!empty($search_query)): ?>
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                    <?php endif; ?>
                    
                    <div class="filter-group">
                        <label for="min_price" class="filter-label">Price Range</label>
                        <div class="price-range">
                            <input type="number" id="min_price" name="min_price" class="filter-input" placeholder="Min" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                            <span>to</span>
                            <input type="number" id="max_price" name="max_price" class="filter-input" placeholder="Max" value="<?php echo $max_price < 10000000 ? $max_price : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="min_beds" class="filter-label">Bedrooms</label>
                        <select id="min_beds" name="min_beds" class="filter-select">
                            <option value="0" <?php echo $min_beds == 0 ? 'selected' : ''; ?>>Any</option>
                            <option value="1" <?php echo $min_beds == 1 ? 'selected' : ''; ?>>1+</option>
                            <option value="2" <?php echo $min_beds == 2 ? 'selected' : ''; ?>>2+</option>
                            <option value="3" <?php echo $min_beds == 3 ? 'selected' : ''; ?>>3+</option>
                            <option value="4" <?php echo $min_beds == 4 ? 'selected' : ''; ?>>4+</option>
                            <option value="5" <?php echo $min_beds == 5 ? 'selected' : ''; ?>>5+</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="min_baths" class="filter-label">Bathrooms</label>
                        <select id="min_baths" name="min_baths" class="filter-select">
                            <option value="0" <?php echo $min_baths == 0 ? 'selected' : ''; ?>>Any</option>
                            <option value="1" <?php echo $min_baths == 1 ? 'selected' : ''; ?>>1+</option>
                            <option value="1.5" <?php echo $min_baths == 1.5 ? 'selected' : ''; ?>>1.5+</option>
                            <option value="2" <?php echo $min_baths == 2 ? 'selected' : ''; ?>>2+</option>
                            <option value="3" <?php echo $min_baths == 3 ? 'selected' : ''; ?>>3+</option>
                            <option value="4" <?php echo $min_baths == 4 ? 'selected' : ''; ?>>4+</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="property_type" class="filter-label">Property Type</label>
                        <select id="property_type" name="property_type" class="filter-select">
                            <option value="" <?php echo empty($property_type) ? 'selected' : ''; ?>>Any</option>
                            <?php foreach($property_types as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo $property_type == $type ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort" class="filter-label">Sort By</label>
                        <select id="sort" name="sort" class="filter-select">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price (High to Low)</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="filter-btn">Apply Filters</button>
                    <button type="button" class="filter-btn secondary" onclick="resetFilters()">Reset Filters</button>
                </form>
            </div>
            
            <!-- Results -->
            <div class="results">
                <div class="results-header">
                    <div class="results-count"><?php echo count($properties); ?> properties found</div>
                </div>
                
                <?php if(count($properties) > 0): ?>
                    <div id="propertiesList">
                        <?php foreach($properties as $property): ?>
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="property-card">
                                <div class="property-image" style="background-image: url('<?php echo $property['image_url']; ?>');"></div>
                                <div class="property-details">
                                    <div class="property-price">$<?php echo number_format($property['price']); ?></div>
                                    <div class="property-title"><?php echo $property['title']; ?></div>
                                    <div class="property-address"><?php echo $property['address'] . ', ' . $property['city'] . ', ' . $property['state'] . ' ' . $property['zip']; ?></div>
                                    
                                    <div class="property-features">
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property['bedrooms']; ?></span> beds
                                        </div>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property['bathrooms']; ?></span> baths
                                        </div>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo number_format($property['square_feet']); ?></span> sqft
                                        </div>
                                    </div>
                                    
                                    <div class="property-description">
                                        <?php echo substr($property['description'], 0, 150) . (strlen($property['description']) > 150 ? '...' : ''); ?>
                                    </div>
                                    
                                    <div class="property-footer">
                                        <div class="property-date">Listed <?php echo date('M d, Y', strtotime($property['created_at'])); ?></div>
                                        <div class="property-type"><?php echo ucfirst($property['property_type']); ?></div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination (simplified for this example) -->
                    <?php if(count($properties) > 10): ?>
                    <div class="pagination">
                        <a href="#" class="pagination-item active">1</a>
                        <a href="#" class="pagination-item">2</a>
                        <a href="#" class="pagination-item">3</a>
                        <a href="#" class="pagination-item">Next</a>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🏠</div>
                        <h3 class="empty-state-title">No properties found</h3>
                        <p class="empty-state-description">Try adjusting your search criteria or browse all properties.</p>
                        <a href="search.php" class="empty-state-btn">Reset Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Reset filters
        function resetFilters() {
            window.location.href = 'search.php';
        }
    </script>
</body>
</html>
