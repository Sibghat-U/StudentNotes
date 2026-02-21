<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "db.php";

$error_msg = "";
$success_msg = "";

if (isset($_POST['register'])) {
    // 1. Sanitize Inputs
    $regno    = trim($_POST['Regno']); 
    $name     = trim($_POST['Name']);
    $email    = trim($_POST['Email']);
    $program  = $_POST['Program'];
    $semester = $_POST['Semester'];
    $password = $_POST['password'];

    // 2. Validate Empty Fields
    if (empty($name) || empty($regno) || empty($email) || empty($password)) {
        $error_msg = "All fields are required.";
    } 
    // 3. Validate Email Format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format. Please use a valid email (e.g. name@domain.com).";
    }
    // 4. Validate Password Strength (Min 8 chars, 1 Uppercase, 1 Number, 1 Special Char)
    elseif (strlen($password) < 8 || 
            !preg_match("/[A-Z]/", $password) || 
            !preg_match("/[0-9]/", $password) || 
            !preg_match("/[\W]/", $password)) { // \W matches any non-word character (special char)
        $error_msg = "Weak Password! <br>Must be at least 8 characters, contain 1 Uppercase, 1 Number, and 1 Special Character.";
    }
    else {
        // 5. Check for Duplicates (RegNo OR Email)
        // We check both in one query to be efficient
        $check = mysqli_prepare($conn, "SELECT RegNo, Email FROM student WHERE RegNo = ? OR Email = ?");
        mysqli_stmt_bind_param($check, "ss", $regno, $email);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['RegNo'] === $regno) {
                $error_msg = "Account with this Registration Number already exists. <a href='Login.php'>Login here</a>";
            } elseif ($row['Email'] === $email) {
                $error_msg = "This Email is already registered by another student.";
            }
        } else {
            // 6. Insert New Student
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO student (RegNo, Name, Email, Program, Semester, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $regno, $name, $email, $program, $semester, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                // Success
                header("Location: Login.php");
                exit();
            } else {
                $error_msg = "Database Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <style>
    * { box-sizing: border-box; }
    body { 
        background: linear-gradient(135deg, #667eea, #764ba2); 
        min-height: 100vh;
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-family: 'Segoe UI', Arial, sans-serif; 
        margin: 0; 
        padding: 20px;
    }
    
    .signup-container { 
        background: white; 
        width: 420px; 
        padding: 35px; 
        border-radius: 16px; 
        box-shadow: 0 20px 40px rgba(0,0,0,0.25); 
    }
    
    .signup-container h2 { 
        text-align: center; 
        color: #4f46e5; 
        margin-bottom: 25px; 
        margin-top: 0; 
    }
    
    .input-group { 
        margin-bottom: 16px; 
    }
    
    .input-group label { 
        display: block; 
        margin-bottom: 6px; 
        color: #374151; 
        font-size: 14px; 
    }
    
    .input-group input, .input-group select { 
        width: 100%; 
        padding: 12px; 
        border-radius: 8px; 
        border: 1px solid #ccc; 
        box-sizing: border-box; 
    }
    
    input:focus, select:focus { 
        outline: none; 
        border-color: #4f46e5; 
        box-shadow: 0 0 0 3px rgba(79,70,229,0.1); 
    }
    
    .signup-btn { 
        width: 100%; 
        padding: 12px; 
        background: #4ade80; 
        border: none; 
        border-radius: 8px; 
        font-size: 16px; 
        cursor: pointer; 
        font-weight: bold; 
        margin-top: 10px; 
        color: white; 
        transition: 0.3s; 
    }
    
    .signup-btn:hover { 
        background: #22c55e; 
    }
    
    .footer-text { 
        margin-top: 18px; 
        text-align: center; 
        font-size: 14px; 
        color: #666; 
    }
    
    .footer-text a { 
        color: #4f46e5; 
        text-decoration: none; 
        font-weight: 600; 
    }
    
    .error-box { 
        background-color: #fee2e2; 
        color: #991b1b; 
        padding: 12px; 
        border-radius: 8px; 
        text-align: center; 
        font-size: 14px; 
        margin-bottom: 20px; 
        border: 1px solid #f87171; 
        line-height: 1.5; 
    }
    
    /* Tablet & Medium Screens (481px to 768px) */
    @media (max-width: 768px) and (min-width: 481px) {
        .signup-container {
            width: 90%;
            max-width: 100%;
            padding: 30px;
        }
        
        .signup-container h2 {
            font-size: 26px;
            margin-bottom: 22px;
        }
        
        .input-group {
            margin-bottom: 16px;
        }
        
        .input-group input,
        .input-group select {
            padding: 11px;
            font-size: 15px;
        }
    }
    
    /* Small Mobile Phones (320px to 480px) */
    @media (max-width: 480px) {
        .signup-container {
            width: 100%;
            padding: 20px;
            border-radius: 12px;
            margin: 10px;
        }
        
        .signup-container h2 {
            font-size: 22px;
            margin-bottom: 18px;
        }
        
        .input-group {
            margin-bottom: 14px;
        }
        
        .input-group label {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .input-group input,
        .input-group select {
            padding: 10px;
            font-size: 14px;
        }
        
        .signup-btn {
            padding: 10px;
            font-size: 14px;
            margin-top: 8px;
        }
        
        .footer-text {
            font-size: 12px;
            margin-top: 14px;
        }
    }
    
    /* Large Tablets & Small Desktops (769px and above) */
    @media (min-width: 769px) {
        .signup-container {
            width: 420px;
        }
    }
</style>
</head>
<body>
<div class="signup-container">
    <h2>Create Account</h2>

    <?php if(!empty($error_msg)): ?>
        <div class="error-box"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="Name" required value="<?php echo isset($_POST['Name']) ? htmlspecialchars($_POST['Name']) : ''; ?>">
        </div>
        <div class="input-group">
            <label>Registration Number</label>
            <input type="text" name="Regno" required placeholder="e.g. BCS243000" value="<?php echo isset($_POST['Regno']) ? htmlspecialchars($_POST['Regno']) : ''; ?>">
        </div>
        <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="Email" required placeholder="student@university.edu" value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>">
        </div>
        <div class="input-group">
            <label>Program</label>
            <select name="Program" required>
                <option value="">Select Program</option>
                <option value="BSCS" <?php echo (isset($_POST['Program']) && $_POST['Program']=='BSCS')?'selected':''; ?>>BSCS</option>
                <option value="BBA" <?php echo (isset($_POST['Program']) && $_POST['Program']=='BBA')?'selected':''; ?>>BBA</option>
                <option value="BTech" <?php echo (isset($_POST['Program']) && $_POST['Program']=='BTech')?'selected':''; ?>>BTech</option>
            </select>
        </div>
        <div class="input-group">
            <label>Semester</label>
            <select name="Semester" required>
                <option value="">Select Semester</option>
                <?php 
                for($i=1; $i<=8; $i++) {
                    $sel = (isset($_POST['Semester']) && $_POST['Semester'] == $i) ? 'selected' : '';
                    echo "<option value='$i' $sel>Semester $i</option>"; 
                }
                ?>
            </select>
        </div>
        <div class="input-group">
            <label>Password (Min 8 chars, 1 Upper, 1 Special)</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" name="register" class="signup-btn">Create Account</button>
    </form>
    <div class="footer-text">Already have an account? <a href="Login.php">Login</a></div>
</div>
</body>
</html>