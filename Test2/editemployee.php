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

        form {
            max-width: 500px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        form input[type="text"], 
        form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form input[type="radio"] {
            margin-right: 8px;
        }

        .form-group {
            margin-bottom: 15px;
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

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #5bc0de;
        }

        a:hover {
            color: #31b0d5;
        }
    </style>
</head>
<body>
    <h1><?php echo $employee_id ? 'แก้ไขพนักงาน' : 'เพิ่มพนักงาน'; ?></h1>
    <form method="POST">
        <div class="form-group">
            <label>ชื่อ:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($employee['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($employee['username'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" value="<?php echo htmlspecialchars($employee['password'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label>สถานะ:</label>
            <input type="radio" name="status" value="1" <?php echo ($employee['status'] ?? 1) == 1 ? 'checked' : ''; ?>> Active
            <input type="radio" name="status" value="0" <?php echo ($employee['status'] ?? 1) == 0 ? 'checked' : ''; ?>> Inactive
        </div>

        <div class="form-group">
            <label>Role:</label>
            <input type="radio" name="role" value="1" <?php echo ($employee['role'] ?? 1) == 1 ? 'checked' : ''; ?>> เสิร์ฟ
            <input type="radio" name="role" value="2" <?php echo ($employee['role'] ?? 1) == 2 ? 'checked' : ''; ?>> เชฟ
        </div>

        <button type="submit">บันทึก</button>
    </form>
    <a href="manageemployee.php">กลับไปยังหน้าจัดการพนักงาน</a>
</body>
</html>

