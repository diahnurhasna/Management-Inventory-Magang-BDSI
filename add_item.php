<?php
session_start();
require 'db.php'; // Ensure this file contains your database connection logic
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);
    $item_unit = trim($_POST['item_unit']);
    $item_value = trim($_POST['item_value']);
    $errors = [];

    // Validate input
    if (empty($item_name)) {
        $errors['item_name'] = 'Item name is required.';
    }

    if (empty($description)) {
        $errors['description'] = 'Description is required.';
    }

    // If there are no errors, proceed to insert the item into the database
    if (empty($errors)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO inventory (item_name, description, value, unit) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $item_name, $description, $item_value, $item_unit);

        if ($stmt->execute()) {
            // ✅ Log the action
            $username = $conn->real_escape_string($_SESSION['username']);
            $safe_item = $conn->real_escape_string($item_name);
            $log_msg = "User '{$username}' added new inventory item: '{$safe_item}'.";
            addLog($conn, $log_msg);

            // Redirect to inventory dashboard or show success message
            header("Location: item_manager.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>IT BDSI - Add Item</title>

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
                    <h1 class="h3 mb-2 text-gray-800">Add Item</h1>
                    <p class="mb-4">Adding item into inventory</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Lets add an item into our inventory!</h6>
                        </div>
                        <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <div>
                                <label for="item_name">Item Name:</label>
                                <input class="form-control bg-light border-0 small" type="text" name="item_name" value="<?php echo isset($item_name) ? htmlspecialchars($item_name) : ''; ?>">
                                <span><?php echo isset($errors['item_name']) ? $errors['item_name'] : ''; ?></span>
                            </div>
                            <div>
                                <label for="description">Description:</label>
                                <textarea class="form-control bg-light border-0 small" name="description"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                                <span><?php echo isset($errors['description']) ? $errors['description'] : ''; ?></span>
                            </div>
                            <div>
                                <label for="item_value">Item Value:</label>
                                <input type="number" class="form-control bg-light border-0 small" name="item_value"><?php echo isset($item_value) ? htmlspecialchars($item_value) : ''; ?></input>
                                <span><?php echo isset($errors['item_value']) ? $errors['item_value'] : ''; ?></span>
                            </div>
                            <div>
                                <label for="item_unit">Item Unit:</label>
                                <input type="text" class="form-control bg-light border-0 small" name="item_unit"><?php echo isset($item_unit) ? htmlspecialchars($item_value) : ''; ?></input>
                                <span><?php echo isset($errors['item_unit']) ? $errors['item_unit'] : ''; ?></span>
                            </div>
                            <div>
                                <button class="btn btn-primary" type="submit">Add Item</button>
                            </div>
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
