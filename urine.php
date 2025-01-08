<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$inactive_time_limit = 48 * 60 * 60 * 1000;

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $inactive_time_limit) {
        session_unset();
        session_destroy();
        header("Location: register.php");
        exit();
    }
} else {
    $_SESSION['last_activity'] = time();
}
$_SESSION['last_activity'] = time();

if (isset($_SESSION['message'])) {
    echo '<div class="notification">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Studies WHERE user_id = ?");
$stmt->execute([$user_id]);
$studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$norms = [
    'specific_weight' => [[1.000, 1.030], [1.000, 1.020]],
    'nitrites' => [[0.01, 5], [5, 100], [100, 250]],
    'pH' => [[5, 6], [7, 7], [8, 9]],
    'ketone_bodies' => [[0.5, 1], [1, 3], [3, 7], [7, 15]],
    'glucose' => [[1.5, 2.8], [2.8, 10], [10, 55]],
    'protein' => [[0.25, 0.3], [0.3, 1], [1, 5]],
    'leukocytes' => [[0.01, 10], [10, 25], [25, 75], [75, 100]],
    'bilirubin' => [[17, 30], [30, 60], [60, 100]],
    'erythrocytes' => [[0.01, 10], [10, 50], [50, 250]],
];

function getColor($value, $norm) {
    if ($value < $norm[0]) return 'gray';
    if ($value > $norm[1]) return 'maroon';
    return ($value >= $norm[0] && $value <= $norm[1]) ? 'lightgreen' : 'pink';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Основная Страница</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="source/urine_styles.css">
    <style>
        .study { cursor: pointer; }
    </style>
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

    <h1 style="margin-bottom: 30px;">Добро пожаловать, <?php echo htmlspecialchars($_SESSION['full_name']); ?> <img src="source/pencil.png" onclick="document.location.href = 'red.php'" class="red_image"></h1>
    <form method="post" action="logout.php" class="logout-form">
        <button type="submit" name="logout">Выйти</button>
    </form>
    <?php if (count($studies) > 0): ?>
        <form method="post" action="create_request.php" style="position: fixed; left: 20px; bottom: 20px; z-index: 1000;">
            <button type="submit" name="create_request">Подготовка отчета</button>
        </form>
        <hr style="width: 90%;">

        <h2>Ваши исследования: 
            <div class='upload_form' onclick="document.location.href = 'upload_file.php'">
                <img src="source/add.png" class='add_img'>
            </div>
        </h2>

        <?php foreach ($studies as $study): ?>
            <div class="study-row">
                <div class="study" onclick="toggleDetails(this);">
                    Исследование №<?php echo htmlspecialchars($study['study_id']); ?>
                </div>
                <div class="details">
                    <table border="1">
                        <tr>
                            <th>Параметр</th><th>Значение</th>
                        </tr>
                        <?php foreach (['specific_weight', 'nitrites', 'pH', 'ketone_bodies', 'glucose', 'protein', 'leukocytes', 'bilirubin', 'erythrocytes'] as $param): ?>
                            <tr>
                                <td><?php echo ucfirst(str_replace('_', ' ', $param)); ?></td>
                                <td style="background-color: <?php echo getColor($study[$param], $norms[$param][0]); ?>" class="hoverable <?php if (getColor($study[$param], $norms[$param][0]) === "maroon") { echo "burgundy-bg"; }?> <?php if (getColor($study[$param], $norms[$param][0]) === "lightgreen") { echo "green-bg"; }?>" data-normal-range="<?php echo "Норма от " . implode(" до ",$norms[$param][0]); ?>">
                                    <?php echo htmlspecialchars($study[$param]); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <th>Дата</th><th><?php echo htmlspecialchars($study['date']); ?></th>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <br><br><br><p>У вас нет записей об исследованиях.</p>
        <div class='upload_form' onclick="document.location.href = 'upload_file.php'">
            <img src="source/add.png" class='add_img'>
        </div>
    <?php endif; ?>
    <?php
        $stmt_recommendations = $pdo->prepare("SELECT recommendations FROM Analysis WHERE user_id = ?");
        $stmt_recommendations->execute([$user_id]);
        $recommendations = $stmt_recommendations->fetchAll(PDO::FETCH_COLUMN);
    ?>
    <div style="width: 100%; padding: 25px; margin-bottom: 40px;">
        <?php if (count($recommendations) > 0): ?>
            <h2>Рекомендации:</h2>
            <div class="recommendations">
                <ul>
                    <?php foreach ($recommendations as $recommendation): ?>
                        <li><?php echo htmlspecialchars($recommendation ?: "Текст отсутствует"); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>Рекомендаций нет.</p>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('fileInput').addEventListener('change', function() {
            const files = this.files;
            if (files.length > 1) {
                alert('Пожалуйста, выберите только один файл.');
                this.value = '';
                return;
            }
            document.getElementById('uploadForm').submit();
        });
    </script>

    <script>
        function toggleDetails(study) {
            const details = study.nextElementSibling;

            if (details.style.height && details.style.height !== '0px') {
                details.style.height = '0';
                details.addEventListener('transitionend', function() {
                    details.style.display = 'none';
                }, {once: true});
            } else {
                details.style.display = 'block';
                const fullHeight = details.scrollHeight + 'px';
                details.style.height = fullHeight;
            }

            const studyId = study.innerText.match(/(\d+)/)[0];
            let openedStudies = JSON.parse(localStorage.getItem('openedStudies')) || [];
            
            if (details.style.height === '0px' || !details.style.height) {
                openedStudies = openedStudies.filter(id => id !== studyId);
            } else {
                if (!openedStudies.includes(studyId)) openedStudies.push(studyId);
            }
            
            localStorage.setItem('openedStudies', JSON.stringify(openedStudies));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const openedStudies = JSON.parse(localStorage.getItem('openedStudies')) || [];
            openedStudies.forEach(id => {
                const studyDivs = document.querySelectorAll('.study');
                studyDivs.forEach(studyDiv => {
                    if (studyDiv.innerText.includes(`Исследование №${id}`)) {
                        const details = studyDiv.nextElementSibling;
                        details.style.display = 'block';
                        details.style.height = details.scrollHeight + 'px';
                    }
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.querySelector('.background-animation');
            const bubbleCount = 20;
            const totalAnimationTime = 12;

            for (let i = 0; i < bubbleCount; i++) {
                const bubble = document.createElement('div');
                bubble.classList.add('bubble');
                if (localStorage.getItem('theme') === 'dark') {
                    bubble.classList.add('dark-theme');
                }

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
            setTimeout(() => {
                const notification = document.querySelector('.notification');
                if (notification) {
                    notification.classList.add('hidden');

                    setTimeout(() => {
                        notification.remove();
                    }, 1000);
                }
            }, 2000);
        });
    </script>
</body>
</html>
