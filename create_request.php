<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM Requests WHERE user_id = ?");
$stmt->execute([$user_id]);
$existing_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($existing_requests) === 0) {
    $stmt = $pdo->prepare("INSERT INTO Requests (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $_SESSION['message'] = "Запрос на подготовку отчета успешно создан!";
} else {
    $_SESSION['message'] = "У вас уже есть существующий запрос.";
}

header("Location: urine.php");
exit();
?>
