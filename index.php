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

// Fetch 5-day forecast
$forecast_url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&appid={$weather_api_key}&units=metric";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $forecast_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$forecast_json = curl_exec($ch);
curl_close($ch);
$forecast = json_decode($forecast_json, true);

// Process forecast data (one entry per day for 5 days)
$forecast_days = [];
if ($forecast && isset($forecast['list'])) {
    $last_date = '';
    foreach ($forecast['list'] as $entry) {
        $date = date('Y-m-d', $entry['dt']);
        if ($date !== $last_date) {
            $forecast_days[] = [
                'day' => date('D', $entry['dt']),
                'temperature' => round($entry['main']['temp']),
                'weather_condition' => ucfirst($forecast['list'][0]['weather'][0]['main'])
            ];
            $last_date = $date;
        }
        if (count($forecast_days) >= 5) break;
    }
}

// Handle complaint submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $complaint = mysqli_real_escape_string($conn, $_POST['complaint']);
    $file_path = null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = uniqid() . '_' . basename($_FILES['file']['name']);
        $file_path = $upload_dir . $file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path);
    }

    $query = "INSERT INTO complaints (name, email, phone_number, complaint, file_path) VALUES ('$name', '$email', '$phone_number', '$complaint', '$file_path')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Complaint filed successfully!');</script>";
    } else {
        echo "<script>alert('Error filing complaint: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Village Monitoring System - Gudalur</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCknv9cAyPuScuJVB_JZhqzPCqcEvWdR2I&callback=initMap" async defer></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body { font-family: 'Inter', sans-serif; }
        .chart-container { min-height: 300px; }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
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
                        <a href="#overview" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Overview</a>
                        <a href="#statistics" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Statistics</a>
                        <a href="#activities" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Activities</a>
                        <a href="complaints.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Complaints</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard.php" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Dashboard</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button onclick="openModal('complaintModal')" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                                <i class="ri-file-warning-line w-4 h-4"></i>
                                <span>Complaint</span>
                            </button>
                            <a href="logout.php" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                                <i class="ri-logout-box-line w-4 h-4"></i>
                                <span>Logout</span>
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                                <i class="ri-user-line w-4 h-4"></i>
                                <span>Login</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div id="mobile-menu" class="mobile-menu hidden md:hidden bg-white shadow-sm">
                <a href="#overview" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Overview</a>
                <a href="#statistics" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Statistics</a>
                <a href="#activities" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Activities</a>
                <a href="complaints.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Complaints</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="dashboard.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Dashboard</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="#" onclick="openModal('complaintModal')" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Complaint</a>
                    <a href="logout.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-20 md:py-8">
        <section id="overview" class="mb-12">
            <div class="relative rounded-lg overflow-hidden h-[400px] md:h-[500px] mb-8">
                <div id="map" class="absolute inset-0"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-primary/90 to-transparent flex items-center">
                    <div class="max-w-lg ml-6 md:ml-12 text-white">
                        <h1 class="text-2xl md:text-4xl font-bold mb-4">Smart Village Monitoring System - Gudalur</h1>
                        <p class="text-sm md:text-lg mb-6">Real-time monitoring and management system for Gudalur, Tamil Nadu infrastructure and community services.</p>
                        <button class="bg-white text-primary px-4 py-2 md:px-6 md:py-3 rounded-button font-medium hover:bg-gray-100">
                            Learn More
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $pop_query = "SELECT total FROM population ORDER BY recorded_date DESC LIMIT 1";
                $pop_result = mysqli_query($conn, $pop_query);
                $population = mysqli_fetch_assoc($pop_result);

                $proj_query = "SELECT COUNT(*) as active FROM projects WHERE status = 'active'";
                $proj_result = mysqli_query($conn, $proj_query);
                $projects = mysqli_fetch_assoc($proj_result);

                $alert_query = "SELECT COUNT(*) as alerts FROM emergency_alerts WHERE status = 'active'";
                $alert_result = mysqli_query($conn, $alert_query);
                $alerts = mysqli_fetch_assoc($alert_result);

                $visitor_query = "SELECT COUNT(*) as visitors FROM bus_schedules WHERE schedule_date = CURDATE()";
                $visitor_result = mysqli_query($conn, $visitor_query);
                $visitors = mysqli_fetch_assoc($visitor_result);
                ?>
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-medium text-gray-900">Population</h3>
                        <i class="ri-group-line w-6 h-6 md:w-8 md:h-8 flex items-center justify-center text-primary"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo $population ? number_format($population['total']) : 'N/A'; ?></p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-medium text-gray-900">Active Projects</h3>
                        <i class="ri-building-line w-6 h-6 md:w-8 md:h-8 flex items-center justify-center text-primary"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo $projects ? $projects['active'] : '0'; ?></p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-medium text-gray-900">Emergency Alerts</h3>
                        <i class="ri-alarm-warning-line w-6 h-6 md:w-8 md:h-8 flex items-center justify-center text-red-500"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo $alerts ? $alerts['alerts'] : '0'; ?></p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-medium text-gray-900">Daily Visitors</h3>
                        <i class="ri-team-line w-6 h-6 md:w-8 md:h-8 flex items-center justify-center text-primary"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo $visitors ? $visitors['visitors'] : '0'; ?></p>
                </div>
            </div>
        </section>

        <section id="statistics" class="mb-12">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6">Village Statistics</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4">Population Demographics</h3>
                    <div id="demographicsChart" class="chart-container"></div>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4">Resource Utilization</h3>
                    <div id="resourceChart" class="chart-container"></div>
                </div>
            </div>
        </section>

        <section id="buses" class="mb-12">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6">Bus Information</h2>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="flex flex-col md:flex-row items-center justify-between mb-6">
                        <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4 md:mb-0">Today's Schedule</h3>
                        <div class="relative w-full md:w-auto">
                            <input type="text" placeholder="Search routes..." class="w-full pl-10 pr-4 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 flex items-center justify-center"></i>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">Route</th>
                                    <th class="pb-4">Next Arrival</th>
                                    <th class="pb-4">Status</th>
                                    <th class="pb-4">Capacity</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php
                                $bus_query = "SELECT * FROM bus_schedules WHERE schedule_date = CURDATE()";
                                $bus_result = mysqli_query($conn, $bus_query);
                                if (mysqli_num_rows($bus_result) > 0):
                                    while ($bus = mysqli_fetch_assoc($bus_result)):
                                ?>
                                    <tr class="border-t">
                                        <td class="py-4"><?php echo $bus['route']; ?></td>
                                        <td><?php echo $bus['next_arrival']; ?></td>
                                        <td>
                                            <span class="px-2 py-1 <?php echo $bus['status'] == 'on_time' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> rounded-full text-xs">
                                                <?php echo ucfirst(str_replace('_', ' ', $bus['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $bus['capacity_percentage']; ?>%</td>
                                    </tr>
                                <?php 
                                    endwhile;
                                else: ?>
                                    <tr><td colspan="4" class="py-4 text-center text-gray-500">No bus schedules available today.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section id="activities" class="mb-12">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6">Recent Activities</h2>
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="space-y-6">
                    <?php
                    $activity_query = "SELECT * FROM activities ORDER BY created_at DESC LIMIT 3";
                    $activity_result = mysqli_query($conn, $activity_query);
                    if (mysqli_num_rows($activity_result) > 0):
                        while ($activity = mysqli_fetch_assoc($activity_result)):
                            $icon = $activity['activity_type'] == 'project' ? 'ri-building-line text-primary bg-blue-100' : 
                                   ($activity['activity_type'] == 'meeting' ? 'ri-calendar-check-line text-green-600 bg-green-100' : 
                                   'ri-alert-line text-yellow-600 bg-yellow-100');
                    ?>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 rounded-full <?php echo $icon; ?> flex items-center justify-center"></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900"><?php echo $activity['title']; ?></h4>
                                    <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($activity['created_at'])); ?></span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500"><?php echo $activity['description']; ?></p>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else: ?>
                        <p class="text-sm text-gray-500">No recent activities available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="weather" class="mb-12">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6">Weather Forecast - Gudalur</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <?php
                if (!empty($forecast_days)):
                    foreach ($forecast_days as $weather):
                        $icon = $weather['weather_condition'] == 'Clear' || $weather['weather_condition'] == 'Sunny' ? 'ri-sun-line text-yellow-500' :
                               ($weather['weather_condition'] == 'Clouds' ? 'ri-cloud-line text-gray-400' :
                               ($weather['weather_condition'] == 'Rain' ? 'ri-drizzle-line text-blue-500' : 'ri-sun-cloudy-line text-yellow-500'));
                ?>
                    <div class="bg-white rounded-lg shadow-sm p-4 text-center">
                        <p class="text-xs md:text-sm text-gray-500"><?php echo $weather['day']; ?></p>
                        <i class="<?php echo $icon; ?> text-3xl md:text-4xl my-4"></i>
                        <p class="text-xl md:text-2xl font-bold"><?php echo $weather['temperature']; ?>°C</p>
                        <p class="text-xs md:text-sm text-gray-500 mt-2"><?php echo $weather['weather_condition']; ?></p>
                    </div>
                <?php 
                    endforeach;
                else: ?>
                    <div class="col-span-5 text-center text-gray-500">Unable to fetch weather forecast data.</div>
                <?php endif; ?>
            </div>
        </section>

        <section id="contact" class="mb-12">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4">Emergency Contacts</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <i class="ri-hospital-line w-6 h-6 flex items-center justify-center text-red-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Medical Emergency</p>
                                <p class="text-sm text-gray-500">+91 12345-67890</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="ri-police-car-line w-6 h-6 flex items-center justify-center text-blue-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Police Station</p>
                                <p class="text-sm text-gray-500">+91 23456-78901</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="ri-fire-line w-6 h-6 flex items-center justify-center text-orange-500"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Fire Department</p>
                                <p class="text-sm text-gray-500">+91 34567-89012</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4">Office Hours</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Monday - Friday</span>
                            <span class="text-gray-900">9:00 AM - 5:00 PM</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Saturday</span>
                            <span class="text-gray-900">10:00 AM - 2:00 PM</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Sunday</span>
                            <span class="text-gray-900">Closed</span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Location</h4>
                        <p class="text-sm text-gray-500">Gudalur Main Road,<br>Gudalur, Tamil Nadu, India</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-medium text-gray-900 mb-4">Quick Contact</h3>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
                        $name = mysqli_real_escape_string($conn, $_POST['name']);
                        $email = mysqli_real_escape_string($conn, $_POST['email']);
                        $message = mysqli_real_escape_string($conn, $_POST['message']);
                        
                        if ($name && $email && $message) {
                            $query = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
                            if (mysqli_query($conn, $query)) {
                                echo "<script>alert('Message sent successfully!');</script>";
                            } else {
                                echo "<script>alert('Error sending message: " . mysqli_error($conn) . "');</script>";
                            }
                        } else {
                            echo "<script>alert('Please fill in all fields');</script>";
                        }
                    }
                    ?>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea name="message" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="contact_submit" class="w-full bg-primary text-white py-2 px-4 rounded-button hover:bg-primary/90 text-sm font-medium">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <!-- Complaint Modal -->
    <div id="complaintModal" class="modal">
        <div class="modal-content">
            <h3 class="text-lg font-medium text-gray-900 mb-4">File a Complaint</h3>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
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
                        <li><a href="#" class="hover:text-white">About Us</a></li>
                        <li><a href="#" class="hover:text-white">Services</a></li>
                        <li><a href="#" class="hover:text-white">Projects</a></li>
                        <li><a href="#" class="hover:text-white">Contact</a></li>
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
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="ri-facebook-fill w-6 h-6 flex items-center justify-center"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="ri-twitter-fill w-6 h-6 flex items-center justify-center"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="ri-linkedin-fill w-6 h-6 flex items-center justify-center"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="ri-instagram-fill w-6 h-6 flex items-center justify-center"></i>
                        </a>
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

        document.addEventListener('DOMContentLoaded', function() {
            const demographicsChart = echarts.init(document.getElementById('demographicsChart'));
            const resourceChart = echarts.init(document.getElementById('resourceChart'));

            <?php
            $demo_query = "SELECT adults, seniors, youth, children FROM population ORDER BY recorded_date DESC LIMIT 1";
            $demo_result = mysqli_query($conn, $demo_query);
            $demo = mysqli_fetch_assoc($demo_result);

            $resource_query = "SELECT resource_name, usage_percentage FROM resource_utilization WHERE recorded_date = (SELECT MAX(recorded_date) FROM resource_utilization)";
            $resource_result = mysqli_query($conn, $resource_query);
            $resources = [];
            $resource_names = [];
            while ($row = mysqli_fetch_assoc($resource_result)) {
                $resources[] = [
                    'value' => $row['usage_percentage'],
                    'itemStyle' => ['color' => 'rgba(' . rand(50, 255) . ',' . rand(50, 255) . ',' . rand(50, 255) . ',1)']
                ];
                $resource_names[] = $row['resource_name'];
            }
            ?>

            const demographicsOption = {
                animation: false,
                tooltip: { trigger: 'item' },
                legend: { top: '5%', left: 'center', textStyle: { color: '#1f2937' } },
                series: [{
                    name: 'Demographics',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
                    label: { show: false, position: 'center' },
                    emphasis: { label: { show: true, fontSize: '18', fontWeight: 'bold' } },
                    labelLine: { show: false },
                    data: [
                        { value: <?php echo $demo ? $demo['adults'] : 0; ?>, name: 'Adults', itemStyle: { color: 'rgba(87, 181, 231, 1)' } },
                        { value: <?php echo $demo ? $demo['seniors'] : 0; ?>, name: 'Seniors', itemStyle: { color: 'rgba(141, 211, 199, 1)' } },
                        { value: <?php echo $demo ? $demo['youth'] : 0; ?>, name: 'Youth', itemStyle: { color: 'rgba(251, 191, 114, 1)' } },
                        { value: <?php echo $demo ? $demo['children'] : 0; ?>, name: 'Children', itemStyle: { color: 'rgba(252, 141, 98, 1)' } }
                    ]
                }]
            };

            const resourceOption = {
                animation: false,
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: [{ 
                    type: 'category', 
                    data: <?php echo json_encode($resource_names); ?>, 
                    axisTick: { alignWithLabel: true }, 
                    axisLabel: { color: '#1f2937' } 
                }],
                yAxis: [{ type: 'value', axisLabel: { color: '#1f2937' } }],
                series: [{
                    name: 'Usage',
                    type: 'bar',
                    barWidth: '60%',
                    data: <?php echo json_encode($resources); ?>
                }]
            };

            demographicsChart.setOption(demographicsOption);
            resourceChart.setOption(resourceOption);

            window.addEventListener('resize', function() {
                demographicsChart.resize();
                resourceChart.resize();
            });
        });

        const searchInput = document.querySelector('input[placeholder="Search routes..."]');
        searchInput.addEventListener('input', function(e) {
            const value = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(value) ? '' : 'none';
            });
        });
    </script>
</body>
</html>