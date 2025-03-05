<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
        $password = password_hash(mysqli_real_escape_string($conn, $_POST['password']), PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $query = "INSERT INTO users (fullname, email, contact_number, password, role) VALUES ('$fullname', '$email', '$contact_number', '$password', '$role')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_project'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
        $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
        $query = "INSERT INTO projects (name, description, status, start_date, end_date) VALUES ('$name', '$description', '$status', '$start_date', '$end_date')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_alert'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $query = "INSERT INTO emergency_alerts (title, description, status) VALUES ('$title', '$description', '$status')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_bus'])) {
        $route = mysqli_real_escape_string($conn, $_POST['route']);
        $next_arrival = mysqli_real_escape_string($conn, $_POST['next_arrival']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $capacity_percentage = mysqli_real_escape_string($conn, $_POST['capacity_percentage']);
        $schedule_date = mysqli_real_escape_string($conn, $_POST['schedule_date']);
        $query = "INSERT INTO bus_schedules (route, next_arrival, status, capacity_percentage, schedule_date) VALUES ('$route', '$next_arrival', '$status', '$capacity_percentage', '$schedule_date')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_activity'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $activity_type = mysqli_real_escape_string($conn, $_POST['activity_type']);
        $query = "INSERT INTO activities (title, description, activity_type) VALUES ('$title', '$description', '$activity_type')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_resource'])) {
        $resource_name = mysqli_real_escape_string($conn, $_POST['resource_name']);
        $usage_percentage = mysqli_real_escape_string($conn, $_POST['usage_percentage']);
        $recorded_date = mysqli_real_escape_string($conn, $_POST['recorded_date']);
        $query = "INSERT INTO resource_utilization (resource_name, usage_percentage, recorded_date) VALUES ('$resource_name', '$usage_percentage', '$recorded_date')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_message'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $query = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
        mysqli_query($conn, $query);
    }
    // Refresh page after submission
    header("Location: dashboard.php");
    exit();
}

// Fetch data from database
$users_query = "SELECT id, fullname, email, contact_number, role, created_at FROM users";
$users_result = mysqli_query($conn, $users_query);

$population_query = "SELECT total, adults, seniors, youth, children, recorded_date FROM population ORDER BY recorded_date DESC LIMIT 1";
$population_result = mysqli_query($conn, $population_query);
$population = mysqli_fetch_assoc($population_result);

$projects_query = "SELECT id, name, description, status, start_date, end_date FROM projects";
$projects_result = mysqli_query($conn, $projects_query);

$alerts_query = "SELECT id, title, description, status, created_at FROM emergency_alerts WHERE status = 'active'";
$alerts_result = mysqli_query($conn, $alerts_query);

$bus_query = "SELECT id, route, next_arrival, status, capacity_percentage, schedule_date FROM bus_schedules WHERE schedule_date = CURDATE()";
$bus_result = mysqli_query($conn, $bus_query);

$activities_query = "SELECT id, title, description, activity_type, created_at FROM activities ORDER BY created_at DESC LIMIT 5";
$activities_result = mysqli_query($conn, $activities_query);

$resources_query = "SELECT resource_name, usage_percentage FROM resource_utilization WHERE recorded_date = (SELECT MAX(recorded_date) FROM resource_utilization)";
$resources_result = mysqli_query($conn, $resources_query);

$messages_query = "SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5";
$messages_result = mysqli_query($conn, $messages_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Village Monitoring System</title>
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
                    <button class="md:hidden text-gray-900 focus:outline-none" onclick="toggleMenu()">
                        <i class="ri-menu-line w-6 h-6"></i>
                    </button>
                    <div class="desktop-menu hidden md:flex items-center space-x-4">
                        <a href="index.php" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Home</a>
                        <a href="#users" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Users</a>
                        <a href="#statistics" class="text-gray-900 hover:text-primary px-3 py-2 text-sm font-medium">Statistics</a>
                        <a href="logout.php" class="flex items-center space-x-2 bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                            <i class="ri-logout-box-line w-4 h-4"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
            <div id="mobile-menu" class="mobile-menu hidden md:hidden bg-white shadow-sm">
                <a href="index.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Home</a>
                <a href="#users" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Users</a>
                <a href="#statistics" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Statistics</a>
                <a href="logout.php" class="block text-gray-900 hover:text-primary px-4 py-2 text-sm font-medium">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-20 md:py-8">
        <section id="welcome" class="mb-12">
            <h1 class="text-2xl md:text-3xl font-['Pacifico'] text-primary mb-6">Admin Dashboard</h1>
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <p class="text-sm md:text-base text-gray-700">Welcome, Admin! Manage users, monitor village data, and oversee operations from here.</p>
            </div>
        </section>

        <section id="users" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Users</h2>
                <button onclick="openModal('userModal')" class="bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                    <i class="ri-user-add-line w-4 h-4 mr-2"></i>Add User
                </button>
            </div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">ID</th>
                                    <th class="pb-4">Full Name</th>
                                    <th class="pb-4">Email</th>
                                    <th class="pb-4">Contact</th>
                                    <th class="pb-4">Role</th>
                                    <th class="pb-4">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                    <tr class="border-t">
                                        <td class="py-4"><?php echo $user['id']; ?></td>
                                        <td class="py-4"><?php echo $user['fullname']; ?></td>
                                        <td class="py-4"><?php echo $user['email']; ?></td>
                                        <td class="py-4"><?php echo $user['contact_number']; ?></td>
                                        <td class="py-4"><?php echo $user['role']; ?></td>
                                        <td class="py-4"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="userModal" class="modal">
                <div class="modal-content">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New User</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="fullname" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <input type="text" name="contact_number" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select name="role" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeModal('userModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                            <button type="submit" name="add_user" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="statistics" class="mb-12">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6">Statistics Overview</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo mysqli_num_rows($projects_result); ?></p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-medium text-gray-900">Emergency Alerts</h3>
                        <i class="ri-alarm-warning-line w-6 h-6 md:w-8 md:h-8 flex items-center justify-center text-red-500"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo mysqli_num_rows($alerts_result); ?></p>
                </div>
                <div class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-medium text-gray-900">Daily Visitors</h3>
                        <i class="ri-team-line w-6 h-6 md:w-8 md:h-8 flex items-center justify-center text-primary"></i>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo mysqli_num_rows($bus_result); ?></p>
                </div>
            </div>
        </section>

        <section id="projects" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Projects</h2>
                <button onclick="openModal('projectModal')" class="bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                    <i class="ri-building-line w-4 h-4 mr-2"></i>Add Project
                </button>
            </div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">ID</th>
                                    <th class="pb-4">Name</th>
                                    <th class="pb-4">Description</th>
                                    <th class="pb-4">Status</th>
                                    <th class="pb-4">Start Date</th>
                                    <th class="pb-4">End Date</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php while ($project = mysqli_fetch_assoc($projects_result)): ?>
                                    <tr class="border-t">
                                        <td class="py-4"><?php echo $project['id']; ?></td>
                                        <td class="py-4"><?php echo $project['name']; ?></td>
                                        <td class="py-4"><?php echo $project['description']; ?></td>
                                        <td class="py-4"><?php echo ucfirst($project['status']); ?></td>
                                        <td class="py-4"><?php echo $project['start_date'] ?: 'N/A'; ?></td>
                                        <td class="py-4"><?php echo $project['end_date'] ?: 'N/A'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="projectModal" class="modal">
                <div class="modal-content">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Project</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeModal('projectModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                            <button type="submit" name="add_project" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="alerts" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Active Emergency Alerts</h2>
                <button onclick="openModal('alertModal')" class="bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                    <i class="ri-alarm-warning-line w-4 h-4 mr-2"></i>Add Alert
                </button>
            </div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">ID</th>
                                    <th class="pb-4">Title</th>
                                    <th class="pb-4">Description</th>
                                    <th class="pb-4">Status</th>
                                    <th class="pb-4">Created</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php while ($alert = mysqli_fetch_assoc($alerts_result)): ?>
                                    <tr class="border-t">
                                        <td class="py-4"><?php echo $alert['id']; ?></td>
                                        <td class="py-4"><?php echo $alert['title']; ?></td>
                                        <td class="py-4"><?php echo $alert['description']; ?></td>
                                        <td class="py-4"><?php echo ucfirst($alert['status']); ?></td>
                                        <td class="py-4"><?php echo date('Y-m-d H:i', strtotime($alert['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="alertModal" class="modal">
                <div class="modal-content">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Alert</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeModal('alertModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                            <button type="submit" name="add_alert" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="buses" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Today's Bus Schedules</h2>
                <button onclick="openModal('busModal')" class="bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                    <i class="ri-bus-line w-4 h-4 mr-2"></i>Add Bus Schedule
                </button>
            </div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">ID</th>
                                    <th class="pb-4">Route</th>
                                    <th class="pb-4">Next Arrival</th>
                                    <th class="pb-4">Status</th>
                                    <th class="pb-4">Capacity</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php while ($bus = mysqli_fetch_assoc($bus_result)): ?>
                                    <tr class="border-t">
                                        <td class="py-4"><?php echo $bus['id']; ?></td>
                                        <td class="py-4"><?php echo $bus['route']; ?></td>
                                        <td class="py-4"><?php echo $bus['next_arrival']; ?></td>
                                        <td class="py-4">
                                            <span class="px-2 py-1 <?php echo $bus['status'] == 'on_time' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?> rounded-full text-xs">
                                                <?php echo ucfirst(str_replace('_', ' ', $bus['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="py-4"><?php echo $bus['capacity_percentage']; ?>%</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="busModal" class="modal">
                <div class="modal-content">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Bus Schedule</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Route</label>
                            <input type="text" name="route" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Next Arrival (HH:MM)</label>
                            <input type="time" name="next_arrival" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                                <option value="on_time">On Time</option>
                                <option value="delayed">Delayed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (%)</label>
                            <input type="number" name="capacity_percentage" min="0" max="100" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Schedule Date</label>
                            <input type="date" name="schedule_date" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeModal('busModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                            <button type="submit" name="add_bus" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="activities" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Recent Activities</h2>
                <button onclick="openModal('activityModal')" class="bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                    <i class="ri-calendar-check-line w-4 h-4 mr-2"></i>Add Activity
                </button>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="space-y-6">
                    <?php while ($activity = mysqli_fetch_assoc($activities_result)): ?>
                        <div class="flex items-start space-x-4">
                            <div class="w-8 h-8 rounded-full <?php echo $activity['activity_type'] == 'project' ? 'ri-building-line text-primary bg-blue-100' : ($activity['activity_type'] == 'meeting' ? 'ri-calendar-check-line text-green-600 bg-green-100' : 'ri-alert-line text-yellow-600 bg-yellow-100'); ?> flex items-center justify-center"></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900"><?php echo $activity['title']; ?></h4>
                                    <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($activity['created_at'])); ?></span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500"><?php echo $activity['description']; ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div id="activityModal" class="modal">
                <div class="modal-content">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Activity</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
                            <select name="activity_type" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                                <option value="project">Project</option>
                                <option value="meeting">Meeting</option>
                                <option value="alert">Alert</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeModal('activityModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                            <button type="submit" name="add_activity" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="resources" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Resource Utilization</h2>
                <button onclick="openModal('resourceModal')" class="bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                    <i class="ri-bar-chart-line w-4 h-4 mr-2"></i>Add Resource
                </button>
            </div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">Resource</th>
                                    <th class="pb-4">Usage (%)</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php while ($resource = mysqli_fetch_assoc($resources_result)): ?>
                                    <tr class="border-t">
                                        <td class="py-4"><?php echo $resource['resource_name']; ?></td>
                                        <td class="py-4"><?php echo $resource['usage_percentage']; ?>%</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="resourceModal" class="modal">
                <div class="modal-content">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Resource</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resource Name</label>
                            <input type="text" name="resource_name" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usage (%)</label>
                            <input type="number" name="usage_percentage" min="0" max="100" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recorded Date</label>
                            <input type="date" name="recorded_date" class="w-full px-3 py-2 border rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeModal('resourceModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                            <button type="submit" name="add_resource" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="messages" class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">Recent Contact Messages</h2>
                <button onclick="openModal('messageModal')" class="bg-primary text-white px-4 py-2 rounded-button text-sm font-medium hover:bg-primary/90">
                    <i class="ri-mail-line w-4 h-4 mr-2"></i>Add Message
                </button>
            </div>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 md:p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs md:text-sm font-medium text-gray-500">
                                    <th class="pb-4">ID</th>
                                    <th class="pb-4">Name</th>
                                    <th class="pb-4">Email</th>
                                    <th class="pb-4">Message</th>
                                    <th class="pb-4">Received</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs md:text-sm">
                                <?php while ($message = mysqli_fetch_assoc($messages_result)): ?>
                                    <tr class="border-t">
                                        <td class="py-4"><?php echo $message['id']; ?></td>
                                        <td class="py-4"><?php echo $message['name']; ?></td>
                                        <td class="py-4"><?php echo $message['email']; ?></td>
                                        <td class="py-4"><?php echo substr($message['message'], 0, 50) . (strlen($message['message']) > 50 ? '...' : ''); ?></td>
                                        <td class="py-4"><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="messageModal" class="modal">
                <div class="modal-content">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Message</h3>
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
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="closeModal('messageModal')" class="bg-gray-300 text-gray-900 px-4 py-2 rounded-button hover:bg-gray-400 text-sm font-medium">Cancel</button>
                            <button type="submit" name="add_message" class="bg-primary text-white px-4 py-2 rounded-button hover:bg-primary/90 text-sm font-medium">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
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

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    </script>
</body>
</html>