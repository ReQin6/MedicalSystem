<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_in_sbmt'])) {
    $login = $_POST['login_in'];
    $password = $_POST['password_in'];
    $stmt = $pdo->prepare("SELECT * FROM Researchers WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if ($user['password'] === $password) {
            $_SESSION['master_id'] = $user['master_id'];
            $_SESSION['full_master_name'] = $user['full_name'];
            $_SESSION['last_master_activity'] = time();
            if (isset($_SESSION['password_message'])) {
                unset($_SESSION['password_message']);
            }
            header("Location: master.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Неверный пароль!";
            header("Location: register.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Такого пользователя не существует!";
        header("Location: register.php");
        exit();
    }
}

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../source/auth_styles.css">
</head>
<body>
    <div class="background-animation">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <div id="theme-toggle" class="theme-toggle">
        <img src="../source/night-icon.png" alt="День" class="icon day-icon">
        <img src="../source/day-icon.png" alt="Ночь" class="icon night-icon" style="display: none;">
    </div>


    <div class="card-container">
        <div class="card" id="card">
            <div class="not-flip-card">
                <h2>Вход</h2>
                <form method="post" action="">
                    <input type="text" name="login_in" placeholder="Логин" required>
                    <input type="password" name="password_in" placeholder="Пароль" required>
                    <button type="submit" name="login_in_sbmt">Войти</button>
                </form>
                <div class="toggle" onclick="window.location.href = '../register.php';">Войти как пользователь</div>
            </div>
        </div>
    </div>
    <script>
        window.onload = function() {
            const card = document.getElementById("card");
            card.classList.add("visible");
        };

        document.addEventListener("DOMContentLoaded", function() {
            const container = document.querySelector('.background-animation');
            const bubbleCount = 20;
            const totalAnimationTime = 12;

            for (let i = 0; i < bubbleCount; i++) {
                const bubble = document.createElement('div');
                bubble.classList.add('bubble');

                const size = Math.random() * (80 - 40) + 40;
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                bubble.style.left = `${Math.random() * 100}vw`;

                const animationDuration = Math.random() * (12 - 8) + 8;
                bubble.style.animation = `rise ${animationDuration}s linear infinite`;
                
                const delay = (i / bubbleCount) * totalAnimationTime;
                bubble.style.animationDelay = `-${delay}s`;

                container.appendChild(bubble);
            }
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
    <script type="text/javascript">
        function toggleTheme() {
            const currentTheme = localStorage.getItem('theme');

            if (currentTheme === 'dark') {
                removeDarkTheme();
                localStorage.setItem('theme', 'light');
            } else {
                addDarkTheme();
                localStorage.setItem('theme', 'dark');
            }

            updateIcons();
        }

        function addDarkTheme() {
            const allElements = document.querySelectorAll('*');
            allElements.forEach(element => {
                element.classList.add('dark-theme');
            });
        }

        function removeDarkTheme() {
            const allElements = document.querySelectorAll('*');
            allElements.forEach(element => {
                element.classList.remove('dark-theme');
            });
        }

        function applyTheme() {
            const savedTheme = localStorage.getItem('theme');

            if (savedTheme === 'dark') {
                addDarkTheme();
            } else {
                removeDarkTheme();
            }

            updateIcons();
        }

        function updateIcons() {
            const dayIcon = document.querySelector('.day-icon');
            const nightIcon = document.querySelector('.night-icon');
            
            if (localStorage.getItem('theme') === 'dark') {
                dayIcon.style.display = 'none';
                nightIcon.style.display = 'block';
            } else {
                dayIcon.style.display = 'block';
                nightIcon.style.display = 'none';
            }
        }

        applyTheme();

        document.getElementById('theme-toggle').addEventListener('click', toggleTheme);

    </script>
</body>
</html>
