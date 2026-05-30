<?php
require_once 'dbconn.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//reset login if stored before
$_SESSION['user_id'] = null;

$error_message = '';

//make sure form is using post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $query = "SELECT user_id, username, role FROM dbProj_users WHERE email = ? AND AES_DECRYPT(password, '123') = ?";

        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, "ss", $email, $password);

            mysqli_stmt_execute($stmt);

            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) === 1) {
                mysqli_stmt_bind_result($stmt, $user_id, $username, $role);
                mysqli_stmt_fetch($stmt);

                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_role'] = $role;
                $_SESSION['username'] = $username;

                mysqli_stmt_close($stmt);

                header("Location: HomePage.php");
                exit();
            } else {
                $error_message = "Invalid email or password. Please try again.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error_message = "An unexpected error occurred. Please try again later.";
        }
    } else {
        $error_message = "Please fill in all fields.";
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

    .hp-login-container {
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
    .hp-login-container input[type="email"],
    .hp-login-container input[type="password"] {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .hp-btn-group {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .hp-login-btn {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        cursor: pointer;
        background: #00cc00;
        color: #fff;
        border: none;
        border-radius: 4px;
        transition: background 0.2s ease;
        text-align: center;
        text-decoration: none;
        box-sizing: border-box;
    }

    .hp-login-btn:hover {
        background: #0056b3;
    }

    .hp-signup-btn {
        background: #007BFF;
    }
    .hp-signup-btn:hover {
        background: #5a6268;
    }

    .hp-guest-btn {
        background: #007BFF;
        width: 100%;
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
        <div class="hp-login-container">
            <h2 style="margin-top: 0; color: #222; text-align: center;">Account Login</h2>

            <?php if (!empty($error_message)): ?>
                <div class="hp-error-alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="hp-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="example@domain.com">
                </div>

                <div class="hp-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <div class="hp-btn-group">
                    <button type="submit" class="hp-login-btn">Log In</button>
                    <a href="signup.php" class="hp-login-btn hp-signup-btn">Sign Up</a>
                </div>
            </form>
            <br>
            <div class="hp-btn-group">
                <a href="HomePage.php" class="hp-login-btn hp-guest-btn">Log in as Guest</a>
            </div>
        </div>
    </main>
</div>
