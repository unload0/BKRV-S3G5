<?php
require_once 'dbconn.php';

$pagination = 3;

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$total_query = "SELECT COUNT(*) FROM dbProj_books";
$total_result = mysqli_query($conn, $total_query);
$total_rows = mysqli_fetch_array($total_result)[0];

$total_pages = ceil($total_rows / $pagination);

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $pagination;

$query = "SELECT b.book_id, b.title, b.author_name, b.short_description, b.image_url, b.media_url, b.publish_date, u.username 
          FROM dbProj_books b
          LEFT JOIN dbProj_users u ON b.creator_id = u.user_id
          ORDER BY b.publish_date DESC
          LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $pagination, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    die("Database query error: " . mysqli_error($conn));
}
?>

<style>
    .hp-container {
        width: 80%;
        margin: 20px auto;
        overflow: hidden;
        font-family: Arial, sans-serif;
        line-height: 1.6;
    }
    .hp-header {
        background: #333;
        color: #fff;
        padding: 1rem;
        border-radius: 5px;
    }
    .hp-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .hp-nav a {
        color: #fff;
        text-decoration: none;
        margin-right: 15px;
    }
    .hp-search-container {
        background: #fff;
        padding: 15px;
        margin: 20px 0;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .hp-search-container input[type="text"] {
        width: 70%;
        padding: 8px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .hp-search-container button {
        padding: 8px 15px;
        font-size: 16px;
        cursor: pointer;
        background: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
    }
    .hp-book-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .hp-book-card {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        display: flex;
        gap: 20px;
    }
    .hp-book-image {
        max-width: 150px;
        max-height: 220px;
        object-fit: cover;
        border-radius: 3px;
    }
    .hp-book-details {
        flex: 1;
    }
    .hp-book-title {
        margin-top: 0;
        color: #222;
    }
    .hp-meta-info {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 10px;
    }
    .hp-view-more-btn {
        display: inline-block;
        background: #007BFF;
        color: white;
        padding: 8px 15px;
        text-decoration: none;
        border-radius: 3px;
        margin-top: 10px;
    }
    .hp-view-more-btn:hover {
        background: #0056b3;
    }
    .hp-media-link {
        margin-top: 10px;
        font-size: 0.9rem;
    }

    .hp-pagination {
        display: flex;
        justify-content: center;
        list-style: none;
        padding: 0;
        margin: 30px 0;
    }
    .hp-pagination li {
        margin: 0 4px;
    }
    .hp-pagination a, .hp-pagination span {
        display: block;
        padding: 8px 14px;
        text-decoration: none;
        color: #007BFF;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .hp-pagination a:hover {
        background-color: #f5f5f5;
    }
    .hp-pagination .active span {
        background-color: #007BFF;
        color: white;
        border-color: #007BFF;
    }
    .hp-pagination .disabled span {
        color: #ccc;
        pointer-events: none;
        background-color: #fafafa;
    }
</style>

<div class="hp-container">

    <div class="hp-search-container">
        <div class="hp-category-header" style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
            <span style="font-weight: bold; margin-right: 15px; color: #555;">Browse Categories:</span>
            <a href="categories.php?cat=fiction" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: 500;">Fiction</a>
            <a href="categories.php?cat=nonfiction" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: 500;">Non-Fiction</a>
            <a href="categories.php?cat=mystery" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: 500;">Mystery</a>
            <a href="categories.php?cat=scifi" style="color: #007BFF; text-decoration: none; font-weight: 500;">Sci-Fi</a>
        </div>

        <form action="search.php" method="GET">
            <input type="text" name="query" placeholder="Search by title" required>
            <button type="submit">Search</button>
        </form>
        <!--<a href="">Advanced Search Page</a>-->
    </div>

    <div class="hp-book-list">
        <?php
        if ($result && mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                $image_src = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://via.placeholder.com/150x220?text=No+Cover';
                ?>
                <div class="hp-book-card">
                    <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['title']); ?> Cover" class="hp-book-image">

                    <div class="hp-book-details">
                        <h3 class="hp-book-title"><?php echo htmlspecialchars($row['title']); ?></h3>

                        <div class="hp-meta-info">
                            By <strong><?php echo htmlspecialchars($row['author_name']); ?></strong> | 
                            Published: <?php echo date('M d, Y', strtotime($row['publish_date'])); ?> | 
                            Published By: <?php echo htmlspecialchars($row['username'] ?? 'Anonymous'); ?>
                        </div>

                        <p><?php echo htmlspecialchars($row['short_description']); ?></p>

                        <?php if (!empty($row['media_url'])): ?>
                            <div class="hp-media-link">
                                🔗 <strong>Attached Media:</strong> <a href="<?php echo htmlspecialchars($row['media_url']); ?>" target="_blank">Watch/Listen to Review</a>
                            </div>
                        <?php endif; ?>

                        <a href="book_details.php?id=<?php echo $row['book_id']; ?>" class="hp-view-more-btn">View More & Comments</a>
                    </div>
                </div>
                <?php
            endwhile;
        else:
            ?>
            <p>No books found.</p>
        <?php endif; ?>
    </div>
    
    
    <!--pagination-->
    <?php if ($total_pages > 1): ?>
        <ul class="hp-pagination">
            <?php if ($page <= 1): ?>
                <li class="disabled"><span>&laquo; Prev</span></li>
            <?php else: ?>
                <li><a href="?page=<?php echo ($page - 1); ?>">&laquo; Prev</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($page == $i): ?>
                    <li class="active"><span><?php echo $i; ?></span></li>
                <?php else: ?>
                    <li><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page >= $total_pages): ?>
                <li class="disabled"><span>Next &raquo;</span></li>
            <?php else: ?>
                <li><a href="?page=<?php echo ($page + 1); ?>">Next &raquo;</a></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

</div>