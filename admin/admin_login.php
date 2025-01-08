<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: admin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $secret_code = trim($_POST["secret_code"]);
    
    if ($username === "admin" && $password === "admin" && $secret_code === "0987654321") {
        $_SESSION['loggedin'] = true;
        $_SESSION['timeout'] = time();
        header("location: admin.php");
        exit;
    } else {
        $error = "Недействительные учетные данные.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="sources\admin.css">
    <title>Авторизация</title>
</head>
<body style="position: absolute; display: flex; align-content: center; justify-content: center; align-items: center; height: 100%;width: 100%; ">
    <?php if (isset($error)) echo "<p id='error'>$error</p>"; ?>
    <form method="post" action="" id="admin_autorisation">
        <label>Логин:</label>
        <input type="text" name="username" required>
        <label>Пароль:</label>
        <input type="password" name="password" required>
        <label>Секретный код:</label>
        <input type="password" name="secret_code" required>
        <br>
        <input type="submit" value="Войти">
    </form>
</body>
</html>

<script>
    document.querySelectorAll('#error').forEach(item => {
        item.addEventListener('click', function() {
            this.remove();
        });
    });
</script>
