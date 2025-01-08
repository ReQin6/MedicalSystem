<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("UPDATE Users SET full_name = ?, email = ? WHERE user_id = ?");
    if ($stmt->execute([$full_name, $email, $user_id])) {
        $user['full_name'] = $full_name;
        $user['email'] = $email;
        $_SESSION['ok_message'] = "Данные успешно обновлены!";
        header("Location: red.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Ошибка обновления данных!";
        header("Location: red.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $stmt = $pdo->prepare("UPDATE Users SET password = ? WHERE user_id = ?");
        if ($stmt->execute([$new_password, $user_id])) {
            $_SESSION['ok_message'] = "Пароль успешно изменен!";
            header("Location: red.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Ошибка изменения пароля!";
            header("Location: red.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Пароли не совпадают!";
        header("Location: red.php");
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
    <title>Редактирование данных</title>
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

    <div class="card-container" style="position: relative;">
        <div class="card" id="card">
            <div class="card-front">
                <h2>Редактирование данных</h2>
                <form method="post" action="">
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    <input type="text" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" class="not-allowed" style="cursor: not-allowed; opacity: 0.3;" readonly>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <button type="submit" name="update">Обновить</button>
                </form>
                <div class="toggle" onclick="toggleCard()">Изменить пароль</div>
                <a href="urine.php" style="position: absolute; bottom: 20px;">Вернуться на главную</a>
            </div>
            <div class="card-back">
                <h2>Сменить пароль</h2>
                <form method="post" action="">
                    <input type="password" name="new_password" placeholder="Новый пароль" required>
                    <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
                    <button type="submit" name="change_password">Изменить пароль</button>
                </form>
                <div class="toggle" onclick="toggleCard()">Изменить другие данные</div>
                <a href="urine.php" style="position: absolute; bottom: 20px;">Вернуться на главную</a>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        const inputs = document.querySelectorAll('input.not-allowed');
        inputs.forEach(input => {
            input.addEventListener('focus', (event) => {
                event.target.blur();
            });
        });
    </script>
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
