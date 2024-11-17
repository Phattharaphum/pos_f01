<?php
session_start();
include('connection.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = $_POST['table_number'];

    // ตรวจสอบว่าโต๊ะมีอยู่แล้วหรือไม่
    $stmt = $conn->prepare("SELECT * FROM tab_table WHERE table_number = ?");
    $stmt->bind_param("i", $table_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Table already exists!";
    } else {
        // เพิ่มโต๊ะใหม่
        $stmt = $conn->prepare("INSERT INTO tab_table (table_number, table_status) VALUES (?, 0)");
        $stmt->bind_param("i", $table_number);
        $stmt->execute();

        header("Location: tablestatus.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Table</title>
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
            max-width: 400px;
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

        form input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
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

        form button:hover {
            background-color: #4cae4c;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
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
    <h1>Add New Table</h1>
    <?php if (isset($error)) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>
    <form method="POST">
        <label>Table Number:</label>
        <input type="number" name="table_number" required>
        <button type="submit">Add Table</button>
    </form>
    <a href="tablestatus.php">Back to Table Status</a>
</body>
</html>

