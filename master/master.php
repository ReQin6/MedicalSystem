<?php
session_start();

if (!isset($_SESSION['master_id'])) {
    header("Location: register.php");
    exit();
}

$timeout_duration = 30 * 60 * 1000; 
if (isset($_SESSION['last_master_activity']) && (time() - $_SESSION['last_master_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: register.php");
    exit();
}

$_SESSION['last_master_activity'] = time();

require '../db.php';

$requests_stmt = $pdo->prepare("SELECT Requests.*, Users.full_name FROM Requests JOIN Users ON Requests.user_id = Users.user_id WHERE done = 0");
$requests_stmt->execute();
$requests_result = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);

$file_requests_stmt = $pdo->prepare("SELECT FileRequest.*, Users.full_name FROM FileRequest JOIN Users ON FileRequest.user_id = Users.user_id");
$file_requests_stmt->execute();
$file_requests_result = $file_requests_stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION['error_message'])) {
    echo "<div class='error_message'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['ok_message'])) {
    echo "<div class='ok_message'>" . $_SESSION['ok_message'] . "</div>";
    unset($_SESSION['ok_message']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Master Dashboard</title>
    <link rel="stylesheet" href="../source/master_styles.css">
</head>
<body>
<h1>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['full_master_name']); ?></h1>
<form method="post" action="logout.php" class="logout-form">
    <button type="submit" name="logout">Выйти</button>
</form>

<div class="tabs">
    <div class="tab active" onclick="showTab('requests')">Запросы</div>
    <div class="tab" onclick="showTab('file_requests')">Файловые запросы</div>
</div>

<div class="workspace">
    <div id="requests" class="tab-content active">
        <?php foreach ($requests_result as $row): ?>
            <div class="card" onclick="document.location.href = 'requestprocessing.php?request=<?php echo $row['request_id'];?>'">
                <h3><?php echo htmlspecialchars($row['full_name']); ?></h3>
                <p>Дата: <?php echo htmlspecialchars($row['date']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="file_requests" class="tab-content">
        <?php foreach ($file_requests_result as $row): ?>
            <div class="card" onclick="document.location.href = 'fileprocessing.php?filerequest=<?php echo $row['file_request_id'];?>'">
                <h3><?php echo htmlspecialchars($row['full_name']); ?></h3>
                <img src="../uploads/<?php echo htmlspecialchars($row['file']); ?>" alt="file">
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    document.querySelector(`.tab[onclick="showTab('${tabName}')"]`).classList.add('active');
    
    localStorage.setItem('activeTab', tabName);
}

document.addEventListener('DOMContentLoaded', function() {
    var activeTab = localStorage.getItem('activeTab') || 'requests';
    showTab(activeTab);
});
</script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const errorMessages = document.querySelectorAll('.error_message');

            errorMessages.forEach(function(message) {
                message.addEventListener('click', function() {
                    message.remove();
                });
            });

            const okMessages = document.querySelectorAll('.ok_message');

            okMessages.forEach(function(message) {
                message.addEventListener('click', function() {
                    message.remove();
                });
            });
        });
    </script>
</body>
</html>
