<?php
session_start();
include('connection.php');

// ตรวจสอบว่า table ถูกส่งมาหรือไม่
if (!isset($_GET['table'])) {
    die("No table specified.");
}
$table_number = $_GET['table'];

// เก็บ table ใน session
$_SESSION['table'] = $table_number;

// ดึงรายการเมนูที่สถานะพร้อมแสดง
$sql = "SELECT * FROM tab_menu WHERE menu_status = 1";
$result = $conn->query($sql);

// ตระกร้าสินค้า
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu List</title>
</head>
<body>
    <h1>ก๋วยเตี๋ยวขาหมูกรุงศรีอยุธยา</h1>
    <a href="confirmmenu.php">Cart (<?php echo $cart_count; ?>)</a>
    <a href="ordered.php">รายการอาหารที่สั่งไป</a>
    <hr>

    <h2>Menu</h2>
    <?php while ($menu = $result->fetch_assoc()) { ?>
        <div>
            <img src="<?php echo $menu['menu_pic']; ?>" alt="Image" width="100">
            <h3><?php echo $menu['menu_name']; ?> - <?php echo $menu['menu_price']; ?> บาท</h3>
            <a href="menude.php?id=<?php echo $menu['menu_id']; ?>">เลือก</a>
        </div>
    <?php } ?>
</body>
</html>
