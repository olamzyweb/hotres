<?php
namespace App\Models;

require_once __DIR__ . '/../config/Database.php';

use App\Config\Database;

class Room {
    private $conn;
    private $table = "rooms";

    public $id;
    public $room_number;
    public $room_type;
    public $price_per_night;
    public $description;
    public $max_occupancy;
    public $amenities;
    public $status;
    public $image_url;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET room_number = :room_number, room_type = :room_type, 
                      price_per_night = :price_per_night, description = :description,
                      max_occupancy = :max_occupancy, amenities = :amenities,
                      status = :status, image_url = :image_url";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":room_number", $this->room_number);
        $stmt->bindParam(":room_type", $this->room_type);
        $stmt->bindParam(":price_per_night", $this->price_per_night);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":max_occupancy", $this->max_occupancy);
        $stmt->bindParam(":amenities", $this->amenities);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":image_url", $this->image_url);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET room_number = :room_number, room_type = :room_type, 
                      price_per_night = :price_per_night, description = :description,
                      max_occupancy = :max_occupancy, amenities = :amenities,
                      status = :status, image_url = :image_url
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":room_number", $this->room_number);
        $stmt->bindParam(":room_type", $this->room_type);
        $stmt->bindParam(":price_per_night", $this->price_per_night);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":max_occupancy", $this->max_occupancy);
        $stmt->bindParam(":amenities", $this->amenities);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":image_url", $this->image_url);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY room_number";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAvailableRooms($check_in, $check_out, $room_type = null, $max_price = null) {
        $query = "SELECT r.* FROM " . $this->table . " r
                  WHERE r.status != 'maintenance'
                  AND r.id NOT IN (
                      SELECT b.room_id FROM bookings b
                      WHERE b.status IN ('confirmed', 'pending')
                      AND (
                          (b.check_in_date <= :check_out AND b.check_out_date >= :check_in)
                      )
                  )";

        $params = [
            ':check_in' => $check_in,
            ':check_out' => $check_out
        ];

        if ($room_type) {
            $query .= " AND r.room_type = :room_type";
            $params[':room_type'] = $room_type;
        }

        if ($max_price) {
            $query .= " AND r.price_per_night <= :max_price";
            $params[':max_price'] = $max_price;
        }

        $query .= " ORDER BY r.price_per_night";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function isRoomAvailable($room_id, $check_in, $check_out, $exclude_booking_id = null) {
        $query = "SELECT COUNT(*) as count FROM bookings 
                  WHERE room_id = :room_id 
                  AND status IN ('confirmed', 'pending')
                  AND check_in_date < :check_out 
                  AND check_out_date > :check_in";

        if ($exclude_booking_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_id", $room_id);
        $stmt->bindParam(":check_in", $check_in);
        $stmt->bindParam(":check_out", $check_out);
        
        if ($exclude_booking_id) {
            $stmt->bindParam(":exclude_id", $exclude_booking_id);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] == 0;
    }

    public function getRoomTypes() {
        $query = "SELECT DISTINCT room_type FROM " . $this->table . " ORDER BY room_type";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStatistics() {
        $query = "SELECT 
                    COUNT(*) as total_rooms,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms,
                    SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked_rooms,
                    AVG(price_per_night) as avg_price
                  FROM " . $this->table;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }
}