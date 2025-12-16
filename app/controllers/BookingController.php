<?php
namespace App\Controllers;

require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Room.php';
require_once __DIR__ . '/../helpers/sanitize.php';

use App\Models\Booking;
use App\Models\Room;
use App\Helpers\Sanitize;

class BookingController {
    private $bookingModel;
    private $roomModel;

    public function __construct() {
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
    }

    public function createBooking($user_id, $data) {
        $errors = [];

        // Validate required fields
        if (empty($data['room_id'])) {
            $errors[] = "Room selection is required";
        }

        if (empty($data['check_in_date']) || empty($data['check_out_date'])) {
            $errors[] = "Dates are required";
        }

        if (empty($data['guests']) || !is_numeric($data['guests']) || $data['guests'] <= 0) {
            $errors[] = "Valid number of guests is required";
        }

        // Validate dates
        $today = date('Y-m-d');
        if ($data['check_in_date'] < $today) {
            $errors[] = "Check-in date cannot be in the past";
        }

        if ($data['check_out_date'] <= $data['check_in_date']) {
            $errors[] = "Check-out date must be after check-in date";
        }

        // Check room availability
        if (!empty($data['room_id'])) {
            $isAvailable = $this->roomModel->isRoomAvailable(
                $data['room_id'],
                $data['check_in_date'],
                $data['check_out_date']
            );

            if (!$isAvailable) {
                $errors[] = "Selected room is not available for the chosen dates";
            }

            // Check max occupancy
            $room = $this->roomModel->getById($data['room_id']);
            if ($room && $data['guests'] > $room['max_occupancy']) {
                $errors[] = "Number of guests exceeds room capacity (max: {$room['max_occupancy']})";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Calculate total price
        $check_in_obj = new \DateTime($data['check_in_date']);
        $check_out_obj = new \DateTime($data['check_out_date']);
        $interval = $check_in_obj->diff($check_out_obj);
        $total_nights = $interval->days;

        $total_price = $this->bookingModel->calculateTotalPrice(
            $data['room_id'],
            $data['check_in_date'],
            $data['check_out_date']
        );

        // Create booking
        $this->bookingModel->user_id = $user_id;
        $this->bookingModel->room_id = Sanitize::input($data['room_id']);
        $this->bookingModel->check_in_date = Sanitize::input($data['check_in_date']);
        $this->bookingModel->check_out_date = Sanitize::input($data['check_out_date']);
        $this->bookingModel->total_nights = $total_nights;
        $this->bookingModel->total_price = $total_price;
        $this->bookingModel->guests = Sanitize::input($data['guests']);
        $this->bookingModel->special_requests = Sanitize::input($data['special_requests'] ?? '');
        $this->bookingModel->status = 'pending';
        $this->bookingModel->payment_status = 'pending';

        if ($this->bookingModel->create()) {
            return ['success' => true, 'message' => 'Booking created successfully! Status: Pending'];
        } else {
            return ['success' => false, 'errors' => ['Failed to create booking']];
        }
    }

    public function getUserBookings($user_id) {
        $bookings = $this->bookingModel->getUserBookings($user_id);
        return ['success' => true, 'bookings' => $bookings];
    }

    public function getAllBookings() {
        $bookings = $this->bookingModel->getAllBookings();
        return ['success' => true, 'bookings' => $bookings];
    }

    public function updateBookingStatus($booking_id, $status) {
        $allowed_statuses = ['pending', 'confirmed', 'cancelled'];
        
        if (!in_array($status, $allowed_statuses)) {
            return ['success' => false, 'errors' => ['Invalid status']];
        }

        $this->bookingModel->id = $booking_id;
        $this->bookingModel->status = $status;
        
        if ($status == 'confirmed') {
            $this->bookingModel->payment_status = 'paid';
        } elseif ($status == 'cancelled') {
            $this->bookingModel->payment_status = 'refunded';
        }

        if ($this->bookingModel->update()) {
            return ['success' => true, 'message' => "Booking {$status} successfully"];
        } else {
            return ['success' => false, 'errors' => ['Failed to update booking']];
        }
    }

    public function cancelBooking($user_id, $booking_id) {
        // Check if user owns this booking
        $booking = $this->bookingModel->getById($booking_id);
        
        if (!$booking) {
            return ['success' => false, 'errors' => ['Booking not found']];
        }

        if ($booking['user_id'] != $user_id) {
            return ['success' => false, 'errors' => ['Unauthorized action']];
        }

        // Check if booking can be cancelled (before check-in)
        $today = date('Y-m-d');
        if ($booking['check_in_date'] <= $today) {
            return ['success' => false, 'errors' => ['Cannot cancel booking on or after check-in date']];
        }

        if ($this->bookingModel->cancel($booking_id)) {
            return ['success' => true, 'message' => 'Booking cancelled successfully'];
        } else {
            return ['success' => false, 'errors' => ['Failed to cancel booking']];
        }
    }

    public function getBookingStatistics() {
        $stats = $this->bookingModel->getStatistics();
        return ['success' => true, 'stats' => $stats];
    }

    public function getRecentBookings($limit = 5) {
        $bookings = $this->bookingModel->getRecentBookings($limit);
        return ['success' => true, 'bookings' => $bookings];
    }
}