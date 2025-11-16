<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>School Result - Search</title>
    <link rel="icon" href="logo.png" type="image/png">

<!-- for Apple devices -->
<link rel="apple-touch-icon" href="logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a56db;
            --primary-dark: #1e429f;
            --secondary: #0ea5e9;
            --accent: #8b5cf6;
            --success: #10b981;
            --dark: #0f172a;
            --light: #f8fafc;
            --gradient: linear-gradient(135deg, #1a56db 0%, #0ea5e9 50%, #8b5cf6 100%);
            --glow: 0 10px 30px rgba(26, 86, 219, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: var(--light);
            position: relative;
            overflow-x: hidden;
            perspective: 1000px;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(26, 86, 219, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .school-header {
            text-align: center;
            padding: 2rem 0;
            background: linear-gradient(135deg, rgba(26, 86, 219, 0.9) 0%, rgba(14, 165, 233, 0.9) 100%);
            margin: -1rem -1rem 2rem -1rem;
            border-radius: 0 0 30px 30px;
            box-shadow: var(--glow);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
        }

        .school-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .school-logo-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            transform-style: preserve-3d;
            animation: float3d 6s ease-in-out infinite;
        }

        .school-logo {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            transform: translateZ(20px);
            transition: transform 0.5s ease;
        }

        .school-logo:hover {
            transform: translateZ(30px) rotateY(180deg);
        }

        .school-logo i {
            font-size: 3rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .school-logo img {
            width: 70%;
            height: auto;
            border-radius: 50%;
            transition: transform 0.5s ease;
        }

        .school-logo:hover img {
            transform: scale(1.1) rotateY(180deg);
        }

        .school-name {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            transform-style: preserve-3d;
            animation: textGlow 3s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            0% {
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3), 0 0 10px rgba(14, 165, 233, 0.5);
            }
            100% {
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3), 0 0 20px rgba(14, 165, 233, 0.8), 0 0 30px rgba(139, 92, 246, 0.6);
            }
        }

        .school-tagline {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .search-container {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 
                0 20px 40px rgba(0,0,0,0.3),
                inset 0 1px 0 rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            transform: translateY(0) translateZ(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            transform-style: preserve-3d;
        }

        .search-container:hover {
            transform: translateY(-5px) translateZ(10px);
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.4),
                inset 0 1px 0 rgba(255,255,255,0.1);
        }

        .search-title {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            transform-style: preserve-3d;
        }

        .search-title h1 {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #0ea5e9 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            transform: translateZ(20px);
        }

        .search-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%) translateZ(10px);
            width: 100px;
            height: 3px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .form-label {
            font-weight: 600;
            color: #0ea5e9;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transform: translateZ(10px);
        }

        .form-control, .form-select {
            background: rgba(15, 23, 42, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--light);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            font-size: 1rem;
            transform: translateZ(0);
            transform-style: preserve-3d;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: #0ea5e9;
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
            color: var(--light);
            transform: translateY(-2px) translateZ(10px);
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(26, 86, 219, 0.4);
            transform: translateZ(0);
            transform-style: preserve-3d;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px) translateZ(10px);
            box-shadow: 0 8px 25px rgba(26, 86, 219, 0.6);
        }

        .info-note {
            background: rgba(14, 165, 233, 0.1);
            border-left: 4px solid #0ea5e9;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 2rem;
            font-size: 0.95rem;
            transform: translateZ(5px);
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            background: rgba(14, 165, 233, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            transform-style: preserve-3d;
        }

        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg) translateZ(0); }
            50% { transform: translateY(-20px) rotate(180deg) translateZ(20px); }
        }

        @keyframes float3d {
            0%, 100% { transform: translateY(0px) rotateX(0deg) rotateY(0deg); }
            33% { transform: translateY(-10px) rotateX(5deg) rotateY(5deg); }
            66% { transform: translateY(5px) rotateX(-5deg) rotateY(-5deg); }
        }

        .feature-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: rgba(30, 41, 59, 0.8);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: transform 0.3s ease;
            transform-style: preserve-3d;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, transparent 50%);
            z-index: -1;
            transform: translateZ(-1px);
        }

        .feature-card:hover {
            transform: translateY(-5px) rotateX(5deg) rotateY(5deg);
        }

        .feature-card i {
            font-size: 2.5rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            display: inline-block;
            transform: translateZ(20px);
        }

        .developer-footer {
            text-align: center;
            margin-top: 3rem;
            padding: 1.5rem;
            background: rgba(30, 41, 59, 0.8);
            border-radius: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            transform-style: preserve-3d;
            position: relative;
        }

        .developer-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(26, 86, 219, 0.1) 0%, transparent 50%);
            z-index: -1;
            transform: translateZ(-1px);
        }

        .developer-name {
            font-size: 1.2rem;
            font-weight: 600;
            background: linear-gradient(135deg, #0ea5e9 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            transform: translateZ(10px);
        }

        .developer-title {
            font-size: 0.9rem;
            opacity: 0.8;
            transform: translateZ(5px);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @media (max-width: 768px) {
            .school-name {
                font-size: 2rem;
            }
            
            .search-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .feature-cards {
                grid-template-columns: 1fr;
            }
            
            .school-logo-container {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body class="p-4">
    <div class="floating-elements">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>

    <div class="container" style="max-width: 1200px;">
        <!-- School Header -->
        <div class="school-header">
            <div class="school-logo-container">
                <div class="school-logo">
                    <img src="logo.png" alt="School Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <i class="fas fa-graduation-cap" style="display:none;"></i>
                </div>
            </div>
            <h1 href="https://rfnhsc.com" class="school-name">Rajakhali Faizun Nessa High School & College</h1>
            <p class="school-tagline">Rajakhali, Pekua, Cox's Bazar</p>
            
        </div>

        <!-- Search Form -->
        


                        
        <div class="search-container">
            <div class="search-title">
               <strong style="color: white;">Student Result Search</strong>
                <p class="text-muted">Enter your details to view your results</p>
            </div>

            <form action="search.php" method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-graduation-cap"></i>Class
                    </label>
                    <select name="class" class="form-select" required>
                        <option value="">Select Class</option>
                        <option value="6">Class 6</option>
                        <option value="7">Class 7</option>
                        <option value="8">Class 8</option>
                        <option value="9">Class 9</option>
                        <option value="10">Class 10</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-users"></i>Section
                    </label>
                    <select name="section" class="form-select" required>
                        <option value="">Select Section</option>
                        <option value="A">Section A</option>
                        <option value="B">Section B</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-object-group"></i>Group
                    </label>
                    <select name="group" class="form-select">
                        <option value="">Select Group</option>
                        <option value="Science">Science</option>
                        <option value="Arts">Arts</option>
                        <option value="Commerce">Commerce</option>
                    </select>
                    <small class="text-muted">For classes 9-10 only</small>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">
                        <i class="fas fa-id-card"></i>Roll Number
                    </label>
                    <input name="roll" class="form-control" placeholder="Enter Roll Number" required>
                </div>
                 
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg pulse">
                        <i class="fas fa-search me-2"></i>Search Results
                    </button>
                </div>
            </form>

            <div class="info-note">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Section is required for all classes. Group selection is mandatory for classes 9 and 10 (Science, Arts, or Commerce).
            </div>
        </div>
         <a href="https://rfnhsc.com" class="home-button">Go to Home</a>

<style>
.home-button {
    text-decoration: none;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border-radius: 5px;
    display: inline-block;
    font-weight: bold;
}
.home-button:hover {
    background-color: #45a049;
}
</style>

        <!-- Feature Cards -->
        <div class="feature-cards">
            <div class="feature-card">
                <i class="fas fa-award"></i>
                <h5>Accurate Results</h5>
            </div>
            <div class="feature-card">
                <i class="fas fa-print"></i>
                <h5>Printable Marksheet</h5>
            </div>
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h5>Secure System</h5>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h5>24/7 Available</h5>
            </div>
        </div>

        <!-- Developer Footer -->
        <div class="developer-footer">
            <div href="salman.rfnhsc.com"class="school-name">Sayed Mahbub Salman</div>
            <div class="developer-title">Designer & Developer</div>
        </div>
    </div>

    <script>
        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control, .form-select');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px) translateZ(10px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0) translateZ(0)';
                });
            });

            // Add floating animation to feature cards
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.2}s`;
                card.style.animation = 'fadeInUp 0.6s ease-out forwards';
            });

            // Handle logo fallback
            const logoImg = document.querySelector('.school-logo img');
            const logoIcon = document.querySelector('.school-logo i');
            
            if (logoImg && logoImg.complete && logoImg.naturalHeight === 0) {
                logoImg.style.display = 'none';
                logoIcon.style.display = 'block';
            }
        });

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px) translateZ(0);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) translateZ(0);
                }
            }
            
            .feature-card {
                opacity: 0;
            }
        `;
        document.head.appendChild(style);

        // Mouse move parallax effect
        document.addEventListener('mousemove', function(e) {
            const floatingElements = document.querySelectorAll('.floating-element');
            const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            
            floatingElements.forEach((element, index) => {
                const depth = (index + 1) * 10;
                element.style.transform = `translateY(${yAxis}px) translateX(${xAxis}px) translateZ(${depth}px)`;
            });
        });
    </script>
</body>
</html>