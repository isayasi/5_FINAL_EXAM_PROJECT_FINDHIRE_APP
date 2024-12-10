<?php
require_once 'core/model.php';
require_once 'core/dbconfig.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

if ($_SESSION['role'] !== 'applicant') {
    header("Location: login.php");
    exit();
}

checkRole('applicant');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Page</title>
    <link rel="stylesheet" href="css/application.css">
</head>

<body>
    <!-- Navigation -->
    <button onclick="history.back()">Back</button>

    <!-- Page Title -->
    <h1>Job Application</h1>
    <p>This is the application page for job post ID: <?php echo htmlspecialchars($_GET['job_post_id']); ?></p>

    <?php 
    // Fetch the job details based on the job post ID
    $job_post_id = intval($_GET['job_post_id']);
    $job = getJobDetails($pdo, $job_post_id);

    // Check if the user has already applied
    $applicationStatus = checkApplication($pdo, $_SESSION['id']);
    ?>

    <?php if ($applicationStatus) { ?>
        <p>You already applied for this job. Current status: <?php echo htmlspecialchars($applicationStatus['status']); ?></p>
    <?php } else { ?>
        <!-- Application Form -->
        <form action="core/handleform.php" method="POST" enctype="multipart/form-data">
            <!-- Job Details -->
            <div>
                <p><?php echo htmlspecialchars($job['title']); ?></p>
                <p>Posted by: HR <?php echo htmlspecialchars($job['created_by_name']); ?></p>
                <p>Description: <?php echo htmlspecialchars($job['description']); ?></p>
            </div>

            <!-- Hidden Job Post ID -->
            <input type="hidden" name="job_post_id" value="<?php echo $job_post_id; ?>">

            <!-- Message Input -->
            <textarea name="message" placeholder="Enter your message..." required></textarea>

            <!-- Resume Upload -->
            <input type="file" name="resume" accept="application/pdf" required>

            <!-- New Submit Button -->
             <p>
            <button type="submit" name="submitApplicationBttn">Submit Application</button>
            </p>
        </form>


    <?php } ?>
</body>

</html>
