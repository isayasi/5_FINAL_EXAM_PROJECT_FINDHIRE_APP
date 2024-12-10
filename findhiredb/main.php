<?php
require_once 'core/model.php';
require_once 'core/handleform.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$info = getUserByID($pdo, $_SESSION['username']);
$showJobPosts = showJobPosts($pdo);

// Fetch messages based on role
if ($_SESSION['role'] == 'hr') {
    $messages = getMessagesForHR($pdo, $info['id']);
} elseif ($_SESSION['role'] == 'applicant') {
    $messages = getMessagesForApplicant($pdo, $info['id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<h1>Findhire</h1>

<!-- Container for Profile and Navigation -->
<div class="container">

    <!-- Profile Section -->
    <div class="profile">
        <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
        <p>Name: <?php echo $info['first_name'] . ' ' . $info['last_name']; ?></p>
        <p>Email: <?php echo $info['email']; ?></p>
    </div>

    <!-- Navigation Links -->
    <div class="nav">
        <a href="accounts.php">View Users</a>
        <a href="logout.php">Log Out</a>
        <a href="message.php">Messages</a>
 

        <?php if ($_SESSION['role'] == 'hr'): ?>
            <!-- Show/Hide link only visible for HR -->
            <a href="#" id="toggleJobPostForm" class="toggle-link">Hide</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($_SESSION['role'] == 'hr'): ?>
<!-- Container for Job Post Form -->
<div class="container1" id="container1">
    <div class="job-post-form">
        <h3>Create a Job Post</h3>
        <form action="core/handleform.php" method="POST" enctype="multipart/form-data">
            <label for="postTitle">Job Title:</label>
            <input type="text" name="jobTitle" id="jobTitle" required>
            
            <label for="jobDescription">Job Description:</label>
            <textarea name="jobDescription" id="jobDescription" rows="4" required></textarea>
            
            <input type="submit" name="insertJobPost" value="Post">
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Job Post Section -->
<h3>All Job Posts</h3>
<div class="job-posts-container">
    <?php foreach ($showJobPosts as $row): ?>
        <div class="job-post">
            <h4><?php echo htmlspecialchars($row['description']); ?></h4>
            <p>Posted by: <?php echo htmlspecialchars($row['created_by_name']); ?></p>
            <p>User ID: <?php echo htmlspecialchars($row['created_by']); ?></p>
            <p>Date: <?php echo htmlspecialchars($row['created_at']); ?></p>

            <!-- Actions -->
            <div class="actions">
                <?php if ($_SESSION['username'] == $row['created_by_name']): ?>
                    <a href="delete.php?job_posts_id=<?php echo $row['job_posts_id']; ?>">Delete</a>
                <?php endif; ?>

                <?php if ($_SESSION['role'] == 'applicant'): ?>
                    <!-- Apply button and Message for Applicants -->
                    <div class="apply-section">
                        <a href="application.php?job_post_id=<?php echo $row['job_posts_id']; ?>" class="apply-button">Apply</a>
                        <a href="message.php" class="message-button">Message</a>
                    </div>
                <?php endif; ?>

                <?php if ($_SESSION['username'] == $row['created_by_name']): ?>
                    <a href="resumes.php?job_posts_id=<?php echo $row['job_posts_id']; ?>">Resumes</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Message Section for HR or Applicant -->
<?php if (isset($messages)): ?>
    <div class="messages-container">
        <?php foreach ($messages as $message): ?>
            <div class="message">
                <p><strong>From: </strong><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?></p>
                <p><strong>Message: </strong><?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                <p><small><?php echo htmlspecialchars($message['created_at']); ?></small></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleLink = document.getElementById('toggleJobPostForm');
        const container1 = document.getElementById('container1');

        if (toggleLink) { // Check if toggleLink exists (for roles other than HR)
            toggleLink.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent the default link behavior

                if (container1.style.display === 'none') {
                    container1.style.display = 'block'; // Show the container
                    toggleLink.textContent = 'Hide'; // Update link text
                } else {
                    container1.style.display = 'none'; // Hide the container
                    toggleLink.textContent = 'Show'; // Update link text
                }
            });
        }
    });
</script>

</body>
</html>
