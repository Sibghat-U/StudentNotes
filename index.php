<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();require_once "db.php";

// Auth Check
if (!isset($_SESSION['regno'])) {
    header("Location: Login.php");
    exit();
}
$current_student = $_SESSION['regno']; // Get Logged-in Student ID
$current_student_name = $_SESSION['name'];
$edit_mode = false;
$row_to_edit = ['CourseID' => '', 'Course_Name' => ''];

// --- HANDLE INSERT COURSE (ENROLL) ---
// --- HANDLE INSERT COURSE (ENROLL) ---
if (isset($_POST['insert_course'])) {
    $cid = $_POST['course_id'];
    $cname = $_POST['course_name'];

    // 1. Check Global Course Catalog
    $check_global = mysqli_query($conn, "SELECT CourseID FROM course WHERE CourseID='$cid'");
    
    if(mysqli_num_rows($check_global) == 0) {
        // Case A: Course doesn't exist -> INSERT IT
        $stmt_global = mysqli_prepare($conn, "INSERT INTO course (CourseID, Course_Name) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_global, "ss", $cid, $cname);
        mysqli_stmt_execute($stmt_global);
    } else {
        // Case B: Course exists -> UPDATE THE NAME (The Fix)
        // This ensures if you re-add with a new name, it overwrites the old one
        $stmt_update = mysqli_prepare($conn, "UPDATE course SET Course_Name=? WHERE CourseID=?");
        mysqli_stmt_bind_param($stmt_update, "ss", $cname, $cid);
        mysqli_stmt_execute($stmt_update);
    }

    // 2. Link to Student (Enroll in Marks table)
    $check_enroll = mysqli_query($conn, "SELECT MarksID FROM marks WHERE RegNo='$current_student' AND CourseID='$cid'");
    if(mysqli_num_rows($check_enroll) == 0) {
        $stmt_enroll = mysqli_prepare($conn, "INSERT INTO marks (RegNo, CourseID) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_enroll, "ss", $current_student, $cid);
        if(mysqli_stmt_execute($stmt_enroll)) {
            header("Location: index.php");
        } else {
            echo "Error enrolling: " . mysqli_error($conn);
        }
    } else {
        header("Location: index.php");
    }
}

// --- HANDLE UPDATE COURSE ---
if (isset($_POST['update_course'])) {
    $old_cid = $_POST['old_course_id'];
    $new_cid = $_POST['course_id'];     
    $cname = $_POST['course_name'];

    // Update global course details
    $sql = "UPDATE course SET CourseID=?, Course_Name=? WHERE CourseID=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $new_cid, $cname, $old_cid);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php");
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

// --- HANDLE DELETE COURSE (UN-ENROLL) ---
if (isset($_GET['delete_course'])) {
    $cid = $_GET['delete_course'];
    
    // Only delete the enrollment record for THIS student
    // This keeps the course in the database but removes it from this student's list
    $sql = "DELETE FROM marks WHERE CourseID='$cid' AND RegNo='$current_student'";
    mysqli_query($conn, $sql);
    header("Location: index.php");
}

// --- PREPARE EDIT MODE ---
if (isset($_GET['edit_course'])) {
    $edit_mode = true;
    $cid = $_GET['edit_course'];
    $res = mysqli_query($conn, "SELECT * FROM course WHERE CourseID='$cid'");
    $row_to_edit = mysqli_fetch_assoc($res);
}

// 1. Fetch Courses (FILTERED by Logged-in Student)
// We join the 'marks' table to find which courses this student is enrolled in
$query = "SELECT c.* FROM course c 
          JOIN marks m ON c.CourseID = m.CourseID 
          WHERE m.RegNo = '$current_student' 
          ORDER BY c.CourseID ASC";
$courses = mysqli_query($conn, $query);

// 2. Fetch Assessments (FILTERED by Logged-in Student)
// We join 'marks' to ensure we only show assessments for enrolled courses
$queryAssess = "SELECT a.*, c.Course_Name 
                FROM Assessment a 
                JOIN Course c ON a.CourseID = c.CourseID 
                JOIN marks m ON c.CourseID = m.CourseID
                WHERE m.RegNo = '$current_student'
                ORDER BY a.Deadline ASC LIMIT 3";
$dash_assessments = mysqli_query($conn, $queryAssess);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management Portal</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; display: flex; margin: 0; background: #f4f7f6; }
        
        /* Toggle Button for Mobile */
        .menu-toggle { display: none; background: #374151; color: white; border: none; padding: 15px 20px; font-size: 20px; cursor: pointer; width: 100%; margin: 0; border-bottom: 1px solid #1f2937; text-align: left; }
        .menu-toggle:hover { background: #4b5563; }
        
        .sidebar { width: 220px; background: #1f2937; color: white; height: 100vh; padding: 0; position: fixed; overflow-y: auto; z-index: 100; display: flex; flex-direction: column; left: 0; }
        .sidebar h2 { color: #10b981; margin-bottom: 20px; margin-top: 0; padding: 20px 20px 0 20px; }
        .sidebar a { display: block; color: #d1d5db; text-decoration: none; padding: 12px 20px; border-bottom: 1px solid #374151; }
        .sidebar a:hover { color: white; background: #374151; padding-left: 5px; transition: 0.3s; }
        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .quick-actions { display: flex; gap: 15px; margin-bottom: 30px; }
        .btn-large { flex: 1; padding: 20px; text-decoration: none; color: white; border-radius: 8px; font-size: 18px; font-weight: bold; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .btn-large:hover { transform: translateY(-3px); }
        .btn-green { background-color: #28a745; }
        .btn-blue { background-color: #007bff; }
        
        /* Form Grid adjusted for Course inputs */
        .form-grid { display: grid; grid-template-columns: 1fr 2fr auto; gap: 15px; align-items: end; }
        
        input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; box-sizing: border-box; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-insert { background: #10b981; color: white; }
        .btn-update { background: #3b82f6; color: white; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-bottom: 30px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9fafb; color: #4b5563; font-size: 13px; text-transform: uppercase; }
        
        .quick-actions { display: flex; gap: 15px; margin-bottom: 30px; }
        .btn-large { flex: 1; padding: 20px; text-decoration: none; color: white; border-radius: 8px; font-size: 18px; font-weight: bold; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .btn-large:hover { transform: translateY(-3px); }
        .btn-green { background-color: #28a745; }
        .btn-blue { background-color: #007bff; }
        
        .action-links a { margin-right: 10px; text-decoration: none; font-size: 14px; font-weight: bold; }
        .edit-btn { color: #3b82f6; }
        .del-btn { color: #ef4444; }
        .deadline-badge { color: #ef4444; font-weight: bold; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .view-all { font-size: 14px; color: #10b981; text-decoration: none; font-weight: bold; }
        
        /* General Button Style */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            width: 100%;
            transition: all 0.3s ease;
            text-align: center;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-insert {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-update {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .action-links a {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            margin-right: 5px;
            transition: background 0.2s;
        }

        .edit-btn {
            background-color: #e0f2fe;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }

        .edit-btn:hover {
            background-color: #0284c7;
            color: white;
        }

        .del-btn {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .del-btn:hover {
            background-color: #dc2626;
            color: white;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .menu-toggle { display: block; position: fixed; top: 0; left: 0; z-index: 102; width: auto; padding: 15px 20px; }
            .sidebar { width: 100%; position: fixed; top: 0; left: 0; height: auto; background: #1f2937; padding: 50px 0 0 0; max-height: 0; overflow: hidden; transition: max-height 0.3s ease; z-index: 101; border-bottom: 1px solid #374151; }
            .sidebar.active { max-height: 500px; }
            .sidebar h2 { display: none; }
            .sidebar a { padding: 15px 20px; border-bottom: 1px solid #374151; font-size: 14px; }
            body::before { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); opacity: 0; visibility: hidden; transition: opacity 0.3s ease; z-index: 99; }
            body.sidebar-open::before { opacity: 1; visibility: visible; }
            .main { margin-left: 0; padding: 20px; margin-top: 50px; }
            
            .form-grid { grid-template-columns: 1fr; gap: 10px; }
            .quick-actions { flex-direction: column; gap: 10px; }
            .btn-large { padding: 15px; font-size: 16px; }
            
            .section-header { flex-direction: column; align-items: flex-start; }
            
            /* Responsive Table */
            table { font-size: 14px; }
            th, td { padding: 10px; }
            
            .action-links { display: flex; flex-direction: column; gap: 5px; }
            .action-links a { margin-right: 0; display: block; }
            
            h1 { font-size: 24px; }
            h2 { font-size: 20px; }
            h3 { font-size: 18px; }
        }
        
        @media (max-width: 480px) {
            .main { padding: 15px; margin-top: 50px; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
            .card { padding: 15px; }
            .btn-large { font-size: 14px; padding: 12px; }
            .menu-toggle { padding: 12px 15px; font-size: 18px; }
        }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <button class="menu-toggle" onclick="toggleMenu()">☰ Menu</button>
    <h2>Student Portal</h2>
    <a href="index.php" style="color: white; font-weight: bold;">Dashboard</a>
    <a href="progressCalculator.php">Check Progress</a>
    <a href="assessment.php">Assessments</a>
    <a href="logout.php" style="color: #f87171; margin-top: 20px;">Logout</a>
</div>

<div class="main">
    <h1> Student Management Portal</h1><?php echo "<h2>Welcome, " . $current_student_name . "</h2>"; ?>

    <div class="quick-actions">
        <a href="assessment.php" class="btn-large btn-green">Plan Assessments</a>
        <a href="progressCalculator.php" class="btn-large btn-blue">Calculate Course Progress</a>
    </div>

    <div class="card">
        <h3><?php echo $edit_mode ? "Edit Course Details" : "Add New Course"; ?></h3>
        <form method="POST" action="index.php">
            <input type="hidden" name="old_course_id" value="<?php echo htmlspecialchars($row_to_edit['CourseID']); ?>">
            
            <div class="form-grid">
                <div>
                    <label style="font-weight:bold; font-size:12px; color:#555;">Course ID</label>
                    <input type="text" name="course_id" placeholder="e.g. CS2141" value="<?php echo htmlspecialchars($row_to_edit['CourseID']); ?>" required>
                </div>
                <div>
                    <label style="font-weight:bold; font-size:12px; color:#555;">Course Name</label>
                    <input type="text" name="course_name" placeholder="e.g. Data Structures" value="<?php echo htmlspecialchars($row_to_edit['Course_Name']); ?>" required>
                </div>
                <div>
                    <button type="submit" name="<?php echo $edit_mode ? 'update_course' : 'insert_course'; ?>" class="btn <?php echo $edit_mode ? 'btn-update' : 'btn-insert'; ?>">
                        <?php echo $edit_mode ? 'Update Course' : 'Add Course'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="section-header">
        <h2>Courses Enrolled</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($courses) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($courses)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['CourseID']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['Course_Name']); ?></td>
                    <td class="action-links">
                        <a href="?edit_course=<?php echo $row['CourseID']; ?>" class="edit-btn">Edit</a>
                        <a href="?delete_course=<?php echo $row['CourseID']; ?>" class="del-btn" onclick="return confirm('Are you sure you want to delete this course? All related marks/assessments will be deleted.')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align:center; padding: 20px; color: #999;">No courses added yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-header">
        <h2>Upcoming Assessments</h2>
        <a href="assessment.php" class="view-all">Manage All →</a>
    </div>
    <table>
        <thead><tr><th>Course Name</th><th>Type</th><th>Assigned</th><th>Deadline</th></tr></thead>
        <tbody>
            <?php if(mysqli_num_rows($dash_assessments) > 0): ?>
                <?php while($ass = mysqli_fetch_assoc($dash_assessments)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($ass['Course_Name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($ass['AssessmentType']); ?></td>
                    <td><?php echo htmlspecialchars($ass['Day']); ?></td>
                    <td><span class="deadline-badge"><?php echo htmlspecialchars($ass['Deadline']); ?></span></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center; padding: 20px; color: #999;">No assessments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    sidebar.classList.toggle('active');
    body.classList.toggle('sidebar-open');
}

// Close menu when clicking on a link
document.querySelectorAll('.sidebar a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    });
});

// Close menu on window resize if screen is large
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        document.getElementById('sidebar').classList.remove('active');
        document.body.classList.remove('sidebar-open');
    }
});
</script>
</body>
</html>