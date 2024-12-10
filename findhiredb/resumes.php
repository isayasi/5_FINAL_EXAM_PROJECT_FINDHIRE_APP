<?php
require_once 'core/model.php';
require_once 'core/handleform.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if the user is an HR
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'hr') {
    die("Unauthorized access.");
}

$job_posts_id = intval($_GET['job_posts_id']);
$applications_id = getApplicationIDByJobPost($pdo, $job_posts_id);

$user_id = $_SESSION['username'];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume</title>
    <link rel="stylesheet" href="css/resumes.css">
</head>

<body>
    <button onclick="history.back()">Back</button>

    <h1>View Resume</h1>

    <p>Job Post ID: <?php echo $job_posts_id; ?></p>
    <p>Logged in as: <?php echo $_SESSION['username']; ?></p>

    <?php 
    // Fetching job post details by job_post_id
    $getPostByID = getPostByID($pdo, $job_posts_id); 
    ?>

    <p>
        <?php echo $getPostByID['title']; ?>">
    </p>

    <h3><?php echo $getPostByID['created_by_name']; ?></h3>
    <p><?php echo $getPostByID['description']; ?></p>
    <p><i>Created on: <?php echo $getPostByID['created_at']; ?></i></p>

    <h3>Applications</h3>

    <?php 
    // Fetching applications for the job post
    $applications = getApplicationsByJobPost($pdo, $job_posts_id); 

    if (empty($applications)) {
        echo "<p>No applications found for this job post.</p>";
    } else {
        foreach ($applications as $application) {
    ?>

            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Submitted at</th>
                        <th>Message</th>
                        <th>Resume File</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <a href="message.php?receiver_id=<?php echo $application['applicant_id']; ?>">
                                <strong><?php echo htmlspecialchars($application['username']); ?></strong>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($application['submitted_at']); ?></td>
                        <td><?php echo htmlspecialchars($application['messages']); ?></td>
                        <td>
                            <a href="resumes/<?php echo htmlspecialchars($application['resume_path']); ?>" target="_blank">View Resume</a>
                        </td>
                        <td><?php echo htmlspecialchars($application['status']); ?></td>
                        <td>
                            <?php if ($application['status'] == 'Pending') { ?>
                                <form action="core/handleform.php" method="POST">
                                    <input type="hidden" name="job_posts_id" value="<?php echo $job_posts_id; ?>">
                                    <input type="hidden" name="applications_id" value="<?php echo $application['applications_id']; ?>"> <!-- Use individual application_id -->
                                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                    <input type="hidden" name="applicant_id" value="<?php echo $application['applicant_id']; ?>">

                                    <!-- Debugging the job post description -->
                                    <input type="hidden" name="job_post_description" value="<?php echo $getPostByID['description']; ?>">

                                    <?php if (in_array($application['status'], ['Accepted', 'Rejected'])) { ?>
                                        <input type="submit" name="undoButton" value="Undo">
                                    <?php } else { ?>
                                        <input type="submit" name="acceptButton" value="Accept">
                                        <input type="submit" name="rejectButton" value="Reject">
                                    <?php } ?>
                                </form>
                            <?php } ?>
                        </td>

                    </tr>
                </tbody>
            </table>

    <?php 
        }
    }
    ?>

</body>

</html>
