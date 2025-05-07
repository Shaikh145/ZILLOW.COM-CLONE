<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZillowClone - Real Estate Marketplace</title>
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
        
        .auth-links {
            display: flex;
            align-items: center;
        }
        
        .auth-links a {
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
        }
        
        .auth-links .sign-in {
            color: #2a2a33;
        }
        
        .auth-links .sign-up {
            background-color: #0061e4;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .auth-links .sign-up:hover {
            background-color: #0052cc;
        }
        
        /* Hero Section */
        .hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }
        
        .search-container {
            width: 100%;
            max-width: 700px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            font-size: 16px;
        }
        
        .search-input:focus {
            outline: none;
        }
        
        .search-btn {
            background-color: #0061e4;
            color: white;
            border: none;
            padding: 0 25px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s;
        }
        
        .search-btn:hover {
            background-color: #0052cc;
        }
        
        /* Featured Properties Section */
        .section-title {
            text-align: center;
            margin: 40px 0 30px;
            font-size: 32px;
            color: #2a2a33;
        }
        
        .properties-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
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
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        
        .property-details {
            padding: 15px;
        }
        
        .property-price {
            font-size: 22px;
            font-weight: bold;
            color: #2a2a33;
            margin-bottom: 5px;
        }
        
        .property-address {
            color: #596b82;
            margin-bottom: 10px;
        }
        
        .property-features {
            display: flex;
            justify-content: space-between;
            color: #596b82;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .property-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #0061e4;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* BuyAbility Section */
        .buyability-section {
            background-color: #f0f6ff;
            padding: 50px 20px;
            margin: 40px 0;
        }
        
        .buyability-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .buyability-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: #2a2a33;
        }
        
        .buyability-subtitle {
            color: #596b82;
            margin-bottom: 30px;
        }
        
        .buyability-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        /* Market Trends Section */
        .market-trends {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .trends-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .trend-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .trend-title {
            font-size: 18px;
            margin-bottom: 10px;
            color: #2a2a33;
        }
        
        .trend-value {
            font-size: 24px;
            font-weight: bold;
            color: #0061e4;
            margin-bottom: 5px;
        }
        
        .trend-description {
            color: #596b82;
            font-size: 14px;
        }
        
        /* Footer */
        footer {
            background-color: #2a2a33;
            color: white;
            padding: 50px 20px 20px;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 30px;
        }
        
        .footer-column h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: #b8c2cc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-column ul li a:hover {
            color: white;
        }
        
        .footer-bottom {
            max-width: 1200px;
            margin: 40px auto 0;
            padding-top: 20px;
            border-top: 1px solid #3d3d47;
            text-align: center;
            color: #b8c2cc;
            font-size: 14px;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }
            
            .nav-links {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 28px;
            }
            
            .search-container {
                flex-direction: column;
            }
            
            .search-btn {
                width: 100%;
                padding: 12px;
            }
        }
        
        /* Carousel Controls */
        .carousel-container {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .carousel-controls {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
            z-index: 10;
        }
        
        .carousel-control {
            background-color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }
        
        .carousel-control:hover {
            background-color: #f0f0f0;
        }
        
        .relative {
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <div class="nav-links">
                <li><a href="#">Buy</a></li>
                <li><a href="#">Rent</a></li>
                <li><a href="#">Sell</a></li>
                <li><a href="#">Home Loans</a></li>
                <li><a href="#">Find an Agent</a></li>
            </div>
            
            <a href="index.php" class="logo">
                <span class="logo-icon">Z</span>illowClone
            </a>
            
            <div class="auth-links">
                <?php if($loggedIn): ?>
                    <a href="dashboard.php" class="sign-in">Dashboard</a>
                    <a href="logout.php" class="sign-in">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="sign-in">Sign In</a>
                    <a href="signup.php" class="sign-up">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <h1>Agents. Tours. Loans. Homes.</h1>
        <div class="search-container">
            <input type="text" class="search-input" placeholder="Enter an address, neighborhood, city, or ZIP code">
            <button class="search-btn" onclick="location.href='search.php'">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </section>
    
    <!-- Featured Properties Section -->
    <h2 class="section-title">Featured Properties</h2>
    <div class="carousel-container">
        <div class="carousel-controls">
            <div class="carousel-control prev">❮</div>
            <div class="carousel-control next">❯</div>
        </div>
        <div class="properties-container">
            <div class="properties-grid">
                <!-- Property Card 1 -->
                <div class="property-card">
                    <div class="property-image" style="background-image: url('https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');"></div>
                    <div class="property-details">
                        <div class="property-price">$750,000</div>
                        <div class="property-address">123 Main St, Los Angeles, CA 90001</div>
                        <div class="property-features">
                            <span>4 bds</span>
                            <span>3 ba</span>
                            <span>2,200 sqft</span>
                        </div>
                    </div>
                </div>
                
                <!-- Property Card 2 -->
                <div class="property-card">
                    <div class="property-image" style="background-image: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');"></div>
                    <div class="property-details">
                        <div class="property-price">$525,000</div>
                        <div class="property-address">456 Oak Ave, San Francisco, CA 94110</div>
                        <div class="property-features">
                            <span>3 bds</span>
                            <span>2 ba</span>
                            <span>1,800 sqft</span>
                        </div>
                    </div>
                </div>
                
                <!-- Property Card 3 -->
                <div class="property-card">
                    <div class="property-image" style="background-image: url('https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');"></div>
                    <div class="property-details">
                        <div class="property-price">$899,000</div>
                        <div class="property-address">789 Pine St, Seattle, WA 98101</div>
                        <div class="property-features">
                            <span>5 bds</span>
                            <span>4 ba</span>
                            <span>3,100 sqft</span>
                        </div>
                    </div>
                </div>
                
                <!-- Property Card 4 -->
                <div class="property-card">
                    <div class="property-image" style="background-image: url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');"></div>
                    <div class="property-details">
                        <div class="property-price">$450,000</div>
                        <div class="property-address">101 Elm St, Austin, TX 78701</div>
                        <div class="property-features">
                            <span>3 bds</span>
                            <span>2 ba</span>
                            <span>1,950 sqft</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- BuyAbility Section -->
    <section class="buyability-section">
        <div class="buyability-container">
            <h2 class="buyability-title">Find homes you can afford with BuyAbility™</h2>
            <p class="buyability-subtitle">Answer a few questions. We'll highlight homes you're likely to qualify for.</p>
            
            <div class="buyability-grid">
                <!-- BuyAbility Property 1 -->
                <div class="property-card relative">
                    <div class="property-badge">Within BuyAbility</div>
                    <div class="property-image" style="background-image: url('https://images.unsplash.com/photo-1568605114967-8130f3a36994?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');"></div>
                    <div class="property-details">
                        <div class="property-price">$320,000</div>
                        <div class="property-address">222 Cedar Ln, Denver, CO 80202</div>
                        <div class="property-features">
                            <span>2 bds</span>
                            <span>2 ba</span>
                            <span>1,400 sqft</span>
                        </div>
                    </div>
                </div>
                
                <!-- BuyAbility Property 2 -->
                <div class="property-card relative">
                    <div class="property-badge">Within BuyAbility</div>
                    <div class="property-image" style="background-image: url('https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');"></div>
                    <div class="property-details">
                        <div class="property-price">$275,000</div>
                        <div class="property-address">333 Maple Dr, Portland, OR 97201</div>
                        <div class="property-features">
                            <span>3 bds</span>
                            <span>1 ba</span>
                            <span>1,250 sqft</span>
                        </div>
                    </div>
                </div>
                
                <!-- BuyAbility Property 3 -->
                <div class="property-card relative">
                    <div class="property-badge">Within BuyAbility</div>
                    <div class="property-image" style="background-image: url('https://images.unsplash.com/photo-1592595896551-12b371d546d5?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1500&q=80');"></div>
                    <div class="property-details">
                        <div class="property-price">$299,000</div>
                        <div class="property-address">444 Birch St, Nashville, TN 37203</div>
                        <div class="property-features">
                            <span>2 bds</span>
                            <span>2 ba</span>
                            <span>1,600 sqft</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Market Trends Section -->
    <section class="market-trends">
        <h2 class="section-title">Market Trends</h2>
        <div class="trends-grid">
            <!-- Trend Card 1 -->
            <div class="trend-card">
                <h3 class="trend-title">Median Home Price</h3>
                <div class="trend-value">$375,000</div>
                <p class="trend-description">Up 8.2% from last year</p>
            </div>
            
            <!-- Trend Card 2 -->
            <div class="trend-card">
                <h3 class="trend-title">Average Days on Market</h3>
                <div class="trend-value">21</div>
                <p class="trend-description">Down 15% from last year</p>
            </div>
            
            <!-- Trend Card 3 -->
            <div class="trend-card">
                <h3 class="trend-title">Mortgage Rate</h3>
                <div class="trend-value">3.2%</div>
                <p class="trend-description">30-year fixed, up 0.3% from last month</p>
            </div>
            
            <!-- Trend Card 4 -->
            <div class="trend-card">
                <h3 class="trend-title">New Listings</h3>
                <div class="trend-value">1,245</div>
                <p class="trend-description">Up 5.7% from last month</p>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <!-- Footer Column 1 -->
            <div class="footer-column">
                <h3>Real Estate</h3>
                <ul>
                    <li><a href="#">Browse Homes</a></li>
                    <li><a href="#">Buy</a></li>
                    <li><a href="#">Sell</a></li>
                    <li><a href="#">Rent</a></li>
                    <li><a href="#">Home Loans</a></li>
                </ul>
            </div>
            
            <!-- Footer Column 2 -->
            <div class="footer-column">
                <h3>Resources</h3>
                <ul>
                    <li><a href="#">Buyers Guide</a></li>
                    <li><a href="#">Sellers Guide</a></li>
                    <li><a href="#">Rental Guide</a></li>
                    <li><a href="#">Housing Market</a></li>
                    <li><a href="#">News & Insights</a></li>
                </ul>
            </div>
            
            <!-- Footer Column 3 -->
            <div class="footer-column">
                <h3>About Us</h3>
                <ul>
                    <li><a href="#">Company</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Investors</a></li>
                    <li><a href="#">Advertising</a></li>
                </ul>
            </div>
            
            <!-- Footer Column 4 -->
            <div class="footer-column">
                <h3>Help</h3>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Community</a></li>
                    <li><a href="#">Fair Housing Guide</a></li>
                    <li><a href="#">Terms of Use</a></li>
                    <li><a href="#">Privacy Portal</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2023 ZillowClone. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // JavaScript for property carousel
        document.addEventListener('DOMContentLoaded', function() {
            const prevBtn = document.querySelector('.carousel-control.prev');
            const nextBtn = document.querySelector('.carousel-control.next');
            const propertiesGrid = document.querySelector('.properties-grid');
            
            let scrollAmount = 300;
            
            prevBtn.addEventListener('click', function() {
                propertiesGrid.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            });
            
            nextBtn.addEventListener('click', function() {
                propertiesGrid.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            });
        });
        
        // Redirect function
        function redirectTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
