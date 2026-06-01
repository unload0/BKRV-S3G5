<?php
require_once 'dbconn.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id']   = $_SESSION['user_id']   ?? null;
$_SESSION['user_role'] = $_SESSION['user_role'] ?? null;
$_SESSION['username']  = $_SESSION['username']  ?? '';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'PageHeader.php';

$is_admin     = $_SESSION['user_role'] === 'Admin';
$message      = '';
$message_type = '';
$per_page     = 10;

if (!$is_admin) {
    ?>
    <div class="hp-container">
        <div class="admin-alert admin-alert-error">
            Access denied. Only administrators can open this page.
        </div>
    </div>
    <?php
    exit();
}

// Handle delete book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_book') {

    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message      = 'Invalid request. Please try again.';
        $message_type = 'error';
    } else {
        $book_id = isset($_POST['book_id']) && is_numeric($_POST['book_id']) ? (int) $_POST['book_id'] : 0;

        if ($book_id <= 0) {
            $message      = 'Invalid book selected.';
            $message_type = 'error';
        } else {
            mysqli_begin_transaction($conn);

            try {
                // Delete comments first (respects FK), then the book
                $delete_comments_sql  = "DELETE FROM dbProj_comments_ratings WHERE book_id = ?";
                $delete_comments_stmt = mysqli_prepare($conn, $delete_comments_sql);

                if (!$delete_comments_stmt) {
                    throw new Exception('Could not prepare comments delete statement.');
                }

                mysqli_stmt_bind_param($delete_comments_stmt, "i", $book_id);

                if (!mysqli_stmt_execute($delete_comments_stmt)) {
                    throw new Exception('Could not delete related comments.');
                }

                mysqli_stmt_close($delete_comments_stmt);

                $delete_book_sql  = "DELETE FROM dbProj_books WHERE book_id = ?";
                $delete_book_stmt = mysqli_prepare($conn, $delete_book_sql);

                if (!$delete_book_stmt) {
                    throw new Exception('Could not prepare book delete statement.');
                }

                mysqli_stmt_bind_param($delete_book_stmt, "i", $book_id);

                if (!mysqli_stmt_execute($delete_book_stmt)) {
                    throw new Exception('Could not delete selected book.');
                }

                $affected_rows = mysqli_stmt_affected_rows($delete_book_stmt);
                mysqli_stmt_close($delete_book_stmt);

                if ($affected_rows < 1) {
                    throw new Exception('The selected book was not found.');
                }

                mysqli_commit($conn);
                $message      = 'Book content removed successfully.';
                $message_type = 'success';

            } catch (Exception $ex) {
                mysqli_rollback($conn);
                // Log the real error to server log, show safe message to user
                error_log('AdminPage delete error: ' . $ex->getMessage());
                $message      = 'An error occurred while removing the book. Please try again.';
                $message_type = 'error';
            }
        }
    }
}

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Total count
$count_sql  = "SELECT COUNT(*) AS total_books FROM dbProj_books";
$count_stmt = mysqli_prepare($conn, $count_sql);

if (!$count_stmt) {
    error_log('AdminPage count error: ' . mysqli_error($conn));
    die("<div class='hp-container'><p>A database error occurred. Please contact the administrator.</p></div>");
}

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_books  = (int) (mysqli_fetch_assoc($count_result)['total_books'] ?? 0);
mysqli_stmt_close($count_stmt);

$total_pages = max(1, (int) ceil($total_books / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $per_page;

// Fetch books with creator name and rating summary
$books_sql = "SELECT
                  b.book_id,
                  b.title,
                  b.author_name,
                  b.category,
                  b.publish_date,
                  b.image_url,
                  b.media_url,
                  u.username AS creator_name,
                  IFNULL(AVG(cr.rating), 0) AS average_rating,
                  COUNT(cr.comment_id)       AS review_count
              FROM dbProj_books b
              LEFT JOIN dbProj_users u
                  ON b.creator_id = u.user_id
              LEFT JOIN dbProj_comments_ratings cr
                  ON b.book_id = cr.book_id
              GROUP BY
                  b.book_id,
                  b.title,
                  b.author_name,
                  b.category,
                  b.publish_date,
                  b.image_url,
                  b.media_url,
                  u.username
              ORDER BY b.publish_date DESC, b.book_id DESC
              LIMIT ? OFFSET ?";

$books_stmt = mysqli_prepare($conn, $books_sql);

if (!$books_stmt) {
    error_log('AdminPage books error: ' . mysqli_error($conn));
    die("<div class='hp-container'><p>A database error occurred. Please contact the administrator.</p></div>");
}

mysqli_stmt_bind_param($books_stmt, "ii", $per_page, $offset);
mysqli_stmt_execute($books_stmt);
$books_result = mysqli_stmt_get_result($books_stmt);

function adminPaginationUrl($page_num) {
    return 'AdminPage.php?page=' . urlencode((string) $page_num);
}
?>

<style>
    .admin-page-title {
        margin-bottom: 5px;
        color: #222;
    }

    .admin-page-subtitle {
        color: #666;
        margin-top: 0;
    }

    .admin-alert {
        padding: 12px 15px;
        border-radius: 5px;
        margin: 20px 0;
        font-weight: bold;
    }

    .admin-alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .admin-alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .admin-table-wrap {
        overflow-x: auto;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-top: 20px;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .admin-table th,
    .admin-table td {
        padding: 12px;
        border-bottom: 1px solid #e6e6e6;
        text-align: left;
        vertical-align: top;
    }

    .admin-table th {
        background: #333;
        color: #fff;
    }

    .admin-table tr:hover {
        background: #f8f9fa;
    }

    .admin-book-title {
        font-weight: bold;
        color: #222;
    }

    .admin-muted {
        color: #777;
        font-size: 0.9rem;
    }

    .admin-remove-btn {
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        padding: 8px 12px;
    }

    .admin-remove-btn:hover {
        background: #b02a37;
    }

    .admin-empty {
        background: #fafafa;
        border: 1px dashed #ccc;
        color: #777;
        padding: 35px;
        text-align: center;
        margin-top: 20px;
    }
</style>

<div class="hp-container">
    <h2 class="admin-page-title">Admin Page</h2>
    <p class="admin-page-subtitle">Manage all published book content. Showing 10 records per page.</p>

    <?php if (!empty($message)): ?>
        <div class="admin-alert admin-alert-<?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($books_result && mysqli_num_rows($books_result) > 0): ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Creator</th>
                        <th>Published</th>
                        <th>Rating</th>
                        <th>Media</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = mysqli_fetch_assoc($books_result)): ?>
                        <tr>
                            <td>
                                <div class="admin-book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                <div class="admin-muted">ID: <?php echo (int) $book['book_id']; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                            <td><?php echo htmlspecialchars($book['category'] ?? 'Uncategorized'); ?></td>
                            <td><?php echo htmlspecialchars($book['creator_name'] ?? 'Unknown'); ?></td>
                            <td>
                                <?php echo !empty($book['publish_date']) ? htmlspecialchars(date('M d, Y', strtotime($book['publish_date']))) : 'N/A'; ?>
                            </td>
                            <td>
                                <?php if ((float) $book['average_rating'] > 0): ?>
                                    <?php echo htmlspecialchars(number_format((float) $book['average_rating'], 1)); ?> / 5
                                <?php else: ?>
                                    No rating
                                <?php endif; ?>
                                <div class="admin-muted"><?php echo (int) $book['review_count']; ?> reviews</div>
                            </td>
                            <td>
                                <?php if (!empty($book['image_url'])): ?>
                                    <div><a href="<?php echo htmlspecialchars($book['image_url']); ?>" target="_blank">Image</a></div>
                                <?php endif; ?>
                                <?php if (!empty($book['media_url'])): ?>
                                    <div><a href="<?php echo htmlspecialchars($book['media_url']); ?>" target="_blank">Media</a></div>
                                <?php endif; ?>
                                <?php if (empty($book['image_url']) && empty($book['media_url'])): ?>
                                    <span class="admin-muted">No media</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="AdminPage.php?page=<?php echo (int) $page; ?>" method="POST"
                                      onsubmit="return confirm('Remove this book and all its comments?');">
                                    <input type="hidden" name="action"     value="delete_book">
                                    <input type="hidden" name="book_id"    value="<?php echo (int) $book['book_id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="admin-remove-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty">
            No book content found.
        </div>
    <?php endif; ?>

    <?php if ($total_pages > 1): ?>
        <ul class="hp-pagination">
            <?php if ($page <= 1): ?>
                <li class="disabled"><span>&laquo; Prev</span></li>
            <?php else: ?>
                <li><a href="<?php echo adminPaginationUrl($page - 1); ?>">&laquo; Prev</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i === $page): ?>
                    <li class="active"><span><?php echo $i; ?></span></li>
                <?php else: ?>
                    <li><a href="<?php echo adminPaginationUrl($i); ?>"><?php echo $i; ?></a></li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page >= $total_pages): ?>
                <li class="disabled"><span>Next &raquo;</span></li>
            <?php else: ?>
                <li><a href="<?php echo adminPaginationUrl($page + 1); ?>">Next &raquo;</a></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
</div>

<?php
mysqli_stmt_close($books_stmt);
?>