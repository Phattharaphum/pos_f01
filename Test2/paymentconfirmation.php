<?php
session_start();
include('connection.php');


$table_number = $_GET['table'] ?? null;
if (!$table_number) {
    die("No table specified.");
}

// ดึงข้อมูลรายการอาหารที่ยืนยันแล้วแต่ยังไม่ได้ชำระเงิน
$stmt = $conn->prepare("
    SELECT 
        o.order_id, 
        o.table_number, 
        od.menu_id, 
        m.menu_name, 
        od.menu_se, 
        od.quantity, 
        od.price, 
        od.substatus
    FROM tab_order o
    JOIN tab_order_details od ON o.order_id = od.order_id
    JOIN tab_menu m ON od.menu_id = m.menu_id
    WHERE o.table_number = ? AND o.order_status = 1
");
$stmt->bind_param("i", $table_number);
$stmt->execute();
$orders = $stmt->get_result();

$total_price = 0;
$order_data = [];
while ($row = $orders->fetch_assoc()) {
    // คำนวณราคาเฉพาะเมนูที่สถานะ substatus = 1 (ยืนยันแล้ว)
    if ($row['substatus'] == 1) {
        $total_price += $row['quantity'] * $row['price'];
    }
    $order_data[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // เปลี่ยนสถานะ order_status เป็น 2 เพื่อระบุว่า "ชำระเงินแล้ว"
    $stmt = $conn->prepare("UPDATE tab_order SET order_status = 2 WHERE table_number = ?");
    $stmt->bind_param("i", $table_number);
    $stmt->execute();

    // อัปเดตสถานะโต๊ะเป็น 0 (Available)
    $stmt = $conn->prepare("UPDATE tab_table SET table_status = 0 WHERE table_number = ?");
    $stmt->bind_param("i", $table_number);
    $stmt->execute();

    header("Location: tablestatus.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm Payment</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <a href="orderde.php?table=<?php echo $table_number; ?>">ย้อนกลับ</a>
    <h1>ยืนยันการชำระเงินสำหรับโต๊ะ <?php echo $table_number; ?></h1>
    <?php if (empty($order_data)) { ?>
        <p>ไม่มีรายการที่รอการชำระเงินสำหรับโต๊ะนี้</p>
    <?php } else { ?>
        <table>
            <tr>
                <th>#</th>
                <th>ชื่อเมนู</th>
                <th>ตัวเลือก</th>
                <th>จำนวน</th>
                <th>ราคา</th>
                <th>สถานะ</th>
            </tr>
            <?php foreach ($order_data as $index => $order) { ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                    <td>
                        <?php
                        $options = json_decode($order['menu_se'], true);
                        if ($options) {
                            foreach ($options as $key => $value) {
                                echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>";
                            }
                        } else {
                            echo "ไม่มีตัวเลือก";
                        }
                        ?>
                    </td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td>
                        <?php
                        if ($order['substatus'] == 1) {
                            echo number_format($order['quantity'] * $order['price'], 2) . " บาท";
                        } else {
                            echo "-"; // ไม่คำนวณราคาเมนูที่ไม่ได้ยืนยัน
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
        <h3>รวมทั้งหมด: <?php echo number_format($total_price, 2); ?> บาท</h3>
        <form method="POST">
            <label>ประเภทการชำระเงิน:</label>
            <select name="payment_type" required>
                <option value="cash">เงินสด</option>
                <option value="credit">บัตรเครดิต</option>
            </select><br><br>
            <button type="submit" name="confirm_payment">ยืนยันการชำระเงิน</button>
        </form>
    <?php } ?>
</body>
</html>
