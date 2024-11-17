<?php
include('connection.php');

$menu_id = $_GET['id'] ?? null;
$menu = null;

if ($menu_id) {
    // ดึงข้อมูลเมนูจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM tab_menu WHERE menu_id = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $menu = $result->fetch_assoc();
}

// จัดการคำขอ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // ลบเมนูออกจากฐานข้อมูล
        $stmt = $conn->prepare("DELETE FROM tab_menu WHERE menu_id = ?");
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();
        header("Location: managermenu.php");
        exit;
    }

    // บันทึกหรืออัปเดตข้อมูลเมนู
    $name = $_POST['name'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $status = isset($_POST['status']) ? 1 : 0;
    $menu_se = $_POST['menu_se'];
    $menu_pic = $_POST['menu_pic'];

    if ($menu_id) {
        // แก้ไขข้อมูลเมนู
        $stmt = $conn->prepare("UPDATE tab_menu SET menu_name = ?, menu_type = ?, menu_price = ?, menu_status = ?, menu_se = ?, menu_pic = ? WHERE menu_id = ?");
        $stmt->bind_param("ssdissi", $name, $type, $price, $status, $menu_se, $menu_pic, $menu_id);
    } else {
        // เพิ่มเมนูใหม่
        $stmt = $conn->prepare("INSERT INTO tab_menu (menu_name, menu_type, menu_price, menu_status, menu_se, menu_pic) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $name, $type, $price, $status, $menu_se, $menu_pic);
    }
    $stmt->execute();
    header("Location: managermenu.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $menu_id ? "Edit Menu" : "Add New Menu"; ?></title>
    <style>
        .option-group {
            margin-bottom: 15px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .option-item {
            margin-bottom: 5px;
        }
        .remove-button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1><?php echo $menu_id ? "Edit Menu" : "Add New Menu"; ?></h1>
    <form method="POST">
        <label>Name: </label>
        <input type="text" name="name" value="<?php echo $menu['menu_name'] ?? ''; ?>" required><br>

        <label>Type: </label>
        <select name="type" required>
            <option value="ก๋วยเตี๋ยว" <?php if (($menu['menu_type'] ?? '') == 'ก๋วยเตี๋ยว') echo 'selected'; ?>>ก๋วยเตี๋ยว</option>
            <option value="ข้าว" <?php if (($menu['menu_type'] ?? '') == 'ข้าว') echo 'selected'; ?>>ข้าว</option>
            <option value="น้ำ" <?php if (($menu['menu_type'] ?? '') == 'น้ำ') echo 'selected'; ?>>น้ำ</option>
            <option value="ทานเล่น" <?php if (($menu['menu_type'] ?? '') == 'ทานเล่น') echo 'selected'; ?>>ทานเล่น</option>
        </select><br>

        <label>Price: </label>
        <input type="number" step="0.01" name="price" value="<?php echo $menu['menu_price'] ?? ''; ?>" required><br>

        <label>Status: </label>
        <input type="checkbox" name="status" <?php if (($menu['menu_status'] ?? 0) == 1) echo 'checked'; ?>><br>

        <label>Image URL: </label>
        <input type="text" name="menu_pic" value="<?php echo $menu['menu_pic'] ?? ''; ?>"><br>

        <label>Menu Options:</label>
        <div id="options-container"></div>
        <button type="button" onclick="addOptionGroup()">Add Option Group</button><br><br>

        <textarea name="menu_se" id="menu_se" hidden><?php echo $menu['menu_se'] ?? '{}'; ?></textarea>

        <button type="submit">Save</button>
    </form>

    <?php if ($menu_id) { ?>
        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="delete" class="remove-button">Delete Menu</button>
        </form>
    <?php } ?>

    <script>
        let optionsData = <?php echo $menu['menu_se'] ?? '{}'; ?>;

        function addOptionGroup(title = '', options = {}) {
            const container = document.getElementById('options-container');
            const group = document.createElement('div');
            group.classList.add('option-group');

            group.innerHTML = `
                <label>Group Name: </label>
                <input type="text" class="group-title" value="${title}" placeholder="Group name" required>
                <div class="options-list">
                    ${Object.entries(options).map(([name, price]) => `
                        <div class="option-item">
                            <input type="text" class="option-name" value="${name}" placeholder="Option name" required>
                            <input type="number" class="option-price" value="${price}" placeholder="Price" required>
                            <button type="button" class="remove-button" onclick="removeOption(this)">Remove</button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" onclick="addOption(this)">Add Option</button>
                <button type="button" class="remove-button" onclick="removeOptionGroup(this)">Remove Group</button>
            `;

            container.appendChild(group);
        }

        function addOption(button) {
            const optionsList = button.previousElementSibling;
            const optionItem = document.createElement('div');
            optionItem.classList.add('option-item');
            optionItem.innerHTML = `
                <input type="text" class="option-name" placeholder="Option name" required>
                <input type="number" class="option-price" placeholder="Price" required>
                <button type="button" class="remove-button" onclick="removeOption(this)">Remove</button>
            `;
            optionsList.appendChild(optionItem);
        }

        function removeOption(button) {
            button.parentElement.remove();
        }

        function removeOptionGroup(button) {
            button.parentElement.remove();
        }

        function saveOptionsToJSON() {
            const groups = document.querySelectorAll('.option-group');
            const data = {};

            groups.forEach(group => {
                const title = group.querySelector('.group-title').value;
                const options = {};

                group.querySelectorAll('.option-item').forEach(option => {
                    const name = option.querySelector('.option-name').value;
                    const price = parseFloat(option.querySelector('.option-price').value) || 0;

                    options[name] = price; // ใช้ชื่อเป็น key และราคาเป็น value
                });

                data[title] = options; // เพิ่มกลุ่มตัวเลือกลงใน JSON
            });

            document.getElementById('menu_se').value = JSON.stringify(data);
        }

        // Load initial data
        Object.keys(optionsData).forEach(title => {
            addOptionGroup(title, optionsData[title]);
        });

        // Add event listener for form submission
        document.querySelector('form').addEventListener('submit', saveOptionsToJSON);
    </script>
</body>
</html>
