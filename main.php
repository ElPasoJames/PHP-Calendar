<?php
//START USER CALENDAR UPDATE
// Get the start date, end date, and availability entered bu User
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$availability = $_POST['availability'];

// Update the SQL database for time availability.
$sql = "UPDATE calendars SET start_date = :start_date, end_date = :end_date, availability = :availability WHERE id = 1";
$stmt = $db->prepare($sql);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->bindParam(':availability', $availability);
$stmt->execute();

// Update the hourly slots into the SQL DB.
for ($i = 0; $i <= 23; $i++) {
    $start_time = $i * 30;
    $end_time = ($i + 1) * 30;
    $availability_hour = $_POST['availability_hour_' . $i];

    $sql = "UPDATE hourly_slots SET availability = :availability WHERE start_time = :start_time AND end_time = :end_time";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':availability', $availability_hour);
    $stmt->bindParam(':start_time', $start_time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->execute();
}

// Redirect the user to the calendar page.
header('Location: /calendar');
/////END USER UPDATE////

//START CUSTOMER CALENDAR UPDATE
$user_id = $_POST['user_id'];
// Get the appointment data from the database.
$sql = "SELECT * FROM appointments WHERE user_id = 1";
$stmt = $db->prepare($sql);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$appointments = $stmt->fetchAll();

foreach ($appointments as $appointment) {
    // Get the customer information, Customer must have an account in DB
    $customer_id = $appointment['customer_id'];
    $sql = "SELECT name, phone_number FROM customers WHERE id = :customer_id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
    $customer = $stmt->fetch();

    echo '<tr>';
    echo '<td>' . $appointment['appointment_id'] . '</td>';
    echo '<td>' . $appointment['start_time'] . '</td>';
    echo '<td>' . $appointment['end_time'] . '</td>';
    echo '<td>' . $customer['name'] . '</td>';
    echo '<td>' . $customer['phone_number'] . '</td>';
    echo '</tr>';

if ($appointments[0]['created_at'] == $appointments[0]['updated_at']) {
    // The appointment is new, so send an email notification to the user.
    $user_email = $appointments[0]['customer_email'];
    $subject = "You have a new appointment";
    $body = "You have a new appointment with $customer at $appointment_time on $appointment_date.";
    mail($user_email, $subject, $body);

    // Also send an SMS notification to the user.
    $user_phone_number = $appointments[0]['customer_phone_number'];
    $message = "You have a new appointment with $customer at $appointment_time on $appointment_date.";
    sendSMS($user_phone_number, $message);
}

//Allow User to delete Customer Appointment
// Get the appointment ID from the form.
$appointment_id = $_POST['appointment_id'];

// Update the SQL database to mark the appointment as cancelled.
$sql = "UPDATE appointments SET cancelled = 1 WHERE id = :appointment_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':appointment_id', $appointment_id);
$stmt->execute()

// Check if the appointment is cancelled.
if ($stmt->rowCount() == 1) {
    // Send an email notification to the user.
    $user_email = $appointments[0]['customer_email'];
    $subject = "Your appointment has been cancelled";
    $body = "Your appointment with $customer has been cancelled $appointment_time on $appointment_date..";
    mail($user_email, $subject, $body);

    // Also send an SMS notification to the user.
    $user_phone_number = $appointments[0]['customer_phone_number'];
    $message = "Your appointment has been canceled or rescheduled with $customer at $appointment_time on $appointment_date.";
    sendSMS($user_phone_number, $message);
}


// Display the appointment data in the calendar management page.
foreach ($appointments as $appointment) {
    echo '<tr>';
    echo '<td>' . $appointment['appointment_id'] . '</td>';
    echo '<td>' . $appointment['start_time'] . '</td>';
    echo '<td>' . $appointment['end_time'] . '</td>';
    echo '<td>' . $appointment['customer_name'] . '</td>';
    echo '<td>' . $appointment['customer_phone_number'] . '</td>';
    echo '</tr>';
}

?>

