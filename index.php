<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- Search and Pagination setup ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 5; // posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// --- Count total posts (with optional search) ---
if ($search !== '') {
    $like = "%{$search}%";
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts WHERE title LIKE ? OR content LIKE ?");
    $countStmt->bind_param('ss', $like, $like);
    $countStmt->execute();
    $countResult = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();
} else {
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM posts")->fetch_assoc();
}

$totalPosts = (int)$countResult['total'];
$totalPages = ($totalPosts > 0) ? (int)ceil($totalPosts / $limit) : 1;

// --- Fetch posts (with optional search) ---
if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ssii', $like, $like, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>All Posts</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 15px; color:#222; }
    header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
    .actions a { margin-left:10px; text-decoration:none; color:#006b6b; }
    form.search { margin-bottom:20px; display:flex; gap:8px; }
    form.search input[type="text"] { flex:1; padding:8px 10px; border:1px solid #ccc; border-radius:4px; }
    form.search button { padding:8px 14px; border-radius:4px; background:#007b7b; color:white; border:none; cursor:pointer; }
    .post { border:1px solid #e0e0e0; padding:12px; border-radius:6px; margin-bottom:12px; background:#fafafa; }
    .post h4 { margin:0 0 6px; }
    .meta { font-size:13px; color:#666; margin-bottom:8px; }
    .post .controls a { margin-right:8px; text-decoration:none; color:#006b6b; }
    .pagination { margin-top:18px; display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
    .pagination a { padding:6px 10px; border-radius:4px; border:1px solid #ddd; text-decoration:none; color:#333; }
    .pagination a.active { background:#007b7b; color:white; border-color:#007b7b; }
    .pagination .disabled { opacity:0.5; pointer-events:none; border-color:#eee; }
    .no-posts { color:#555; padding:18px; border:1px dashed #ddd; border-radius:6px; background:#fff; }
</style>
</head>
<body>
<header>
    <div>
        <h2>Welcome, <?= htmlspecialchars($_SESSION['username']); ?> 👋</h2>
        <div style="font-size:13px; color:#666;">You have <?= $totalPosts; ?> post<?= $totalPosts !== 1 ? 's' : ''; ?>.</div>
    </div>
    <div class="actions">
        <a href="create.php">➕ Add New Post</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</header>

<!-- Search form -->
<form method="GET" action="index.php" class="search" role="search">
    <input type="text" name="search" placeholder="Search posts by title or content..." value="<?= htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
</form>

<main>
    <h3>All Posts:</h3>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <article class="post">
                <h4><?= htmlspecialchars($row['title']); ?></h4>
                <div class="meta">Posted on <?= htmlspecialchars($row['created_at']); ?></div>
                <div><?= nl2br(htmlspecialchars($row['content'])); ?></div>
                <div class="controls" style="margin-top:8px;">
                    <a href="edit.php?id=<?= (int)$row['id']; ?>">✏ Edit</a> |
                    <a href="delete.php?id=<?= (int)$row['id']; ?>" onclick="return confirm('Delete this post?');">🗑 Delete</a>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-posts">No posts found. Try a different search or add a new post.</div>
    <?php endif; ?>

    <!-- Pagination -->
    <div class="pagination" aria-label="Pagination">
        <?php
        $prevPage = $page - 1;
        $nextPage = $page + 1;
        $searchParam = ($search !== '') ? '&search=' . urlencode($search) : '';
        ?>

        <?php if ($page > 1): ?>
            <a href="index.php?page=<?= $prevPage . $searchParam; ?>">&laquo; Prev</a>
        <?php else: ?>
            <a class="disabled">&laquo; Prev</a>
        <?php endif; ?>

        <?php
        $start = max(1, $page - 3);
        $end = min($totalPages, $page + 3);

        if ($start > 1) {
            echo '<a href="index.php?page=1' . $searchParam . '">1</a>';
            if ($start > 2) echo '<span style="padding:6px 10px;">…</span>';
        }

        for ($i = $start; $i <= $end; $i++):
            $class = ($i === $page) ? 'active' : '';
        ?>
            <a class="<?= $class; ?>" href="index.php?page=<?= $i . $searchParam; ?>"><?= $i; ?></a>
        <?php endfor;

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) echo '<span style="padding:6px 10px;">…</span>';
            echo '<a href="index.php?page=' . $totalPages . $searchParam . '">' . $totalPages . '</a>';
        }
        ?>

        <?php if ($page < $totalPages): ?>
            <a href="index.php?page=<?= $nextPage . $searchParam; ?>">Next &raquo;</a>
        <?php else: ?>
            <a class="disabled">Next &raquo;</a>
        <?php endif; ?>
    </div>
</main>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
?>
</body>
</html>
