@php
    use App\Models\Plan;
    use App\Models\RentBotPackage;
    use App\Models\Setting;
    $plans = Plan::active()->ordered()->get();
    $pexPlans = RentBotPackage::active()->ordered()->get();
    $setting = Setting::get();
    $logoUrl = $setting->logo_url ?? null;
    $projectName = $setting->company_name ?? 'AI Trading Bot';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $projectName }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 50%, #16213e 100%);
            --neon-blue: #00d4ff;
            --neon-purple: #8b5cf6;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-gradient);
            color: white;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Ensure proper spacing */
        .container {
            padding-left: 15px;
            padding-right: 15px;
        }

        /* Fix any potential blank spaces */
        .hero-section {
            margin-top: 0;
        }

        /* Custom Navbar */
        .navbar-custom {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .navbar-custom.scrolled {
            background: rgba(0, 0, 0, 0.95);
            box-shadow: 0 4px 20px rgba(0, 212, 255, 0.1);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.5);
            background: transparent;
            padding: 0.25rem 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2.5' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            width: 1.5em;
            height: 1.5em;
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--neon-blue) !important;
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: var(--neon-blue);
            transition: all 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }

        .dropdown-menu {
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
        }

        .dropdown-item {
            color: white !important;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: rgba(0, 212, 255, 0.2);
            color: var(--neon-blue) !important;
        }

        .dropdown-toggle::after {
            margin-left: 0.5rem;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: var(--dark-gradient);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
            padding-top: 80px; /* Account for fixed navbar */
        }

        .hero-container-wrapper {
            position: relative;
            width: 100%;
            height: calc(100vh - 80px);
            display: flex;
            align-items: center;
        }

        .hero-video-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }

        .hero-video-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(12, 12, 12, 0.3) 0%, transparent 50%, transparent 100%);
            z-index: 2;
            pointer-events: none;
        }

        .hero-video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }

        .hero-video-background.active {
            opacity: 1;
        }

        @media (max-width: 992px) {
            .hero-video-container {
                width: 100%;
            }
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.2) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.2) 0%, transparent 50%),
                        radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.15) 0%, transparent 50%);
            animation: float 6s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .hero-content {
            position: relative;
            z-index: 3;
            background: rgba(0, 0, 0, 0.5);
            padding: 4rem 3rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            max-width: 700px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            margin-left: 8%;
        }

        @media (max-width: 992px) {
            .hero-content {
                margin-left: 5%;
                max-width: 85%;
            }
        }

        @media (max-width: 768px) {
            .hero-content {
                padding: 3rem 2rem;
                max-width: 90%;
                margin-left: 5%;
            }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff 0%, var(--neon-blue) 50%, var(--neon-purple) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: glow 2s ease-in-out infinite alternate;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        @keyframes glow {
            from { text-shadow: 0 0 20px rgba(0, 212, 255, 0.5); }
            to { text-shadow: 0 0 30px rgba(0, 212, 255, 0.8), 0 0 40px rgba(139, 92, 246, 0.3); }
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2rem;
            line-height: 1.6;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.7);
        }

        .btn-glow {
            background: var(--primary-gradient);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-glow:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .btn-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-glow:hover::before {
            left: 100%;
        }

        .btn-glow.nav-link::after {
            display: none;
        }

        button.btn-glow {
            cursor: pointer;
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .glass-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 212, 255, 0.2);
            border-color: rgba(0, 212, 255, 0.3);
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--neon-blue), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .glass-card:hover::before {
            opacity: 1;
        }


        /* Package Cards */
        .package-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
            min-height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .package-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 212, 255, 0.3);
            border-color: var(--neon-blue);
        }

        .package-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, var(--neon-blue), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
            animation: rotate 3s linear infinite;
        }

        .package-card:hover::before {
            opacity: 0.1;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .package-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .package-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--neon-blue);
            margin-bottom: 1rem;
        }

        .package-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }

        .package-features li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        .package-features li:last-child {
            border-bottom: none;
        }

        .package-features i {
            color: var(--neon-blue);
            margin-right: 0.5rem;
        }

        /* Stats Section */
        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 212, 255, 0.2);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--neon-blue);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        /* How It Works Section */
        .step-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 212, 255, 0.2);
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
            color: white;
        }

        /* Referral Benefits */
        .referral-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .referral-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(0, 212, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .referral-card:hover::before {
            opacity: 1;
        }

        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 0 2rem;
        }

        .social-icon {
            width: 50px;
            height: 50px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
        }

        .social-icon:hover {
            background: var(--neon-blue);
            transform: translateY(-3px);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .package-card {
                margin-bottom: 2rem;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }

            .hero-content {
                padding: 1.5rem;
            }

            .hero-video-background {
                opacity: 0.3;
            }
        }

        @media (max-width: 480px) {
            .hero-video-background {
                opacity: 0.15;
            }
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Loading Animation */
        .loading {
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* PEX AI Plans Section */
        .pex-plans-section {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(12, 12, 12, 0.85) 0%, rgba(26, 26, 46, 0.8) 50%, rgba(22, 33, 62, 0.85) 100%);
        }

        .pex-plans-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ asset("images/pex_images/pex2.jpeg") }}');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0.7;
            z-index: 0;
            filter: brightness(0.75) contrast(1.2) saturate(1.1);
        }

        .pex-plans-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, 
                        rgba(12, 12, 12, 0.4) 0%, 
                        rgba(26, 26, 46, 0.3) 30%,
                        rgba(22, 33, 62, 0.3) 70%,
                        rgba(12, 12, 12, 0.4) 100%),
                        radial-gradient(circle at 20% 50%, rgba(0, 212, 255, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 50%, rgba(139, 92, 246, 0.08) 0%, transparent 50%);
            z-index: 1;
            pointer-events: none;
        }

        .pex-plans-container {
            position: relative;
            z-index: 2;
        }

        .pex-package-card {
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(0, 212, 255, 0.3);
            border-radius: 30px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            height: 100%;
            min-height: 520px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .pex-package-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple), var(--neon-blue));
            border-radius: 30px;
            opacity: 0;
            z-index: -1;
            transition: opacity 0.4s ease;
        }

        .pex-package-card:hover::before {
            opacity: 0.5;
        }

        .pex-package-card:hover {
            transform: translateY(-20px) scale(1.03);
            box-shadow: 0 30px 60px rgba(0, 212, 255, 0.4);
            border-color: var(--neon-blue);
        }

        .pex-package-card::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
            z-index: 0;
        }

        .pex-package-card:hover::after {
            width: 300px;
            height: 300px;
        }

        .pex-package-content {
            position: relative;
            z-index: 1;
        }

        .pex-package-name {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--neon-blue) 0%, var(--neon-purple) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .pex-package-price {
            font-size: 3rem;
            font-weight: 900;
            color: var(--neon-blue);
            margin-bottom: 0.5rem;
            text-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
            position: relative;
        }

        .pex-package-price::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--neon-blue), transparent);
        }

        .pex-package-validity {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .pex-package-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            text-align: left;
        }

        .pex-package-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .pex-package-features li:hover {
            color: var(--neon-blue);
            padding-left: 10px;
        }

        .pex-package-features li:last-child {
            border-bottom: none;
        }

        .pex-package-features i {
            color: var(--neon-blue);
            margin-right: 0.75rem;
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        .pex-section-title {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, var(--neon-blue) 50%, var(--neon-purple) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            margin-bottom: 1rem;
        }

        .pex-section-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 3rem;
        }

        .pex-robot-badge {
            position: absolute;
            top: -15px;
            right: -15px;
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            box-shadow: 0 5px 20px rgba(0, 212, 255, 0.4);
            z-index: 2;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        @media (max-width: 768px) {
            .pex-section-title {
                font-size: 2rem;
            }
            
            .pex-package-card {
                min-height: 480px;
                margin-bottom: 2rem;
            }
            
            .pex-package-price {
                font-size: 2.5rem;
            }

            .pex-robot-badge {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                top: -10px;
                right: -10px;
            }

            .pex-plans-section::before {
                background-attachment: scroll;
                opacity: 0.65;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home" style="display: flex; align-items: center; gap: 0.5rem;">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" style="height: 40px; width: auto; object-fit: contain;">
                @else
                    <img src="{{ asset('admin-assets/img/nexa-ai-robot.jpg') }}" alt="Nexa AI Robot" style="height: 40px; width: auto; object-fit: contain; border-radius: 8px;">
                @endif
                {{ $projectName }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="plansDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Plans
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="plansDropdown">
                            <li><a class="dropdown-item" href="#packages">Nexa</a></li>
                            <li><a class="dropdown-item" href="#pex-plans">Pex</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#referral">Referral</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn-glow ms-2 border-0 bg-transparent text-white">
                                    Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-glow ms-2" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-container-wrapper">
            <!-- Video Container on Right Side -->
            <div class="hero-video-container">
                <!-- First Video -->
                <video autoplay muted loop playsinline class="hero-video-background active" id="video1">
                    <source src="{{ asset('videos/robot2.mp4') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <!-- Second Video -->
                <video autoplay muted loop playsinline class="hero-video-background" id="video2">
                    <source src="{{ asset('videos/robot1.mp4') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <!-- Text Content on Left Side -->
            <div class="hero-content" data-aos="fade-right" data-aos-duration="1000">
                <h1 class="hero-title">Revolutionary AI Trading System</h1>
                <p class="hero-subtitle">
                    Experience the future of investing with our cutting-edge AI trading bots. 
                    Automated 24/7 trading, intelligent market analysis, and guaranteed returns. 
                    Join over 15,000 successful traders earning passive income daily.
                </p>
                <a href="#packages" class="btn-glow">
                    <i class="bi bi-rocket-takeoff me-2"></i>Get Started Now
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-4 fw-bold mb-3" data-aos="fade-up">How Our AI System Works</h2>
                    <p class="lead" data-aos="fade-up" data-aos-delay="200">Three simple steps to start your automated trading journey</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="step-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="step-number">1</div>
                        <h4>Select Your Trading Plan</h4>
                        <p>Choose from our carefully crafted trading packages ranging from $100 to $50,000. Each plan offers unique benefits, more AI bots, and higher profit potential.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="step-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="step-number">2</div>
                        <h4>AI Bots Begin Trading</h4>
                        <p>Our sophisticated AI trading algorithms instantly start analyzing global markets, executing profitable trades, and managing your portfolio 24/7 without any manual intervention.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="step-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="step-number">3</div>
                        <h4>Earn Daily Profits & Bonuses</h4>
                        <p>Watch your account grow with daily profit distributions, referral commissions, and automatic retrading. Build your network and earn from multiple income streams.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Plans Section -->
    <section id="packages" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-4 fw-bold mb-3" data-aos="fade-up">Nexa AI Trading Plans</h2>
                    <p class="lead" data-aos="fade-up" data-aos-delay="200">Select the perfect trading plan that matches your financial goals and risk tolerance</p>
                </div>
            </div>
            <div class="row g-4">
                @foreach($plans as $index => $plan)
                <div class="col-lg-4 col-md-6">
                    <div class="package-card" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                        <h3 class="package-name">{{ $plan->name }}</h3>
                        <div class="package-price">${{ number_format($plan->investment_amount) }}</div>
                        <ul class="package-features">
                            <li><i class="bi bi-check-circle"></i>{{ $plan->bots_allowed }} BOT{{ $plan->bots_allowed > 1 ? 's' : '' }}</li>
                            <li><i class="bi bi-check-circle"></i>{{ $plan->trades_per_day }} Trades per Day</li>
                            <li><i class="bi bi-check-circle"></i>${{ number_format($plan->joining_fee, 2) }} Joining Fee</li>
                            <li><i class="bi bi-check-circle"></i>${{ number_format($plan->direct_bonus, 2) }} Direct Bonus</li>
                            <li><i class="bi bi-check-circle"></i>{{ $plan->referral_level_1 }}% - {{ $plan->referral_level_2 }}% - {{ $plan->referral_level_3 }}% Referral ROI Share</li>
                        </ul>
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-glow w-100">Choose Plan</a>
                        @else
                            <a href="{{ route('register') }}" class="btn-glow w-100">Choose Plan</a>
                        @endauth
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- PEX AI Plans Section -->
    <section id="pex-plans" class="pex-plans-section py-5">
        <div class="pex-plans-container">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center mb-5">
                        <h2 class="pex-section-title" data-aos="fade-up">PEX Trading Plans</h2>
                        <p class="pex-section-subtitle" data-aos="fade-up" data-aos-delay="200">
                            Rent powerful AI trading bots and unlock unlimited trading potential. 
                            Choose the perfect package that fits your trading strategy and budget.
                        </p>
                    </div>
                </div>
                <div class="row g-4">
                    @forelse($pexPlans as $index => $pexPlan)
                    <div class="col-lg-4 col-md-6">
                        <div class="pex-package-card" data-aos="fade-up" data-aos-delay="{{ ($index + 1) * 100 }}">
                            <div class="pex-robot-badge">
                                <i class="bi bi-robot"></i>
                            </div>
                            <div class="pex-package-content">
                                <h3 class="pex-package-name">PEX-{{ $index + 1 }}</h3>
                                <div class="pex-package-price">${{ number_format($pexPlan->amount, 2) }}</div>
                                <div class="pex-package-validity">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    {{ ucfirst($pexPlan->validity) }}ly Subscription
                                </div>
                                <ul class="pex-package-features">
                                    <li>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>{{ $pexPlan->allowed_bots }} AI Bot{{ $pexPlan->allowed_bots > 1 ? 's' : '' }}</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>{{ $pexPlan->allowed_trades }} Trade{{ $pexPlan->allowed_trades > 1 ? 's' : '' }} Allowed</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>24/7 Automated Trading</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Real-time Market Analysis</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Advanced AI Algorithms</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Full Dashboard Access</span>
                                    </li>
                                </ul>
                                @auth
                                    <a href="{{ route('dashboard') }}" class="btn-glow w-100 mt-3">
                                        <i class="bi bi-rocket-takeoff me-2"></i>Rent Now
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="btn-glow w-100 mt-3">
                                        <i class="bi bi-rocket-takeoff me-2"></i>Get Started
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: rgba(255, 255, 255, 0.3);"></i>
                            <p class="lead mt-3" style="color: rgba(255, 255, 255, 0.6);">No PEX AI plans available at the moment.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- Referral Benefits Section -->
    <section id="referral" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-4 fw-bold mb-3" data-aos="fade-up">Multi-Level Referral System</h2>
                    <p class="lead" data-aos="fade-up" data-aos-delay="200">Build your network and earn commissions from multiple levels of referrals</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="referral-card" data-aos="fade-up" data-aos-delay="300">
                        <i class="bi bi-people-fill" style="font-size: 4rem; color: var(--neon-blue); margin-bottom: 2rem;"></i>
                        <h3 class="mb-4">Build Your Network & Earn Commissions</h3>
                        <p class="lead mb-4">
                            Our revolutionary 3-level referral system allows you to earn from your direct referrals and their referrals. 
                            Higher trading packages unlock better referral percentages and unlimited earning potential.
                        </p>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h5 class="text-primary">Level 1</h5>
                                    <p>Direct referrals earn you the highest percentage</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h5 class="text-primary">Level 2</h5>
                                    <p>Your referrals' referrals earn you a smaller percentage</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h5 class="text-primary">Level 3</h5>
                                    <p>Third-level referrals provide additional income</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-4 fw-bold mb-3" data-aos="fade-up">Trusted by Thousands</h2>
                    <p class="lead" data-aos="fade-up" data-aos-delay="200">Join our growing community of successful AI traders</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="stat-number">15,000+</div>
                        <div class="stat-label">Total Investors</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="stat-number">45,000+</div>
                        <div class="stat-label">Active Bots</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="stat-number">2.5M+</div>
                        <div class="stat-label">Total Trades</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="stat-number">$850K+</div>
                        <div class="stat-label">Daily Payouts</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="glass-card" data-aos="fade-up">
                        <h2 class="display-4 fw-bold mb-4">Ready to Transform Your Financial Future?</h2>
                        <p class="lead mb-4">
                            Join thousands of successful investors who are already earning daily profits with our AI trading system. 
                            Start your automated trading journey today and watch your wealth grow exponentially.
                        </p>
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="#register" class="btn-glow">
                                <i class="bi bi-person-plus me-2"></i>Join Now
                            </a>
                            <a href="#packages" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-eye me-2"></i>View Plans
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="mb-3">
                        <i class="bi bi-robot me-2"></i>AI Trading Bot System
                    </h5>
                    <p class="text-muted">
                        Revolutionizing the trading landscape with cutting-edge AI technology. 
                        Experience automated trading, intelligent market analysis, and guaranteed returns 
                        with our advanced trading algorithms.
                    </p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="#packages" class="text-muted text-decoration-none">Plans</a></li>
                        <li><a href="#pex-plans" class="text-muted text-decoration-none">PEX AI Plans</a></li>
                        <li><a href="#how-it-works" class="text-muted text-decoration-none">How It Works</a></li>
                        <li><a href="#referral" class="text-muted text-decoration-none">Referral</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h6 class="mb-3">Connect With Us</h6>
                    <div class="d-flex">
                        <a href="#" class="social-icon">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi bi-linkedin"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi bi-telegram"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.1);">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; {{ date('Y') }} AI Trading Bot System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">Powered by Advanced AI Technology</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (counter.textContent.includes('+')) {
                        counter.textContent = Math.floor(current).toLocaleString() + '+';
                    } else if (counter.textContent.includes('K')) {
                        counter.textContent = '$' + Math.floor(current).toLocaleString() + 'K+';
                    } else if (counter.textContent.includes('M')) {
                        counter.textContent = Math.floor(current).toLocaleString() + 'M+';
                    }
                }, 16);
            });
        }

        // Trigger counter animation when stats section is visible
        const statsSection = document.querySelector('.stat-card');
        if (statsSection) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        observer.unobserve(entry.target);
                    }
                });
            });
            observer.observe(statsSection);
        }

        // Add loading animation and video rotation
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loading');
            
            // Get all videos
            const heroVideos = document.querySelectorAll('.hero-video-background');
            const rotationInterval = 5000; // 5 seconds per video
            let rotationTimer = null;
            
            // Find which video is currently active (should be the first one)
            let currentVideoIndex = 0;
            heroVideos.forEach(function(video, index) {
                if (video.classList.contains('active')) {
                    currentVideoIndex = index;
                }
            });
            
            // Ensure all videos play and are ready
            heroVideos.forEach(function(heroVideo) {
                if (heroVideo) {
                    // Preload all videos
                    heroVideo.load();
                    
                    heroVideo.play().catch(function(error) {
                        // Video autoplay was prevented, try to play on user interaction
                        console.log('Video autoplay prevented:', error);
                        document.addEventListener('click', function playVideo() {
                            heroVideos.forEach(function(video) {
                                video.play().catch(console.error);
                            });
                            document.removeEventListener('click', playVideo);
                        }, { once: true });
                    });
                }
            });
            
            // Video rotation function
            function rotateVideos() {
                // Remove active class from all videos and pause them
                heroVideos.forEach(function(video) {
                    video.classList.remove('active');
                    video.pause();
                });
                
                // Move to next video
                currentVideoIndex = (currentVideoIndex + 1) % heroVideos.length;
                
                // Add active class to current video and play it
                const currentVideo = heroVideos[currentVideoIndex];
                currentVideo.classList.add('active');
                currentVideo.currentTime = 0; // Reset to start
                currentVideo.play().catch(function(error) {
                    console.log('Video play error:', error);
                });
            }
            
            // Start continuous rotation immediately
            // First video (index 0) is already active and playing
            // After rotationInterval, it will switch to next video and continue rotating
            rotationTimer = setInterval(rotateVideos, rotationInterval);
        });
    </script>
</body>
</html>
