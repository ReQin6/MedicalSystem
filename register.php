<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $login = $_POST['login'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    if (isset($_SESSION['password_message'])) {
        unset($_SESSION['password_message']);
    }

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE login = ?");
    $stmt->execute([$login]);
    $stmtn = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmtn->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error_message'] = "Пользователь с таким логином уже существует!";
        header("Location: register.php");
        exit();
    } elseif ($stmtn->rowCount() > 0) {
        $_SESSION['error_message'] = "Пользователь с таким e-mail уже существует!";
        header("Location: register.php");
        exit();
    } else {
        $stmt = $pdo->prepare("INSERT INTO Users (full_name, login, password, email) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$full_name, $login, $password, $email])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['full_name'] = $full_name;
            $_SESSION['last_activity'] = time();
            header("Location: urine.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Ошибка регистрации!";
            header("Location: register.php");
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_in_sbmt'])) {
    $login = $_POST['login_in'];
    $password = $_POST['password_in'];
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if ($user['password'] === $password) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['last_activity'] = time();
            if (isset($_SESSION['password_message'])) {
                unset($_SESSION['password_message']);
            }
            header("Location: urine.php");
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

if (isset($_SESSION['password_message'])) {
    echo "<div class='password_message'>" . $_SESSION['password_message'] . "<br><a href='register.php?hide=1'>скрыть</a></div>";
}

if (isset($_GET['hide']) && isset($_SESSION['password_message'])) {
    unset($_SESSION['password_message']);
    header("Location: register.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="source/auth_styles.css">
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
        <img src="source/night-icon.png" alt="День" class="icon day-icon">
        <img src="source/day-icon.png" alt="Ночь" class="icon night-icon" style="display: none;">
    </div>


    <div class="card-container">
        <div class="card" id="card">
            <div class="card-front">
                <h2>Вход</h2>
                <form method="post" action="">
                    <input type="text" name="login_in" placeholder="Логин" required>
                    <input type="password" name="password_in" placeholder="Пароль" required>
                    <button type="submit" name="login_in_sbmt">Войти</button>
                </form>
                <?php if (!(isset($_SESSION['password_message']))) {?>
                <div class="toggle" onclick="window.location.href = 'recover.php';">Забыли пароль? Восстановить</div>
                <?php } ?>
                <div class="toggle" onclick="window.location.href = 'master/register.php';">Войти как исследователь</div>
                <div class="toggle" onclick="toggleCard()">Нет аккаунта? Зарегистрироваться</div>
            </div>
            <div class="card-back">
                <h2>Регистрация</h2>
                <form method="post" action="">
                    <input type="text" name="full_name" placeholder="Полное имя" required>
                    <input type="text" name="login" placeholder="Логин" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <input type="email" name="email" placeholder="E-mail" required>
                    <button type="submit" name="register">Зарегистрироваться</button>
                </form>
                <div class="toggle" onclick="toggleCard()">Уже есть аккаунт? Войти</div>
            </div>
        </div>
    </div>
    <script>
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
    <script>
        window.onload = function() {
            const card = document.getElementById("card");
            const state = history.state && history.state.cardState;

            if (state === 'registration') {
                card.classList.add("flip");
            } else {
                card.classList.remove("flip");
            }

            card.classList.add("visible");
        };

        function toggleCard() {
            const card = document.getElementById("card");
            card.classList.toggle("flip");
            const isFlipped = card.classList.contains("flip");
            const state = isFlipped ? 'registration' : 'login';
            history.pushState({ cardState: state }, '');
        }

        window.addEventListener('popstate', function(event) {
            const card = document.getElementById("card");
            if (event.state && event.state.cardState === 'registration') {
                card.classList.add("flip");
            } else {
                card.classList.remove("flip");
            }
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
