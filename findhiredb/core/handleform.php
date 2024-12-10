<?php
require_once 'dbconfig.php';
require_once 'model.php';

if (isset($_POST['loginUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $loginQuery = checkIfUserExists($pdo, $username);

        if ($loginQuery && isset($loginQuery['userInfoArray'])) {
            $userInfo = $loginQuery['userInfoArray'];

            if (password_verify($password, $userInfo['password'])) {
                $_SESSION['id'] = $userInfo['id'];
                $_SESSION['username'] = $userInfo['username'];
                $_SESSION['first_name'] = $userInfo['first_name'];
                $_SESSION['role'] = $userInfo['role'];
                header("Location: ../main.php");
                exit();
            }
        }

        $_SESSION['message'] = "Username/password invalid";
        $_SESSION['status'] = "400";
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = "400";
    }

    header("Location: ../login.php");
    exit();
}

if (isset($_GET['logoutButton'])) {
    unset($_SESSION['id']);
    unset($_SESSION['username']);
    unset($_SESSION['role']);
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['insertNewUserBtn'])) {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim($_POST['role']);

    $errors = [];

    // Input validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
        define('HR_CODE', 'mysecure456');  // Define a constant for the HR code.

            if ($role === 'hr') {
                $CODE_HR = trim($_POST['CODE_HR']);
                if ($CODE_HR !== HR_CODE) {  // Compare with the constant HR_CODE
                    $_SESSION['message'] = "Invalid HR code!";
                    $_SESSION['status'] = '400';
                    header("Location: ../register.php");
                    exit();
                }
            }

            $insertQuery = insertNewUserBtn($pdo, $username, $first_name, $last_name, $email, $role, password_hash($password, PASSWORD_DEFAULT));
            $_SESSION['message'] = $insertQuery['message'];
            $_SESSION['status'] = $insertQuery['status'];

            if ($insertQuery['status'] == '200') {
                header("Location: ../login.php");
                exit();
            } else {
                header("Location: ../register.php");
                exit();
            }
        }
    }


if (isset($_POST['insertJobPost'])) {

        // Fix: Changed $_POST['postTitle'] to $_POST['jobTitle']
        $postTitle = $_POST['jobTitle'];
        $postDescription = $_POST['jobDescription']; // Assuming this was intended
    
        // Check if we want to edit a post
        $job_posts_id = isset($_POST['job_posts_id']) ? $_POST['job_posts_id'] : "";
    
        // Save the post to the database (with jobTitle)
        $savePostToDB = newJobPost($pdo, $postTitle, $postDescription, $_SESSION['id'], $job_posts_id);
    
        if ($savePostToDB) {
            // Redirect to the main page after saving the post
            header("Location: ../main.php");
        } else {
            // Redirect to the main page if saving fails
            header("Location: ../main.php");
            echo "Something went wrong";
        }
}  

if (isset($_POST['submitApplicationBttn'])) {

    // Get the applicant ID from session
    $applicant_id = $_SESSION['id'];

    // Get the job post ID from the form and ensure it's an integer
    $job_post_id = intval($_POST['job_post_id']);

    // Get the message from the form
    $messages = $_POST['message'];

    // Get file details
    $fileName = $_FILES['resume']['name'];
    $tempFileName = $_FILES['resume']['tmp_name'];

    // Get the file extension
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    // Generate a unique ID for the resume file
    $uniqueID = sha1(md5(rand(1, 9999999)));
    $resumeName = $uniqueID . "." . $fileExtension;

    // Insert resume data into the database
    $insertResumeToDB = ResumeToDB($pdo, $job_post_id, $applicant_id, $messages, $resumeName, $applications_id);

    if ($insertResumeToDB) {

        // Define the folder path to store the resume
        $folder = "../attachments/" . $resumeName;

        // Move the uploaded resume to the desired folder
        if (move_uploaded_file($tempFileName, $folder)) {
            // Redirect to index page if the file is uploaded successfully
            header("Location: ../main.php");
            exit();
        } else {
            echo "<h1>File upload failed. Please try again.</h1>";
        }

    } else {
        // If database insertion fails
        echo "<h1>Something went wrong. Please try again later.</h1>";
    }
}

if (isset($_POST['deleteBtn'])) {
    $title = $_POST['title'];
    $job_posts_id = $_POST['job_posts_id'];
    $deleteJobPost = deleteJobPost($pdo, $job_posts_id);

    if ($deleteJobPost) {
        header("Location: ../main.php");
    }
}

if (isset($_POST['acceptButton']) || isset($_POST['rejectButton']) || isset($_POST['undoButton'])) {
    // Retrieve necessary form data
    $job_posts_id = $_POST['job_posts_id'];
    $applications_id = $_POST['applications_id'];
    $HR_id = $_POST['user_id'];
    $applicant_id = $_POST['applicant_id'];
    $jobPostDescription = $_POST['job_post_description'];

    // Initialize variables
    $newStatus = '';
    $message = '';

    // Determine action based on button clicked
    if (isset($_POST['acceptButton'])) {
        $newStatus = 'Accepted';
        $message = "Congratulations! Your application for <i>$jobPostDescription</i> has been accepted.";
    } elseif (isset($_POST['rejectButton'])) {
        $newStatus = 'Rejected';
        $message = "We're sorry. Your application for <i>$jobPostDescription</i> posted by <b>$HR_id</b> has been rejected.";
    } elseif (isset($_POST['undoButton'])) {
        $newStatus = 'Pending';
    }

    // Handle resume action (status update)
    $resumeAction = resumeAction($pdo, $newStatus, $applications_id);

    // Redirect based on resume action success
    if ($resumeAction) {
        header("Location: ../resumes.php?job_posts_id=$job_posts_id");
    } else {
        header("Location: ../notauthorized.php");
    }
    
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $content = $_POST['content'];
    $receiver_id = $_POST['receiver_id'];
    $sender_id = $_SESSION['id']; // The logged-in user's ID stored in the session
    $job_post_id = isset($_POST['job_posts_id']) ? $_POST['job_posts_id'] : null; // Check if job_post_id is set
    $sender_username = $_SESSION['username'];

    // Call the sendMessage function to handle inserting the message into the database
    $result = sendMessage($pdo, $sender_id, $receiver_id, $content);

    // Remove notification logic

    // Redirect based on the success of the message sending
    if ($result['success']) {
        header("Location: ../message.php?receiver_id=$receiver_id");
        exit;
    } else {
        header("Location: ../message.php?receiver_id=$receiver_id&error=" . urlencode($result['message']));
        exit;
    }
}

?>
