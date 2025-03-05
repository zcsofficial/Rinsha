<?php
session_start();
include 'config.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $success = "If an account exists with this email, a password reset link has been sent.";
        // Placeholder: Add email sending logic here (e.g., using PHPMailer)
    } else {
        $error = "No account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Village Monitoring System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                        secondary: '#64748b'
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body { font-family: 'Inter', sans-serif; }
        .mobile-menu { display: none; }
        @media (max-width: 768px) {
            .mobile-menu { display: block; }
            .desktop-menu { display: none; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm fixed w-full z-10">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-['Pacifico'] text-primary">VillageSys</span>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="md:hidden text-gray-900 focus:outline-none" onclick="toggleMenu()">
                        <i class="ri-menu-line w-6 h-6"></i>
                    </button>
                    <div class="desktop-menu hidden md:flex items-center space-x-4">
                        <a href="index.php" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Home</a>
                        <a href="login.php" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                            <i class="ri-user-line w-4 h-4"></i>
                            <span>Login</span>
                        </a>
                    </div>
                </div>
            </div>
            <div id="mobile-menu" class="mobile-menu hidden md:hidden bg-white shadow-sm">
                <a href="index.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Home</a>
                <a href="login.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Login</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-20 md:py-24 flex items-center justify-center min-h-screen">
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm w-full max-w-md">
            <h2 class="text-2xl md:text-3xl font-['Pacifico'] text-primary mb-6 text-center">Forgot Password</h2>
            <?php if ($error): ?>
                <p class="text-red-500 text-sm mb-4 text-center"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="text-green-500 text-sm mb-4 text-center"><?php echo $success; ?></p>
            <?php endif; ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>
                <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-button hover:bg-primary/90 text-sm font-medium">
                    Send Reset Link
                </button>
            </form>
            <div class="mt-4 text-center text-sm">
                <a href="login.php" class="text-primary hover:underline">Back to Login</a>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm text-gray-400">© 2025 Village Monitoring System - Gudalur. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>