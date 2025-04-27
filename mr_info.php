<?php
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

$stmt = $conn->prepare("SELECT * FROM material_request WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
// Fetch associated items from mr_item table
$item_stmt = $conn->prepare("SELECT * FROM mr_item WHERE mr_id = ?");
$item_stmt->bind_param("i", $id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();

if ($result->num_rows === 0) {
    echo "Material request not found.";
    exit();
}

$request = $result->fetch_assoc();
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
                    <p class="mb-4">Material Request Details</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Material Detailed Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr><th>No</th><td><?php echo $request['No']; ?></td></tr>
                                    <tr><th>Ordered By</th><td><?php echo $request['requester_name']; ?></td></tr>
                                    <tr><th>China Controller</th><td><?php echo $request['china_controller']; ?></td></tr>
                                    <tr><th>Item</th><td><?php echo $request['item']; ?></td></tr>
                                    <tr><th>Description</th><td><?php echo $request['description']; ?></td></tr>
                                    <tr><th>Part Number</th><td><?php echo $request['pn']; ?></td></tr>
                                    <tr><th>Quantity</th><td><?php echo $request['quantity']; ?></td></tr>
                                    <tr><th>Unit</th><td><?php echo $request['unit']; ?></td></tr>
                                    <tr><th>Approved By</th><td><?php echo $request['approved_by']; ?></td></tr>
                                    <?php
                                    $color = 'black';
                                    switch ($request['status']) {
                                        case 'pending':
                                            $color = 'orange';
                                            break;
                                        case 'done':
                                            $color = 'lightgreen';
                                            break;
                                        case 'cancelled':
                                        case 'out of stock':
                                            $color = 'red';
                                            break;
                                    }
                                    ?>
                                    <tr>
                                        <th>Status</th>
                                        <td style="color: <?php echo $color; ?>;"><?php echo $request['status']; ?></td>
                                    </tr>
                                    <tr><th>Added Time</th><td><?php echo $request['added_time']; ?></td></tr>
                                    <tr><th>Last Changed</th><td><?php echo $request['last_changed']; ?></td></tr>
                                </tbody>
                            </table>
                            <h5 class="mt-4 mb-3">Items</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Part Number</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($item_result->num_rows > 0): ?>
                                        <?php while ($item = $item_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['item']); ?></td>
                                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                                <td><?php echo htmlspecialchars($item['pn']); ?></td>
                                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                                <td><?php echo htmlspecialchars($item['status']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No items found for this request.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <a href="material_request.php" class="btn btn-secondary">Back to List</a>
                            <a href="edit_request.php?id=<?php echo $request['id']; ?>" class="btn btn-success">Edit</a>

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