<?php
session_start();
include('connection.php');

// ตรวจสอบว่า table อยู่ใน session หรือไม่
if (!isset($_SESSION['table'])) {
    die("No table specified.");
}
$table_number = $_SESSION['table'];

// ดึงข้อมูลคำสั่งซื้อจากฐานข้อมูล
$stmt = $conn->prepare("
    SELECT 
        od.id AS order_detail_id,
        o.table_number,
        o.order_status,
        od.menu_id,
        od.quantity,
        od.price,
        od.menu_se,
        od.substatus,
        m.menu_name
    FROM tab_order o
    JOIN tab_order_details od ON o.order_id = od.order_id
    JOIN tab_menu m ON od.menu_id = m.menu_id
    WHERE o.table_number = ? AND o.order_status IN (0, 1)
    ORDER BY o.order_id DESC
");
$stmt->bind_param("i", $table_number);
$stmt->execute();
$result = $stmt->get_result();

// เก็บข้อมูลรายการอาหาร
$orders = [];
$total_price = 0;
while ($row = $result->fetch_assoc()) {
    // คำนวณเฉพาะเมนูที่มี substatus = 1 (ยืนยันแล้ว)
    if ($row['substatus'] == 1) {
        $total_price += $row['price'] * $row['quantity'];
    }
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ordered Menu</title>
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
        }

        a:hover {
            background-color: #31b0d5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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

        td small {
            font-size: 12px;
            color: #666;
        }

        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .status-confirmed {
            background-color: #5cb85c;
            color: white;
        }

        .status-cancelled {
            background-color: #d9534f;
            color: white;
        }

        .status-pending {
            background-color: #f0ad4e;
            color: white;
        }

        h3 {
            text-align: right;
            margin-top: 20px;
            color: #333;
        }
    </style>
</head>
<body>
    <h1>รายการอาหารที่สั่งไป</h1>
    <a href="menulist.php?table=<?php echo $table_number ?>">ย้อนกลับ</a>
    <?php if (empty($orders)) { ?>
        <p style="text-align: center; font-size: 18px; color: #666;">ยังไม่มีการสั่งอาหาร</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>ชื่ออาหาร</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) { ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($order['menu_name']); ?>
                            <?php 
                            $menu_se = json_decode($order['menu_se'], true);
                            if (!empty($menu_se)) {
                                echo "<br><small>ตัวเลือก: ";
                                foreach ($menu_se as $group => $option) {
                                    echo htmlspecialchars($group) . ": " . htmlspecialchars($option) . "; ";
                                }
                                echo "</small>";
                            }
                            ?>
                        </td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>
                            <?php 
                            if ($order['substatus'] == 1) {
                                echo number_format($order['price'] * $order['quantity'], 2) . " บาท";
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            $status_class = '';
                            switch ($order['substatus']) {
                                case 1:
                                    $status = "ยืนยันแล้ว";
                                    $status_class = "status-confirmed";
                                    break;
                                case 2:
                                    $status = "ยกเลิก";
                                    $status_class = "status-cancelled";
                                    break;
                                case 3:
                                    $status = "ยกเลิกของหมด";
                                    $status_class = "status-cancelled";
                                    break;
                                default:
                                    $status = "รอยืนยัน";
                                    $status_class = "status-pending";
                                    break;
                            }
                            echo "<span class='status $status_class'>$status</span>";
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <h3>รวม: <?php echo number_format($total_price, 2); ?> บาท</h3>
    <?php } ?>
</body>
</html>

