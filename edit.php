<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $sql = "UPDATE posts SET title=?, content=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $content, $id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

$result = $conn->query("SELECT * FROM posts WHERE id=$id");
$post = $result->fetch_assoc();
?>

<h2>Edit Post</h2>
<form method="POST">
  Title: <input type="text" name="title" value="<?= htmlspecialchars($post['title']); ?>" required><br><br>
  Content:<br>
  <textarea name="content" rows="5" cols="40" required><?= htmlspecialchars($post['content']); ?></textarea><br><br>
  <button type="submit">Update</button>
</form>

<br>
<a href="index.php">⬅ Back to Posts</a>
