<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
function addLog($conn, $message) {
    // Use prepared statement to prevent SQL injection and handle special characters
    $stmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
    if ($stmt === false) {
        // Error preparing the statement
        die('Error preparing statement: ' . $conn->error);
    }

    // Bind parameters securely
    $stmt->bind_param("s", $message);

    // Execute the query
    if (!$stmt->execute()) {
        die('Error executing query: ' . $stmt->error);
    }

    // Close the statement
    $stmt->close();
}
$user_id = $_SESSION['user_id'];
$username = isset($_SESSION['username']) ? $conn->real_escape_string($_SESSION['username']) : 'Unknown';
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update username
    if (!empty($_POST['username'])) {
        $new_username = $conn->real_escape_string($_POST['username']);
        if ($new_username !== $_SESSION['username']) {
            $conn->query("UPDATE users SET username = '$new_username' WHERE id = $user_id");
            addLog($conn, "INSERT INTO logs (log_message) VALUES ('User \"$username\" changed username to \"$new_username\"')");
            $_SESSION['username'] = $new_username;
        }
    }

    // Update password
    if (!empty($_POST['password'])) {
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password = '$new_password' WHERE id = $user_id");
        addLog($conn, "INSERT INTO logs (log_message) VALUES ('User \"$username\" updated their password')");
    }

    // Upload profile picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/profile/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $conn->query("UPDATE users SET profile_path = '$target_file' WHERE id = $user_id");
                addLog($conn, "INSERT INTO logs (log_message) VALUES ('User \"$username\" updated their profile picture')");
            } else {
                $message = "Error uploading the profile picture.";
            }
        } else {
            $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    $message = $message ?: "Profile updated successfully!";
    $_SESSION['profile'] = $target_file;
}

// Get current user data
$result = $conn->query("SELECT username, profile_path FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>IT BDSI - Profile Settings</title>

    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include 'includes/navbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">BDSI IT Inventory</h1>
                    <p class="mb-4">Profile Settings</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Modify the profile as you wish!</h6>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Profile Settings</h5>

                            <?php if (!empty($message)): ?>
                                <div class="alert alert-info"><?php echo $message; ?></div>
                            <?php endif; ?>

                            <form method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input class="form-control bg-light border-0 small" type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                    <input class="form-control bg-light border-0 small" type="password" name="password" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label  for="profile_picture" class="form-label">Profile Picture</label><br>
                                    <?php if (!empty($user['profile_path'])): ?>
                                        <img src="<?php echo $user['profile_path']; ?>" alt="Profile Picture" style="width: 100px; height: auto; border-radius: 50%; margin-bottom: 10px;">
                                    <?php endif; ?>
                                    <input class="form-control bg-light border-0 small" type="file" name="profile_picture" class="form-control">
                                </div>

                                <button class="btn btn-primary" type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>

</body>

</html>