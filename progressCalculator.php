<?php
session_start();
require_once "db.php";

// Auth Check
if (!isset($_SESSION['regno'])) {
    header("Location: Login.php");
    exit();
}

// --- CONFIGURATION ---
$LIMIT_QUIZ = 10; $LIMIT_ASSIGN = 10; $LIMIT_MIDS = 20;
$LIMIT_FINALS = 40; $LIMIT_PROJ = 10; $LIMIT_CP = 5;

// --- AUTOMATICALLY GET LOGGED-IN STUDENT ---
$selectedReg = $_SESSION['regno']; 
$selectedCourse = $_POST['CourseID'] ?? '';

// --- BACKEND LOGIC ---
$msg = ""; $msgType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    // Secure Inputs
    $s_reg = mysqli_real_escape_string($conn, $selectedReg);
    $s_course = mysqli_real_escape_string($conn, $selectedCourse);

    // 1. SAVE CORE
    if ($action == 'save_core') {
        $mids = (float)$_POST['Mids'];
        $finals = (float)$_POST['Finals'];
        $proj = (float)$_POST['Project'];
        $cp = (float)$_POST['ClassParticipation'];

        if ($mids > $LIMIT_MIDS || $finals > $LIMIT_FINALS || $proj > $LIMIT_PROJ || $cp > $LIMIT_CP) {
            $msg = "Error: Marks exceed maximum limit allowed."; $msgType = "error";
        } else {
            $check = mysqli_query($conn, "SELECT MarksID FROM marks WHERE RegNo='$s_reg' AND CourseID='$s_course'");
            if (mysqli_num_rows($check) > 0) {
                $row = mysqli_fetch_assoc($check);
                $id = $row['MarksID'];
                mysqli_query($conn, "UPDATE marks SET Mids='$mids', Finals='$finals', Project='$proj', ClassParticipation='$cp' WHERE MarksID='$id'");
            } else {
                mysqli_query($conn, "INSERT INTO marks (RegNo, CourseID, Mids, Finals, Project, ClassParticipation) VALUES ('$s_reg', '$s_course', '$mids', '$finals', '$proj', '$cp')");
            }
            $msg = "Core assessments saved."; $msgType = "success";
        }
    }
    // 2. ADD WORK
    elseif ($action == 'add_work') {
        $type = $_POST['work_type'];
        $marks = (float)$_POST['work_marks'];
        $limit = ($type == 'quiz') ? $LIMIT_QUIZ : $LIMIT_ASSIGN;
        
        if ($marks > $limit) {
            $msg = "Error: Marks cannot exceed $limit"; $msgType = "error";
        } else {
            $col1 = ($type == 'quiz') ? $marks : "NULL";
            $col2 = ($type == 'quiz') ? "NULL" : $marks;
            mysqli_query($conn, "INSERT INTO continuousTests (RegNo, CourseID, quiz, assignment) VALUES ('$s_reg', '$s_course', $col1, $col2)");
            $msg = "Added new " . ucfirst($type); $msgType = "success";
        }
    }
    // 3. DELETE WORK
    elseif ($action == 'delete_work') {
        $cwId = (int)$_POST['cw_id'];
        mysqli_query($conn, "DELETE FROM continuousTests WHERE id='$cwId'");
        $msg = "Entry deleted."; $msgType = "warning";
    }
}

// --- FETCH DATA ---
$courses = mysqli_query($conn, "SELECT course.CourseID, Course_Name FROM course join marks ON marks.CourseID = course.CourseID WHERE marks.RegNo = '$selectedReg'");

$coreData = []; $courseWorkData = [];
$obt_quiz = 0; $max_quiz_total = 0;
$obt_assign = 0; $max_assign_total = 0;
$obt_core = 0; $max_core_total = 0;

if ($selectedCourse) {
    $s_reg = mysqli_real_escape_string($conn, $selectedReg);
    $s_course = mysqli_real_escape_string($conn, $selectedCourse);

    // Fetch Core
    $coreRes = mysqli_query($conn, "SELECT * FROM marks WHERE RegNo='$s_reg' AND CourseID='$s_course'");
    $coreData = mysqli_fetch_assoc($coreRes) ?: [];

    if (!empty($coreData)) {
        if ($coreData['Mids'] > 0) { $obt_core += $coreData['Mids']; $max_core_total += $LIMIT_MIDS; }
        if ($coreData['Finals'] > 0) { $obt_core += $coreData['Finals']; $max_core_total += $LIMIT_FINALS; }
        if ($coreData['Project'] > 0) { $obt_core += $coreData['Project']; $max_core_total += $LIMIT_PROJ; }
        if ($coreData['ClassParticipation'] > 0) { $obt_core += $coreData['ClassParticipation']; $max_core_total += $LIMIT_CP; }
    }

    // Fetch CourseWork
    $cwRes = mysqli_query($conn, "SELECT * FROM continuousTests WHERE RegNo='$s_reg' AND CourseID='$s_course'");
    $courseWorkData = mysqli_fetch_all($cwRes, MYSQLI_ASSOC);

    foreach ($courseWorkData as $cw) {
        if (!is_null($cw['quiz'])) { $obt_quiz += $cw['quiz']; $max_quiz_total += $LIMIT_QUIZ; }
        if (!is_null($cw['assignment'])) { $obt_assign += $cw['assignment']; $max_assign_total += $LIMIT_ASSIGN; }
    }
}

// Calculate
$grandObtained = $obt_quiz + $obt_assign + $obt_core;
$grandMax = $max_quiz_total + $max_assign_total + $max_core_total;
$percentage = ($grandMax > 0) ? ($grandObtained / $grandMax) * 100 : 0;

function getGrade($p) {
    if ($p > 85) return "A"; elseif ($p >= 82) return "A-"; elseif ($p >= 78) return "B+";
    elseif ($p >= 74) return "B"; elseif ($p >= 70) return "B-"; elseif ($p >= 66) return "C+";
    elseif ($p >= 62) return "C"; elseif ($p >= 58) return "C-"; elseif ($p >= 54) return "D+";
    elseif ($p >= 50) return "D"; else return "F";
}
$grade = ($grandMax > 0) ? getGrade($percentage) : "N/A";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Calculator</title>
    <style>
        * { box-sizing: border-box; }
        /* Shared Styles with Dashboard */
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
        
        /* Calculator Specific Styles */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; box-sizing: border-box; }
        label { font-weight: bold; color: #555; display: block; margin-bottom: 5px; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; width: 100%; }
        .btn-green { background: #10b981; }
        .btn-blue { background: #3b82f6; }
        .btn-red { background: #ef4444; width: auto; padding: 5px 10px; }
        
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
        .warning { background: #fef3c7; color: #92400e; }
        
        .grade-box { background: #1f2937; color: white; text-align: center; padding: 20px; border-radius: 8px; }
        .grade-box h1 { font-size: 3em; margin: 10px 0; color: #10b981; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f9fafb; color: #4b5563; font-size: 13px; text-transform: uppercase; }
        
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
            
            .grid-2 { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .form-row { flex-direction: column; gap: 10px; }
            
            h1 { font-size: 24px; }
            h2 { font-size: 20px; }
            h3 { font-size: 18px; }
            
            .grade-box h1 { font-size: 2em; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
        }
        
        @media (max-width: 480px) {
            .main { padding: 15px; margin-top: 50px; }
            .card { padding: 15px; }
            .btn { padding: 8px 12px; font-size: 14px; }
            table { font-size: 11px; }
            th, td { padding: 5px; }
            .menu-toggle { padding: 12px 15px; font-size: 18px; }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <button class="menu-toggle" onclick="toggleMenu()">☰ Menu</button>
    <h2>Student Portal</h2>
    <a href="index.php">Dashboard</a>
    <a href="progressCalculator.php" style="color: white; font-weight: bold;">Check Progress</a>
    <a href="assessment.php">Assessments</a>
    <a href="logout.php" style="color: #f87171; margin-top: 20px;">Logout</a>
</div>

<div class="main">
    <h1>Course Progress Calculator</h1>
    
    <?php if($msg) echo "<div class='alert $msgType'>$msg</div>"; ?>

    <div class="card">
        <h3>Select Course to View Progress</h3>
        <form method="POST" class="form-row">
            <select name="CourseID" onchange="this.form.submit()">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['CourseID'] ?>" <?= $selectedCourse==$c['CourseID']?'selected':'' ?>>
                        <?= htmlspecialchars($c['Course_Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>


    <?php if ($selectedCourse): ?>
    
    <div class="grid-2">
        <div class="card">
            <h3>One-Time Exams</h3>
            <form method="POST">
                <input type="hidden" name="CourseID" value="<?= $selectedCourse ?>">
                
                <label>Mids (Max <?= $LIMIT_MIDS ?>)</label>
                <input type="number" name="Mids" step="0.5" max="<?= $LIMIT_MIDS ?>" value="<?= $coreData['Mids'] ?? '' ?>">
                
                <label>Finals (Max <?= $LIMIT_FINALS ?>)</label>
                <input type="number" name="Finals" step="0.5" max="<?= $LIMIT_FINALS ?>" value="<?= $coreData['Finals'] ?? '' ?>">
                
                <label>Project (Max <?= $LIMIT_PROJ ?>)</label>
                <input type="number" name="Project" step="0.5" max="<?= $LIMIT_PROJ ?>" value="<?= $coreData['Project'] ?? '' ?>">
                
                <label>CP (Max <?= $LIMIT_CP ?>)</label>
                <input type="number" name="ClassParticipation" step="0.5" max="<?= $LIMIT_CP ?>" value="<?= $coreData['ClassParticipation'] ?? '' ?>">
                
                <button name="action" value="save_core" class="btn btn-green" style="margin-top: 15px;">Save Core Marks</button>
            </form>
        </div>

        <div class="card">
            <h3>Quizzes & Assignments</h3>
            <form method="POST" style="background: #f9fafb; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <input type="hidden" name="CourseID" value="<?= $selectedCourse ?>">
                <input type="hidden" name="action" value="add_work">
                
                <div style="display: flex; gap: 10px;">
                    <select name="work_type" style="flex:1;">
                        <option value="quiz">Quiz (Max <?= $LIMIT_QUIZ ?>)</option>
                        <option value="assignment">Assignment (Max <?= $LIMIT_ASSIGN ?>)</option>
                    </select>
                    <input type="number" name="work_marks" placeholder="Marks" step="0.5" required style="flex:1;">
                    <button class="btn btn-blue" style="width: 50px;">+</button>
                </div>
            </form>

            <div style="max-height: 300px; overflow-y: auto;">
                <table>
                    <thead><tr><th>Type</th><th>Obtained</th><th>Max</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($courseWorkData as $cw): 
                            $isQuiz = !is_null($cw['quiz']);
                            $val = $isQuiz ? $cw['quiz'] : $cw['assignment'];
                            $type = $isQuiz ? 'Quiz' : 'Assignment';
                            $max = $isQuiz ? $LIMIT_QUIZ : $LIMIT_ASSIGN;
                        ?>
                        <tr>
                            <td><span style="color: <?= $isQuiz?'#d97706':'#2563eb' ?>; font-weight:bold;"><?= $type ?></span></td>
                            <td><?= $val ?></td>
                            <td style="color:#999;">/ <?= $max ?></td>
                            <td>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="CourseID" value="<?= $selectedCourse ?>">
                                    <input type="hidden" name="cw_id" value="<?= $cw['id'] ?>">
                                    <button name="action" value="delete_work" class="btn btn-red">X</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card full-width">
            <h3>Summary & Grading</h3>
            <div class="grid-2">
                <div>
                    <table>
                        <tr style="background:#f9fafb;"><th>Component</th><th>Obtained</th><th>Total Possible</th></tr>
                        <tr><td>Quizzes</td><td><?= $obt_quiz ?></td><td><?= $max_quiz_total ?></td></tr>
                        <tr><td>Assignments</td><td><?= $obt_assign ?></td><td><?= $max_assign_total ?></td></tr>
                        <tr><td>Core Exams</td><td><?= $obt_core ?></td><td><?= $max_core_total ?></td></tr>
                        <tr style="border-top: 2px solid #333; font-weight:bold;">
                            <td>GRAND TOTAL</td>
                            <td><?= $grandObtained ?></td>
                            <td><?= $grandMax ?></td>
                        </tr>
                    </table>
                </div>
                <div class="grade-box">
                    <p>Current Percentage</p>
                    <h1><?= number_format($percentage, 2) ?>%</h1>
                    <p style="font-size: 1.2em; border-top: 1px solid #374151; padding-top: 10px;">
                        Grade: <strong style="color: #10b981;"><?= $grade ?></strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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