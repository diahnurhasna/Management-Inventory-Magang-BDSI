<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid request ID.";
    exit();
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update material_request
    $no = $_POST['No'];
    $requester_name = $_POST['requester_name'];
    $china_controller = $_POST['china_controller'];
    $item = $_POST['item'];
    $description = $_POST['description'];
    $pn = $_POST['pn'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $approved_by = $_POST['approved_by'];
    $status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE material_request SET No=?, requester_name=?, require_for=? ,china_controller=? ,aknowledge_by=? ,approved_by=?, status=?, last_changed=NOW() WHERE id=?");
    $update_stmt->bind_param("isssssssssi", $no, $requester_name, $china_controller, $approved_by, $status, $id);
    $update_stmt->execute();

    // update mr_item records
    foreach ($_POST['item_id'] as $index => $item_id) {
        $item_name = $_POST['item_name'][$index];
        $item_description = $_POST['item_description'][$index];
        $item_pn = $_POST['item_pn'][$index];
        $item_quantity = $_POST['item_quantity'][$index];
        $item_unit = $_POST['item_unit'][$index];

        $item_update_stmt = $conn->prepare("UPDATE mr_item SET item=?, description=?, pn=?, quantity=?, unit=? WHERE id=?");
        $item_update_stmt->bind_param("sssssi", $item_name, $item_description, $item_pn, $item_quantity, $item_unit, $item_id);
        $item_update_stmt->execute();
    }

    echo "<script>alert('Request and item details updated successfully!'); window.location.href='material_request.php?id=$id';</script>";
    exit();
}

// Fetch material request
$stmt = $conn->prepare("SELECT * FROM material_request WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Material request not found.";
    exit();
}
$request = $result->fetch_assoc();

// Fetch mr_item records
$item_stmt = $conn->prepare("SELECT * FROM mr_item WHERE mr_id = ?");
$item_stmt->bind_param("i", $id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();
$items = [];
while ($row = $item_result->fetch_assoc()) {
    $items[] = $row;
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

    <title>IT BDSI - Material Requests</title>

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
                    <h1 class="h3 mb-2 text-gray-800">Material Request Info</h1>
                    <p class="mb-4">Material Request Edit</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Material Detailed Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <form method="post">
                                <!-- Material Request Editable Table -->
                                <form method="POST">

                            <table class="table table-bordered">
                                <tbody>
                                    <tr><th>No</th><td><input type="number" name="No" value="<?php echo $request['No']; ?>" class="form-control"></td></tr>
                                    <tr><th>Ordered By</th><td><input type="text" name="requester_name" value="<?php echo $request['requester_name']; ?>" class="form-control"></td></tr>
                                    <tr><th>Require For</th><td><input type="text" name="require_for" value="<?php echo $request['require_for']; ?>" class="form-control"></td></tr>
                                    <tr><th>China Controller</th><td><input type="text" name="china_controller" value="<?php echo $request['china_controller']; ?>" class="form-control"></td></tr>
                                    <tr><th>Aknowledge By</th><td><input type="text" name="aknowledge_by" value="<?php echo $request['aknowledge_by']; ?>" class="form-control"></td></tr>
                                    <tr><th>Approved By</th><td><input type="text" name="approved_by" value="<?php echo $request['approved_by']; ?>" class="form-control"></td></tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <select name="status" class="form-control">
                                                <option value="pending" <?php if($request['status']=='pending') echo 'selected'; ?>>Pending</option>
                                                <option value="done" <?php if($request['status']=='done') echo 'selected'; ?>>Done</option>
                                                <option value="cancelled" <?php if($request['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                                                <option value="approve" <?php if($request['status']=='approve') echo 'selected'; ?>>Approve</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <h4>Item Details</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>P/N</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($items as $row) {
                                        echo "<tr>";
                                        echo "<td>$no</td>";
                                        echo "<input type='hidden' name='item_id[]' value='" . $row['id'] . "'>";
                                        echo "<td><input type='text' name='item_name[]' value='" . htmlspecialchars($row['item']) . "' class='form-control'></td>";
                                        echo "<td><input type='text' name='item_description[]' value='" . htmlspecialchars($row['description']) . "' class='form-control'></td>";
                                        echo "<td><input type='text' name='item_pn[]' value='" . htmlspecialchars($row['pn']) . "' class='form-control'></td>";
                                        echo "<td><input type='text' name='item_quantity[]' value='" . htmlspecialchars($row['quantity']) . "' class='form-control'></td>";
                                        echo "<td><input type='text' name='item_unit[]' value='" . htmlspecialchars($row['unit']) . "' class='form-control'></td>";
                                        echo "</tr>";
                                        $no++;
                                    }
                                    ?>
                                </tbody>
                            </table>

                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="mr_info.php?id=<?php echo $id; ?>" class="btn btn-secondary">Back</a>
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