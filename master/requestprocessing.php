<?php
session_start();

$timeout_duration = 30 * 60 * 1000;
if (isset($_SESSION['last_master_activity']) && (time() - $_SESSION['last_master_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: register.php");
    exit();
}
$_SESSION['last_master_activity'] = time();

require '../db.php';

if (!isset($_SESSION['master_id'])) header('Location: master.php');

$request_id = $_GET['request'];
$stmt = $pdo->prepare("SELECT * FROM Requests WHERE request_id = ?");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if ($request['done'] == 1) {
    $_SESSION['error_message'] = "Запрос уже был обработан!";
    header('Location: master.php');
    exit();
}

$user_id = $request['user_id'];

$analyses = $pdo->prepare("SELECT a.*, r.full_name FROM Analysis a JOIN Researchers r ON a.master_id = r.master_id WHERE a.user_id = ?");
$analyses->execute([$user_id]);

$studies = $pdo->prepare("SELECT * FROM Studies WHERE user_id = ?");
$studies->execute([$user_id]);

$norms = [
    'specific_weight' => [[1.000, 1.030], [1.000, 1.020]], 'nitrites' => [[0.01, 5], [5, 100], [100, 250]],
    'pH' => [[5, 6], [7, 7], [8, 9]], 'ketone_bodies' => [[0.5, 1], [1, 3], [3, 7], [7, 15]],
    'glucose' => [[1.5, 2.8], [2.8, 10], [10, 55]], 'protein' => [[0.25, 0.3], [0.3, 1], [1, 5]],
    'leukocytes' => [[0.01, 10], [10, 25], [25, 75], [75, 100]], 'bilirubin' => [[17, 30], [30, 60], [60, 100]],
    'erythrocytes' => [[0.01, 10], [10, 50], [50, 250]],
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("UPDATE Requests SET done = 1 WHERE request_id = ?");
    $stmt->execute([$request_id]);
    
    $stmt = $pdo->prepare("INSERT INTO Analysis (user_id, master_id, recommendations) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $_SESSION['master_id'], $_POST['recommendations']]);
    
    $_SESSION['ok_message'] = "Запись успешно добавлена!";
    header("Location: master.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Processing</title>
    <link rel="stylesheet" type="text/css" href="../source/requestprocessingstyles.css">
</head>
<body>
    <img src="../source/exit.png" class="exit" onclick="document.location.href = 'master.php'">

    <h2>Analysis</h2>
    <?php while ($a = $analyses->fetch()): ?>
        <p><?php echo "{$a['full_name']}: {$a['recommendations']}"; ?></p>
    <?php endwhile; ?>

    <h2>Studies</h2>
    <ul class="studies-list">
        <?php while ($s = $studies->fetch()): ?>
            <li class="study-item">
                <table>
                    <tbody>
                        <tr>
                            <?php foreach ($norms as $key => $ranges): ?>
                                <td class="indicator-name"><?php echo $key; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($norms as $key => $ranges): ?>
                                <td class="indicator-value 
                                    <?php 
                                        $value = $s[$key]; 
                                        if ($value < $ranges[0][0]) echo 'out-of-range';
                                        elseif ($value >= $ranges[0][0] && $value <= $ranges[0][1]) echo 'normal';
                                        else {
                                            for ($i = 1; $i < count($ranges); $i++) {
                                                if ($value >= $ranges[$i][0] && $value < $ranges[$i][1]) {
                                                    echo 'warning'; break;
                                                } elseif ($i == count($ranges) - 1 || $value >= $ranges[$i][1]) {
                                                    echo 'danger'; break;
                                                }
                                            }
                                        }
                                    ?>" data-norm="<?php echo array_map(fn($r) => "{$r[0]} - {$r[1]}", $ranges)[0]; ?>">
                                    <?php echo $value; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($norms as $key => $ranges): ?>
                                <td class="indicator-normal">
                                    <?php echo "{$ranges[0][0]} - {$ranges[0][1]}"; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td colspan="<?php echo count($norms); ?>" class="date">Date: <?php echo $s['date']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </li>
        <?php endwhile; ?>
    </ul>

    <h2>Add Recommendations</h2>
    <form method="post">
        <textarea name="recommendations" required></textarea>
        <input type="submit" value="Отправить">
    </form>
</body>
</html>
