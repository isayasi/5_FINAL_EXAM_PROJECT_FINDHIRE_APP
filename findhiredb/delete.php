<?php
require_once 'core/model.php';
require_once 'core/dbconfig.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

$job_posts_id = intval($_GET['job_posts_id']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Job Post</title>
    <link rel="stylesheet" href="css/delete.css">
</head>

<body>
    <!-- Navigation -->
    <button onclick="history.back()">Back</button>

    <!-- Page Title -->
    <h1>Delete Job Post</h1>
    <p>You are about to delete the following job post.</p>

    <?php 
    // Fetch the job post details
    $job_posts_id = $_GET['job_posts_id'];
    $getPostByID = getPostByID($pdo, $job_posts_id); 
    ?>

    <!-- Confirmation Section -->
    <section>
        <h2>Are you sure you want to delete this Job Post?</h2>

        <!-- Display Job Post Details -->
        <div class = "description">
            <p>Description: <?php echo htmlspecialchars($getPostByID['description']); ?></p>
            <p>Posted on: <?php echo htmlspecialchars($getPostByID['created_at']); ?></p>
        </div>
    </section>

    <!-- Delete Confirmation Form -->
    <form action="core/handleform.php" method="POST">
        <input type="hidden" name="job_post_name" value="<?php echo htmlspecialchars($getPostByID['title']); ?>">
        <input type="hidden" name="job_posts_id" value="<?php echo htmlspecialchars($job_posts_id); ?>">
        <div class = button1>
            <button type="submit" name="deleteBtn" style="margin-top: 10px;">Delete</button>
        </div>
    </form>
</body>

</html>
