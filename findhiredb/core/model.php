<?php 
require_once 'dbconfig.php';
require_once 'handleform.php';

function checkIfUserExists($pdo, $username)
{
    // Prepare the response array
    $response = array();

    try {
        // Prepare and execute the SQL query
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);

        // Check if any rows were returned
        if ($stmt->rowCount() > 0) {
            $userInfoArray = $stmt->fetch();
            $response = array(
                "result" => true,
                "status" => "200",
                "userInfoArray" => $userInfoArray
            );
        } else {
            $response = array(
                "result" => false,
                "status" => "400",
                "message" => "User doesn't exist in the database"
            );
        }
    } catch (PDOException $e) {
        // Handle any database-related errors
        $response = array(
            "result" => false,
            "status" => "500",
            "message" => "Database error: " . $e->getMessage()
        );
    }

    // Return the response array
    return $response;
}

function insertNewUserBtn($pdo, $username, $first_name, $last_name, $email, $role, $password)
{
    // Prepare the response array
    $response = array();

    // Check if the username already exists
    $checkIfUserExists = checkIfUserExists($pdo, $username);

    if (!$checkIfUserExists['result']) {
        // Check if the email is already in use
        $checkEmailQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = $pdo->prepare($checkEmailQuery);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            // Email is already in use
            $response = array(
                "status" => "400",
                "message" => "This email is already in use!"
            );
        } else {
            // Insert the new user into the database
            $insertQuery = "INSERT INTO users (username, first_name, last_name, email, role, password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insertQuery);
            $executeQuery = $stmt->execute([$username, $first_name, $last_name, $email, $role, $password]);

            if ($executeQuery) {
                // User successfully inserted
                $response = array(
                    "status" => "200",
                    "message" => "User successfully inserted!"
                );
            } else {
                // Query execution failed
                $response = array(
                    "status" => "400",
                    "message" => "An error occurred while executing the query!"
                );
            }
        }
    } else {
        // Username already exists
        $response = array(
            "status" => "400",
            "message" => "User already exists!"
        );
    }

    // Return the response array
    return $response;
}

function getAllUsers($pdo)
{
    $sql = "SELECT * FROM users";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute();

    if ($executeQuery) {
        return $stmt->fetchAll();
    }
}

function getUserByID($pdo, $username)
{
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$username]);

    if ($executeQuery) {
        return $stmt->fetch();
    }
}

function checkRole($requiredRole)
{
    if ($_SESSION['role'] !== $requiredRole) {
        header('Location: notauthorized.php'); // Redirect to a custom error page
        exit;
    }
}

function newJobPost($pdo, $postTitle, $postDescription, $created_by, $job_posts_id = null)
{
    // Check if job_posts_id is not provided (for new post)
    if (empty($job_posts_id)) {
        $sql = "INSERT INTO job_posts (title, description, created_by) VALUES(?,?,?)";
        $stmt = $pdo->prepare($sql);

        // Execute the query with the provided parameters
        $executeQuery = $stmt->execute([$postTitle, $postDescription, $created_by]);

        // Return true if the query was successful
        if ($executeQuery) {
            return true;
        }
    } else {
        // If job_posts_id is provided (for updating an existing post)
        $sql = "UPDATE job_posts SET title = ?, description = ?, created_by = ? WHERE job_posts_id = ?";
        $stmt = $pdo->prepare($sql);

        // Execute the update query with the provided parameters
        $executeQuery = $stmt->execute([$postTitle, $postDescription, $created_by, $job_posts_id]);

        // Return true if the update was successful
        if ($executeQuery) {
            return true;
        }
    }
}


function ResumeToDB($pdo, $job_post_id, $applicant_id, $messages, $resumePath, $applications_id = null)
{
    // If applications_id is not provided (for new application)
    if (empty($applications_id)) {
        $sql = "INSERT INTO applications (job_post_id, applicant_id, messages, resume_path) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Execute the insert query
        $executeQuery = $stmt->execute([$job_post_id, $applicant_id, $messages, $resumePath]);

        // Return true if the query was successful
        if ($executeQuery) {
            return true;
        } 
    } else {
        // If applications_id is provided (for updating an existing application)
        $sql = "UPDATE applications SET job_post_id = ?, applicant_id = ?, messages = ?, resume_path = ? WHERE applications_id = ?";
        $stmt = $pdo->prepare($sql);

        // Execute the update query with the provided parameters
        $executeQuery = $stmt->execute([$job_post_id, $applicant_id, $messages, $resumePath, $applications_id]);

        // Return true if the update was successful
        if ($executeQuery) {
            return true;
        }
    }
}

function showJobPosts($pdo, $created_by = null)
{
    // Base SQL query
    $sql = "SELECT job_posts.*, users.username AS created_by_name 
            FROM job_posts 
            JOIN users ON job_posts.created_by = users.id
            ORDER BY job_posts.created_at DESC";

    // If a specific user is provided, filter by 'created_by'
    if (!empty($created_by)) {
        $sql .= " WHERE job_posts.created_by = ?";
        $params = [$created_by];
    } else {
        $params = [];
    }

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute($params);

    // Return results if query executed successfully
    if ($executeQuery) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Optionally return false or handle error if query fails
    return false;
}

function showJobPostsProfile($pdo, $username = null)
{
    // Base SQL query with join
    $sql = "SELECT job_posts.*, users.username AS created_by_name 
            FROM job_posts 
            JOIN users ON job_posts.created_by = users.id
            ORDER BY job_posts.created_at DESC";

    // Add condition to filter by username if provided
    $params = [];
    if (!empty($username)) {
        $sql .= " WHERE users.username = ?";
        $params = [$username];
    }

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute($params);

    // Return results if query executed successfully
    if ($executeQuery) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Optionally return false or handle error if query fails
    return false;
}

function getJobDetails($pdo, $job_post_id)
{
    $query = "
        SELECT job_posts.*, users.username AS created_by_name 
        FROM job_posts 
        JOIN users ON job_posts.created_by = users.id 
        WHERE job_posts.job_posts_id = ?
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$job_post_id]);

    // If the query executed successfully, fetch and return the result
    return ($stmt) ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
}

function getPostByID($pdo, $job_posts_id)
{
    $query = "
        SELECT job_posts.*, users.username AS created_by_name 
        FROM job_posts 
        JOIN users ON job_posts.created_by = users.id 
        WHERE job_posts.job_posts_id = ?
    ";

    $stmt = $pdo->prepare($query);
    $success = $stmt->execute([$job_posts_id]);

    // Return the result if the query was successful, otherwise return null
    return ($success) ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
}

function deleteJobPost($pdo, $job_posts_id)
{
    $sql = "DELETE FROM job_posts WHERE job_posts_id  = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$job_posts_id]);

    if ($executeQuery) {
        return true;
    }
}

function collectApplicationByJobPost($pdo, $job_posts_id)
{
    // SQL query to fetch applications for the given job post, including the applicant's username
    $sql = "
        SELECT applications.*, users.username 
        FROM applications 
        JOIN users ON applications.applicant_id = users.id 
        WHERE applications.job_post_id = ? 
        ORDER BY applications.submitted_at DESC
    ";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_posts_id]);

    // Fetch and return all results if the query executed successfully
    return ($stmt) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : null;
}

function getApplicationIDByJobPost($pdo, $job_posts_id)
{
    // Prepare the SQL query to fetch the application ID for the given job post
    $sql = "
        SELECT applications_id 
        FROM applications 
        WHERE job_post_id = ?
    ";

    // Execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_posts_id]);

    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the application ID or null if no result is found
    return ($result) ? $result['applications_id'] : null;
}

function checkApplication($pdo, $applicant_id)
{
    $sql = "SELECT * FROM applications WHERE applicant_id = ?";
    $stmt = $pdo->prepare($sql);
    $executeQuery = $stmt->execute([$applicant_id]);

    if ($executeQuery) {
        return $stmt->fetch();
    }

}

function getApplicationsByJobPost($pdo, $job_posts_id)
{
    $sql = "SELECT applications.*, users.username FROM applications JOIN users ON applications.applicant_id = users.id WHERE applications.job_post_id = ? ORDER BY applications.submitted_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$job_posts_id]);
    $executeQuery = $stmt->execute([$job_posts_id]);

    if ($executeQuery) {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


function showMessages($pdo, $sender_id, $receiver_id)
{
    $sql = "
        SELECT messages.*, users.username AS sender_name
        FROM messages
        JOIN users ON messages.sender_id = users.id
        WHERE (messages.sender_id = ? AND messages.receiver_id = ?)
           OR (messages.sender_id = ? AND messages.receiver_id = ?)
        ORDER BY messages.created_at ASC
    ";

    $stmt = $pdo->prepare($sql);
    $isQueryExecuted = $stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);

    return $isQueryExecuted ? $stmt->fetchAll(PDO::FETCH_ASSOC) : null;
}


function sendMessage($pdo, $sender_id, $receiver_id, $content, $job_post_id = null)
{
    // Return error if content or receiver ID is missing
    if (empty($content) || empty($receiver_id)) {
        return [
            'success' => false,
            'message' => 'Missing data'
        ];
    }

    try {
        // SQL query to insert a new message
        $sql = "INSERT INTO messages (sender_id, receiver_id, content, job_post_id) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sender_id, $receiver_id, $content, $job_post_id]);

        // Return success message
        return [
            'success' => true,
            'message' => 'Message sent successfully'
        ];
    } catch (PDOException $e) {
        // Return error message if there is an exception
        return [
            'success' => false,
            'message' => 'Failed to send message: ' . $e->getMessage()
        ];
    }
}

function resumeAction($pdo, $newStatus, $applications_id)
{
    // SQL query to update application status
    $sql = "UPDATE applications SET status = ? WHERE applications_id = ?";
    $stmt = $pdo->prepare($sql);

    // Execute query and return success or error based on the result
    if ($stmt->execute([$newStatus, $applications_id])) {
        return true;
    }

    // If query fails, output error details
    $errorInfo = $stmt->errorInfo();
    die("SQL Error: " . $errorInfo[2]);
}

function getMessagesByJobPost($pdo, $content, $sender_id, $receiver_id) {
    $stmt = $pdo->prepare("SELECT m.content, m.created_at, u.username as sender_username 
                           FROM messages m
                           JOIN users u ON m.sender_id = u.id
                           WHERE (m.sender_id = :sender_id AND m.receiver_id = :receiver_id)
                           OR (m.sender_id = :receiver_id AND m.receiver_id = :sender_id)
                           ORDER BY m.created_at ASC");
    $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
    $stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch Messages for HR based on the job post and applicant interaction
function getMessagesForHR($pdo, $hr_id) {
    // Get the job posts created by the HR
    $stmt = $pdo->prepare("SELECT jp.job_posts_id, u.first_name, u.last_name, m.content, m.created_at
                           FROM job_posts jp
                           JOIN applications a ON jp.job_posts_id = a.job_post_id
                           JOIN users u ON a.applicant_id = u.id
                           LEFT JOIN messages m ON m.job_post_id = jp.job_posts_id
                           WHERE jp.created_by = :hr_id
                           ORDER BY m.created_at DESC");
    $stmt->execute(['hr_id' => $hr_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch Messages for Applicants based on the job post they applied to
function getMessagesForApplicant($pdo, $applicant_id) {
    // Get the job applications for the applicant
    $stmt = $pdo->prepare("SELECT jp.job_posts_id, jp.title, m.content, m.created_at
                           FROM applications a
                           JOIN job_posts jp ON a.job_post_id = jp.job_posts_id
                           LEFT JOIN messages m ON m.job_post_id = jp.job_posts_id
                           WHERE a.applicant_id = :applicant_id
                           ORDER BY m.created_at DESC");
    $stmt->execute(['applicant_id' => $applicant_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>