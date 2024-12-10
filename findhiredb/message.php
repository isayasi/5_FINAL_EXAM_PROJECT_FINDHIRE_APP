<?php
require_once 'core/model.php';
require_once 'core/handleform.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['id'];
$sender_username = $_SESSION['username'];

// Fetch all users (HR or applicants) to display in the dropdown
$users = getUsersForMessages($pdo); // A function to fetch users for messages

// Check if there's a selected receiver_id from the form submission
$receiver_id = isset($_GET['receiver_id']) ? $_GET['receiver_id'] : null;
$messages = null;

if ($receiver_id) {
    // Fetch the messages between the logged-in user and the selected receiver
    $messages = showMessages($pdo, $sender_id, $receiver_id);
}

// Function to get users for messages (you can adjust this query depending on your requirements)
function getUsersForMessages($pdo)
{
    $sql = "SELECT id, first_name, last_name, username FROM users WHERE role != 'admin'"; // Exclude admin if needed
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="css/message.css">
</head>
<body>

<button onclick="history.back()">Back</button>

<h1>Chat</h1>

<!-- Container for Profile and Navigation -->
<div class="container">
    <div class="profile">
        <h2>Welcome, <?php echo $_SESSION['username']; ?></h2>
    </div>

    <!-- Select User to Send Message -->
    <form action="javascript:void(0);" method="GET" id="chatForm">
        <label for="receiver_id">Select Recipient:</label>
        <select name="receiver_id" id="receiver_id" required>
            <option value="" disabled selected>Select a recipient</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>" <?php echo ($receiver_id == $user['id']) ? 'selected' : ''; ?>>
                    <?php echo $user['first_name'] . ' ' . $user['last_name']; ?> (<?php echo $user['username']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Start Chat</button>
    </form>

    <div id="chatBox" style="display:none;">
        <?php if ($receiver_id): ?>
            <h3>Chat with <?php echo htmlspecialchars($users[array_search($receiver_id, array_column($users, 'id'))]['first_name'] . ' ' . $users[array_search($receiver_id, array_column($users, 'id'))]['last_name']); ?></h3>

            <!-- Display the chat messages -->
            <div class="messages-container" id="messagesContainer">
                <?php if ($messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message">
                            <?php if ($message['sender_id'] == $sender_id): ?>
                                <!-- Message from the logged-in user (sender) -->
                                <p style="text-align: right;"><strong>You:</strong> <?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                            <?php else: ?>
                                <!-- Message from the recipient -->
                                <p><strong><?php echo htmlspecialchars($message['sender_name']); ?>:</strong> <?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                            <?php endif; ?>
                            <p><small><?php echo htmlspecialchars($message['created_at']); ?></small></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No messages yet.</p>
                <?php endif; ?>
            </div>

            <!-- Message Sending Form -->
            <form action="core/handleform.php" method="POST" id="messageForm">
                <input type="hidden" name="action" value="send_message">
                <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                <input type="hidden" name="sender_username" value="<?php echo $sender_username; ?>">
                <textarea name="content" placeholder="Type your message..." required></textarea>
                <button type="submit" name="submit_message">Send</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // JavaScript to toggle chat box display and load messages dynamically
    document.getElementById('chatForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Get the selected receiver_id
        const receiverId = document.getElementById('receiver_id').value;

        if (receiverId) {
            // Show the chat box immediately
            document.getElementById('chatBox').style.display = 'block';

            // Fetch messages using AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'chat.php?receiver_id=' + receiverId, true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    // Update the messages container with the new content
                    document.getElementById('messagesContainer').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    });
</script>

</body>
</html>
