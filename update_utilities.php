<?php
include 'connect.php';
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบและเป็น Admin หรือไม่
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่ามีการส่งค่า utility_id มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['utility_id'])) {
    $utilityId = $_POST['utility_id'];
    $waterBill = $_POST['water_bill'];
    $electricityBill = $_POST['electricity_bill'];

    // เตรียมคำสั่ง SQL สำหรับอัปเดตข้อมูล
    $stmt = $conn->prepare("UPDATE utility_bills SET water_bill = ?, electricity_bill = ? WHERE id = ?");
    $stmt->bind_param("ddi", $waterBill, $electricityBill, $utilityId);

    // ตรวจสอบว่าอัปเดตสำเร็จหรือไม่
    if ($stmt->execute()) {
        // ถ้าอัปเดตสำเร็จ เปลี่ยนเส้นทางไปยังหน้าก่อนหน้าและแสดงข้อความสำเร็จ
        $_SESSION['success_message'] = "Utility bill updated successfully!";
    } else {
        // ถ้าเกิดข้อผิดพลาด แสดงข้อความข้อผิดพลาด
        $_SESSION['error_message'] = "Error updating utility bill: " . $conn->error;
    }

    // ปิดการเชื่อมต่อ
    $stmt->close();
} else {
    // ถ้าไม่มีการส่งข้อมูลให้กลับไปที่หน้าหลัก
    $_SESSION['error_message'] = "Invalid request.";
}

// เปลี่ยนเส้นทางกลับไปยังหน้าที่แสดงบิล
header("Location: view_utilities.php");
exit();
?>
