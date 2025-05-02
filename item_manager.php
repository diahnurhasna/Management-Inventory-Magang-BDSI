<?php
session_start();
require 'db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Take Item request
if (isset($_POST['take_item'])) {
    $take_item_id = $_POST['take_item_id'];
    $takenBy = $_POST['taken_by'];
    $takenDate = $_POST['taken_date'];
    $takenValue = $_POST['taken_value'];

    // Sanitize and validate
    $take_item_id = mysqli_real_escape_string($conn, $take_item_id);
    $takenBy = htmlspecialchars(trim($takenBy));
    $takenDate = htmlspecialchars(trim($takenDate));
    $takenValue = filter_var($takenValue, FILTER_VALIDATE_INT);

    if ($takenValue === false || $takenValue <= 0) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Invalid taken value.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
        exit();
    }

    // 1. Get current item_name and status from inventory
    $sql_select = "SELECT item_name, status, `value` FROM inventory WHERE id = '$take_item_id'";
    $result_select = mysqli_query($conn, $sql_select);

    if ($result_select && $row_inventory = mysqli_fetch_assoc($result_select)) {
        $itemName = $row_inventory['item_name'];
        $itemStatus = $row_inventory['status'];
        $itemValue = $row_inventory['value'];

        // 2. Check if there's enough value
        if ($itemValue >= $takenValue) {
            // 3. Update inventory table (reduce 'value' and set status to 'taken')
            $newValue = $itemValue - $takenValue;
            $sql_update = "UPDATE inventory SET `value` = '$newValue', status = 'taken', taken_by = '$takenBy', taken_date = '$takenDate' WHERE id = '$take_item_id'";
            if (mysqli_query($conn, $sql_update)) {
                // 4. Insert into taken_values
                $sql_insert = "INSERT INTO taken_values (item_name, taken_by, taken_date, taken_value, item_id) 
                               VALUES ('$itemName', '$takenBy', '$takenDate', '$takenValue', '$take_item_id')";

                if (mysqli_query($conn, $sql_insert)) {
                    echo "<script>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Item taken successfully!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'item_manager.php';
                        }
                    });
                    </script>";
                } else {
                    $error_message = "Error inserting into taken_values: " . mysqli_error($conn);
                    echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: '$error_message',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    </script>";
                }
            } else {
                $error_message = "Error updating inventory: " . mysqli_error($conn);
                echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: '$error_message',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                </script>";
            }
        } else {
            echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Not enough value to take.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            </script>";
        }
        mysqli_free_result($result_select);
    } else {
        $error_message = "Error fetching inventory data: " . mysqli_error($conn);
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: '$error_message',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Fetch inventory list
$query = "SELECT * FROM inventory";
if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
    $status_filter = $_GET['status_filter'];
    $query .= " WHERE status = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status_filter);
} else {
    $stmt = $conn->prepare($query);
}

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
    <title>IT BDSI - Item Manager</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'includes/navbar.php'; ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Item Manager</h1>
                    <p class="mb-4">Item List in our inventory in and out</a>.</p>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Item Manager table</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <label for="status_filter">Filter by Status: </label>
                                <form method="GET" action="" style="display: flex; align-items: center;">
                                    <select style="width: 200px;" class="form-control border-50 bg-light" name="status_filter" id="status_filter">
                                        <option value="">All</option>
                                        <option value="taken" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'taken') ? 'selected' : ''; ?>>Taken</option>
                                        <option value="available" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'available') ? 'selected' : ''; ?>>Available</option>
                                    </select>
                                    <button class="btn btn-danger" type="submit" style="margin-left: 10px;">Filter</button>
                                </form>
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Description</th>
                                            <th>Value</th>
                                            <th>Status</th>
                                            <th>Added Date</th>
                                            <th>Last Taken By</th>
                                            <th>Taken Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        while ($item = $result->fetch_assoc()) : ?>
                                            <tr>
                                                <form method="POST" action="">
                                                    <td><a href="item_info.php?id=<?php echo $item['id']; ?>"><?php echo $count++; ?></a></td>
                                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['value']); ?></td>
                                                    <td>
                                                        <?php echo $item['status']; ?>
                                                    </td>
                                                    <td><?php echo date('Y-m-d H:i', strtotime($item['added_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($item['taken_by']); ?></td>
                                                    <td><?php echo ($item['taken_date'] ? date('Y-m-d H:i', strtotime($item['taken_date'])) : ''); ?></td>
                                                    <td width="120px">
                                                        <input type="hidden" name="take_item_id" value="<?php echo $item['id']; ?>">
                                                        <button type="button" class="btn btn-success btn-sm take-button" data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>" data-item-id="<?php echo $item['id']; ?>">
                                                            Take
                                                        </button>
                                                        <button type="button" 
                                                        class="btn btn-danger btn-sm remove-button"
                                                        data-item-id="<?php echo $item['id']; ?>"
                                                        data-item-name="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                        Remove
                                                    </button>

                                                    </td>
                                                </form>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <a href="add_item.php" class="btn btn-danger btn-icon-split btn-sm"><span class="text">Add Item</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

    <div id="takeItemModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Take Item: <span id="modal-item-name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="takeItemForm" method="POST" action="">
                        <input type="hidden" name="take_item_id" id="modal-item-id">
                        <div class="form-group">
                            <label for="taken_by">Taken By:</label>
                            <input type="text" class="form-control" id="taken_by" name="taken_by" required>
                        </div>
                        <div class="form-group">
                            <label for="taken_date">Taken Date:</label>
                            <input type="datetime-local" class="form-control" id="taken_date" name="taken_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="taken_value">Taken Value:</label>
                            <input type="number" class="form-control" id="taken_value" name="taken_value" min="1" required>
                        </div>
                        <button type="submit" name="take_item" class="btn btn-primary">Submit Take</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="js/demo/datatables-demo.js"></script>
    <script>
        $(document).ready(function() {
            $('.take-button').click(function() {
                var itemName = $(this).data('item-name');
                var itemId = $(this).data('item-id');
                $('#modal-item-name').text(itemName);
                $('#modal-item-id').val(itemId);
                $('#takeItemModal').modal('show');
            });

            $('#takeItemModal').on('hidden.bs.modal', function() {
                $('#takeItemForm')[0].reset();
            });
        });
        $('.remove-button').click(function() {
            var itemId = $(this).data('item-id');
            var itemName = $(this).data('item-name');

            Swal.fire({
                title: 'Are you sure?',
                text: 'This will remove "' + itemName + '" from the inventory!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to remove_item.php?id=
                    window.location.href = "remove_item.php?id=" + itemId;
                }
            });
        });
    </script>
</body>

</html>