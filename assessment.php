<?php
session_start();
require_once "db.php";

// Auth Check
if (!isset($_SESSION['regno'])) {
    header("Location: Login.php");
    exit();
}

$current_student = $_SESSION['regno'];

// --- HANDLE INSERT ---
if(isset($_POST['add'])){
    $course = $_POST['CourseID'];
    $type = $_POST['AssessmentType']; 
    $day = $_POST['Day'];
    $deadline = $_POST['Deadline'];
    
    // Insert into Assessment
    $sql = "INSERT INTO Assessment (CourseID, AssessmentType, Day, Deadline) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $course, $type, $day, $deadline);
        if(mysqli_stmt_execute($stmt)){
            header("Location: assessment.php?status=added");
            exit();
        } else {
            die("Database Error: " . mysqli_stmt_error($stmt));
        }
    } else {
        die("Structure Error: Ensure 'AssessmentType' exists in your Assessment table.");
    }
}

// --- HANDLE DELETE ---
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM Assessment WHERE AssessmentID = $id");
    header("Location: assessment.php");
    exit();
}

// --- FETCH ASSESSMENTS ---
$query = "SELECT a.AssessmentID, a.CourseID, a.AssessmentType, a.Day, a.Deadline, c.Course_Name 
          FROM Assessment a 
          JOIN Course c ON a.CourseID = c.CourseID 
          JOIN marks m ON a.CourseID = m.CourseID
          WHERE m.RegNo = '$current_student'
          ORDER BY a.Deadline ASC";
$assessments = mysqli_query($conn, $query);

// --- FETCH COURSES FOR DROPDOWN ---
$course_query = "SELECT c.CourseID, c.Course_Name 
                 FROM Course c
                 JOIN marks m ON c.CourseID = m.CourseID
                 WHERE m.RegNo = '$current_student'";
$courses_dropdown = mysqli_query($conn, $course_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Management</title>
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
        .main { margin-left: 280px; flex: 1; padding: 40px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        input, select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 100%; box-sizing: border-box; }
        .btn-add { background: #10b981; color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 15px; font-size: 16px; transition: 0.3s; }
        .btn-add:hover { background: #059669; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9fafb; color: #4b5563; font-size: 13px; text-transform: uppercase; }
        .del-link { color: white; text-decoration: none; font-weight: bold; background: #ef4444; padding: 5px 10px; border-radius: 6px; display: inline-block; }
        .del-link:hover { background: white; color: #ef4444; border: 1px solid #ef4444; }
        .status-msg { background: #dcfce7; color: #166534; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        
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
            
            /* Responsive Table */
            table { font-size: 14px; }
            th, td { padding: 10px; }
            
            h1 { font-size: 24px; }
            h2 { font-size: 20px; }
            h3 { font-size: 18px; }
        }
        
        @media (max-width: 480px) {
            .main { padding: 15px; margin-top: 50px; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
            .card { padding: 15px; }
            .btn-add { padding: 12px; font-size: 14px; }
            .menu-toggle { padding: 12px 15px; font-size: 18px; }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <button class="menu-toggle" onclick="toggleMenu()">☰ Menu</button>
    <h2>Student Portal</h2>
    <a href="index.php">Dashboard</a>
    <a href="progressCalculator.php">Check Progress</a>
    <a href="assessment.php" class="active">Assessments</a>
    <a href="logout.php" style="color: #f87171; margin-top: 30px;">Logout</a>
</div>

<div class="main">
    <h1>Assessment Management</h1>

    <?php if(isset($_GET['status'])) echo "<div class='status-msg'>Assessment successfully added!</div>"; ?>

    <div class="card">
        <h3>Add New Assessment</h3>
        <form method="post">
            <div class="form-grid">
                <div>
                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Select Course</label>
                    <select name="CourseID" required>
                        <option value="">-- Choose Course --</option>
                        <?php while($c = mysqli_fetch_assoc($courses_dropdown)): ?>
                            <option value="<?php echo $c['CourseID']; ?>"><?php echo $c['Course_Name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Assessment Type</label>
                    <select name="AssessmentType" required>
                        <option value=""> -- Select Type -- </option>
                        <option value="Project">Project</option>
                        <option value="Assignment">Assignment</option>
                        <option value="Quiz">Quiz</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Submission Day</label>
                    <select name="Day" required>
                        <option value="">Choose the Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Deadline</label>
                    <input type="date" name="Deadline" required>
                </div>
            </div>
            <button name="add" class="btn-add">Add Assessment</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Course</th>
                <th>Type</th>
                <th>Day</th>
                <th>Deadline</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($assessments && mysqli_num_rows($assessments) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($assessments)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['Course_Name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['AssessmentType']); ?></td>
                    <td><?php echo htmlspecialchars($row['Day']); ?></td>
                    <td><span style="color:red; font-weight:bold;"><?php echo htmlspecialchars($row['Deadline']); ?></span></td>
                    <td>
                        <a href="?delete=<?php echo $row['AssessmentID']; ?>" 
                           class="del-link" 
                           onclick="return confirm('Delete this assessment?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding: 20px; color: #999;">No assessments found.</td></tr>
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