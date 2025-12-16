<?php
require_once '../app/helpers/auth.php';
require_once '../app/controllers/BookingController.php';

use App\Helpers\Auth;
use App\Controllers\BookingController;

Auth::requireLogin();

$bookingController = new BookingController();
$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $result = $bookingController->cancelBooking($user_id, $booking_id);
    
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = implode('<br>', $result['errors']);
    }
}

header('Location: dashboard.php');
exit();