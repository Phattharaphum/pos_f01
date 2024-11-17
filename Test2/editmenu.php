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
            max-width: 600px;
            margin: 0 auto;
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
        form input[type="number"],
        form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            height: 100px;
        }

        .option-group {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .option-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .option-item input {
            margin-right: 10px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .remove-button {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }

        .remove-button:hover {
            background-color: #c9302c;
        }

        button {
            display: inline-block;
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        button:hover {
            background-color: #4cae4c;
        }

        #options-container button {
            margin-top: 10px;
        }

        .delete-menu {
            background-color: #d9534f;
        }

        .delete-menu:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <h1><?php echo $menu_id ? "Edit Menu" : "Add New Menu"; ?></h1>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($menu['menu_name'] ?? ''); ?>" required>

        <label>Type:</label>
        <select name="type" required>
            <option value="ก๋วยเตี๋ยว" <?php if (($menu['menu_type'] ?? '') == 'ก๋วยเตี๋ยว') echo 'selected'; ?>>ก๋วยเตี๋ยว</option>
            <option value="ข้าว" <?php if (($menu['menu_type'] ?? '') == 'ข้าว') echo 'selected'; ?>>ข้าว</option>
            <option value="น้ำ" <?php if (($menu['menu_type'] ?? '') == 'น้ำ') echo 'selected'; ?>>น้ำ</option>
            <option value="ทานเล่น" <?php if (($menu['menu_type'] ?? '') == 'ทานเล่น') echo 'selected'; ?>>ทานเล่น</option>
        </select>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($menu['menu_price'] ?? ''); ?>" required>

        <label>Status:</label>
        <input type="checkbox" name="status" <?php if (($menu['menu_status'] ?? 0) == 1) echo 'checked'; ?>> Active

        <label>Image URL:</label>
        <input type="text" name="menu_pic" value="<?php echo htmlspecialchars($menu['menu_pic'] ?? ''); ?>">

        <label>Menu Options:</label>
        <div id="options-container"></div>
        <button type="button" onclick="addOptionGroup()">Add Option Group</button>

        <textarea name="menu_se" id="menu_se" hidden><?php echo $menu['menu_se'] ?? '{}'; ?></textarea>

        <button type="submit">Save</button>
    </form>

    <?php if ($menu_id) { ?>
        <form method="POST" style="max-width: 600px; margin: 20px auto;">
            <button type="submit" name="delete" class="delete-menu">Delete Menu</button>
        </form>
    <?php } ?>

    <script>
        let optionsData = <?php echo $menu['menu_se'] ?? '{}'; ?>;

        function addOptionGroup(title = '', options = {}) {
            const container = document.getElementById('options-container');
            const group = document.createElement('div');
            group.classList.add('option-group');

            group.innerHTML = `
                <label>Group Name:</label>
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

                    options[name] = price;
                });

                data[title] = options;
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

