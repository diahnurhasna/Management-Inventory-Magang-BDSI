<?php
session_start();
require 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if registration is enabled
$stmt = $conn->prepare("SELECT enable_register FROM settings WHERE id = 1");
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();

if (isset($settings['enable_register']) && $settings['enable_register'] == 0) {
    header("Location: login.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate input
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if (empty($confirm_password)) {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = 'Username already exists.';
    }

    // Insert if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $email = "";
        $profile_path = "";
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, profile_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $profile_path);
        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Inventory Manager</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.2/tailwind.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .glassmorphism {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }
    .button-hover:hover {
        background-color: #6b46c1;
        color: white;
        transform: scale(1.05);
    }
</style>
</head>
<body class="bg-gray-100">

<div class="min-h-screen flex flex-col justify-center sm:py-12">
    <div class="glassmorphism lg:w-4/12 md:w-1/2 sm:w-6/12 w-full mx-auto rounded shadow">
        <div class="p-5 border-b-2">
            <h4 class="font-semibold uppercase text-gray-700">Register</h4>
        </div>
        <div class="p-5">
            <img class="mx-auto" src="logo.png" alt="Logo" style="width:120px;margin-bottom:10px" />
            <center>
                <p class="mb-3">Create your account</p>
            </center>

            <form class="w-full" action="" method="POST">
                <div class="inline-grid w-full mb-3">
                    <input type="text" name="username" class="focus:outline-none focus:ring-2 ring-purple-300 bg-gray-200 w-full p-2 rounded" placeholder="Username" required>
                </div>
                <div class="inline-grid w-full mb-3">
                    <input type="password" name="password" class="focus:outline-none focus:ring-2 ring-purple-300 bg-gray-200 w-full p-2 rounded" placeholder="Password" required>
                </div>
                <div class="inline-grid w-full mb-3">
                    <input type="password" name="confirm_password" class="focus:outline-none focus:ring-2 ring-purple-300 bg-gray-200 w-full p-2 rounded" placeholder="Confirm Password" required>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-purple-600 w-full p-2 rounded font-semibold text-white button-hover">Register</button>
                    <a href="login.php" class="bg-gray-400 w-full p-2 rounded font-semibold text-white text-center button-hover">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
<?php if (!empty($errors)) : ?>
    Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>',
        confirmButtonColor: '#6b46c1'
    });
<?php endif; ?>
</script>

</body>
</html>
