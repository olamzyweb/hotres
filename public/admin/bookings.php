<?php
require_once '../../app/helpers/auth.php';
require_once '../../app/controllers/BookingController.php';

use App\Helpers\Auth;
use App\Controllers\BookingController;

Auth::requireAdmin();

$bookingController = new BookingController();
$message = '';
$error = '';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $booking_id = $_GET['id'];
    
    $allowed_actions = ['confirm', 'cancel', 'delete'];
    if (in_array($action, $allowed_actions)) {
        if ($action === 'confirm' || $action === 'cancel') {
            $status = ($action === 'cancel') ? 'cancelled' : $action . 'ed';
            $result = $bookingController->updateBookingStatus($booking_id, $status);
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = implode('<br>', $result['errors']);
            }
        }
    }
}

// Get all bookings
$bookingsResult = $bookingController->getAllBookings();
$bookings = $bookingsResult['success'] ? $bookingsResult['bookings'] : [];

// Get statistics
$stats = $bookingController->getBookingStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Admin</title>
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
                            <a href="dashboard.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                            <a href="rooms.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-bed mr-1"></i> Rooms
                            </a>
                            <a href="bookings.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">
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
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i> <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['stats']['total_bookings'] ?? 0 ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Confirmed</p>
                    <p class="text-2xl font-bold text-green-600"><?= $stats['stats']['confirmed_bookings'] ?? 0 ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600"><?= $stats['stats']['pending_bookings'] ?? 0 ?></p>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-center">
                    <p class="text-sm text-gray-500">Revenue</p>
                    <p class="text-2xl font-bold text-blue-600">$<?= number_format($stats['stats']['total_revenue'] ?? 0, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">All Bookings (<?= count($bookings) ?>)</h2>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booked On</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $status_colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'completed' => 'bg-gray-100 text-gray-800'
                            ];
                            
                            $payment_colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'paid' => 'bg-green-100 text-green-800',
                                'refunded' => 'bg-red-100 text-red-800'
                            ];
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= $booking['full_name'] ?></div>
                                    <div class="text-sm text-gray-500"><?= $booking['email'] ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $booking['room_number'] ?> (<?= $booking['room_type'] ?>)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= date('M d', strtotime($booking['check_in_date'])) ?></div>
                                    <div class="text-sm text-gray-500">to <?= date('M d', strtotime($booking['check_out_date'])) ?></div>
                                    <div class="text-xs text-gray-500"><?= $booking['total_nights'] ?> nights</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    $<?= number_format($booking['total_price'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_colors[$booking['status']] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $payment_colors[$booking['payment_status']] ?? 'bg-gray-100 text-gray-800' ?>">
                                        <?= ucfirst($booking['payment_status'] ?? 'unknown') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($booking['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($booking['status'] == 'pending'): ?>
                                        <a href="?action=confirm&id=<?= $booking['id'] ?>" 
                                           class="text-green-600 hover:text-green-900 mr-3"
                                           onclick="return confirm('Confirm this booking?')">
                                            <i class="fas fa-check"></i> Confirm
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                        <a href="?action=cancel&id=<?= $booking['id'] ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Cancel this booking?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Hotel Paradise Admin Panel. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>