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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #d9534f;
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            margin: 10px 0;
            text-decoration: none;
            background-color: #5bc0de;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
        }

        a:hover {
            background-color: #31b0d5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f8f8;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-active {
            color: #5cb85c;
            font-weight: bold;
        }

        .status-inactive {
            color: #d9534f;
            font-weight: bold;
        }

        .actions a {
            margin: 0 5px;
            color: #fff;
            background-color: #5bc0de;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 13px;
        }

        .actions a:hover {
            background-color: #31b0d5;
        }
    </style>
</head>
<body>
    <a href="managermenu.php">จัดการรายการอาหาร</a>
    <h1>จัดการพนักงาน</h1>
    <a href="editemployee.php">เพิ่มพนักงาน</a>
    <table>
        <thead>
            <tr>
                <th>ชื่อ</th>
                <th>สถานะ</th>
                <th>การกระทำ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($employee = $employees->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                    <td>
                        <span class="<?php echo $employee['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $employee['status'] == 1 ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="editemployee.php?id=<?php echo $employee['id']; ?>">แก้ไข</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>

