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

$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$category_filter = isset($_GET['cat']) ? trim($_GET['cat']) : '';

$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(b.title LIKE ? OR b.author_name LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = $search_param;
    $params[] = $search_param;
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

$total_query = "SELECT COUNT(*) FROM dbProj_books b" . $where_sql;
$total_stmt = mysqli_prepare($conn, $total_query);

if ($total_stmt) {
    if (!empty($types)) {
        mysqli_stmt_bind_param($total_stmt, $types, ...$params);
    }
    mysqli_stmt_execute($total_stmt);
    $total_result = mysqli_stmt_get_result($total_stmt);
    $total_rows = mysqli_fetch_array($total_result)[0];
} else {
    die("Count query error: " . mysqli_error($conn));
}

$total_pages = ceil($total_rows / $pagination);

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $pagination;

$query = "SELECT b.book_id, b.title, b.author_name, b.short_description, b.image_url, b.media_url, b.publish_date, b.category, u.username 
          FROM dbProj_books b
          LEFT JOIN dbProj_users u ON b.creator_id = u.user_id"
        . $where_sql .
        " ORDER BY b.publish_date DESC
          LIMIT ? OFFSET ?";

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
    die("Database query error: " . mysqli_error($conn));
}

function buildPaginationUrl($page_num, $search, $cat) {
    $query_args = ['page' => $page_num];
    if (!empty($search))
        $query_args['query'] = $search;
    if (!empty($cat))
        $query_args['cat'] = $cat;
    return '?' . http_build_query($query_args);
}
?>


<div class="hp-container">

    <div class="hp-search-container">
        <div class="hp-category-header" style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
            <span style="font-weight: bold; margin-right: 15px; color: #555;">Browse Categories:</span>
            <a href="?cat=Fiction" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: <?php echo ($category_filter === 'Fiction') ? 'bold' : '500'; ?>;">Fiction</a>
            <a href="?cat=Non-Fiction" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: <?php echo ($category_filter === 'Non-Fiction') ? 'bold' : '500'; ?>;">Non-Fiction</a>
            <a href="?cat=Mystery" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: <?php echo ($category_filter === 'Mystery') ? 'bold' : '500'; ?>;">Mystery</a>
            <a href="?cat=Sci-Fi" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: <?php echo ($category_filter === 'Sci-Fi') ? 'bold' : '500'; ?>;">Sci-Fi</a>
            <a href="?cat=Astronomy" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: <?php echo ($category_filter === 'Astronomy') ? 'bold' : '500'; ?>;">Astronomy</a>
            <a href="?cat=Culinary" style="margin-right: 15px; color: #007BFF; text-decoration: none; font-weight: <?php echo ($category_filter === 'Culinary') ? 'bold' : '500'; ?>;">Culinary</a>
            <a href="?cat=Computer Science" style="color: #007BFF; text-decoration: none; font-weight: <?php echo ($category_filter === 'Computer Science') ? 'bold' : '500'; ?>;">Computer Science</a>

            <?php if (!empty($search_query) || !empty($category_filter)): ?>
                <a href="?" class="hp-clear-btn">❌ Clear Filters</a>
            <?php endif; ?>
        </div>

        <form action="" method="GET">
            <?php if (!empty($category_filter)): ?>
                <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category_filter); ?>">
            <?php endif; ?>
            <input type="text" name="query" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>
        <br><a href="AdvancedSearchPage.php">Advanced Search</a>
    </div>

    <?php if (!empty($search_query) || !empty($category_filter)): ?>
        <p style="color: #666; font-style: italic;">
            Showing results for: 
            <?php
            $status = [];
            if (!empty($category_filter))
                $status[] = "Category: <strong>" . htmlspecialchars($category_filter) . "</strong>";
            if (!empty($search_query))
                $status[] = "Keyword: <strong>" . htmlspecialchars($search_query) . "</strong>";
            echo implode(" & ", $status);
            ?>
        </p>
    <?php endif; ?>

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
                            Authored By <strong><?php echo htmlspecialchars($row['author_name']); ?></strong> | 
                            Category: <strong><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></strong> | 
                            Publish Date: <?php echo date('M d, Y', strtotime($row['publish_date'])); ?> | 
                            Uploaded By: <?php echo htmlspecialchars($row['username'] ?? 'Anonymous'); ?>
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
            <p>No books found matching your criteria.</p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <ul class="hp-pagination">
            <?php if ($page <= 1): ?>
                <li class="disabled"><span>&laquo; Prev</span></li>
            <?php else: ?>
                <li><a href="<?php echo buildPaginationUrl($page - 1, $search_query, $category_filter); ?>">&laquo; Prev</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($page == $i): ?>
                    <li class="active"><span><?php echo $i; ?></span></li>
                <?php else: ?>
                    <li><a href="<?php echo buildPaginationUrl($i, $search_query, $category_filter); ?>"><?php echo $i; ?></a></li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page >= $total_pages): ?>
                <li class="disabled"><span>Next &raquo;</span></li>
            <?php else: ?>
                <li><a href="<?php echo buildPaginationUrl($page + 1, $search_query, $category_filter); ?>">Next &raquo;</a></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

</div>