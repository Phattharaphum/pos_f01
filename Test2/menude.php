<?php
session_start();
include('connection.php');

$menu_id = $_GET['id'] ?? null;
$edit_index = $_GET['edit_index'] ?? null;

if ($edit_index !== null) {
    // กรณีแก้ไขรายการที่มีอยู่ในตะกร้า
    $cart_item = $_SESSION['cart'][$edit_index];
    $menu_id = $cart_item['id'];
    $quantity = $cart_item['quantity'];
    $options = $cart_item['options'];
    $details = $cart_item['details'];
} else {
    // กรณีเพิ่มเมนูใหม่
    $quantity = 1;
    $options = [];
    $details = '';
}

if (!$menu_id) {
    die("No menu specified.");
}

// ดึงข้อมูลเมนู
$stmt = $conn->prepare("SELECT * FROM tab_menu WHERE menu_id = ?");
$stmt->bind_param("i", $menu_id);
$stmt->execute();
$result = $stmt->get_result();
$menu = $result->fetch_assoc();

if (!$menu) {
    die("Menu not found.");
}

// แปลง menu_se จาก JSON เป็น Array
$menu_se = json_decode($menu['menu_se'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_item']) && $edit_index !== null) {
        // ลบรายการจากตะกร้า
        unset($_SESSION['cart'][$edit_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // จัดเรียง index ใหม่
        header("Location: confirmmenu.php");
        exit;
    }

    $quantity = $_POST['quantity'];
    $details = $_POST['details'] ?? '';
    $options = [];

    // จัดเก็บตัวเลือกที่เลือกใน options
    foreach ($_POST['options'] as $group => $value) {
        $options[$group] = $value;
    }

    if ($edit_index !== null) {
        // อัปเดตรายการที่แก้ไขในตะกร้า
        $_SESSION['cart'][$edit_index] = [
            'id' => $menu_id,
            'name' => $menu['menu_name'],
            'price' => $menu['menu_price'],
            'quantity' => $quantity,
            'options' => $options,
            'details' => $details
        ];
    } else {
        // เพิ่มรายการใหม่ลงในตะกร้า (แยกออกเป็นรายการใหม่)
        $_SESSION['cart'][] = [
            'id' => $menu_id,
            'name' => $menu['menu_name'],
            'price' => $menu['menu_price'],
            'quantity' => $quantity,
            'options' => $options,
            'details' => $details
        ];
    }

    header("Location: confirmmenu.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu Details</title>
</head>
<body>
<a href="menulist.php?table=<?php echo $_SESSION['table'] ?>">ย้อนกลับ</a>
    <h1><?php echo $menu['menu_name']; ?></h1>
    <img src="<?php echo $menu['menu_pic']; ?>" alt="Image" width="200">
    <form method="POST">
        <h3>ตัวเลือก</h3>
        <?php if ($menu_se) { ?>
            <?php foreach ($menu_se as $group => $options_group) { ?>
                <div>
                    <h4><?php echo htmlspecialchars($group); ?></h4>
                    <?php foreach ($options_group as $option => $price) { ?>
                        <?php
                        $checked = (isset($options[$group]) && $options[$group] === $option) ? 'checked' : '';
                        ?>
                        <div>
                            <input type="radio" name="options[<?php echo htmlspecialchars($group); ?>]" value="<?php echo htmlspecialchars($option); ?>" <?php echo $checked; ?> required>
                            <?php echo htmlspecialchars($option); ?> (+<?php echo number_format($price, 2); ?> บาท)
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>ไม่มีตัวเลือก</p>
        <?php } ?>

        <h3>จำนวน</h3>
        <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1" required>

        <h3>รายละเอียดเพิ่มเติม</h3>
        <textarea name="details"><?php echo htmlspecialchars($details); ?></textarea>

        <button type="submit">บันทึก</button>
        <?php if ($edit_index !== null) { ?>
            <button type="submit" name="delete_item" style="background-color: red; color: white;">ลบ</button>
        <?php } ?>
    </form>
</body>
</html>
