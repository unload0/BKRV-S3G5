<?php
require_once 'dbconn.php';
include 'PageHeader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$book_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
if ($book_id <= 0) {
    die("<div class='hp-container'><h3>Error: Invalid Book Selection.</h3></div>");
}

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
    if ($user_role === 'Admin') {
        $comment_id_to_delete = (int)($_POST['comment_id'] ?? 0);
        
        $del_sql = "DELETE FROM dbProj_comments_ratings WHERE comment_id = ? AND book_id = ?";
        $del_stmt = mysqli_prepare($conn, $del_sql);
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "ii", $comment_id_to_delete, $book_id);
            if (mysqli_stmt_execute($del_stmt)) {
                $message = "<p style='color: green; font-weight: bold;'>Comment deleted successfully.</p>";
            } else {
                $message = "<p style='color: red;'>Error deleting comment.</p>";
            }
            mysqli_stmt_close($del_stmt);
        }
    } else {
        $message = "<p style='color: red;'>Unauthorized access. Only Admins can delete comments.</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    if ($user_id !== null) {
        $rating = floatval($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $message = "<p style='color: red;'>Please provide a valid rating between 1 and 5.</p>";
        } elseif (empty($comment)) {
            $message = "<p style='color: red;'>Comment section cannot be empty.</p>";
        } else {
            $ins_sql = "INSERT INTO dbProj_comments_ratings (book_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $ins_stmt = mysqli_prepare($conn, $ins_sql);
            if ($ins_stmt) {
                mysqli_stmt_bind_param($ins_stmt, "iids", $book_id, $user_id, $rating, $comment);
                if (mysqli_stmt_execute($ins_stmt)) {
                    $message = "<p style='color: green; font-weight: bold;'>Your review has been added successfully!</p>";
                } else {
                    $message = "<p style='color: red;'>Error adding your review. You may have already reviewed this book.</p>";
                }
                mysqli_stmt_close($ins_stmt);
            }
        }
    } else {
        $message = "<p style='color: red;'>You must be logged in to submit a rating or comment.</p>";
    }
}

$book_query = "SELECT b.*, u.username, 
                      IFNULL(AVG(cr.rating), 0.00) as avg_rating,
                      COUNT(cr.comment_id) as review_count
               FROM dbProj_books b
               LEFT JOIN dbProj_users u ON b.creator_id = u.user_id
               LEFT JOIN dbProj_comments_ratings cr ON b.book_id = cr.book_id
               WHERE b.book_id = ?
               GROUP BY b.book_id";

$book_stmt = mysqli_prepare($conn, $book_query);
if ($book_stmt) {
    mysqli_stmt_bind_param($book_stmt, "i", $book_id);
    mysqli_stmt_execute($book_stmt);
    $book_result = mysqli_stmt_get_result($book_stmt);
    $book = mysqli_fetch_assoc($book_result);
    mysqli_stmt_close($book_stmt);
}

if (!$book) {
    die("<div class='hp-container'><h3>Book not found.</h3></div>");
}

$comments = [];
$comment_query = "SELECT cr.*, u.username 
                  FROM dbProj_comments_ratings cr
                  JOIN dbProj_users u ON cr.user_id = u.user_id
                  WHERE cr.book_id = ?
                  ORDER BY cr.created_at DESC";

$comment_stmt = mysqli_prepare($conn, $comment_query);
if ($comment_stmt) {
    mysqli_stmt_bind_param($comment_stmt, "i", $book_id);
    mysqli_stmt_execute($comment_stmt);
    $comment_result = mysqli_stmt_get_result($comment_stmt);
    while ($row = mysqli_fetch_assoc($comment_result)) {
        $comments[] = $row;
    }
    mysqli_stmt_close($comment_stmt);
}
?>

<style>
    .details-card {
        background: #fff;
        padding: 25px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        display: flex;
        gap: 30px;
    }
    .comment-box {
        background: #fdfdfd;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }
    .delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 0.8rem;
        float: right;
    }
    .delete-btn:hover { background: #c82333; }
    .form-group {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
    }
    .form-group label { font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group textarea {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-family: inherit;
    }
    .submit-btn {
        background: #007BFF;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 15px;
    }
    .submit-btn:hover { background: #0056b3; }
    .login-warning {
        background: #fff3cd;
        color: #856404;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid #ffeeba;
    }
</style>

<div class="hp-container">
    <a href="HomePage.php" style="text-decoration: none; color: #007BFF;">&larr; Home Page</a>
    <br><br>

    <?php if (!empty($message)) echo $message; ?>

    <div class="details-card">
        <img src="<?php echo !empty($book['image_url']) ? htmlspecialchars($book['image_url']) : 'https://via.placeholder.com/150x220?text=No+Cover'; ?>" 
             alt="Cover Image" class="hp-book-image">
        
        <div class="hp-book-details">
            <h2 class="hp-book-title" style="margin-bottom: 10px;"><?php echo htmlspecialchars($book['title']); ?></h2>
            
            <div class="hp-meta-info">
                Authored By <strong><?php echo htmlspecialchars($book['author_name']); ?></strong> | 
                Category: <strong><?php echo htmlspecialchars($book['category'] ?? 'Uncategorized'); ?></strong> | 
                Published: <strong><?php echo !empty($book['publish_date']) ? date('M d, Y', strtotime($book['publish_date'])) : 'N/A'; ?></strong>
            </div>
            
            <div class="hp-meta-info" style="color: #333;">
                Overall Rating: <strong><?php echo ($book['avg_rating'] > 0) ? number_format($book['avg_rating'], 1) . " ★" : "No ratings yet"; ?></strong> 
                (<?php echo $book['review_count']; ?> community reviews) | 
                Uploaded By: <em><?php echo htmlspecialchars($book['username'] ?? 'Anonymous'); ?></em>
            </div>

            <p style="margin-top: 15px; font-size: 1.05rem; color: #333;"><?php echo htmlspecialchars($book['short_description']); ?></p>

            <?php if (!empty($book['media_url'])): ?>
                <div class="hp-media-link" style="margin-top: 20px;">
                    🔗 <strong>Attached Media Link:</strong> <a href="<?php echo htmlspecialchars($book['media_url']); ?>" target="_blank">Watch/Listen to Review</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<!--### Community Review Hub-->
    
    <?php if ($user_id !== null): ?>
        <div class="hp-search-container" style="margin-top: 0;">
            <h4>Leave a Review & Rating</h4>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add_comment">
                
                <div class="search-grid">
                    <div class="search-group">
                        <label for="rating">Score Out of 5 ★</label>
                        <select id="rating" name="rating" required>
                            <option value="5.0">5.0 - Excellent</option>
                            <option value="4.5">4.5 - Very Good</option>
                            <option value="4.0">4.0 - Good</option>
                            <option value="3.5">3.5 - Above Average</option>
                            <option value="3.0">3.0 - Average</option>
                            <option value="2.0">2.0 - Poor</option>
                            <option value="1.0">1.0 - Terrible</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comment">Your Written Comment</label>
                    <textarea id="comment" name="comment" rows="4" placeholder="Write your thoughts here..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <div class="login-warning">
            You must be <strong>logged in</strong> to write a comment or rate this title.
        </div>
        <br>
    <?php endif; ?>

    Reviews
    
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $com): ?>
            <div class="comment-box">
                <?php if ($user_role === 'Admin'): ?>
                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                        <input type="hidden" name="action" value="delete_comment">
                        <input type="hidden" name="comment_id" value="<?php echo $com['comment_id']; ?>">
                        <button type="submit" class="delete-btn">Remove</button>
                    </form>
                <?php endif; ?>

                <strong><?php echo htmlspecialchars($com['username']); ?></strong> 
                <span style="color: #ff9800; margin-left: 10px;"><?php echo number_format($com['rating'], 1); ?> ★</span>
                <span style="font-size: 0.85rem; color: #888; margin-left: 15px;"><?php echo date('M d, Y @ h:i A', strtotime($com['created_at'])); ?></span>
                
                <p style="margin: 10px 0 0 0; color: #444;"><?php echo htmlspecialchars($com['comment']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: #777; font-style: italic;">No thoughts uploaded yet. Be the first to express yours!</p>
    <?php endif; ?>
</div>