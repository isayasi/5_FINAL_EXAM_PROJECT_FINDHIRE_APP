<?php
require_once 'core/model.php';
require_once 'core/handleform.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login&register.css">
</head>

<body>

    <h1>Findhire</h1>

    <div class="container">
        <h1>Login</h1>

        <div class="action">
            <?php
            if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
                if ($_SESSION['status'] == "200") {
                    echo "<p class='success-message'>{$_SESSION['message']}</p>";
                } else {
                    echo "<p class='error-message'>{$_SESSION['message']}</p>";
                }


                unset($_SESSION['message']);
                unset($_SESSION['status']);
            }
            ?>
        </div>

        <form action="core/handleform.php" method="POST">
            <p>
                <label for="username">Username</label>
                <input type="text" name="username" required>
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </p>
            <p>
                <input type="submit" name="loginUserBtn" value="Log In" class="submit-button">
            </p>
        </form>
        <hr>
        <div class="asking">
            <p>Don't have an account? Register <a href="register.php">here</a></p>
        </div>
    </div>

</body>

</html>
