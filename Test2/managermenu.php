<?php
include('connection.php');

$sql = "SELECT * FROM tab_menu";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Menu</title>
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

        img {
            max-width: 50px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
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
    <a href="manageemployee.php">จัดการพนักงาน</a>
    <h1>จัดการรายการอาหาร</h1>
    <a href="editmenu.php">Add New Menu</a>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><img src="<?php echo $row['menu_pic']; ?>" alt="Image"></td>
                    <td><?php echo htmlspecialchars($row['menu_name']); ?></td>
                    <td><?php echo number_format($row['menu_price'], 2); ?> บาท</td>
                    <td class="actions">
                        <a href="editmenu.php?id=<?php echo $row['menu_id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>

