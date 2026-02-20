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
            transition: background 0.3s;
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

        .footer-text a:hover {
            text-decoration: underline;
        }
        
        /* Mobile Responsive */
        @media (max-width: 480px) {
            .signup-container {
                width: 100%;
                padding: 25px;
                border-radius: 12px;
            }
            
            .signup-container h2 {
                font-size: 24px;
                margin-bottom: 20px;
            }
            
            .input-group {
                margin-bottom: 15px;
            }
            
            .input-group label {
                font-size: 13px;
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
                font-size: 13px;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
<div class="signup-container">
    <h2>Create Account</h2>
    <form action="signup.php" method="POST">
        <div class="input-group">
            <label>Full Name</label>
            <input type="text" name="Name" required>
        </div>
        <div class="input-group">
            <label>Registration Number</label>
            <input type="text" name="Regno" required>
        </div>
        <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="Email" required>
        </div>
        <div class="input-group">
            <label>Program</label>
            <select name="Program" required>
                <option value="">Select Program</option>
                <option value="BSCS">BSCS</option>
                <option value="BBA">BBA</option>
                <option value="BTech">BTech</option>
            </select>
        </div>
        <div class="input-group">
            <label>Semester</label>
            <select name="Semester" required>
                <option value="">Select Semester</option>
                <?php for($i=1; $i<=8; $i++) echo "<option value='$i'>Semester $i</option>"; ?>
            </select>
        </div>
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" name="register" class="signup-btn">Create Account</button>
    </form>
    <div class="footer-text">Already have an account? <a href="Login.php">Login</a></div>
</div>
</body>
</html>