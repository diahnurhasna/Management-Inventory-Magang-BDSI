<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php';
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
    $requester_name = trim($_POST['requester_name']);
    $require_for = trim($_POST['require_for']);
    
    $errors = [];

    // Validate input
    if (empty($requester_name)) {
        $errors['requester_name'] = 'Requester name is required.';
    }
    if (empty($require_for)) {
        $errors['require_for'] = 'require_for is required.';
    }

    // If no errors, insert request into database
    if (empty($errors)) {
        // Insert into material_request table
        $stmt = $conn->prepare("INSERT INTO material_request (requester_name, require_for) VALUES (?, ?)");
        $stmt->bind_param("ss", $requester_name, $require_for);
        
        if ($stmt->execute()) {
            // Get the last inserted ID
            $last_id = $stmt->insert_id;
    
            // Now insert each item row into mr_item
            if (!empty($_POST['item'])) {
                $items = $_POST['item'];
                $descriptions = $_POST['description'];
                $pns = $_POST['pn'];
                $quantities = $_POST['quantity'];
                $units = $_POST['unit'];
    
                // Prepare the insert statement once
                $stmt_item = $conn->prepare("INSERT INTO mr_item (mr_id, item, description, pn, quantity, unit) VALUES (?, ?, ?, ?, ?, ?)");
    
                // Loop through each item row
                for ($i = 0; $i < count($items); $i++) {
                    // Skip empty rows if necessary
                    if (empty($items[$i]) && empty($descriptions[$i]) && empty($pns[$i])) {
                        continue;
                    }
    
                    $item = $items[$i];
                    $description = $descriptions[$i];
                    $pn = $pns[$i];
                    $quantity = $quantities[$i];
                    $unit = $units[$i];
    
                    $stmt_item->bind_param("isssis", $last_id, $item, $description, $pn, $quantity, $unit);
                    $stmt_item->execute();
                }
    
                $stmt_item->close();
            }
    
            // ✅ Log the action
            $username = $conn->real_escape_string($_SESSION['username']);
            $safe_require_for = $conn->real_escape_string($require_for);
            $log_msg = "User '{$username}' submitted a material request for '{$safe_require_for}'.";
            addLog($conn, $log_msg);
    
            header("Location: material_request.php");
            exit();
    
        } else {
            $errors['general'] = 'Failed to add request. Please try again.';
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

    <title>IT BDSI - Add Material Requests</title>

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
                    <h1 class="h3 mb-2 text-gray-800">Add Material Request</h1>
                    <p class="mb-4">Adding material request into our list</a>.</p>

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">lets add Material request into our list!</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <div>
                                    <label for="requester_name">Ordered By:</label>
                                    <input class="form-control bg-light border-0 small" type="text" name="requester_name" value="<?php echo isset($requester_name) ? $requester_name : ''; ?>">
                                    <span><?php echo isset($errors['requester_name']) ? $errors['requester_name'] : ''; ?></span>
                                </div>
                                <div>
                                    <label for="require_for">require for:</label>
                                    <input class="form-control bg-light border-0 small" type="text" name="require_for" value="<?php echo isset($require_for) ? $require_for : ''; ?>">
                                    <span><?php echo isset($errors['require_for']) ? $errors['require_for'] : ''; ?></span>
                                </div>
                                <div>
                                <table id="itemsTable" class="table table-bordered">
    <thead>
        <tr>
            <th>Item</th>
            <th>Description</th>
            <th>P/N</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <!-- Initial row -->
        <tr>
            <td><input type="text" name="item[]"></td>
            <td><input type="text" name="description[]"></td>
            <td><input type="text" name="pn[]"></td>
            <td><input type="number" name="quantity[]"></td>
            <td><input type="text" name="unit[]"></td>
            <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
            </td>
        </tr>
    </tbody>
</table>



<script>
function addRow() {
    const tableBody = document.getElementById("itemsTable").getElementsByTagName('tbody')[0];
    const newRow = document.createElement('tr');

    newRow.innerHTML = `
        <td><input type="text" name="item[]"></td>
        <td><input type="text" name="description[]"></td>
        <td><input type="text" name="pn[]"></td>
        <td><input type="number" name="quantity[]"></td>
        <td><input type="text" name="unit[]"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
    `;

    tableBody.appendChild(newRow);
}

function removeRow(button) {
    button.closest('tr').remove();
}
</script>



                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm" type="submit">Add Request</button>
                                    <button class="btn btn-primary btn-sm" type="button" onclick="addRow()">Add Row</button>

                                </div>
                                </form>
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