<?php
session_start();

// Проверьте, вошел ли пользователь в систему
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
</head>
<body>
    <h1>Добро пожаловать в наше приложение!</h1>

    <?php if ($isLoggedIn): ?>
        <h2>Привет, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
        <p><a href="logout.php">Выйти</a></p>
        <!-- Здесь могут быть дополнительные функции для вошедшего пользователя -->
    <?php else: ?>
        <h2>Пожалуйста, <a href="register.php">зарегистрируйтесь</a> или <a href="login.php">войдите</a></h2>
    <?php endif; ?>
</body>
</html>
