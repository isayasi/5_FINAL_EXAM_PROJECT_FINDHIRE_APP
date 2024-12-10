<?php
require_once 'core/model.php';
require_once 'core/handleform.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/login&register.css">
    <script>
        // Function to show or hide the HR code input based on selected role
        function toggleHRCodeInput() {
            var role = document.getElementById('role').value;
            var hrCodeInput = document.getElementById('hr_code_input');
            
            if (role === 'hr') {
                hrCodeInput.style.display = 'block';  // Show HR code input
            } else {
                hrCodeInput.style.display = 'none';   // Hide HR code input
            }
        }
    </script>
</head>
<body>
    <h1>Findhire</h1>

    <div class="container">
        <h1>Register</h1>

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

        <form method="POST" action="core/handleform.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required><br><br>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required><br><br>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <label for="role">Role</label>
            <select id="role" name="role" required onchange="toggleHRCodeInput()">
                <option value="applicant">Applicant</option>
                <option value="hr">HR</option>
            </select><br><br>

            <!-- HR code input (hidden by default) -->
            <div id="hr_code_input" style="display:none;">
                <label for="CODE_HR">HR Code</label>
                <input type="text" id="CODE_HR" name="CODE_HR"><br><br>
            </div>

            <p>
                <button type="submit" name="insertNewUserBtn">Register</button>
            </p>

            <div class="haveaccount">
                <p>Already have an account? Sign in <a href="login.php">here</a></p>
            </div>
        </form>
    </div>
</body>
</html>
