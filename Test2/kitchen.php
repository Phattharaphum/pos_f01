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

        td {
            font-size: 14px;
        }

        .no-options {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>Kitchen Orders</h1>
    <table>
        <thead>
            <tr>
                <th>Menu</th>
                <th>Options</th>
                <th>Quantity</th>
                <th>Table</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders->num_rows > 0) { ?>
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
                                echo "<span class='no-options'>No options</span>";
                            }
                            ?>
                        </td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($order['table_number']); ?></td>
                        <td><?php echo date('H:i:s', strtotime($order['timestmp'])); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #888;">No orders available</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>

