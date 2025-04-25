<?php
/******************************************************************
 *  login.php  —  Inventory Manager
 ******************************************************************/
session_start();
require 'db.php';                         //  database connection

/* ---------- helper: add entry to logs table ------------------- */
function addLog(mysqli $conn, string $msg): void
{
    $stmt = $conn->prepare("INSERT INTO logs (log_message) VALUES (?)");
    if (!$stmt) { die('Log prepare error: '.$conn->error); }
    $stmt->bind_param('s', $msg);
    if (!$stmt->execute()) { die('Log execute error: '.$stmt->error); }
    $stmt->close();
}

/* ---------- 1. read global settings --------------------------- */
$settings = ['enable_login'=>1,'enable_register'=>1,'enable_website'=>1];      // failsafe defaults
$res = $conn->query("SELECT enable_login,enable_register,enable_website
                     FROM settings LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $settings = array_map('intval', $row);
}

/* ---------- 2. maintenance mode ? ----------------------------- */
if (!$settings['enable_website']) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
          <title>Maintenance</title>
          <style>body{margin:0;display:flex;justify-content:center;align-items:center;
                 height:100vh;background:#2b5876;color:#fff;font-family:Arial;}
          </style></head><body><h2>Site is currently under maintenance.<br>
          Please check back later.</h2></body></html>';
    exit;
}

/* ---------- 3. already logged in? ------------------------------*/
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');  exit;
}

/* ---------- 4. process login when allowed --------------------- */
$errors = [];
$username = '';
$login_disabled_msg = '';

if ($settings['enable_login'] && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '')    $errors['username'] = 'Username is required.';
    if ($password === '')    $errors['password'] = 'Password is required.';

    if (!$errors) {                                        // DB lookup
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $userRes = $stmt->get_result();

        if ($userRes && $userRes->num_rows) {
            $user = $userRes->fetch_assoc();
            if (password_verify($password, $user['password'])) {

                /* login success */
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['profile']  = $user['profile_path'] ?: 'img/undraw_profile.svg';

                addLog($conn, "User \"{$username}\" successfully logged in.");
                header('Location: dashboard.php'); exit;

            } else {
                $errors['password'] = 'Invalid password.';
                addLog($conn, "Failed login for user \"{$username}\": wrong password.");
            }
        } else {
            $errors['username'] = 'No user found with that username.';
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
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300" rel="stylesheet">
    <style>
        body{background:#2b5876;background:linear-gradient(to right,#2b5876,#4e4376)}
        .login-form{font-family:Arial;width:346px;text-align:center;position:absolute;
                    top:50%;left:50%;transform:translate(-50%,-50%)}
        .login-form input{height:45px;border:none;border-radius:4px;font-size:13px;
                          box-shadow:rgba(0,0,0,.1) 3px 3px 5px}
        .login-form .login-username,
        .login-form .login-password{width:256px;padding:0 20px;border-radius:4px 0 0 4px}
        .login-form label{width:45px;height:45px;background:#fff;display:inline-block;
                          border-radius:0 4px 4px 0;margin-left:-2px}
        .login-form label svg{fill:#2b5876;width:15px;height:100%}
        .login-submit{width:301px;color:#fff;background:#2ecc71;font-weight:bold;cursor:pointer}
        .login-submit:hover{background:#27ae60}
        .login-motd{color:#fff;font-size:14px;width:300px;margin:0 auto 20px}
        .alert{color:#fff;margin-top:10px;font-size:13px}
    </style>
</head>
<body>
<div class="login-form">
    <img src="logo.png" alt="Logo" style="width:120px;margin-bottom:10px">
    <p class="login-motd">IT Department Inventory Manager</p>

    <?php if ($login_disabled_msg): ?>
        <div class="alert"><?php echo $login_disabled_msg; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div style="margin-top:15px">
            <input id="username" class="login-username" type="text" name="username"
                   placeholder="Username" value="<?php echo htmlspecialchars($username); ?>">
            <label for="username"><svg><use xlink:href="#user"/></svg></label><br>
            <span class="alert"><?php echo $errors['username'] ?? ''; ?></span>
        </div>

        <div style="margin-top:15px">
            <input id="password" class="login-password" type="password" name="password"
                   placeholder="Password">
            <label for="password"><svg><use xlink:href="#padlock"/></svg></label><br>
            <span class="alert"><?php echo $errors['password'] ?? ''; ?></span>
        </div>

        <div style="margin-top:15px">
            <input class="login-submit" type="submit" value="Log in"
                   <?php echo $settings['enable_login'] ? '' : 'disabled style="opacity:.5;cursor:not-allowed"' ?>>
        </div>
    </form>

    <?php if ($settings['enable_register']): ?>
        <a href="register.php" style="color:#fff;font-size:12px;display:block;margin-top:18px">Register?</a>
    <?php endif; ?>
</div>

<!-- svg icons -->
<svg style="display:none">
    <symbol id="user" viewBox="0 0 1792 1792">
        <path d="M1329 784q47 14 89.5 38t89 73 79.5 115.5 55 172 22 236.5q0 154-100 263.5t-241 109.5h-854q-141 0-241-109.5t-100-263.5q0-131 22-236.5t55-172 79.5-115.5 89-73 89.5-38q-79-125-79-272 0-104 40.5-198.5t109.5-163.5 163.5-109.5 198.5-40.5 198.5 40.5 163.5 109.5 109.5 163.5 40.5 198.5q0 147-79 272zm-433-656q-159 0-271.5 112.5t-112.5 271.5 112.5 271.5 271.5 112.5 271.5-112.5 112.5-271.5-112.5-271.5-271.5-112.5zm427 1536q88 0 150.5-71.5t62.5-173.5q0-239-78.5-377t-225.5-145q-145 127-336 127t-336-127q-147 7-225.5 145t-78.5 377q0 102 62.5 173.5t150.5 71.5h854z"/>
    </symbol>
    <symbol id="padlock" viewBox="0 0 1792 1792">
        <path d="M640 768h512V576q0-106-75-181t-181-75-181 75-75 181v192zm832 96v576q0 40-28 68t-68 28H416q-40 0-68-28t-28-68V864q0-40 28-68t68-28h32V576q0-184 132-316t316-132 316 132 132 316v192h32q40 0 68 28t28 68z"/>
    </symbol>
</svg>
</body>
</html>
