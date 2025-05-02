<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $no = intval($_POST['No']);
    $requester_name = $_POST['requester_name'];
    $china_controller = $_POST['china_controller'];
    $item = $_POST['item'];
    $description = $_POST['Description'];
    $pn = $_POST['pn'];
    $quantity = intval($_POST['quantity']);
    $unit = $_POST['unit'];
    $status = $_POST['status'];
    $added_date = $_POST['added_date'];
    $last_changed = $_POST['last_changed'];

    $stmt = $conn->prepare("UPDATE material_request 
        SET No=?, requester_name=?, china_controller=?, item=?, Description=?, pn=?, quantity=?, unit=?, status=?, added_date=?, last_changed=? 
        WHERE id=?");

    $stmt->bind_param("isssssisssssi", $no, $requester_name, $china_controller, $item, $description, $pn, $quantity, $unit, $status, $added_date, $last_changed, $id);

    if ($stmt->execute()) {
        header("Location: material_request.php?message=Row updated");
        exit();
    } else {
        echo "Error updating row: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}


$stmt = $conn->prepare("SELECT * FROM material_request");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <h1 class="h3 mb-2 text-gray-800">Material Request</h1>
                    <p class="mb-4">All the materials request</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Material Request Table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                    <th>#</th>
                                    <th>No</th>
                                        <th>Ordered By</th>
                                        <th>require for</th>
                                        <th>aknowledge by</th>
                                        <th>approved_by</th>
                                        <th>China Controller Name</th>
                                        <th>Status</th>
                                        <th>Added Date</th>
                                        <th>Last Changed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $count = 1;
                                     while ($request = $result->fetch_assoc()): ?>
                                    <tr>
                                        <form method="POST" action="">
                                            <td><a href="mr_info.php?id=<?php echo $request['id']; ?>"><?php echo $count++; ?></a></td>


                                            <td><?php echo htmlspecialchars($request['No']); ?></td>
                                            <td><?php echo htmlspecialchars($request['requester_name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['require_for']); ?></td>
                                            <td><?php echo htmlspecialchars($request['aknowledge_by']); ?></td>
                                            <td><?php echo htmlspecialchars($request['approved_by']); ?></td>
                                            <td><?php echo htmlspecialchars($request['china_controller']); ?></td>

                                            <!-- Status Dropdown -->
                                            <?php
                                                $color = 'black';
                                                switch ($request['status']) {
                                                    case 'pending': $color = 'orange'; break;
                                                    case 'done': $color = 'lightgreen'; break;
                                                    case 'cancelled':
                                                    case 'out of stock': $color = 'red'; break;
                                                }
                                            ?>
                                            <td style="color: <?php echo $color; ?>;">
                                                <?php echo $request['status'] ?>
                                            </td>

                                            <td>
                                            <?php echo date('Y-m-d H:i', strtotime($request['added_date']));?>
                                            </td>
                                            <td>
                                            <?php echo date('Y-m-d H:i', strtotime($request['last_changed'])); ?>
                                            </td>


                                            <!-- Action Buttons -->
                                            <td>
                                                <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmRemove(<?php echo $request['id']; ?>)">Remove</button>
                                                </td>
                                        </form>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                                <a href="add_request.php" class="btn btn-danger btn-icon-split btn-sm"><span class="text">Add Request</span></a>
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
<script>
function confirmRemove(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will permanently remove the request!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove it!',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'remove_request.php?id=' + id;
        }
    })
}
</script>

</html>