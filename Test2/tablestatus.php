<?php
session_start();
include('connection.php');


// ดึงข้อมูลสถานะโต๊ะ
$stmt = $conn->prepare("SELECT * FROM tab_table");
$stmt->execute();
$tables = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Table Status</title>
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

        a {
            display: inline-block;
            margin: 10px 0;
            text-decoration: none;
            background-color: #5bc0de;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
        }

        a:hover {
            background-color: #31b0d5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f8f8;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .status-occupied {
            color: red;
            font-weight: bold;
        }

        .status-available {
            color: green;
            font-weight: bold;
        }

        .actions a {
            margin: 0 5px;
            color: #fff;
            background-color: #5cb85c;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 13px;
        }

        .actions a:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <h1>Table Status</h1>
    <a href="addtable.php">เพิ่มโต๊ะ</a>
    <table>
        <thead>
            <tr>
                <th>Table Number</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($table = $tables->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $table['table_number']; ?></td>
                    <td class="<?php echo $table['table_status'] == 1 ? 'status-occupied' : 'status-available'; ?>">
                        <?php echo $table['table_status'] == 1 ? "Occupied" : "Available"; ?>
                    </td>
                    <td class="actions">
                        <a href="orderde.php?table=<?php echo $table['table_number']; ?>">View Order</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>

