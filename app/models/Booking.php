<?php
namespace App\Models;

require_once __DIR__ . '/../config/Database.php';

use App\Config\Database;

class Booking {
    private $conn;
    private $table = "bookings";

    public $id;
    public $user_id;
    public $room_id;
    public $check_in_date;
    public $check_out_date;
    public $total_nights;
    public $total_price;
    public $guests;
    public $special_requests;
    public $status;
    public $payment_status;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = :user_id, room_id = :room_id, 
                      check_in_date = :check_in_date, check_out_date = :check_out_date,
                      total_nights = :total_nights, total_price = :total_price,
                      guests = :guests, special_requests = :special_requests,
                      status = :status, payment_status = :payment_status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":room_id", $this->room_id);
        $stmt->bindParam(":check_in_date", $this->check_in_date);
        $stmt->bindParam(":check_out_date", $this->check_out_date);
        $stmt->bindParam(":total_nights", $this->total_nights);
        $stmt->bindParam(":total_price", $this->total_price);
        $stmt->bindParam(":guests", $this->guests);
        $stmt->bindParam(":special_requests", $this->special_requests);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_status", $this->payment_status);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET status = :status, payment_status = :payment_status,
                      special_requests = :special_requests, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_status", $this->payment_status);
        $stmt->bindParam(":special_requests", $this->special_requests);

        return $stmt->execute();
    }

    public function cancel($id) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id AND status IN ('pending', 'confirmed')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getById($id) {
        $query = "SELECT b.*, r.room_number, r.room_type, r.price_per_night, 
                         u.full_name, u.email, u.phone
                  FROM " . $this->table . " b
                  JOIN rooms r ON b.room_id = r.id
                  JOIN users u ON b.user_id = u.id
                  WHERE b.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getUserBookings($user_id) {
        $query = "SELECT b.*, r.room_number, r.room_type, r.price_per_night, r.image_url
                  FROM " . $this->table . " b
                  JOIN rooms r ON b.room_id = r.id
                  WHERE b.user_id = :user_id
                  ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllBookings() {
        $query = "SELECT b.*, r.room_number, r.room_type, r.price_per_night,
                         u.full_name, u.email
                  FROM " . $this->table . " b
                  JOIN rooms r ON b.room_id = r.id
                  JOIN users u ON b.user_id = u.id
                  ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(total_price) as total_revenue,
                    AVG(total_nights) as avg_nights
                  FROM " . $this->table;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getRecentBookings($limit = 5) {
        $query = "SELECT b.*, r.room_number, r.room_type, u.full_name
                  FROM " . $this->table . " b
                  JOIN rooms r ON b.room_id = r.id
                  JOIN users u ON b.user_id = u.id
                  ORDER BY b.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function calculateTotalPrice($room_id, $check_in, $check_out) {
        $roomModel = new Room();
        $room = $roomModel->getById($room_id);
        
        if (!$room) {
            return 0;
        }

        $check_in_obj = new \DateTime($check_in);
        $check_out_obj = new \DateTime($check_out);
        $interval = $check_in_obj->diff($check_out_obj);
        $total_nights = $interval->days;

        return $room['price_per_night'] * $total_nights;
    }
}