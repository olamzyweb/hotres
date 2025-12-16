<?php
require_once '../../app/helpers/auth.php';
require_once '../../app/controllers/BookingController.php';
require_once '../../app/controllers/RoomController.php';
require_once '../../app/controllers/AuthController.php';

use App\Helpers\Auth;
use App\Controllers\BookingController;
use App\Controllers\RoomController;

Auth::requireAdmin();

$bookingController = new BookingController();
$roomController = new RoomController();

$bookingStats = $bookingController->getBookingStatistics();
$roomStats = $roomController->getRoomStatistics();
$recentBookings = $bookingController->getRecentBookings(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Hotel Reservation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-hotel text-blue-400 text-2xl mr-2"></i>
                        <span class="text-xl font-bold text-white">Hotel Paradise Admin</span>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex space-x-4">
                            <a href="dashboard.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                            <a href="rooms.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-bed mr-1"></i> Rooms
                            </a>
                            <a href="bookings.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-calendar-alt mr-1"></i> Bookings
                            </a>
                            <a href="users.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-users mr-1"></i> Users
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-300">
                                <i class="fas fa-user-shield mr-1"></i> <?= $_SESSION['full_name'] ?>
                            </span>
                            <a href="../logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Message -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
            <p class="text-gray-600">Manage hotel operations and bookings</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Bookings Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-calendar-check text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Total Bookings</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $bookingStats['stats']['total_bookings'] ?? 0 ?></p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Confirmed</p>
                        <p class="font-semibold text-green-600"><?= $bookingStats['stats']['confirmed_bookings'] ?? 0 ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="font-semibold text-yellow-600"><?= $bookingStats['stats']['pending_bookings'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Revenue Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Total Revenue</h3>
                        <p class="text-2xl font-bold text-gray-900">$<?= number_format($bookingStats['stats']['total_revenue'] ?? 0, 2) ?></p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-500">Average per booking</p>
                    <p class="font-semibold text-gray-800">
                        $<?= number_format(($bookingStats['stats']['total_revenue'] ?? 0) / max(1, ($bookingStats['stats']['total_bookings'] ?? 1)), 2) ?>
                    </p>
                </div>
            </div>
            
            <!-- Rooms Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-bed text-purple-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Total Rooms</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= $roomStats['stats']['total_rooms'] ?? 0 ?></p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Available</p>
                        <p class="font-semibold text-green-600"><?= $roomStats['stats']['available_rooms'] ?? 0 ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Occupied</p>
                        <p class="font-semibold text-red-600"><?= $roomStats['stats']['booked_rooms'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Average Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-chart-line text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Average Stats</h3>
                        <p class="text-xl font-bold text-gray-900"><?= number_format($bookingStats['stats']['avg_nights'] ?? 0, 1) ?> nights</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-500">Avg room price</p>
                    <p class="font-semibold text-gray-800">$<?= number_format($roomStats['stats']['avg_price'] ?? 0, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Recent Bookings</h2>
                    <a href="bookings.php" class="text-blue-600 hover:text-blue-800 text-sm">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentBookings['bookings'] as $booking): ?>
                            <?php
                            $status_colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'completed' => 'bg-gray-100 text-gray-800'
                            ];
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $booking['full_name'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $booking['room_number'] ?> (<?= $booking['room_type'] ?>)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d', strtotime($booking['check_in_date'])) ?> - <?= date('M d', strtotime($booking['check_out_date'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    $<?= number_format($booking['total_price'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_colors[$booking['status']] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="rooms.php?action=add" class="bg-blue-600 text-white p-6 rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-plus-circle text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-bold text-lg">Add New Room</h3>
                        <p class="text-blue-100 text-sm">Create new room listing</p>
                    </div>
                </div>
            </a>
            
            <a href="bookings.php" class="bg-green-600 text-white p-6 rounded-lg shadow-md hover:bg-green-700 transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-calendar-check text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-bold text-lg">Manage Bookings</h3>
                        <p class="text-green-100 text-sm">Approve or cancel bookings</p>
                    </div>
                </div>
            </a>
            
            <a href="rooms.php" class="bg-purple-600 text-white p-6 rounded-lg shadow-md hover:bg-purple-700 transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-bed text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-bold text-lg">Room Management</h3>
                        <p class="text-purple-100 text-sm">Edit room details</p>
                    </div>
                </div>
            </a>
            
            <a href="users.php" class="bg-red-600 text-white p-6 rounded-lg shadow-md hover:bg-red-700 transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-users-cog text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-bold text-lg">User Management</h3>
                        <p class="text-red-100 text-sm">Manage user accounts</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Hotel Paradise Admin Panel. All rights reserved.</p>
                <p class="text-gray-400 mt-2">Logged in as Administrator</p>
            </div>
        </div>
    </footer>
</body>
</html>