<?php
session_start();
include('connection.php');

// ดึงข้อมูลรายการอาหารที่ได้รับการยืนยันแล้ว (substatus = 1)
$stmt = $conn->prepare("
    SELECT 
        od.order_id, 
        od.menu_id, 
        m.menu_name, 
        od.menu_se, 
        od.quantity, 
        o.table_number, 
        o.timestmp
    FROM tab_order o
    JOIN tab_order_details od ON o.order_id = od.order_id
    JOIN tab_menu m ON od.menu_id = m.menu_id
    WHERE o.order_status = 1 AND od.substatus = 1
    ORDER BY o.timestmp ASC
");
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Orders</title>
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
    <h1>Kitchen Orders</h1>
    <table>
        <tr>
            <th>Menu</th>
            <th>Options</th>
            <th>Quantity</th>
            <th>Table</th>
            <th>Time</th>
        </tr>
        <?php while ($order = $orders->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($order['menu_name']); ?></td>
                <td>
                    <?php
                    $options = json_decode($order['menu_se'], true);
                    if ($options) {
                        foreach ($options as $key => $value) {
                            echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>";
                        }
                    } else {
                        echo "No options";
                    }
                    ?>
                </td>
                <td><?php echo $order['quantity']; ?></td>
                <td><?php echo htmlspecialchars($order['table_number']); ?></td>
                <td><?php echo date('H:i:s', strtotime($order['timestmp'])); ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
