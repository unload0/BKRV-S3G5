<?php
require_once 'dbconn.php';
include 'PageHeader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Creator') {
    die("<div class='hp-container'><h3>Access denied. Creator account required.</h3></div>");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creator_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name']);
    $short_description = trim($_POST['short_description']);
    $category = trim($_POST['category']);
    $image_url = trim($_POST['image_url']);
    $media_url = trim($_POST['media_url']);

    if (empty($title) || empty($author_name) || empty($short_description) || empty($category)) {
        $message = "<p style='color:red;'>Please fill in all required fields.</p>";
    } else {
        $query = "INSERT INTO dbProj_books 
                  (creator_id, title, author_name, short_description, category, image_url, media_url, publish_date)
                  VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "issssss",
                $creator_id,
                $title,
                $author_name,
                $short_description,
                $category,
                $image_url,
                $media_url
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: CreatorPage.php");
                exit();
            } else {
                $message = "<p style='color:red;'>Error adding book: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
            }

            mysqli_stmt_close($stmt);
        } else {
            $message = "<p style='color:red;'>Database error: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
        }
    }
}
?>

<div class="hp-container">
    <h2>Add New Book</h2>

    <p>
        <a href="CreatorPage.php" class="hp-view-more-btn">Back to Creator Dashboard</a>
    </p>

    <?php if (!empty($message)) echo $message; ?>

    <div class="hp-search-container">
        <form method="POST" action="AddBook.php">
            <div class="search-group">
                <label>Title *</label>
                <input type="text" name="title" required>
            </div>
            <br>

            <div class="search-group">
                <label>Author Name *</label>
                <input type="text" name="author_name" required>
            </div>
            <br>

            <div class="search-group">
                <label>Short Description *</label>
                <textarea name="short_description" rows="5" required></textarea>
            </div>
            <br>

            <div class="search-group">
                <label>Category *</label>
                <input type="text" name="category" required>
            </div>
            <br>

            <div class="search-group">
                <label>Image URL</label>
                <input type="text" name="image_url" placeholder="Optional book cover image link">
            </div>
            <br>

            <div class="search-group">
                <label>Media URL</label>
                <input type="text" name="media_url" placeholder="Optional video/audio/review link">
            </div>
            <br>

            <button type="submit" class="hp-view-more-btn">Publish Book</button>
        </form>
    </div>
</div>