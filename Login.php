<?php
session_start();
require_once "db.php"; // Ensure this file exists in the same folder

$error_msg = "";

// Handle Form Submission
if (isset($_POST['login'])) {
    // FIX 1: trim() removes accidental spaces from input
    $regno = trim($_POST['regno']);
    $regno = mysqli_real_escape_string($conn, $regno);
    
    $password = $_POST['password'];

    $query = "SELECT * FROM student WHERE RegNo = '$regno'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify Password
        if (password_verify($password, $row['password'])) {
            $_SESSION['regno'] = $row['RegNo'];
            $_SESSION['name'] = $row['Name'];
            
            // Redirect to Dashboard
            header("Location: index.php");
            exit();
        } else {
            $error_msg = "Invalid Password. Please try again.";
        }
    } else {
        $error_msg = "User not found. Please check RegNo.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login</title>
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

    .login-container {
        background: white;
        width: 380px;
        padding: 35px;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        animation: fadeIn 0.7s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-container h2 {
        text-align: center;
        color: #4f46e5;
        margin-bottom: 25px;
        margin-top: 0;
    }

    .input-group {
        margin-bottom: 18px;
    }

    .input-group label {
        display: block;
        margin-bottom: 6px;
        color: #374151;
        font-size: 14px;
    }

    .input-group input {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
        box-sizing: border-box;
    }

    .input-group input:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.2);
    }

    .login-btn {
        width: 100%;
        padding: 12px;
        background: #4ade80;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        font-weight: bold;
        color: white;
        transition: background 0.3s;
    }

    .login-btn:hover {
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

    .footer-text a:hover {
        text-decoration: underline;
    }

    /* Error Message Style */
    .error-box {
        background-color: #fee2e2;
        color: #991b1b;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        font-size: 14px;
        margin-bottom: 20px;
        border: 1px solid #f87171;
    }
    
    /* Mobile Responsive */
    @media (max-width: 480px) {
        .login-container {
            width: 100%;
            padding: 25px;
            border-radius: 12px;
        }
        
        .login-container h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        .input-group label {
            font-size: 13px;
        }
        
        .input-group input {
            padding: 10px;
            font-size: 14px;
        }
        
        .login-btn {
            padding: 10px;
            font-size: 14px;
        }
        
        .footer-text {
            font-size: 13px;
            margin-top: 15px;
        }
    }
</style>
</head>

<body>

<div class="login-container">
    <h2>Student Login</h2>
    
    <?php if(!empty($error_msg)): ?>
        <div class="error-box">
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">

        <div class="input-group">
            <label>Registration Number</label>
            <input type="text" name="regno" required placeholder="e.g. BCS243000">
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit" name="login" class="login-btn">
            Login
        </button>
    </form>

    <div class="footer-text">
        Don’t have an account?
        <a href="signup.php">Create Account</a>
    </div>
</div>

</body>
</html>