<?php
include 'connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $delivery_method = $_POST['delivery_method'];

    $sql = "INSERT INTO notifications (title, content, delivery_method) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $content, $delivery_method);
    $stmt->execute();

    if ($delivery_method == 'line' || $delivery_method == 'both') {
        // Send notification to Line group (need to integrate Line API)
        sendLineNotification($title, $content);
    }

    // echo "Notification sent successfully!";
}

function sendLineNotification($title, $content) {
    // Use Line API to send the message
    $line_token = 'O93KpzscBhDpot5oDQCwUzm5BQoD6ksd1feZ8052Tl7';
    $message = $title . "\n" . $content;

    $data = [
        'message' => $message,
    ];

    $ch = curl_init("https://notify-api.line.me/api/notify");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $line_token]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Create Notification</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Create Notification</h1>
    <form action="create_notification.php" method="post">
        <div class="form-group">
            <label>Title</label>
            <input type="text" class="form-control" name="title" required>
        </div>
        <div class="form-group">
            <label>Content</label>
            <textarea class="form-control" name="content" rows="5" required></textarea>
        </div>
        <div class="form-group">
            <label>Delivery Method</label>
            <select class="form-control" name="delivery_method">
                <option value="website">Website</option>
                <option value="line">Line</option>
                <option value="both">Both</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Send Notification</button>
    </form>
</div>
</body>
</html>
