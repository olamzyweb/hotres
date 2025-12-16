<?php
namespace App\Controllers;

require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../helpers/sanitize.php';

use App\Models\Room;
use App\Helpers\Sanitize;

class RoomController {
    private $roomModel;

    public function __construct() {
        $this->roomModel = new Room();
    }

    public function getAvailableRooms($check_in, $check_out, $room_type = null, $max_price = null) {
        // Validate dates
        $today = date('Y-m-d');
        if (empty($check_in) || empty($check_out)) {
            return ['success' => false, 'errors' => ['Check-in and check-out dates are required']];
        }

        if ($check_in < $today) {
            return ['success' => false, 'errors' => ['Check-in date cannot be in the past']];
        }

        if ($check_out <= $check_in) {
            return ['success' => false, 'errors' => ['Check-out date must be after check-in date']];
        }

        // Calculate minimum stay (1 night)
        $check_in_obj = new \DateTime($check_in);
        $check_out_obj = new \DateTime($check_out);
        $interval = $check_in_obj->diff($check_out_obj);
        if ($interval->days < 1) {
            return ['success' => false, 'errors' => ['Minimum stay is 1 night']];
        }

        $rooms = $this->roomModel->getAvailableRooms($check_in, $check_out, $room_type, $max_price);
        
        return [
            'success' => true,
            'rooms' => $rooms,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'total_nights' => $interval->days
        ];
    }

    public function getAllRooms() {
        $rooms = $this->roomModel->getAll();
        return ['success' => true, 'rooms' => $rooms];
    }

    public function getRoomById($id) {
        return $this->roomModel->getById($id);
    }

    public function getRoomTypes() {
        $types = $this->roomModel->getRoomTypes();
        return ['success' => true, 'types' => $types];
    }

    public function createRoom($data) {
        $errors = $this->validateRoomData($data);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->roomModel->room_number = Sanitize::input($data['room_number']);
        $this->roomModel->room_type = Sanitize::input($data['room_type']);
        $this->roomModel->price_per_night = Sanitize::input($data['price_per_night']);
        $this->roomModel->description = Sanitize::input($data['description']);
        $this->roomModel->max_occupancy = Sanitize::input($data['max_occupancy']);
        $this->roomModel->amenities = Sanitize::input($data['amenities']);
        $this->roomModel->status = Sanitize::input($data['status']);
        $this->roomModel->image_url = Sanitize::input($data['image_url']);

        if ($this->roomModel->create()) {
            return ['success' => true, 'message' => 'Room created successfully'];
        } else {
            return ['success' => false, 'errors' => ['Failed to create room']];
        }
    }

    public function updateRoom($id, $data) {
        $errors = $this->validateRoomData($data, $id);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->roomModel->id = $id;
        $this->roomModel->room_number = Sanitize::input($data['room_number']);
        $this->roomModel->room_type = Sanitize::input($data['room_type']);
        $this->roomModel->price_per_night = Sanitize::input($data['price_per_night']);
        $this->roomModel->description = Sanitize::input($data['description']);
        $this->roomModel->max_occupancy = Sanitize::input($data['max_occupancy']);
        $this->roomModel->amenities = Sanitize::input($data['amenities']);
        $this->roomModel->status = Sanitize::input($data['status']);
        $this->roomModel->image_url = Sanitize::input($data['image_url']);

        if ($this->roomModel->update()) {
            return ['success' => true, 'message' => 'Room updated successfully'];
        } else {
            return ['success' => false, 'errors' => ['Failed to update room']];
        }
    }

    public function deleteRoom($id) {
        if ($this->roomModel->delete($id)) {
            return ['success' => true, 'message' => 'Room deleted successfully'];
        } else {
            return ['success' => false, 'errors' => ['Failed to delete room']];
        }
    }

    public function getRoomStatistics() {
        $stats = $this->roomModel->getStatistics();
        return ['success' => true, 'stats' => $stats];
    }

    private function validateRoomData($data, $room_id = null) {
        $errors = [];

        if (empty($data['room_number'])) {
            $errors[] = "Room number is required";
        }

        if (empty($data['room_type'])) {
            $errors[] = "Room type is required";
        }

        if (empty($data['price_per_night']) || !is_numeric($data['price_per_night']) || $data['price_per_night'] <= 0) {
            $errors[] = "Valid price per night is required";
        }

        if (empty($data['max_occupancy']) || !is_numeric($data['max_occupancy']) || $data['max_occupancy'] <= 0) {
            $errors[] = "Valid max occupancy is required";
        }

        if (empty($data['status'])) {
            $errors[] = "Status is required";
        }

        return $errors;
    }
}