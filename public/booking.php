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
$errors = [];
$success = '';

// Get room_id from query string if provided
$room_id = $_GET['room_id'] ?? null;
$check_in = $_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day'));
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+3 days'));

// Get room details if room_id is provided
$room = null;
if ($room_id) {
    $room = $roomController->getRoomById($room_id);
}

if (isset($_POST['submit_booking'])) {
    $data = [
        'room_id' => $_POST['room_id'],
        'check_in_date' => $_POST['check_in_date'],
        'check_out_date' => $_POST['check_out_date'],
        'guests' => $_POST['guests'],
        'special_requests' => $_POST['special_requests'] ?? ''
    ];
    
    $result = $bookingController->createBooking($user_id, $data);
    
    if ($result['success']) {
        $success = $result['message'];
        // Clear form data after successful booking
        $room_id = null;
        $check_in = date('Y-m-d', strtotime('+1 day'));
        $check_out = date('Y-m-d', strtotime('+3 days'));
        $room = null;
    } else {
        $errors = $result['errors'];
    }
}

// Get available rooms for date selection
$available_rooms = [];
if (!empty($_POST['check_availability'])) {
    $check_in = $_POST['check_in_date'] ?? $check_in;
    $check_out = $_POST['check_out_date'] ?? $check_out;
    $room_type = $_POST['room_type'] ?? null;
    
    $result = $roomController->getAvailableRooms($check_in, $check_out, $room_type);
    if ($result['success']) {
        $available_rooms = $result['rooms'];
    } else {
        $errors = array_merge($errors, $result['errors']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking - Hotel Reservation</title>
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
                            <a href="dashboard.php" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                            <a href="rooms.php" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-bed mr-1"></i> Rooms
                            </a>
                            <a href="booking.php" class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium">
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

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">New Booking</h1>
            <p class="text-gray-600">Book your stay at Hotel Paradise</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <?php foreach ($errors as $error): ?>
                    <p><i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
                <div class="mt-2">
                    <a href="dashboard.php" class="text-green-800 font-semibold hover:text-green-900">
                        View your bookings →
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Booking Form -->
        <div class="bg-white rounded-lg shadow-xl p-6">
            <form method="POST" action="">
                <!-- Step 1: Dates and Room Type -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">1. Select Dates</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date *</label>
                            <input type="date" name="check_in_date" id="check_in_date" 
                                   value="<?= $check_in ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Date *</label>
                            <input type="date" name="check_out_date" id="check_out_date" 
                                   value="<?= $check_out ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                            <select name="room_type" id="room_type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Types</option>
                                <?php 
                                $types = $roomController->getRoomTypes();
                                foreach ($types['types'] as $type): 
                                ?>
                                    <option value="<?= $type['room_type'] ?>"><?= $type['room_type'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="check_availability" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">
                            <i class="fas fa-search mr-2"></i> Check Availability
                        </button>
                    </div>
                </div>

                <!-- Step 2: Select Room -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">2. Select Room</h2>
                    
                    <?php if (!empty($available_rooms) || $room): ?>
                        <div class="space-y-4">
                            <?php 
                            $rooms_to_display = $room ? [$room] : $available_rooms;
                            foreach ($rooms_to_display as $room_item): 
                            ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:bg-blue-50 transition duration-200">
                                    <div class="flex items-center">
                                        <input type="radio" name="room_id" id="room_<?= $room_item['id'] ?>" 
                                               value="<?= $room_item['id'] ?>" 
                                               class="h-5 w-5 text-blue-600" 
                                               <?= ($room_id == $room_item['id']) ? 'checked' : '' ?> 
                                               required>
                                        <label for="room_<?= $room_item['id'] ?>" class="ml-4 flex-1">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h3 class="font-bold text-lg text-gray-800"><?= $room_item['room_number'] ?> - <?= $room_item['room_type'] ?></h3>
                                                    <p class="text-gray-600"><?= $room_item['description'] ?></p>
                                                    <div class="mt-2">
                                                        <span class="text-sm text-gray-500">
                                                            <i class="fas fa-user mr-1"></i> Max <?= $room_item['max_occupancy'] ?> guests
                                                        </span>
                                                        <span class="text-sm text-gray-500 ml-4">
                                                            <i class="fas fa-wifi mr-1"></i> <?= $room_item['amenities'] ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-2xl font-bold text-blue-600">
                                                        $<?= number_format($room_item['price_per_night'], 2) ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">per night</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (isset($_POST['check_availability'])): ?>
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded">
                            <i class="fas fa-exclamation-triangle mr-2"></i> No rooms available for selected dates. Please try different dates or room type.
                        </div>
                    <?php else: ?>
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                            <i class="fas fa-info-circle mr-2"></i> Select dates and click "Check Availability" to see available rooms.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Step 3: Booking Details -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">3. Booking Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Guests *</label>
                            <input type="number" name="guests" id="guests" min="1" max="10" value="1" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <p class="text-xs text-gray-500 mt-1">Maximum guests depends on room capacity</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
                            <textarea name="special_requests" id="special_requests" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Any special requests or requirements..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Summary -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">4. Booking Summary</h2>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div id="booking_summary" class="space-y-2">
                            <p class="text-gray-600">Select dates and room to see booking summary</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <a href="rooms.php" class="mr-4 px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" name="submit_booking" 
                            class="bg-blue-600 text-white px-8 py-3 rounded-md font-medium hover:bg-blue-700 transition duration-300">
                        <i class="fas fa-calendar-check mr-2"></i> Confirm Booking
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Information -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-bold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i> Need Help?</h3>
            <ul class="text-blue-700 space-y-1">
                <li>• All bookings require admin confirmation</li>
                <li>• You can cancel bookings up to 24 hours before check-in</li>
                <li>• Check-in time: 3:00 PM | Check-out time: 11:00 AM</li>
                <li>• Contact support for special requirements</li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Hotel Paradise. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum dates
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            const checkInInput = document.getElementById('check_in_date');
            const checkOutInput = document.getElementById('check_out_date');
            
            checkInInput.min = today;
            checkOutInput.min = tomorrowStr;
            
            checkInInput.addEventListener('change', updateSummary);
            checkOutInput.addEventListener('change', updateSummary);
            
            // Room selection change
            document.querySelectorAll('input[name="room_id"]').forEach(radio => {
                radio.addEventListener('change', updateSummary);
            });
            
            // Guests change
            document.getElementById('guests').addEventListener('input', updateSummary);
            
            function updateSummary() {
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                
                if (!checkIn || !checkOut) return;
                
                // Calculate nights
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                
                if (nights <= 0) {
                    document.getElementById('booking_summary').innerHTML = 
                        '<p class="text-red-600">Check-out date must be after check-in date</p>';
                    return;
                }
                
                // Get selected room
                const selectedRoom = document.querySelector('input[name="room_id"]:checked');
                const guests = document.getElementById('guests').value;
                
                let summaryHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Check-in</p>
                            <p class="font-semibold">${formatDate(checkIn)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Check-out</p>
                            <p class="font-semibold">${formatDate(checkOut)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Duration</p>
                            <p class="font-semibold">${nights} night${nights > 1 ? 's' : ''}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Guests</p>
                            <p class="font-semibold">${guests} guest${guests > 1 ? 's' : ''}</p>
                        </div>
                `;
                
                if (selectedRoom) {
                    // Get room price from label or data attribute
                    const roomLabel = document.querySelector(`label[for="${selectedRoom.id}"]`);
                    const priceMatch = roomLabel.textContent.match(/\$([\d,]+\.?\d*)/);
                    
                    if (priceMatch) {
                        const price = parseFloat(priceMatch[1].replace(',', ''));
                        const total = price * nights;
                        
                        summaryHTML += `
                            <div>
                                <p class="text-sm text-gray-500">Price per night</p>
                                <p class="font-semibold">$${price.toFixed(2)}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Cost</p>
                                <p class="font-semibold text-lg text-blue-600">$${total.toFixed(2)}</p>
                            </div>
                        `;
                    }
                }
                
                summaryHTML += '</div>';
                document.getElementById('booking_summary').innerHTML = summaryHTML;
            }
            
            function formatDate(dateStr) {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    month: 'short', 
                    day: 'numeric',
                    year: 'numeric'
                });
            }
            
            // Initialize summary
            updateSummary();
            
            // Update check-out minimum when check-in changes
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutInput.min = nextDay.toISOString().split('T')[0];
                
                if (new Date(checkOutInput.value) <= checkInDate) {
                    checkOutInput.value = nextDay.toISOString().split('T')[0];
                }
                
                updateSummary();
            });
        });
    </script>
</body>
</html>