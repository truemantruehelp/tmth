<?php
// login.php
require_once 'includes/config.php';
$page_title = "Login";

// Check if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']); // Can be email or phone
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        $error = "Please enter both email/phone and password!";
    } else {
        // Try to login with email or phone using the function from functions.php
        if (loginUserWithEmailOrPhone($login, $password)) {
            if (isAdmin()) {
                redirect('admin/index.php');
            } else {
                redirect('dashboard.php');
            }
        } else {
            $error = "Invalid login credentials!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getSiteName(); ?> - Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a3a5f;
            --primary-light: #2c5282;
            --primary-dark: #0f2a4a;
            --secondary: #e53e3e;
            --secondary-light: #fc8181;
            --secondary-dark: #c53030;
            --accent: #f6ad55;
            --accent-light: #fbd38d;
            --light: #f7fafc;
            --dark: #2d3748;
            --text-light: #718096;
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            --gradient-dark: linear-gradient(135deg, #1e3a5f 0%, #0f1e35 100%);
            --gradient-warning: linear-gradient(135deg, var(--warning) 0%, var(--accent) 100%);
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 15px 40px rgba(0, 0, 0, 0.12);
            --shadow-large: 0 25px 60px rgba(0, 0, 0, 0.15);
            --shadow-hover: 0 30px 70px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 10;
        }

        .auth-header {
            background: var(--gradient-primary);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.1)" d="M0,0 L100,0 L100,100 L0,100 Z"></path></svg>');
            background-size: cover;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .brand-logo i {
            font-size: 2.5rem;
            margin-right: 12px;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .brand-name {
            font-weight: 800;
            font-size: 1.8rem;
             background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .auth-title {
            font-weight: 600;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .auth-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        .auth-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            display: block;
            font-size: 0.9rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group .form-control {
            border-radius: 12px;
            padding: 14px 20px 14px 50px;
            border: 2px solid #e9ecef;
            font-size: 1rem;
            transition: all 0.3s ease;
            height: 52px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .input-group .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.1), 0 4px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.1rem;
            z-index: 5;
            transition: all 0.3s ease;
        }

        .input-group .form-control:focus + .input-icon {
            color: var(--secondary);
            transform: translateY(-50%) scale(1.1);
        }

        .btn-auth {
            background: var(--gradient-secondary);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.4s ease;
            color: white;
            width: 100%;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(230, 62, 62, 0.3);
        }

        .btn-auth::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }

        .btn-auth:hover::before {
            left: 100%;
        }

        .btn-auth:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(230, 62, 62, 0.4);
        }

        .auth-links {
            text-align: center;
            margin-top: 25px;
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .auth-links a:hover {
            color: var(--secondary);
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
        }

        .back-home a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-home a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .back-home i {
            margin-right: 8px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 12px 15px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--secondary);
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .auth-container {
                max-width: 100%;
                margin: 0 15px;
            }
            
            .auth-header {
                padding: 25px 20px;
            }
            
            .auth-body {
                padding: 25px 20px;
            }
            
            .brand-name {
                font-size: 1.6rem;
            }
            
            .auth-title {
                font-size: 1.3rem;
            }
        }

        /* Animation for form elements */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-body > * {
            animation: fadeInUp 0.5s ease-out;
        }

        .auth-body > *:nth-child(1) { animation-delay: 0.1s; }
        .auth-body > *:nth-child(2) { animation-delay: 0.2s; }
        .auth-body > *:nth-child(3) { animation-delay: 0.3s; }
        .auth-body > *:nth-child(4) { animation-delay: 0.4s; }
        .auth-body > *:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>

<body>
    <!-- Back to Home Link -->
    <div class="back-home">
        <a href="index.php">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>

    <!-- Main Auth Container -->
    <div class="auth-container">
        <!-- Auth Header -->
        <div class="auth-header">
            <div class="brand-logo">
                <i class="fas fa-hands-helping"></i>
                <div class="brand-name"><?php echo getSiteName(); ?></div>
            </div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to your account to continue</p>
        </div>

        <!-- Auth Body -->
        <div class="auth-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" id="login" name="login"
                        value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                        placeholder="Enter your email or phone number" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter your password" required>
                </div>

                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Remember me
                        </label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-auth">
                    <i class="fas fa-sign-in-alt me-2"></i> Login to Account
                </button>
            </form>

            <div class="auth-links">
                <p>Don't have an account? <a href="register.php" class="fw-bold">Create one here</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function () {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function () {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });
    </script>
</body>
</html>