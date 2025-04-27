<?php
include 'db.php'; // DB connection
session_start();  // Start session to get username if available

$message = "";
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
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Settings toggle values
    $enable_login = isset($_POST['enable_login']) ? 1 : 0;
    $enable_register = isset($_POST['enable_register']) ? 1 : 0;
    $enable_website = isset($_POST['enable_website']) ? 1 : 0;

    // Update settings table
    $sql = "UPDATE settings SET enable_login=?, enable_register=?, enable_website=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $enable_login, $enable_register, $enable_website);

    if ($stmt->execute()) {
        $message = "Settings updated successfully.";

        // ✅ Log the settings update
        $username = isset($_SESSION['username']) ? $conn->real_escape_string($_SESSION['username']) : 'Unknown';
        $log_msg = "User '{$username}' updated system settings: login={$enable_login}, register={$enable_register}, website={$enable_website}";
        addLog($conn, $log_msg);
    } else {
        $message = "Error updating settings: " . $conn->error;
    }

    $stmt->close();
}

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT enable_login, enable_register, enable_website FROM settings LIMIT 1");
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
}

// Dummy user array for demo (replace with real user data)
$user = [
    'username' => 'admin',
    'profile_path' => '' // or actual profile image path
];
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>IT BDSI - Settings</title>

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
                    <p class="mb-4">Item List in our inventory in and out</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">System Settings</h6>
                        </div>
                        <div class="card-body">
                        <div class="card-body">
    <h5 class="card-title">Our System Settings</h5>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <!-- Settings Toggles -->
        <hr>
        <h6 class="mt-3">System Toggles</h6>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="enable_login" id="enable_login" value="1" <?php echo ($settings['enable_login'] ?? 0) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="enable_login">Enable Login</label>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="enable_register" id="enable_register" value="1" <?php echo ($settings['enable_register'] ?? 0) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="enable_register">Enable Register</label>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="enable_website" id="enable_website" value="1" <?php echo ($settings['enable_website'] ?? 0) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="enable_website">Enable Website</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-success">Save Settings</button>
    </form>
</div>
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