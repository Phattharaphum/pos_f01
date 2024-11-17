<?php
session_start();
include('connection.php');


$table_number = $_GET['table'] ?? null;
if (!$table_number) {
    die("No table specified.");
}

// ดึงข้อมูลรายการอาหารจากโต๊ะ
$stmt = $conn->prepare("
    SELECT od.order_id, od.menu_id, m.menu_name, od.menu_se, od.quantity
    FROM tab_order_details od
    JOIN tab_menu m ON od.menu_id = m.menu_id
    JOIN tab_order o ON od.order_id = o.order_id
    WHERE o.table_number = ? AND o.order_status = 0
");
$stmt->bind_param("i", $table_number);
$stmt->execute();
$orders = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $stmt = $conn->prepare("UPDATE tab_order SET order_status = 1 WHERE table_number = ?");
    $stmt->bind_param("i", $table_number);
    $stmt->execute();
    header("Location: tablestatus.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
</head>
<body>
    <h1>Order Details for Table <?php echo $table_number; ?></h1>
    <form method="POST">
        <table border="1">
            <tr>
                <th>Menu</th>
                <th>Options</th>
                <th>Quantity</th>
            </tr>
            <?php while ($order = $orders->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $order['menu_name']; ?></td>
                    <td>
                        <?php
                        $options = json_decode($order['menu_se'], true);
                        foreach ($options as $key => $value) {
                            echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "<br>";
                        }
                        ?>
                    </td>
                    <td><?php echo $order['quantity']; ?></td>
                </tr>
            <?php } ?>
        </table>
        <button type="submit" name="confirm_order">Confirm Order</button>
    </form>
</body>
</html>
