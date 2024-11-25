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

        h3 {
            text-align: right;
            margin-top: 20px;
            color: #333;
        }

        form {
            max-width: 400px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .no-orders {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>ยืนยันการชำระเงินสำหรับโต๊ะ <?php echo $table_number; ?></h1>
    <a href="orderde.php?table=<?php echo $table_number; ?>">ย้อนกลับ</a>
    <?php if (empty($order_data)) { ?>
        <p class="no-orders">ไม่มีรายการที่รอการชำระเงินสำหรับโต๊ะนี้</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อเมนู</th>
                    <th>ตัวเลือก</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
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
            </tbody>
        </table>
        <h3>รวมทั้งหมด: <?php echo number_format($total_price, 2); ?> บาท</h3>
        <form method="POST">
            <label>ประเภทการชำระเงิน:</label>
            <select name="payment_type" required>
                <option value="cash">เงินสด</option>
                <option value="credit">บัตรเครดิต</option>
                <option value="promptpay">พรอมเพย์</option>
                <option value="scan">สแกนจ่าย</option>
            </select>
            <button type="submit" name="confirm_payment">ยืนยันการชำระเงิน</button>
        </form>
    <?php } ?>
</body>
</html>

