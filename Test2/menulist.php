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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1 {
            background-color: #d9534f;
            color: white;
            text-align: center;
            padding: 15px 0;
            margin: 0;
        }

        a {
            text-decoration: none;
            color: #d9534f;
            margin: 10px;
        }

        a:hover {
            color: #b52a27;
        }

        hr {
            border: 0;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
        }

        h2 {
            color: #5a5a5a;
            margin: 20px;
        }

        div {
            margin: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: inline-block;
            vertical-align: top;
        }

        img {
            display: block;
            margin: 0 auto 10px;
            border-radius: 8px;
        }

        h3 {
            font-size: 18px;
            text-align: center;
            margin: 10px 0;
            color: #333;
        }

        .menu-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .header-links {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #f8f8f8;
            border-bottom: 1px solid #ddd;
        }

        .header-links a {
            padding: 10px 15px;
            background-color: #d9534f;
            color: white;
            border-radius: 5px;
            font-size: 14px;
        }

        .header-links a:hover {
            background-color: #b52a27;
        }
    </style>
</head>
<body>
    <h1>ก๋วยเตี๋ยวขาหมูกรุงศรีอยุธยา</h1>
    <div class="header-links">
        <a href="confirmmenu.php">Cart (<?php echo $cart_count; ?>)</a>
        <a href="ordered.php">รายการอาหารที่สั่งไป</a>
    </div>
    <hr>

    <h2>Menu</h2>
    <div class="menu-container">
        <?php while ($menu = $result->fetch_assoc()) { ?>
            <div>
                <img src="<?php echo $menu['menu_pic']; ?>" alt="Image" width="100">
                <h3><?php echo $menu['menu_name']; ?> - <?php echo $menu['menu_price']; ?> บาท</h3>
                <a href="menude.php?id=<?php echo $menu['menu_id']; ?>">เลือก</a>
            </div>
        <?php } ?>
    </div>
</body>
</html>

