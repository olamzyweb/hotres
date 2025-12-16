<?php
require_once '../../app/helpers/auth.php';
require_once '../../app/controllers/RoomController.php';

use App\Helpers\Auth;
use App\Controllers\RoomController;

Auth::requireAdmin();

$roomController = new RoomController();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        $result = $roomController->createRoom($_POST);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = implode('<br>', $result['errors']);
        }
    } elseif (isset($_POST['update_room'])) {
        $result = $roomController->updateRoom($_POST['id'], $_POST);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = implode('<br>', $result['errors']);
        }
    }
}

if (isset($_GET['delete'])) {
    $result = $roomController->deleteRoom($_GET['delete']);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = implode('<br>', $result['errors']);
    }
}

// Get all rooms
$roomsResult = $roomController->getAllRooms();
$rooms = $roomsResult['success'] ? $roomsResult['rooms'] : [];

// Get room types
$roomTypes = $roomController->getRoomTypes();

// For edit mode
$editRoom = null;
if (isset($_GET['edit'])) {
    $room = $roomController->getRoomById($_GET['edit']);
    if ($room) {
        $editRoom = $room;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Admin</title>
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
                            <a href="rooms.php" class="bg-gray-900 text-white px-3 py-2 rounded-md text-sm font-medium">
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

        <!-- Room Form -->
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                <?= $editRoom ? 'Edit Room' : 'Add New Room' ?>
            </h2>
            <form method="POST" action="">
                <?php if ($editRoom): ?>
                    <input type="hidden" name="id" value="<?= $editRoom['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Number *</label>
                        <input type="text" name="room_number" 
                               value="<?= $editRoom ? $editRoom['room_number'] : '' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Type *</label>
                        <select name="room_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select Type</option>
                            <?php foreach ($roomTypes['types'] as $type): ?>
                                <option value="<?= $type['room_type'] ?>" 
                                    <?= ($editRoom && $editRoom['room_type'] == $type['room_type']) ? 'selected' : '' ?>>
                                    <?= $type['room_type'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price per Night ($) *</label>
                        <input type="number" name="price_per_night" step="0.01" min="0" 
                               value="<?= $editRoom ? $editRoom['price_per_night'] : '' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Occupancy *</label>
                        <input type="number" name="max_occupancy" min="1" max="10" 
                               value="<?= $editRoom ? $editRoom['max_occupancy'] : '2' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= $editRoom ? $editRoom['description'] : '' ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amenities</label>
                        <input type="text" name="amenities" 
                               value="<?= $editRoom ? $editRoom['amenities'] : 'WiFi, TV, AC' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Comma separated amenities">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="available" <?= ($editRoom && $editRoom['status'] == 'available') ? 'selected' : '' ?>>Available</option>
                            <option value="booked" <?= ($editRoom && $editRoom['status'] == 'booked') ? 'selected' : '' ?>>Booked</option>
                            <option value="maintenance" <?= ($editRoom && $editRoom['status'] == 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                        <input type="url" name="image_url" 
                               value="<?= $editRoom ? $editRoom['image_url'] : '' ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://example.com/image.jpg">
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-4">
                    <?php if ($editRoom): ?>
                        <a href="rooms.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" name="update_room" 
                                class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                            Update Room
                        </button>
                    <?php else: ?>
                        <button type="submit" name="add_room" 
                                class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                            Add Room
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Rooms List -->
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">All Rooms (<?= count($rooms) ?>)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price/Night</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Guests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= $room['room_number'] ?></div>
                                    <?php if ($room['image_url']): ?>
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-image mr-1"></i> Has image
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $room['room_type'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    $<?= number_format($room['price_per_night'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $room['max_occupancy'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_colors = [
                                        'available' => 'bg-green-100 text-green-800',
                                        'booked' => 'bg-red-100 text-red-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800'
                                    ];
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_colors[$room['status']] ?>">
                                        <?= ucfirst($room['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($room['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="?edit=<?= $room['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?delete=<?= $room['id'] ?>" 
                                       onclick="return confirm('Are you sure you want to delete this room?')"
                                       class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
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