<?php
session_start();
include('connection.php');



// ดึงข้อมูลพนักงานจากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM tab_employee");
$stmt->execute();
$employees = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Employees</title>
    <style>
        .status-active {
            color: green;
        }
        .status-inactive {
            color: red;
        }
    </style>
</head>
<body>
<a href="managermenu.php">จัดการรายการอาหาร</a>
    <h1>จัดการพนักงาน</h1>
    <a href="editemployee.php" style="margin-bottom: 20px; display: inline-block;">เพิ่มพนักงาน</a>
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr>
            <th>ชื่อ</th>
            <th>สถานะ</th>
            <th>การกระทำ</th>
        </tr>
        <?php while ($employee = $employees->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                <td>
                    <span class="<?php echo $employee['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo $employee['status'] == 1 ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td>
                    <a href="editemployee.php?id=<?php echo $employee['id']; ?>">แก้ไข</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
