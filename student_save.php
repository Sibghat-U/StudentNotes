<?php
session_start();
require_once "db.php";

/* SAFETY */
if (!isset($_SESSION['regno'])) {
    header("Location: Login.php");
    exit();
}

$sessionRegno = $_SESSION['regno'];

/* CREATE */
if (isset($_POST['save'])) {

    $first = $_POST['first_name'];
    $last  = $_POST['last_name'];
    $email = $_POST['email'];
    $prog  = $_POST['program'];
    $sem   = $_POST['semester'];

    /* DEFAULT PASSWORD (hashed) */
    $defaultPassword = password_hash("123456", PASSWORD_DEFAULT);

    $sql = "INSERT INTO students 
            (regno, first_name, last_name, email, program, semester, password)
            VALUES (?,?,?,?,?,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssss",
        $sessionRegno,
        $first,
        $last,
        $email,
        $prog,
        $sem,
        $defaultPassword
    );

    mysqli_stmt_execute($stmt);
}

/* UPDATE */
if (isset($_POST['update'])) {

    $id    = $_POST['id'];
    $first = $_POST['first_name'];
    $last  = $_POST['last_name'];
    $email = $_POST['email'];
    $prog  = $_POST['program'];
    $sem   = $_POST['semester'];

    $sql = "UPDATE students SET
            first_name=?,
            last_name=?,
            email=?,
            program=?,
            semester=?
            WHERE id=?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $first,
        $last,
        $email,
        $prog,
        $sem,
        $id
    );

    mysqli_stmt_execute($stmt);
}

header("Location: index.php");
exit();
?>