<?php
session_start();
include('connection.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = $_POST['table_number'];

    // ตรวจสอบว่าโต๊ะมีอยู่แล้วหรือไม่
    $stmt = $conn->prepare("SELECT * FROM tab_table WHERE table_number = ?");
    $stmt->bind_param("i", $table_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Table already exists!";
    } else {
        // เพิ่มโต๊ะใหม่
        $stmt = $conn->prepare("INSERT INTO tab_table (table_number, table_status) VALUES (?, 0)");
        $stmt->bind_param("i", $table_number);
        $stmt->execute();

        header("Location: tablestatus.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Table</title>
</head>
<body>
    <h1>Add New Table</h1>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form method="POST">
        <label>Table Number:</label>
        <input type="number" name="table_number" required>
        <button type="submit">Add Table</button>
    </form>
    <a href="tablestatus.php">Back to Table Status</a>
</body>
</html>
