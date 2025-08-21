<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
// Prevent browser caching
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
 <script>
window.history.forward();
function noBack() { window.history.forward(); }
window.onload = noBack;
window.onpageshow = function(evt) { if(evt.persisted) noBack(); };
window.onunload = function() {};
</script>

    <title>Home</title>
    <link rel="stylesheet" href="style.css">
     

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .table-wrapper {
            width: 80%;
            margin-left: auto;
            margin-right: 0;
        }
        .duplicate {
            background-color: #f8d7da; /* red background for duplicates */
        }
    </style>
</head>

<body >
    <header>
        <div class="logo-container">
            <img src="logo.png" alt="Website Logo" class="logo">
        </div>
        <h3>Utility Service Request and Provision Management System for Wachemo University</h3>
    
    </header>

    <!-- Sidebar Dashboard -->
    <!-- Sidebar Dashboard -->
<!-- Sidebar Dashboard -->
<nav class="sidebar">
    <h2>Dashboard</h2>
    <ul>
        <li class="dropdown">
    <a href="#">👥 Manage Users ▾</a>
    <ul class="dropdown-menu">
        <li><a href="add_user.php">⬆️ Upload Staff</a></li>
        <li><a href="manage_users.php?status=all">👤 UserAccount</a></li>
    </ul>
</li>

        <li><a href="logout.php">⏻ Logout</a></li>
    </ul>
</nav>


    
    <div class="home-content">
        <!-- Upload Form -->
<form action="" method="post" enctype="multipart/form-data" class="mb-3">
    <input type="file" name="file" accept=".csv" required>
    <button type="submit" name="preview" class="btn btn-primary">Preview</button>
</form>

<?php
require_once 'database_connection.php';



$db = new Database();
$conn = $db->getConnection();
if(isset($_POST['preview']) && isset($_FILES['file'])){
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if($ext != 'csv'){
        echo "<div class='alert alert-danger'>Only CSV files allowed.</div>";
        exit;
    }

    $uploadDir = 'uploads/';
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filePath = $uploadDir.basename($file['name']);
    if(!move_uploaded_file($file['tmp_name'], $filePath)){
        die("Failed to upload file.");
    }

    $rows = [];
    if(($handle = fopen($filePath,'r')) !== false){
        $header = fgetcsv($handle, 1000, ",");
        while(($data = fgetcsv($handle, 1000, ",")) !== false){
            $rows[] = array_combine($header,$data);
        }
        fclose($handle);
    }

    if(count($rows) > 0){
        echo "<div class='table-wrapper'>";
        echo "<form action='' method='post'>";
        echo "<input type='hidden' name='file_path' value='$filePath'>";
        echo "<table id='previewTable' class='table table-bordered table-striped'>";
        echo "<thead><tr><th>#</th><th>Username</th><th>Email</th><th>Status</th><th>Duplicate</th></tr></thead><tbody>";

        $i=1;
        foreach($rows as $row){
            $username = $row['username'] ?? '';
            $email = $row['email'] ?? '';
            $status = strtolower($row['status'] ?? 'active');

            // Check duplicate
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username=?");
            $stmt->bind_param('s',$email);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            $isDuplicate = $count > 0 ? true : false;

            echo "<tr".($isDuplicate ? " class='duplicate'" : "").">";
            echo "<td>$i</td>";
            echo "<td><input type='hidden' name='username[]' value='$username'>$username</td>";
            echo "<td><input type='hidden' name='email[]' value='$email'>$email</td>";
            echo "<td><input type='hidden' name='status[]' value='$status'>$status</td>";
            echo "<td>".($isDuplicate ? 'Yes' : 'No')."</td>";
            echo "</tr>";
            $i++;
        }

        echo "</tbody></table>";
        echo "<button type='submit' name='insert' class='btn btn-success'>Insert into Database</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>No data found in file.</div>";
    }
}

if(isset($_POST['insert'])){
    $usernames = $_POST['username'];
    $emails = $_POST['email'];
    $statuses = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO users (username,email,status) VALUES (?,?,?)");
    $inserted = 0;

    for($i=0;$i<count($emails);$i++){
        // Skip duplicates
        $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE email=?");
        $check->bind_param('s',$emails[$i]);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
        if($count>0) continue;

        $stmt->bind_param('sss',$usernames[$i],$emails[$i],$statuses[$i]);
        if($stmt->execute()) $inserted++;
    }
    echo "<div class='alert alert-success'>$inserted users inserted successfully.</div>";
    $stmt->close();
}
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#previewTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        lengthChange: true,
        pageLength: 10
    });
});
</script>
        </div>



<script>
document.addEventListener("DOMContentLoaded", function() {
    const dropdownToggle = document.querySelector(".dropdown-toggle");
    const dropdownMenu = document.querySelector(".dropdown-menu");

    dropdownToggle.addEventListener("click", function(e) {
        e.preventDefault(); // stop page reload
        dropdownMenu.style.display =
            dropdownMenu.style.display === "block" ? "none" : "block";
    });

    // Close if clicked outside
    document.addEventListener("click", function(e) {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = "none";
        }
    });
});
</script>

</body>
<!--footer>
    &copy; 2025 Externship Group-AAP | All rights reserved.
</footer-->

</html>