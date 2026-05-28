<header class="hp-header">
    <nav class="hp-nav">
        <div>
            <strong><a href="index.php">BKRV - Book Reviews</a></strong>
            <a href="index.php">Home</a>    
            <a href="#">Advanced Search</a>
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color: #fff;"><?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                
                <?php if ($_SESSION['role'] == 'Creator'): ?>
                    <a href="creator_panel.php" style="margin-left:15px; color:#ffc107;">Creator Page</a>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <a href="admin_panel.php" style="margin-left:15px; color:#dc3545;">Admin Page</a>
                    <a href="#" style="margin-left:15px; color:#dc3545;">Reporting Page</a>
                <?php endif; ?>
                
                <a href="login.php" style="margin-left:15px;">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>
</header>