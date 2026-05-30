<?php
require_once 'dbconn.php';
include 'PageHeader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagination = 3;

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$search_title    = isset($_GET['title']) ? trim($_GET['title']) : '';
$start_date      = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date        = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$search_creator  = isset($_GET['creator']) ? trim($_GET['creator']) : '';
$category_filter = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$sort_by         = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'newest';

$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_title)) {
    //fulltext search
    $where_clauses[] = "MATCH(b.title, b.author_name, b.short_description, b.category) AGAINST(? IN NATURAL LANGUAGE MODE)";
    $params[] = $search_title;
    $types .= "s";
}

if (!empty($start_date)) {
    $where_clauses[] = "b.publish_date >= ?";
    $params[] = $start_date . " 00:00:00";
    $types .= "s";
}
if (!empty($end_date)) {
    $where_clauses[] = "b.publish_date <= ?";
    $params[] = $end_date . " 23:59:59";
    $types .= "s";
}

if (!empty($search_creator)) {
    $where_clauses[] = "(b.author_name LIKE ? OR u.username LIKE ?)";
    $creator_param = "%" . $search_creator . "%";
    $params[] = $creator_param;
    $params[] = $creator_param;
    $types .= "ss";
}

if (!empty($category_filter)) {
    $where_clauses[] = "b.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

$order_by_sql = "ORDER BY b.publish_date DESC";
if ($sort_by === 'oldest') {
    $order_by_sql = "ORDER BY b.publish_date ASC";
} elseif ($sort_by === 'rating') {
    $order_by_sql = "ORDER BY avg_rating DESC, review_count DESC";
} elseif ($sort_by === 'reviews') {
    $order_by_sql = "ORDER BY review_count DESC";
}

$categories_list = [];
$cat_res = mysqli_query($conn, "SELECT DISTINCT category FROM dbProj_books WHERE category IS NOT NULL AND category != ''");
if ($cat_res) {
    while ($c_row = mysqli_fetch_assoc($cat_res)) {
        $categories_list[] = $c_row['category'];
    }
}

$total_query = "SELECT COUNT(DISTINCT b.book_id) 
                FROM dbProj_books b
                LEFT JOIN dbProj_users u ON b.creator_id = u.user_id" . $where_sql;

$total_stmt = mysqli_prepare($conn, $total_query);

if ($total_stmt) {
    if (!empty($types)) {
        mysqli_stmt_bind_param($total_stmt, $types, ...$params);
    }
    mysqli_stmt_execute($total_stmt);
    $total_result = mysqli_stmt_get_result($total_stmt);
    $total_rows = mysqli_fetch_array($total_result)[0];
} else {
    die("Count query execution error: " . mysqli_error($conn));
}

$total_pages = ceil($total_rows / $pagination);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}
$offset = ($page - 1) * $pagination;

$query = "SELECT b.book_id, b.title, b.author_name, b.short_description, b.image_url, b.media_url, b.publish_date, b.category, u.username,
                 IFNULL(AVG(cr.rating), 0.00) as avg_rating,
                 COUNT(cr.comment_id) as review_count
          FROM dbProj_books b
          LEFT JOIN dbProj_users u ON b.creator_id = u.user_id
          LEFT JOIN dbProj_comments_ratings cr ON b.book_id = cr.book_id"
        . $where_sql .
        " GROUP BY b.book_id "
        . $order_by_sql .
        " LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    $final_params = $params;
    $final_params[] = $pagination;
    $final_params[] = $offset;
    $final_types = $types . "ii";

    mysqli_stmt_bind_param($stmt, $final_types, ...$final_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    die("Database execution error: " . mysqli_error($conn));
}

function buildPaginationUrl($page_num, $title, $start, $end, $creator, $cat, $sort) {
    $query_args = ['page' => $page_num];
    if (!empty($title))   $query_args['title'] = $title;
    if (!empty($start))   $query_args['start_date'] = $start;
    if (!empty($end))     $query_args['end_date'] = $end;
    if (!empty($creator)) $query_args['creator'] = $creator;
    if (!empty($cat))     $query_args['cat'] = $cat;
    if (!empty($sort))    $query_args['sort_by'] = $sort;
    return '?' . http_build_query($query_args);
}
?>

<style>
    .hp-container {
        width: 85%;
        margin: 20px auto;
        overflow: hidden;
        font-family: Arial, sans-serif;
        line-height: 1.6;
    }
    .hp-search-container {
        background: #fdfdfd;
        padding: 20px;
        margin: 20px 0;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .search-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }
    .search-group {
        display: flex;
        flex-direction: column;
    }
    .search-group label {
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #444;
        font-weight: bold;
    }
    .search-group input, .search-group select {
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .search-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 15px;
        margin-top: 10px;
    }
    .hp-search-container button {
        padding: 10px 25px;
        font-size: 15px;
        cursor: pointer;
        background: #333;
        color: #fff;
        border: none;
        border-radius: 4px;
        transition: background 0.2s;
    }
    .hp-search-container button:hover {
        background: #555;
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
    .hp-clear-btn {
        font-size: 0.9rem;
        color: #dc3545;
        text-decoration: none;
    }
</style>

<div class="hp-container">
    <h2>Advanced Search Engine</h2>

    <div class="hp-search-container">
        <form action="" method="GET">
            <div class="search-grid">
                <div class="search-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Search title, desc.." value="<?php echo htmlspecialchars($search_title); ?>">
                </div>

                <div class="search-group">
                    <label for="cat">Category Filter</label>
                    <select id="cat" name="cat">
                        <option value="">All Categories</option>
                        <?php foreach ($categories_list as $cat_option): ?>
                            <option value="<?php echo htmlspecialchars($cat_option); ?>" <?php echo ($category_filter === $cat_option) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat_option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="search-group">
                    <label for="creator">Creator / Author</label>
                    <input type="text" id="creator" name="creator" placeholder="Author, uploader.." value="<?php echo htmlspecialchars($search_creator); ?>">
                </div>

                <div class="search-group">
                    <label for="start_date">Published From</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>

                <div class="search-group">
                    <label for="end_date">Published To</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>

                <div class="search-group">
                    <label for="sort_by">Order By (Popularity / Date)</label>
                    <select id="sort_by" name="sort_by">
                        <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Date: Newest First</option>
                        <option value="oldest" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>Date: Oldest First</option>
                        <option value="rating" <?php echo ($sort_by === 'rating') ? 'selected' : ''; ?>>Popularity: Top Rated</option>
                        <option value="reviews" <?php echo ($sort_by === 'reviews') ? 'selected' : ''; ?>>Popularity: Most Reviewed</option>
                    </select>
                </div>
            </div>

            <div class="search-actions">
                <?php if (!empty($search_title) || !empty($start_date) || !empty($end_date) || !empty($search_creator) || !empty($category_filter) || $sort_by !== 'newest'): ?>
                    <a href="?" class="hp-clear-btn">❌ Clear All Filters</a>
                <?php endif; ?>
                <button type="submit">Execute Search</button>
            </div>
        </form>
    </div>

    <?php if ($total_rows > 0): ?>
        <p style="color: #666; font-style: italic; margin-bottom: 15px;">
            Found <strong><?php echo $total_rows; ?></strong> matches matching your target criteria. Viewing page <?php echo $page; ?> of <?php echo $total_pages; ?>.
        </p>
    <?php endif; ?>

    <div class="hp-book-list">
        <?php
        if ($result && mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                $image_src = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://via.placeholder.com/150x220?text=No+Cover';
                $rating_display = ($row['avg_rating'] > 0) ? number_format($row['avg_rating'], 1) . " ★" : "No ratings";
                ?>
                <div class="hp-book-card">
                    <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['title']); ?> Cover" class="hp-book-image">

                    <div class="hp-book-details">
                        <h3 class="hp-book-title"><?php echo htmlspecialchars($row['title']); ?></h3>

                        <div class="hp-meta-info">
                            Authored By <strong><?php echo htmlspecialchars($row['author_name']); ?></strong> | 
                            Category: <strong><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></strong> | 
                            Published: <strong><?php echo !empty($row['publish_date']) ? date('M d, Y', strtotime($row['publish_date'])) : 'N/A'; ?></strong>
                        </div>
                        
                        <div class="hp-meta-info" style="color: #333; margin-top: -5px;">
                            Rating: <strong><?php echo $rating_display; ?></strong> (<?php echo $row['review_count']; ?> reviews) | 
                            Uploaded By: <em><?php echo htmlspecialchars($row['username'] ?? 'Anonymous'); ?></em>
                        </div>

                        <p style="margin-top: 8px;"><?php echo htmlspecialchars($row['short_description']); ?></p>

                        <?php if (!empty($row['media_url'])): ?>
                            <div class="hp-media-link">
                                🔗 <strong>Attached Media Link:</strong> <a href="<?php echo htmlspecialchars($row['media_url']); ?>" target="_blank">Watch/Listen to Review</a>
                            </div>
                        <?php endif; ?>

                        <a href="book_details.php?id=<?php echo $row['book_id']; ?>" class="hp-view-more-btn">View More & Comments</a>
                    </div>
                </div>
                <?php
            endwhile;
        else:
            ?>
            <div style="text-align: center; padding: 40px; color: #777; border: 1px dashed #ccc; background: #fafafa;">
                <h3>No items matched your specific parameters.</h3>
                <p>Try clearing fields or narrowing criteria inputs to broaden the lookup scope.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <ul class="hp-pagination">
            <?php if ($page <= 1): ?>
                <li class="disabled"><span>&laquo; Prev</span></li>
            <?php else: ?>
                <li><a href="<?php echo buildPaginationUrl($page - 1, $search_title, $start_date, $end_date, $search_creator, $category_filter, $sort_by); ?>">&laquo; Prev</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($page == $i): ?>
                    <li class="active"><span><?php echo $i; ?></span></li>
                <?php else: ?>
                    <li><a href="<?php echo buildPaginationUrl($i, $search_title, $start_date, $end_date, $search_creator, $category_filter, $sort_by); ?>"><?php echo $i; ?></a></li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page >= $total_pages): ?>
                <li class="disabled"><span>Next &raquo;</span></li>
            <?php else: ?>
                <li><a href="<?php echo buildPaginationUrl($page + 1, $search_title, $start_date, $end_date, $search_creator, $category_filter, $sort_by); ?>">Next &raquo;</a></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
</div>