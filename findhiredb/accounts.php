<?php 
require_once 'core/dbconfig.php';
require_once 'core/model.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Accounts</title>
    <link rel="stylesheet" href="css/accounts.css">
</head>

<body>
    <!-- Navigation -->
    <button onclick="history.back()">Back</button>

    <!-- Page Title -->
    <h1>View Accounts</h1>
    <p>List of registered users and their roles.</p>

    <?php 
    // Fetch all users
    $getAllUsers = getAllUsers($pdo); 
    ?>

    <!-- User List -->
    <section>
        <?php if (!empty($getAllUsers)) { ?>
            <?php foreach ($getAllUsers as $user) { ?>
                <div>
                    <p>
                        <a <?php echo htmlspecialchars($user['username']); ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <span>(<?php echo htmlspecialchars($user['role']); ?>)</span>
                    </p>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No users found.</p>
        <?php } ?>
    </section>
</body>

</html>
