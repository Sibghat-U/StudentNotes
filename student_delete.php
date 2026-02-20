<?php
require_once "db.php";

$regno = $_GET['regno'];

$stmt = mysqli_prepare($conn, "DELETE FROM students WHERE regno=?");
mysqli_stmt_bind_param($stmt, "s", $regno);
mysqli_stmt_execute($stmt);

/*
ON DELETE CASCADE will automatically remove:
- Marks
- Enrollments
- Results
*/

header("Location: index.php");
exit();
?>
