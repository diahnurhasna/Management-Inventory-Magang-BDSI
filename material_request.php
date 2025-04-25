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
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <!-- <i class="fas fa-laugh-wink"></i> -->
                </div>
                <div class="sidebar-brand-text mx-3">BDSI IT <sup>inventory</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Inventory
            </div>

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Inventory</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Custom Components:</h6>
                        <a class="collapse-item" href="item_manager.php">Item Manager</a>
                        <a class="collapse-item" href="material_request.php">Material Request</a>
                    </div>
                </div>
            </li>
            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <form class="form-inline">
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
                    </form>

                    <!-- Topbar Search -->
                    <form
                        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                                aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                       

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['username']; ?></span>
                                <img class="img-profile rounded-circle"
                                    src=<?php echo $_SESSION['profile']; ?>>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile_settings.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="settings.php">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="activity_log.php">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
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
                                        <th>ID</th>
                                        <th>No</th>
                                        <th>Ordered By</th>
                                        <th>China Controller Name</th>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Part Number</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                        <th>Added Date</th>
                                        <th>Last Changed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($request = $result->fetch_assoc()): ?>
                                    <tr>
                                        <form method="POST" action="">
                                            <td><a href="mr_info.php?id=<?php echo $request['id']; ?>"><?php echo $request['id']; ?></a></td>


                                            <!-- Editable Inputs -->
                                            <td><input type="text" name="No" value="<?php echo htmlspecialchars($request['No']); ?>" class="form-control"></td>
                                            <td><input type="text" name="requester_name" value="<?php echo htmlspecialchars($request['requester_name']); ?>" class="form-control" style="width: 150px;"></td>
                                            <td><input type="text" name="china_controller" value="<?php echo htmlspecialchars($request['china_controller']); ?>" class="form-control" style="width: 150px;"></td>
                                            <td><input type="text" name="item" value="<?php echo htmlspecialchars($request['item']); ?>" class="form-control" style="width: 120px;"></td>

                                            <td><input type="text" name="Description" value="<?php echo htmlspecialchars($request['Description']); ?>" class="form-control"></td>
                                            <td><input type="text" name="pn" value="<?php echo htmlspecialchars($request['pn']); ?>" class="form-control"></td>
                                            <td><input type="number" name="quantity" value="<?php echo htmlspecialchars($request['quantity']); ?>" class="form-control"></td>
                                            <td><input type="text" name="unit" value="<?php echo htmlspecialchars($request['unit']); ?>" class="form-control"></td>

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
                                            <td>
                                                <select name="status" style="width: 120px; color: <?php echo $color; ?>;" class="form-control">
                                                    <option value="pending" <?php if($request['status'] === 'pending') echo 'selected'; ?>>Pending</option>
                                                    <option value="done" <?php if($request['status'] === 'done') echo 'selected'; ?>>Done</option>
                                                    <option value="cancelled" <?php if($request['status'] === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                                    <option value="out of stock" <?php if($request['status'] === 'out of stock') echo 'selected'; ?>>Out of Stock</option>
                                                </select>
                                            </td>

                                            <td>
                                            <input type="datetime-local" name="added_date" value="<?php echo date('Y-m-d\TH:i', strtotime($request['added_date'])); ?>" class="form-control">
                                            </td>
                                            <td>
                                            <input type="datetime-local" name="last_changed" value="<?php echo date('Y-m-d\TH:i', strtotime($request['last_changed'])); ?>" class="form-control">
                                            </td>


                                            <!-- Action Buttons -->
                                            <td>
                                                <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                                <a href="remove_request.php?id=<?php echo $request['id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                                                <button type="submit" class="btn btn-success btn-sm">Update</button>
                                            </td>
                                        </form>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                                <a href="add_request.php" class="btn btn-primary btn-icon-split btn-sm"><span class="text">Add Request</span></a>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                    <span>Copyright &copy; BDSI IT Department 2025</span>
                    </div>
                </div>
            </footer>
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