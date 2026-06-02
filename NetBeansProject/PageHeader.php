<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="MainStyles.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<header class="hp-header">
    <nav class="hp-nav">
        <div>
            <strong><a href="">BKRV - Book Reviews</a></strong>
            <a href="HomePage.php">Home</a>    
            <a href="AdvancedSearchPage.php">Advanced Search</a>
        </div>
        <div>
            <?php if ($_SESSION['user_id'] != null): ?>
                <span style="color: #fff;">@<?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['user_role']); ?>)</span>

                <?php if ($_SESSION['user_role'] == 'Creator'): ?>
                    <a href="CreatorPage.php" style="margin-left:15px; color:#ffc107;">Creator Page</a>
                <?php endif; ?>

                <?php if ($_SESSION['user_role'] == 'Admin'): ?>
                    <a href="AdminPage.php" style="margin-left:15px; color:#dc3545;">Admin Page</a>
                    <a href="admin_reports.php" style="margin-left:15px; color:#dc3545;">Reporting Page</a>
                <?php endif; ?>

                    <a href="login.php" style="margin-left: 0.5rem; color:#ff0000;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="margin-left: 0.5rem;">Login</a>
            <?php endif; ?>
        </div>
    </nav>
</header>