<?php
require_once 'dbconn.php';
include 'PageHeader.php';

// Fetch all users to populate the dropdown (MySQLi style)
$userResult = $conn->query("SELECT user_id, username FROM dbProj_users ORDER BY username ASC");
$users = $userResult->fetch_all(MYSQLI_ASSOC);

// Check if a user was selected
$selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$userContent = [];
$totalPages = 0;

if ($selectedUserId > 0) {
    // Pagination setup
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // Count total books for this user
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM dbProj_books WHERE creator_id = ?");
    $countStmt->bind_param("i", $selectedUserId); // "i" means integer
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $totalRow = $countRes->fetch_assoc();
    $totalBooks = $totalRow['total'];
    $totalPages = ceil($totalBooks / $limit);

    // Fetch books created by this specific user
    $sql = "SELECT book_id, title, category, publish_date 
            FROM dbProj_books 
            WHERE creator_id = ? 
            ORDER BY publish_date DESC 
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $selectedUserId, $limit, $offset); // "iii" = integer, integer, integer
    $stmt->execute();
    $res = $stmt->get_result();
    $userContent = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Content by User Report</h2>
        <a href="admin_reports.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>
    
    <form method="GET" class="card p-3 mb-4 bg-light shadow-sm">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="fw-bold form-label">Select a User</label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Choose a User --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['user_id'] ?>" <?= ($selectedUserId == $u['user_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">View Content</button>
            </div>
        </div>
    </form>

    <?php if ($selectedUserId > 0): ?>
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Book ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Publish Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($userContent)): ?>
                        <tr><td colspan="4" class="text-center py-4">This user has not uploaded any books.</td></tr>
                    <?php else: ?>
                        <?php foreach ($userContent as $content): ?>
                            <tr>
                                <td><?= $content['book_id'] ?></td>
                                <td><strong><?= htmlspecialchars($content['title']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($content['category'] ?? 'N/A') ?></span></td>
                                <td><?= date('M d, Y', strtotime($content['publish_date'])) ?></td>
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
                        <a class="page-link" href="?user_id=<?= $selectedUserId ?>&page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?user_id=<?= $selectedUserId ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?user_id=<?= $selectedUserId ?>&page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php // require_once 'footer.php'; ?>
