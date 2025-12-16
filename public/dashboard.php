<?php
require_once '../app/helpers/auth.php';
require_once '../app/controllers/BookingController.php';
require_once '../app/controllers/RoomController.php';

use App\Helpers\Auth;
use App\Controllers\BookingController;
use App\Controllers\RoomController;

Auth::requireLogin();

$bookingController = new BookingController();
$roomController = new RoomController();

$user_id = $_SESSION['user_id'];
$bookings = $bookingController->getUserBookings($user_id);
$stats = $bookingController->getBookingStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hotel Reservation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <div class="hidden md:block ml-10">
                        <div class="flex space-x-4">
                            <a href="dashboard.php" class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                            <a href="rooms.php" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-bed mr-1"></i> Rooms
                            </a>
                            <a href="booking.php" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-calendar-plus mr-1"></i> New Booking
                            </a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700">
                                <i class="fas fa-user-circle mr-1"></i> <?= $_SESSION['full_name'] ?>
                            </span>
                            <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700">
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
            <h1 class="text-3xl font-bold text-gray-800">Welcome back, <?= $_SESSION['full_name'] ?>!</h1>
            <p class="text-gray-600">Manage your bookings and explore our rooms</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-calendar-check text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Total Bookings</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= count($bookings['bookings']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Confirmed</h3>
                        <p class="text-2xl font-bold text-gray-900">
                            <?= count(array_filter($bookings['bookings'], function($b) { return $b['status'] == 'confirmed'; })) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-800">Pending</h3>
                        <p class="text-2xl font-bold text-gray-900">
                            <?= count(array_filter($bookings['bookings'], function($b) { return $b['status'] == 'pending'; })) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="booking.php" class="bg-blue-600 text-white p-6 rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-plus text-2xl mr-4"></i>
                        <div>
                            <h3 class="font-bold text-lg">New Booking</h3>
                            <p class="text-blue-100">Book a new room</p>
                        </div>
                    </div>
                </a>
                
                <a href="rooms.php" class="bg-green-600 text-white p-6 rounded-lg shadow-md hover:bg-green-700 transition duration-300">
                    <div class="flex items-center">
                        <i class="fas fa-bed text-2xl mr-4"></i>
                        <div>
                            <h3 class="font-bold text-lg">Browse Rooms</h3>
                            <p class="text-green-100">View available rooms</p>
                        </div>
                    </div>
                </a>
                
                <a href="dashboard.php#bookings" class="bg-purple-600 text-white p-6 rounded-lg shadow-md hover:bg-purple-700 transition duration-300">
                    <div class="flex items-center">
                        <i class="fas fa-history text-2xl mr-4"></i>
                        <div>
                            <h3 class="font-bold text-lg">Booking History</h3>
                            <p class="text-purple-100">View past bookings</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div id="bookings">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Your Bookings</h2>
                <a href="booking.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-plus mr-1"></i> New Booking
                </a>
            </div>
            
            <?php if (empty($bookings['bookings'])): ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No bookings yet</h3>
                    <p class="text-gray-500 mb-4">Start by booking your first room!</p>
                    <a href="rooms.php" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Browse Rooms
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nights</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($bookings['bookings'] as $booking): ?>
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-gray-100 text-gray-800'
                                    ];
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= $booking['room_number'] ?></div>
                                            <div class="text-sm text-gray-500"><?= $booking['room_type'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= date('M d, Y', strtotime($booking['check_in_date'])) ?></div>
                                            <div class="text-sm text-gray-500">to <?= date('M d, Y', strtotime($booking['check_out_date'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= $booking['total_nights'] ?> nights
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            $<?= number_format($booking['total_price'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_colors[$booking['status']] ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                                <?php
                                                $today = date('Y-m-d');
                                                if ($booking['check_in_date'] > $today):
                                                ?>
                                                    <button onclick="cancelBooking(<?= $booking['id'] ?>)" 
                                                            class="text-red-600 hover:text-red-900 mr-3">
                                                        <i class="fas fa-times-circle"></i> Cancel
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <a href="#" class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Hotel Paradise. All rights reserved.</p>
                <p class="text-gray-400 mt-2">Logged in as <?= $_SESSION['email'] ?></p>
            </div>
        </div>
    </footer>

    <script>
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                window.location.href = `cancel_booking.php?id=${bookingId}`;
            }
        }
    </script>
</body>
</html>