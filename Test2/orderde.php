<?php
session_start();
include('connection.php');

$table_number = $_GET['table'] ?? null;
if (!$table_number) {
    die("No table specified.");
}

// ดึงข้อมูลรายการอาหารจากโต๊ะ
$stmt = $conn->prepare("
    SELECT od.id AS order_detail_id, od.menu_id, m.menu_name, od.menu_se, od.quantity, od.substatus
    FROM tab_order_details od
    JOIN tab_menu m ON od.menu_id = m.menu_id
    JOIN tab_order o ON od.order_id = o.order_id
    WHERE o.table_number = ? AND o.order_status = 0
");
$stmt->bind_param("i", $table_number);
$stmt->execute();
$orders = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['substatus'] as $order_detail_id => $substatus) {
        // อัปเดต substatus ในฐานข้อมูล
        $stmt = $conn->prepare("UPDATE tab_order_details SET substatus = ? WHERE id = ?");
        $stmt->bind_param("ii", $substatus, $order_detail_id);
        $stmt->execute();
    }

    // อัปเดตสถานะออเดอร์เป็นยืนยัน
    if (isset($_POST['confirm_order'])) {
        $stmt = $conn->prepare("UPDATE tab_order SET order_status = 1 WHERE table_number = ?");
        $stmt->bind_param("i", $table_number);
        $stmt->execute();
    }

    header("Location: tablestatus.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
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

        td input[type="radio"] {
            margin-right: 10px;
        }

        button {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 20px auto;
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
    </style>
</head>
<body>
    <h1>Order Details for Table <?php echo $table_number; ?></h1>
    <a href="tablestatus.php">ย้อนกลับ</a>
    <a href="paymentconfirmation.php?table=<?php echo $table_number; ?>">Payment confirmation</a>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Options</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                        <td>
                            <?php
                            $options = json_decode($order['menu_se'], true);
                            foreach ($options as $key => $value) {
                                echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>";
                            }
                            ?>
                        </td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>
                            <input type="radio" name="substatus[<?php echo $order['order_detail_id']; ?>]" value="1" <?php echo ($order['substatus'] ?? 1) == 1 ? 'checked' : ''; ?>> ยืนยัน<br>
                            <input type="radio" name="substatus[<?php echo $order['order_detail_id']; ?>]" value="2" <?php echo $order['substatus'] == 2 ? 'checked' : ''; ?>> ยกเลิก<br>
                            <input type="radio" name="substatus[<?php echo $order['order_detail_id']; ?>]" value="3" <?php echo $order['substatus'] == 3 ? 'checked' : ''; ?>> ยกเลิกของหมด
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button type="submit" name="confirm_order">Confirm Order</button>
    </form>
</body>
</html>

