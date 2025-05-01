<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if ID is passed
if (!isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "No item ID specified.";
    exit();
}

// Handle POST: Update item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $added_date = $_POST['added_date'];
    $taken_by = $_POST['taken_by'];
    $taken_date = $_POST['taken_date'];
    $item_value = $_POST['item_value'];
    $stmt = $conn->prepare("UPDATE inventory SET item_name = ?, description = ?, status = ?, added_date = ?, taken_by = ?, taken_date = ?, value = ? WHERE id = ?");
$stmt->bind_param("ssssssii", $item_name, $description, $status, $added_date, $taken_by, $taken_date, $item_value, $id);

if ($stmt->execute()) {
    echo "Item updated successfully."; // This will let you know if it's updating correctly
    header("Location: edit_item.php?id=" . $_GET['id']);
    exit();
} else {
    // Check for SQL errors
    echo "Failed to update item. Error: " . $stmt->error;
}

}

// Fetch current data for display (for GET or after failed POST)
$id = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);

$stmt = $conn->prepare("SELECT * FROM inventory WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Item not found.";
    exit();
}

$item = $result->fetch_assoc();
?>



<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>IT BDSI - Item Information</title>

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
                    <h1 class="h3 mb-2 text-gray-800">Inventory Item Info</h1>
                    <p class="mb-4">Item Editing</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Edit Item Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <form method="post" action="">
                                    <table class="table table-bordered" cellpadding="10">
                                        <tr>
                                            <th>ID</th>
                                            <td>
                                                <input type="text" name="id" value="<?php echo $item['id']; ?>" readonly class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Item Name</th>
                                            <td>
                                                <input type="text" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>
                                                <textarea name="description" class="form-control"><?php echo htmlspecialchars($item['description']); ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <select name="status" class="form-control">
                                                    <option value="available" <?php if($item['status'] === 'available') echo 'selected'; ?>>Available</option>
                                                    <option value="taken" <?php if($item['status'] === 'taken') echo 'selected'; ?>>Taken</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Added Date</th>
                                            <td>
                                                <input type="datetime-local" name="added_date" value="<?php echo date('Y-m-d\TH:i', strtotime($item['added_date'])); ?>" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Taken By</th>
                                            <td>
                                                <input type="text" name="taken_by" value="<?php echo htmlspecialchars($item['taken_by']); ?>" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Taken Date</th>
                                            <td>
                                                <input type="datetime-local" name="taken_date" value="<?php echo date('Y-m-d\TH:i', strtotime($item['taken_date'])); ?>" class="form-control">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Value</th>
                                            <td>
                                                <input type="text" name="item_value" value="<?php echo htmlspecialchars($item['value']); ?>" class="form-control">
                                            </td>
                                        </tr>
                                    </table>

                                    <button type="submit" class="btn btn-success">Update Item</button>
                                    <a href=<?php echo "item_info.php?id=" . $item['id']; ?> class="btn btn-primary">Back</a>
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