<?php
require_once 'db.php';
require_once 'header.php';

// Set default dates if the form hasn't been submitted yet
$startDateRaw = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 month'));
$endDateRaw = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$startDate = $startDateRaw . ' 00:00:00';
$endDate = $endDateRaw . ' 23:59:59';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total books for pagination
$countSql = "SELECT COUNT(*) FROM dbProj_books WHERE publish_date BETWEEN :start_date AND :end_date";
$countStmt = $pdo->prepare($countSql);
$countStmt->bindValue(':start_date', $startDate);
$countStmt->bindValue(':end_date', $endDate);
$countStmt->execute();
$totalBooks = $countStmt->fetchColumn();
$totalPages = ceil($totalBooks / $limit);

// Fetch books, count reviews, and calculate average rating
$sql = "SELECT b.book_id, b.title, b.author_name, 
               COUNT(c.comment_id) as total_reviews, 
               COALESCE(AVG(c.rating), 0) as average_rating 
        FROM dbProj_books b
        LEFT JOIN dbProj_comments_ratings c ON b.book_id = c.book_id
        WHERE b.publish_date BETWEEN :start_date AND :end_date
        GROUP BY b.book_id
        ORDER BY average_rating DESC, total_reviews DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':start_date', $startDate);
$stmt->bindValue(':end_date', $endDate);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Most Popular Books</h2>
        <a href="admin_reports.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
    
    <form method="GET" class="card p-3 mb-4 bg-light shadow-sm">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="fw-bold form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDateRaw) ?>">
            </div>
            <div class="col-md-4">
                <label class="fw-bold form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDateRaw) ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </div>
    </form>

    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Book ID</th>
                    <th>Title & Author</th>
                    <th>Average Rating</th>
                    <th>Total Reviews</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr><td colspan="4" class="text-center py-4">No books published in this date range.</td></tr>
                <?php else: ?>
                    <?php foreach ($books as $b): ?>
                        <tr>
                            <td><?= $b['book_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($b['title']) ?></strong> <br>
                                <small class="text-muted">by <?= htmlspecialchars($b['author_name']) ?></small>
                            </td>
                            <td><span class="badge bg-warning text-dark fs-6"><?= number_format($b['average_rating'], 1) ?> ★</span></td>
                            <td><?= $b['total_reviews'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?start_date=<?= $startDateRaw ?>&end_date=<?= $endDateRaw ?>&page=<?= $page - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?start_date=<?= $startDateRaw ?>&end_date=<?= $endDateRaw ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?start_date=<?= $startDateRaw ?>&end_date=<?= $endDateRaw ?>&page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
