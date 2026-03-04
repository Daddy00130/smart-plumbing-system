<!DOCTYPE html>
<html>
<head>
    <title>Smart Plumbing Service System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
        }

        /* Navigation Bar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 60px;
            position: absolute;
            width: 100%;
            z-index: 2;
        }

        .navbar h2 {
            margin: 0;
        }

        .nav-links a {
            margin-left: 20px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .nav-links a:hover {
            background: white;
            color: #2a5298;
        }

        /* Hero Section */
        .hero {
            position: relative;
            height: 100vh;
            background: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Dark overlay */
        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            padding: 20px;
            max-width: 700px;
        }

        .hero h1 {
            font-size: 50px;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .buttons a {
            text-decoration: none;
            padding: 15px 30px;
            margin: 10px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s;
        }

        .register-btn {
            background: #00c6ff;
            color: white;
        }

        .login-btn {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .register-btn:hover {
            background: #009bd4;
        }

        .login-btn:hover {
            background: white;
            color: #2a5298;
        }

        /* Features Section */
        .features {
            background: white;
            color: #333;
            padding: 60px 20px;
            text-align: center;
        }

        .features h2 {
            margin-bottom: 40px;
        }

        .feature-box {
            display: inline-block;
            width: 250px;
            margin: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: 0.3s;
        }

        .feature-box:hover {
            transform: translateY(-10px);
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: #1e3c72;
            color: white;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }
            .feature-box {
                width: 90%;
            }
        }

    </style>
</head>
<body>

<!-- Navigation -->
<div class="navbar">
    <h2>Smart Plumbing</h2>
    <div class="nav-links">
        <a href="register.php">Register</a>
        <a href="login.php">Login</a>
    </div>
</div>

<!-- Hero Section -->
<div class="hero">
    <div class="hero-content">
        <h1>Professional Plumbing Services You Can Trust</h1>
        <p>
            From leak repairs to full installations, our certified plumbers
            are ready to serve you quickly and reliably.
        </p>

        <div class="buttons">
            <a href="register.php" class="register-btn">Get Started</a>
            <a href="login.php" class="login-btn">Login</a>
        </div>
    </div>
</div>

<!-- Features -->
<div class="features">
    <h2>Why Choose Us?</h2>

    <div class="feature-box">
        <h3>✔ Verified Plumbers</h3>
        <p>Work with experienced and trusted professionals.</p>
    </div>

    <div class="feature-box">
        <h3>✔ Fast Booking</h3>
        <p>Submit your request and get help quickly.</p>
    </div>

    <div class="feature-box">
        <h3>✔ Secure Payments</h3>
        <p>Safe and reliable payment processing.</p>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    © 2026 Smart Plumbing Service System | All Rights Reserved
</div>

</body>
</html>