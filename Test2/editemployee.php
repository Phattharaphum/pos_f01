<?php
session_start();
include('connection.php');


$employee_id = $_GET['id'] ?? null;
$employee = null;

if ($employee_id) {
    // ดึงข้อมูลพนักงานเพื่อแก้ไข
    $stmt = $conn->prepare("SELECT * FROM tab_employee WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $status = $_POST['status'];
    $role = $_POST['role'];

    if ($employee_id) {
        // อัปเดตพนักงานที่มีอยู่
        $stmt = $conn->prepare("UPDATE tab_employee SET name = ?, username = ?, password = ?, status = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssiii", $name, $username, $password, $status, $role, $employee_id);
    } else {
        // เพิ่มพนักงานใหม่
        $stmt = $conn->prepare("INSERT INTO tab_employee (name, username, password, status, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $name, $username, $password, $status, $role);
    }
    $stmt->execute();

    header("Location: manageemployee.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $employee_id ? 'แก้ไขพนักงาน' : 'เพิ่มพนักงาน'; ?></title>
</head>
<body>
    <h1><?php echo $employee_id ? 'แก้ไขพนักงาน' : 'เพิ่มพนักงาน'; ?></h1>
    <form method="POST">
        <label>ชื่อ:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($employee['name'] ?? ''); ?>" required><br>

        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($employee['username'] ?? ''); ?>" required><br>

        <label>Password:</label>
        <input type="password" name="password" value="<?php echo htmlspecialchars($employee['password'] ?? ''); ?>" required><br>

        <label>สถานะ:</label>
        <input type="radio" name="status" value="1" <?php echo ($employee['status'] ?? 1) == 1 ? 'checked' : ''; ?>> Active
        <input type="radio" name="status" value="0" <?php echo ($employee['status'] ?? 1) == 0 ? 'checked' : ''; ?>> Inactive<br>

        <label>Role:</label>
        <input type="radio" name="role" value="1" <?php echo ($employee['role'] ?? 1) == 1 ? 'checked' : ''; ?>> เสิร์ฟ
        <input type="radio" name="role" value="2" <?php echo ($employee['role'] ?? 1) == 2 ? 'checked' : ''; ?>> เชฟ<br>

        <button type="submit">บันทึก</button>
    </form>
    <a href="manageemployee.php">กลับไปยังหน้าจัดการพนักงาน</a>
</body>
</html>
