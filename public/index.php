<?php
require_once '../app/helpers/auth.php';
use App\Helpers\Auth;

Auth::redirectIfLoggedIn();

require_once '../app/controllers/RoomController.php';
use App\Controllers\RoomController;

$roomController = new RoomController();
$roomTypes = $roomController->getRoomTypes();

// Handle search
$rooms = [];
$search_results = null;
$check_in = date('Y-m-d', strtotime('+1 day'));
$check_out = date('Y-m-d', strtotime('+3 days'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $check_in = $_POST['check_in'] ?? $check_in;
    $check_out = $_POST['check_out'] ?? $check_out;
    $room_type = $_POST['room_type'] ?? null;
    $max_price = $_POST['max_price'] ?? null;
    
    $search_results = $roomController->getAvailableRooms($check_in, $check_out, $room_type, $max_price);
    if ($search_results['success']) {
        $rooms = $search_results['rooms'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reservation System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-bg {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-hotel text-blue-600 text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-gray-800">Hotel Paradise</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="login.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-in-alt mr-1"></i> Login
                    </a>
                    <a href="register.php" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                        <i class="fas fa-user-plus mr-1"></i> Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-bg text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Experience Luxury & Comfort</h1>
                <p class="text-xl mb-8">Book your perfect stay at Hotel Paradise</p>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
        <div class="bg-white rounded-lg shadow-xl p-6">
            <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in</label>
                    <input type="date" name="check_in" value="<?= $check_in ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out</label>
                    <input type="date" name="check_out" value="<?= $check_out ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                    <select name="room_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <?php foreach ($roomTypes['types'] as $type): ?>
                            <option value="<?= $type['room_type'] ?>"><?= $type['room_type'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Price ($/night)</label>
                    <input type="number" name="max_price" placeholder="Any price" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-4">
                    <button type="submit" name="search" 
                            class="w-full bg-blue-600 text-white px-4 py-3 rounded-md font-medium hover:bg-blue-700 transition duration-300">
                        <i class="fas fa-search mr-2"></i> Search Available Rooms
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <?php if (isset($search_results) && !$search_results['success']): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <?php foreach ($search_results['errors'] as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($rooms)): ?>
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Available Rooms (<?= count($rooms) ?> found)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($rooms as $room): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <?php if ($room['image_url']): ?>
                            <img src="<?= $room['image_url'] ?>" alt="<?= $room['room_number'] ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-gray-800"><?= $room['room_number'] ?> - <?= $room['room_type'] ?></h3>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded"><?= $room['status'] ?></span>
                            </div>
                            <p class="text-gray-600 mb-4"><?= $room['description'] ?></p>
                            <div class="mb-4">
                                <p class="text-sm text-gray-500"><i class="fas fa-user mr-1"></i> Max <?= $room['max_occupancy'] ?> guests</p>
                                <p class="text-sm text-gray-500"><i class="fas fa-wifi mr-1"></i> <?= $room['amenities'] ?></p>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-blue-600">$<?= number_format($room['price_per_night'], 2) ?> <span class="text-sm font-normal text-gray-500">/night</span></span>
                                <a href="login.php" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_POST['search'])): ?>
            <div class="text-center py-12">
                <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No rooms available</h3>
                <p class="text-gray-500">Try adjusting your search criteria</p>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Welcome to Hotel Paradise</h2>
                <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                    Search for available rooms using the form above. We offer various room types to suit your needs, from cozy singles to luxurious suites.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-600 text-3xl mb-4">
                            <i class="fas fa-bed"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Comfortable Rooms</h3>
                        <p class="text-gray-600">Modern amenities and cozy beds for a perfect stay.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-600 text-3xl mb-4">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">Free WiFi</h3>
                        <p class="text-gray-600">High-speed internet throughout the hotel.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-blue-600 text-3xl mb-4">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">24/7 Service</h3>
                        <p class="text-gray-600">Round-the-clock concierge and room service.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Hotel Paradise. All rights reserved.</p>
                <p class="text-gray-400 mt-2">123 Luxury Street, City, Country | Phone: +1 234 567 890</p>
            </div>
        </div>
    </footer>

    <script>
        // Set minimum dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            const checkInInput = document.querySelector('input[name="check_in"]');
            const checkOutInput = document.querySelector('input[name="check_out"]');
            
            checkInInput.min = today;
            checkOutInput.min = tomorrowStr;
            
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutInput.min = nextDay.toISOString().split('T')[0];
                
                if (new Date(checkOutInput.value) <= checkInDate) {
                    checkOutInput.value = nextDay.toISOString().split('T')[0];
                }
            });
        });
    </script>
</body>
</html>