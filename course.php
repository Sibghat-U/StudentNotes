<?php
require 'db.php';

// Fetch all courses
$courses = mysqli_query($conn, "SELECT * FROM Course");

// Add course
if(isset($_POST['add'])){
    $name = $_POST['Course_Name'];
    $grade = $_POST['Grade'];
    mysqli_query($conn, "INSERT INTO Course (Course_Name, Grade) VALUES ('$name','$grade')");
    header("Location: course.php");
}

// Delete course
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM Course WHERE Course_id=$id");
    header("Location: course.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: white;
            max-width: 600px;
            width: 100%;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .container h2 {
            color: #4f46e5;
            margin-top: 0;
            text-align: center;
        }

        form {
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }

        input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        button {
            padding: 12px 20px;
            background: #4ade80;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        button:hover {
            background: #22c55e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f9fafb;
            color: #4b5563;
            font-size: 13px;
            text-transform: uppercase;
            font-weight: 600;
        }

        tr:hover {
            background: #f9fafb;
        }

        a {
            color: #ef4444;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            background: #fee2e2;
            display: inline-block;
        }

        a:hover {
            background: #ef4444;
            color: white;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr;
            }

            button {
                width: 100%;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
                border-radius: 8px;
            }

            .container h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }

            form {
                gap: 8px;
            }

            input {
                padding: 10px;
                font-size: 13px;
            }

            button {
                padding: 10px;
                font-size: 13px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 8px;
            }

            a {
                padding: 4px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Course Management</h2>

        <form method="post">
            <input name="Course_Name" placeholder="Course Name" required>
            <input name="Grade" placeholder="Grade" required>
            <button name="add">Add Course</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($courses)) { ?>
                <tr>
                    <td><?php echo $row['CourseId']; ?></td>
                    <td><?php echo $row['Course_Name']; ?></td>
                    <td><?php echo $row['Grade']; ?></td>
                    <td><a href="course.php?delete=<?php echo $row['CourseId']; ?>" onclick="return confirm('Delete this course?')">Delete</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
