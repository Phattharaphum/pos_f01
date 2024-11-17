<?php
session_start();
include('connection.php');

// ตรวจสอบว่า table ถูกตั้งไว้ใน session หรือไม่
if (!isset($_SESSION['table'])) {
    die("No table specified. Please return to the menu list.");
}

// ตระกร้าสินค้า
$cart = $_SESSION['cart'] ?? [];

// ดึงข้อมูลตัวเลือกทั้งหมดจากฐานข้อมูล
$menu_data = [];
foreach ($cart as $item) {
    $stmt = $conn->prepare("SELECT menu_se FROM tab_menu WHERE menu_id = ?");
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $menu_se_data = $result->fetch_assoc();
    $menu_data[$item['id']] = json_decode($menu_se_data['menu_se'] ?? '{}', true);
}

// ฟังก์ชันคำนวณราคาตัวเลือก
function calculateOptionPrice($options, $menu_se) {
    $total = 0;
    foreach ($options as $group => $option) {
        if (isset($menu_se[$group][$option])) {
            $total += $menu_se[$group][$option];
        }
    }
    return $total;
}

// คำนวณราคารวม
$total_price = 0;
foreach ($cart as &$item) {
    $menu_se = $menu_data[$item['id']] ?? [];
    $option_price = calculateOptionPrice($item['options'], $menu_se);
    $item['total_price'] = ($item['price'] + $option_price) * $item['quantity'];
    $total_price += $item['total_price'];
}
unset($item); // ยกเลิกการอ้างอิง


// จัดการ POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_order'])) {
        $table_number = $_SESSION['table'];
        $stmt = $conn->prepare("INSERT INTO tab_order (table_number, order_status, order_total_price) VALUES (?, 0, ?)");
        $stmt->bind_param("id", $table_number, $total_price);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        foreach ($cart as $item) {
            $menu_se_json = json_encode($item['options']);
            $stmt = $conn->prepare("INSERT INTO tab_order_details (order_id, menu_id, menu_se, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisid", $order_id, $item['id'], $menu_se_json, $item['quantity'], $item['price']);
            $stmt->execute();
        }

        $stmt = $conn->prepare("UPDATE tab_table SET table_status = 1 WHERE table_number = ?");
        $stmt->bind_param("i", $table_number);
        $stmt->execute();

        unset($_SESSION['cart']);
        header("Location: menulist.php?table=" . $table_number);
        exit;
    }

    if (isset($_POST['delete_item'])) {
        $delete_index = $_POST['delete_index'];
        unset($_SESSION['cart'][$delete_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // จัดเรียง index ใหม่
        header("Location: confirmmenu.php");
        exit;
    }

    if (isset($_POST['edit_item'])) {
        $edit_index = $_POST['edit_index'];
        header("Location: menude.php?edit_index=" . $edit_index);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    
    <title>Confirm Menu</title>
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
        .delete-button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .edit-button {
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<a href="menulist.php?table=<?php echo $_SESSION['table'] ?>">เลือกซื้อเพิ่ม</a>
    <h1>รายการอาหาร</h1>
    <?php if (empty($cart)) { ?>
        <p>ยังไม่มีรายการในตะกร้า</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>ชื่อ</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>การกระทำ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $index => $item) { ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['name']); ?>
                            <?php 
                            // แสดงตัวเลือก
                            if (!empty($item['options'])) {
                                echo "<br><small>ตัวเลือก: ";
                                foreach ($item['options'] as $group => $option) {
                                    echo htmlspecialchars($group) . ": " . htmlspecialchars($option) . "; ";
                                }
                                echo "</small>";
                            }
                            ?>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['total_price'], 2); ?> บาท</td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="edit_index" value="<?php echo $index; ?>">
                                <button type="submit" name="edit_item" class="edit-button">แก้ไข</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_index" value="<?php echo $index; ?>">
                                <button type="submit" name="delete_item" class="delete-button">ลบ</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <h3>รวม: <?php echo number_format($total_price, 2); ?> บาท</h3>
        <form method="POST">
            <button type="submit" name="confirm_order">ยืนยันการสั่งซื้อ</button>
        </form>
    <?php } ?>
</body>
</html>
