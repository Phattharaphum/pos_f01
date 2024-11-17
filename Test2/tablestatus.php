<?php
session_start();
include('connection.php');


// ดึงข้อมูลสถานะโต๊ะ
$stmt = $conn->prepare("SELECT * FROM tab_table");
$stmt->execute();
$tables = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Table Status</title>
</head>
<body>
    <h1>Table Status</h1>
    <a href="addtable.php" style="display: inline-block; margin-bottom: 20px; padding: 10px; background-color: green; color: white; text-decoration: none;">เพิ่มโต๊ะ</a>
    <table border="1">
        <tr>
            <th>Table Number</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($table = $tables->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $table['table_number']; ?></td>
                <td><?php echo $table['table_status'] == 1 ? "Occupied" : "Available"; ?></td>
                <td>
                    <a href="orderde.php?table=<?php echo $table['table_number']; ?>">View Order</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
