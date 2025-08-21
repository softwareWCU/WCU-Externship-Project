<?php
require 'database_connection.php'; // your database connection
$db = new Database();
$conn = $db->getConnection();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Upload Users CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-wrapper {
            width: 80%;
            margin-left: auto;   /* push to right */
            margin-right: 0;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .duplicate {
            background-color: #f8d7da; /* red-ish background for duplicates */
        }
    </style>
</head>
<body class="p-4">

<h3>Upload CSV Users</h3>

<!-- Upload Form -->
<form action="" method="post" enctype="multipart/form-data" class="mb-3">
    <input type="file" name="file" accept=".csv" required>
    <button type="submit" name="preview" class="btn btn-primary">Preview</button>
</form>

<?php
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
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-striped'>";
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
        echo "</div>"; // close table-responsive
        echo "<button type='submit' name='insert' class='btn btn-success'>Insert into Database</button>";
        echo "</form>";
        echo "</div>"; // close table-wrapper
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
</body>
</html>
