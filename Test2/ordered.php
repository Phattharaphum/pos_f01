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
</head>
<body>
    <h1>รายการอาหารที่สั่งไป</h1>
    <a href="menulist.php?table=<?php echo $table_number ?>">ย้อนกลับ</a>
    <?php if (empty($orders)) { ?>
        <p>ยังไม่มีการสั่งอาหาร</p>
    <?php } else { ?>
        <table border="1">
            <tr>
                <th>ชื่ออาหาร</th>
                <th>จำนวน</th>
                <th>ราคา</th>
                <th>สถานะ</th>
            </tr>
            <?php foreach ($orders as $order) { ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($order['menu_name']); ?>
                        <?php 
                        // แสดงตัวเลือกที่บันทึกไว้
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
                        // แสดงราคาเฉพาะเมนูที่ยืนยันแล้ว
                        if ($order['substatus'] == 1) {
                            echo number_format($order['price'] * $order['quantity'], 2) . " บาท";
                        } else {
                            echo "-"; // ไม่แสดงราคาของเมนูที่ไม่ได้ยืนยัน
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        // แสดงสถานะตามค่า substatus
                        switch ($order['substatus']) {
                            case 1:
                                echo "ยืนยันแล้ว";
                                break;
                            case 2:
                                echo "ยกเลิก";
                                break;
                            case 3:
                                echo "ยกเลิกของหมด";
                                break;
                            default:
                                echo "รอยืนยัน";
                                break;
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <h3>รวม: <?php echo number_format($total_price, 2); ?> บาท</h3>
    <?php } ?>
</body>
</html>
