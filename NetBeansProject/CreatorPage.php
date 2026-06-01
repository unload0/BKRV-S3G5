<?php
require_once 'dbconn.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'PageHeader.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Creator') {
    die("<div class='hp-container'><h3>Access denied. Creator account required.</h3></div>");
}

$creator_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
    $book_id = isset($_POST['book_id']) && is_numeric($_POST['book_id']) ? (int) $_POST['book_id'] : 0;

    if ($book_id > 0) {
        $delete_query = "DELETE FROM dbProj_books
                         WHERE book_id = ?
                         AND creator_id = ?";

        $delete_stmt = mysqli_prepare($conn, $delete_query);

        if ($delete_stmt) {
            mysqli_stmt_bind_param($delete_stmt, "ii", $book_id, $creator_id);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
        }
    }

    header("Location: CreatorPage.php");
    exit();
}

$query = "SELECT book_id, title, author_name, category, publish_date
          FROM dbProj_books
          WHERE creator_id = ?
          ORDER BY publish_date DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $creator_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="hp-container">
    <h2>Creator Dashboard</h2>

    <p>
        <a href="AddBook.php" class="hp-view-more-btn">Add New Book</a>
    </p>

    <div class="hp-book-list">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="hp-book-card">
                    <div class="hp-book-details">
                        <h3 class="hp-book-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>

                        <div class="hp-meta-info">
                            Author:
                            <strong><?php echo htmlspecialchars($row['author_name']); ?></strong>
                            |
                            Category:
                            <strong><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></strong>
                            |
                            Published:
                            <?php echo date('M d, Y', strtotime($row['publish_date'])); ?>
                        </div>

                        <div style="margin-top: 15px; display:flex; gap:10px; flex-wrap:wrap;">
                            <a href="BookComments.php?id=<?php echo $row['book_id']; ?>" class="hp-view-more-btn">View</a>

                            <a href="EditBook.php?id=<?php echo $row['book_id']; ?>" class="hp-view-more-btn">Edit</a>

                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                                <input type="hidden" name="delete_book" value="1">

                                <button type="submit" class="hp-view-more-btn" style="background:#dc3545;border:none;cursor:pointer;">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have not uploaded any books yet.</p>
        <?php endif; ?>
    </div>
</div>