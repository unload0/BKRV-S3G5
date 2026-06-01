<?php
require_once 'db.php';
require_once 'header.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Admin Reporting Dashboard</h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center p-5">
                    <h5 class="card-title fw-bold text-primary">Popular Content Report</h5>
                    <p class="text-muted">View the highest-rated books within a specific date range.</p>
                    <a href="report_popular.php" class="btn btn-primary mt-2">Run Report 1</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center p-5">
                    <h5 class="card-title fw-bold text-success">User Content Report</h5>
                    <p class="text-muted">View all books uploaded by a specific user account.</p>
                    <a href="report_user.php" class="btn btn-success mt-2">Run Report 2</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
