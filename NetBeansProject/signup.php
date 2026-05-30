<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'dbconn.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($email) && !empty($password) && !empty($role)) {
        if (in_array($role, ['Viewer', 'Creator'])) {
            $check_query = "SELECT user_id FROM dbProj_users WHERE email = ?";
            if ($stmt = mysqli_prepare($conn, $check_query)) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $error_message = "An account with this email already exists";
                    mysqli_stmt_close($stmt);
                } else {
                    mysqli_stmt_close($stmt);
                    $insert_query = "INSERT INTO dbProj_users (username, email, password, role) VALUES (?, ?, AES_ENCRYPT(?, '123'), ?)";

                    if ($stmt = mysqli_prepare($conn, $insert_query)) {
                        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $role);

                        if (mysqli_stmt_execute($stmt)) {
                            $success_message = "Registration successful! You can log in now";
                        } else {
                            $error_message = "Something went wrong during account creation. Please try again.";
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
            } else {
                $error_message = "Database error. Please try again later.";
            }
        } else {
            $error_message = "Invalid account role selected.";
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}
?>
<style>
    .hp-container {
        width: 80%;
        margin: 20px auto;
        overflow: hidden;
        font-family: Arial, sans-serif;
        line-height: 1.6;
    }
    .hp-header {
        background: #333;
        color: #fff;
        max-width: 450px;
        margin: 40px auto;
        display: flex;
        justify-content: center;
        padding: 1rem;
        border-radius: 5px;
    }
    .hp-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .hp-nav a {
        color: #fff;
        text-decoration: none;
        margin-right: 15px;
    }

    .hp-signup-container {
        background: #fff;
        padding: 30px;
        margin: 40px auto;
        max-width: 450px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .hp-form-group {
        margin-bottom: 20px;
    }
    .hp-form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }
    .hp-signup-container input[type="text"],
    .hp-signup-container input[type="email"],
    .hp-signup-container input[type="password"],
    .hp-signup-container select {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }
    .hp-signup-btn {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        cursor: pointer;
        background: #007BFF;
        color: #fff;
        border: none;
        border-radius: 4px;
        transition: background 0.2s ease;
    }
    .hp-signup-btn:hover {
        background: #0056b3;
    }

    .hp-error-alert {
        background-color: #f8d7da;
        color: #dc3545;
        padding: 12px;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }
    .hp-success-alert {
        background-color: #d4edda;
        color: #28a745;
        padding: 12px;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }
    .hp-login-link {
        text-align: center;
        margin-top: 15px;
        font-size: 0.9rem;
    }
    .hp-login-link a {
        color: #007BFF;
        text-decoration: none;
    }
    .hp-login-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="hp-container">
    <header class="hp-header">
        <nav class="hp-nav">
            <div class="hp-logo">
                <h2>BKRV - Book Reviews</h2>
            </div>
        </nav>
    </header>

    <main>
        <div class="hp-signup-container">
            <h2 style="margin-top: 0; color: #222; text-align: center;">Create Account</h2>

            <?php if (!empty($error_message)): ?>
                <div class="hp-error-alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="hp-success-alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST">
                <div class="hp-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Choose a public display name">
                </div>

                <div class="hp-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="yourname@example.com">
                </div>

                <div class="hp-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a secure password">
                </div>

                <div class="hp-form-group">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select your role...</option>
                        <option value="Viewer">Viewer (Read & post comments/ratings)</option>
                        <option value="Creator">Creator (Upload Books)</option>
                    </select>
                </div>

                <button type="submit" class="hp-signup-btn">Register Account</button>
            </form>

            <div class="hp-login-link">
                Already have an account? <a href="login.php">Log in here</a>
            </div>
        </div>
    </main>
</div>