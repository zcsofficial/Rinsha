<?php
session_start();
include 'config.php';

// OpenWeatherMap API Key
$weather_api_key = '033abd5b4e2a503525b7c73e7728a949';

// Gudalur, Tamil Nadu coordinates
$lat = 11.50;
$lon = 76.50;

// Fetch current weather
$current_weather_url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$weather_api_key}&units=metric";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $current_weather_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$current_weather_json = curl_exec($ch);
curl_close($ch);
$current_weather = json_decode($current_weather_json, true);

// Fetch complaints based on user role
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

if ($role === 'admin') {
    $complaints_query = "SELECT * FROM complaints ORDER BY created_at DESC";
} else {
    $complaints_query = "SELECT * FROM complaints WHERE email = (SELECT email FROM users WHERE id = '$user_id') ORDER BY created_at DESC";
}
$complaints_result = mysqli_query($conn, $complaints_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints - Village Monitoring System</title>
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCknv9cAyPuScuJVB_JZhqzPCqcEvWdR2I&callback=initMap" async defer></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body { font-family: 'Inter', sans-serif; }
        #map { height: 100%; width: 100%; }
        .mobile-menu { display: none; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 20; }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 8px; }
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
                    <div class="flex items-center space-x-2 text-sm">
                        <i class="ri-sun-line w-5 h-5 flex items-center justify-center text-yellow-500"></i>
                        <span id="weather-temp" class="font-medium">
                            <?php echo ($current_weather && isset($current_weather['main']['temp'])) ? round($current_weather['main']['temp']) . '°C' : 'N/A'; ?>
                        </span>
                    </div>
                    <button class="md:hidden text-gray-900 focus:outline-none" onclick="toggleMenu()">
                        <i class="ri-menu-line w-6 h-6"></i>
                    </button>
                    <div class="desktop-menu hidden md:flex items-center space-x-4">
                        <a href="index.php#overview" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Overview</a>
                        <a href="index.php#statistics" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Statistics</a>
                        <a href="index.php#activities" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Activities</a>
                        <a href="index.php#contact" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Contact</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard.php" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Dashboard</a>
                        <?php endif; ?>
                        <button onclick="openModal('complaintModal')" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                            <i class="ri-file-warning-line w-4 h-4"></i>
                            <span>Complaint</span>
                        </button>
                        <a href="logout.php" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                            <i class="ri-logout-box-line w-4 h-4"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
            <div id="mobile-menu" class="mobile-menu hidden md:hidden bg-white shadow-sm">
                <a href="index.php#overview" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Overview</a>
                <a href="index.php#statistics" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Statistics</a>
                <a href="index.php#activities" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Activities</a>
                <a href="index.php#contact" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Contact</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="dashboard.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Dashboard</a>
                <?php endif; ?>
                <a href="#" onclick="openModal('complaintModal')" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Complaint</a>
                <a href="logout.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-20 md:py-8">
        <section id="complaints" class="mb-12">
            <div class="relative rounded-lg overflow-hidden h-[400px] md:h-[500px] mb-8">
                <div id="map" class="absolute inset-0"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-primary/90 to-transparent flex items-center">
                    <div class="max-w-lg ml-6 md:ml-12 text-white">
                        <h1 class="text-2xl md:text-4xl font-bold mb-4">Your Complaints - Gudalur</h1>
                        <p class="text-sm md:text-lg mb-6"><?php echo $role === 'admin' ? 'View and manage all complaints filed by users.' : 'View your filed complaints below.'; ?></p>
                    </div>
                </div>
            </div>

            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6"><?php echo $role === 'admin' ? 'All Complaints' : 'My Complaints'; ?></h2>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">ID</th>
                                    <th class="pb-4">Name</th>
                                    <th class="pb-4">Email</th>
                                    <th class="pb-4">Phone</th>
                                    <th class="pb-4">Complaint</th>
                                    <th class="pb-4">File</th>
                                    <th class="pb-4">Date</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php if (mysqli_num_rows($complaints_result) > 0): ?>
                                    <?php while ($complaint = mysqli_fetch_assoc($complaints_result)): ?>
                                        <tr class="border-t">
                                            <td class="py-4"><?php echo $complaint['id']; ?></td>
                                            <td class="py-4"><?php echo $complaint['name']; ?></td>
                                            <td class="py-4"><?php echo $complaint['email']; ?></td>
                                            <td class="py-4"><?php echo $complaint['phone_number']; ?></td>
                                            <td class="py-4"><?php echo substr($complaint['complaint'], 0, 50) . (strlen($complaint['complaint']) > 50 ? '...' : ''); ?></td>
                                            <td class="py-4">
                                                <?php if ($complaint['file_path']): ?>
                                                    <a href="<?php echo $complaint['file_path']; ?>" target="_blank" class="text-primary hover:underline">View</a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4"><?php echo date('Y-m-d H:i', strtotime($complaint['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="py-4 text-center text-gray-500">No complaints found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Complaint Modal (same as index.php) -->
    <div id="complaintModal" class="modal">
        <div class="modal-content">
            <h3 class="text-lg font-medium text-gray-900 mb-4">File a Complaint</h3>
            <form method="POST" action="index.php" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complaint</label>
                    <textarea name="complaint" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" rows="4" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload File (optional)</label>
                    <input type="file" name="file" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-button file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('complaintModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                    <button type="submit" name="complaint_submit" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <span class="text-2xl font-['Pacifico']">VillageSys</span>
                    <p class="mt-4 text-gray-400 text-sm">Smart solutions for Gudalur village monitoring and management.</p>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="index.php" class="hover:text-white">Home</a></li>
                        <li><a href="#" class="hover:text-white">Services</a></li>
                        <li><a href="#" class="hover:text-white">Projects</a></li>
                        <li><a href="index.php#contact" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white">Documentation</a></li>
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-medium mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="ri-facebook-fill w-6 h-6 flex items-center justify-center"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="ri-twitter-fill w-6 h-6 flex items-center justify-center"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="ri-linkedin-fill w-6 h-6 flex items-center justify-center"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="ri-instagram-fill w-6 h-6 flex items-center justify-center"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-sm text-gray-400">
                <p>© 2025 Village Monitoring System - Gudalur. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function initMap() {
            const gudalur = { lat: 11.50, lng: 76.50 };
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: gudalur
            });
            new google.maps.Marker({
                position: gudalur,
                map: map,
                title: 'Gudalur, Tamil Nadu'
            });
        }

        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    </script>
</body>
</html>