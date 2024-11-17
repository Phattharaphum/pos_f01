<?php
include('connection.php');

$sql = "SELECT * FROM tab_menu";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Menu</title>
</head>
<body>
    <a href="manageemployee.php">จัดการพนักงาน</a>
    <h1>Manage Menu</h1>
    <a href="editmenu.php">Add New Menu</a>
    <table border="1">
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><img src="<?php echo $row['menu_pic']; ?>" alt="Image" width="50"></td>
                <td><?php echo $row['menu_name']; ?></td>
                <td><?php echo $row['menu_price']; ?></td>
                <td>
                    <a href="editmenu.php?id=<?php echo $row['menu_id']; ?>">Edit</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
