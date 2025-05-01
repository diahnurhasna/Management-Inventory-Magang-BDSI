<?php
session_start();
require 'db.php';

if ($_POST['action'] === 'register') {
    header('Location: register.php');
    exit;
}

function addLog(mysqli $conn, string $msg): void {
    $stmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
    if (!$stmt) { die('Log prepare error: '.$conn->error); }
    $stmt->bind_param('s', $msg);
    if (!$stmt->execute()) { die('Log execute error: '.$stmt->error); }
    $stmt->close();
}

$settings = ['enable_login'=>1,'enable_register'=>1,'enable_website'=>1];
$res = $conn->query("SELECT enable_login,enable_register,enable_website FROM settings LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $settings = array_map('intval', $row);
}

if (!$settings['enable_website']) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
          <title>Maintenance</title>
          <style>body{margin:0;display:flex;justify-content:center;align-items:center;
                 height:100vh;background:#2b5876;color:#fff;font-family:Arial;}
          </style></head><body><h2>Site is currently under maintenance.<br>
          Please check back later.</h2></body></html>';
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); exit;
}

$errors = [];
$username = '';
$login_disabled_msg = '';

if ($settings['enable_login'] && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '') $errors[] = 'Username is required.';
    if ($password === '') $errors[] = 'Password is required.';

    if (!$errors) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $userRes = $stmt->get_result();

        if ($userRes && $userRes->num_rows) {
            $user = $userRes->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['profile'] = $user['profile_path'] ?: 'img/undraw_profile.svg';
                addLog($conn, "User \"{$username}\" successfully logged in.");
                header('Location: dashboard.php'); exit;
            } else {
                $errors[] = 'Invalid password.';
                addLog($conn, "Failed login for user \"{$username}\": wrong password.");
            }
        } else {
            $errors[] = 'No user found with that username.';
            addLog($conn, "Failed login: username \"{$username}\" not found.");
        }
        $stmt->close();
    }

} elseif (!$settings['enable_login']) {
    $login_disabled_msg = 'Login is temporarily disabled by the administrator.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | Inventory Manager</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.2/tailwind.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<!-- Font Awesome for the icons -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

<!-- SweetAlert2 Library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Glassmorphism Effect */
    .glassmorphism {
        background: rgba(255, 255, 255, 0.1); /* Semi-transparent white */
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }

    /* Button Hover Animation */
    .button-hover:hover {
        background-color: #6b46c1;
        color: white;
        transform: scale(1.1);
    }

    .button-disabled {
        background-color: #e2e8f0;
        color: #a0aec0;
        cursor: not-allowed;
    }

    /* Modal Animation */
    .fadeIn {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }
</style>
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex flex-col justify-center sm:py-12">
    <div class="glassmorphism lg:w-4/12 md:w-1/2 sm:w-6/12 w-full mx-auto rounded shadow">
        <div class="p-5 border-b-2">
            <h4 class="font-semibold uppercase text-gray-700">Login</h4>
        </div>
        <div class="p-5">
            <img class="mx-auto" src="logo.png" alt="Logo" style="width:120px;margin-bottom:10px" />
            <center>
                <p class="mb-3">Please login to continue</p>
            </center>

            <form id="loginForm" class="w-full" action="" method="POST">
                <div class="inline-grid w-full mb-3">
                    <input type="text" name="username" class="focus:outline-none focus:ring-2 ring-purple-300 bg-gray-200 w-full p-2 rounded" placeholder="Username">
                </div>
                <div class="inline-grid w-full mb-3">
                    <input type="password" name="password" class="focus:outline-none focus:ring-2 ring-purple-300 bg-gray-200 w-full p-2 rounded" placeholder="Password">
                </div>
            </form>
        </div>
        <div class="p-5 border-t-2 flex gap-1">
            <div class="inline-grid w-1/2 mb-3">
                <?php 
                if ($settings['enable_register'] === 1) {
                    echo '<button form="loginForm" type="submit" name="action" value="register" class="bg-purple-600 p-1.5 rounded font-semibold text-white button-hover">Register</button>';
                } else {
                    echo '<button class="bg-gray-300 text-gray-500 p-1.5 rounded font-semibold cursor-not-allowed button-disabled">Register Disabled</button>';
                }
                ?>
            </div>
            <div class="inline-grid w-1/2 mb-3">
                <?php 
                if ($settings['enable_login'] === 1) {
                    echo '<button form="loginForm" type="submit" name="action" value="login" class="p-1.5 rounded font-semibold text-purple-500 border border-purple-500 button-hover">Login</button>';
                } else {
                    echo '<button class="bg-gray-300 text-gray-500 p-1.5 rounded font-semibold cursor-not-allowed button-disabled">Login Disabled</button>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script src="js/index.js"></script>
<script>
    <?php if ($errors): ?>
        showSweetAlert('error', 'Error', '<?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>');
    <?php endif; ?>

    <?php if (!$errors && $login_disabled_msg): ?>
        showSweetAlert('error', 'Error', '<?php echo $login_disabled_msg; ?>');
    <?php endif; ?>
</script>

</body>
</html>
