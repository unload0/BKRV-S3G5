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

$book_id = isset($_GET['id']) && is_numeric($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($book_id <= 0) {
    die("<div class='hp-container'><h3>Invalid book selected.</h3></div>");
}

$message = "";

/*
 
 * Only load books that belong to the logged in creator.
 */
$select_query = "SELECT *
                 FROM dbProj_books
                 WHERE book_id = ?
                 AND creator_id = ?";

$select_stmt = mysqli_prepare($conn, $select_query);
mysqli_stmt_bind_param($select_stmt, "ii", $book_id, $creator_id);
mysqli_stmt_execute($select_stmt);

$result = mysqli_stmt_get_result($select_stmt);
$book = mysqli_fetch_assoc($result);

mysqli_stmt_close($select_stmt);

if (!$book) {
    die("<div class='hp-container'><h3>You do not have permission to edit this book.</h3></div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name']);
    $short_description = trim($_POST['short_description']);
    $category = trim($_POST['category']);
    $image_url = trim($_POST['image_url']);
    $media_url = trim($_POST['media_url']);

    if (
        empty($title) ||
        empty($author_name) ||
        empty($short_description) ||
        empty($category)
    ) {

        $message = "<p style='color:red;'>Please fill in all required fields.</p>";

    } else {

        $update_query = "UPDATE dbProj_books
                         SET title = ?,
                             author_name = ?,
                             short_description = ?,
                             category = ?,
                             image_url = ?,
                             media_url = ?
                         WHERE book_id = ?
                         AND creator_id = ?";

        $update_stmt = mysqli_prepare($conn, $update_query);

        if ($update_stmt) {

            mysqli_stmt_bind_param(
                $update_stmt,
                "ssssssii",
                $title,
                $author_name,
                $short_description,
                $category,
                $image_url,
                $media_url,
                $book_id,
                $creator_id
            );

            if (mysqli_stmt_execute($update_stmt)) {

                header("Location: CreatorPage.php");
                exit();

            } else {

                $message = "<p style='color:red;'>Failed to update book.</p>";
            }

            mysqli_stmt_close($update_stmt);
        }
    }
}
?>

<div class="hp-container">

    <h2>Edit Book</h2>

    <p>
        <a href="CreatorPage.php" class="hp-view-more-btn">
            Back to Creator Dashboard
        </a>
    </p>

    <?php echo $message; ?>

    <div class="hp-search-container">

        <form method="POST">

            <div class="search-group">
                <label>Title *</label>
                <input type="text"
                       name="title"
                       value="<?php echo htmlspecialchars($book['title']); ?>"
                       required>
            </div>

            <br>

            <div class="search-group">
                <label>Author Name *</label>
                <input type="text"
                       name="author_name"
                       value="<?php echo htmlspecialchars($book['author_name']); ?>"
                       required>
            </div>

            <br>

            <div class="search-group">
                <label>Short Description *</label>
                <textarea name="short_description"
                          rows="5"
                          required><?php echo htmlspecialchars($book['short_description']); ?></textarea>
            </div>

            <br>

            <div class="search-group">
                <label>Category *</label>
                <input type="text"
                       name="category"
                       value="<?php echo htmlspecialchars($book['category']); ?>"
                       required>
            </div>

            <br>

            <div class="search-group">
                <label>Image URL</label>
                <input type="text"
                       name="image_url"
                       value="<?php echo htmlspecialchars($book['image_url']); ?>">
            </div>

            <br>

            <div class="search-group">
                <label>Media URL</label>
                <input type="text"
                       name="media_url"
                       value="<?php echo htmlspecialchars($book['media_url']); ?>">
            </div>

            <br>

            <button type="submit" class="hp-view-more-btn">
                Save Changes
            </button>

        </form>

    </div>

</div>