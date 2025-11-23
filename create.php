<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $user_id = $_SESSION['user_id'];

    // ---------------------------
    // IMAGE UPLOAD
    // ---------------------------
    $imageName = null;

    if (!empty($_FILES["image"]["name"])) {
        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $target = "uploads/" . $imageName;

        // Create uploads folder if not exists
        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }

        move_uploaded_file($_FILES["image"]["tmp_name"], $target);
    }

    // ---------------------------
    // INSERT POST
    // ---------------------------
    $sql = "INSERT INTO posts (title, content, image, category, user_id) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $content, $imageName, $category, $user_id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add New Post</title>

<style>
    body { 
        font-family: Arial; 
        margin: 30px auto; 
        max-width: 600px;
        padding: 20px;
        background: #f8f8f8; 
    }
    h2 { margin-bottom: 15px; }
    form {
        background: white;
        padding: 20px;
        border-radius: 10px;
        border: 1px solid #ddd;
    }
    input, textarea, select {
        width: 100%;
        padding: 10px;
        margin: 8px 0 15px 0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
    }
    button {
        background: #007b7b;
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
        color: white;
        cursor: pointer;
        font-size: 16px;
    }
    button:hover { background: #005f5f; }
    a { text-decoration: none; color: #006b6b; }
</style>
</head>

<body>

<h2>➕ Add New Post</h2>

<form method="POST" enctype="multipart/form-data">

  <label>Title</label>
  <input type="text" name="title" required>

  <label>Content</label>
  <textarea name="content" rows="5" required></textarea>

  <label>Category</label>
  <select name="category" required>
      <option value="">-- Select Category --</option>
      <option value="General">General</option>
      <option value="News">News</option>
      <option value="Technology">Technology</option>
      <option value="Lifestyle">Lifestyle</option>
      <option value="Education">Education</option>
  </select>

  <label>Image (optional)</label>
  <input type="file" name="image" accept="image/*">

  <button type="submit">Publish Post</button>
</form>

<br>
<a href="index.php">⬅ Back to Posts</a>

</body>
</html>
