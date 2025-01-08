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

$filerequest_id = $_GET['filerequest'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM FileRequest WHERE file_request_id = ?");
$stmt->execute([$filerequest_id]);
$file_request = $stmt->fetch();

if (!$file_request) {
    $_SESSION['error_message'] = "Запрос не найден!";
    header("Location: master.php");
    exit();
}

$file_path = '../uploads/' . $file_request['file'];
if (!file_exists($file_path)) {
    $_SESSION['error_message'] = "Файл поврежден или не доступен!";
    header("Location: master.php");
    exit();
}

$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM FileRequest WHERE file_request_id = ?");
    $stmt->execute([$filerequest_id]);
    $file_request = $stmt->fetch();

    if (!$file_request) {
        $_SESSION['error_message'] = "Запрос уже был обработан.";
        header("Location: master.php");
        exit();
    }

    $data = $_POST;

    $stmt = $pdo->prepare("SELECT type_id FROM Test_Strips_Types WHERE company_name = ?");
    $result = $stmt->execute([$data['type_name']]);

    if (!$result) {
        $errorInfo = $stmt->errorInfo();
        $_SESSION['error_message'] = "Ошибка SQL: " . $errorInfo[2];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $type_id = $stmt->fetchColumn();
    }
    if (!$type_id) {
        $_SESSION['error_message'] = "Тип тест-полосок не найден.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT phone_id FROM Smartphones WHERE name = ?");
    $result = $stmt->execute([$data['phone_name']]);

    if (!$result) {
        $errorInfo = $stmt->errorInfo();
        $_SESSION['error_message'] = "Ошибка SQL: " . $errorInfo[2];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $phone_id = $stmt->fetchColumn();
    }


    if (!$phone_id) {
        $_SESSION['error_message'] = "Смартфон не найден.";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    $insert_stmt = $pdo->prepare("INSERT INTO Studies (specific_weight, nitrites, pH, ketone_bodies, glucose, protein, leukocytes, bilirubin, erythrocytes, file, user_id, type_id, phone_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->execute([
        $data['specific_weight'] ?? null,
        $data['nitrites'] ?? null,
        $data['pH'] ?? null,
        $data['ketone_bodies'] ?? null,
        $data['glucose'] ?? null,
        $data['protein'] ?? null,
        $data['leukocytes'] ?? null,
        $data['bilirubin'] ?? null,
        $data['erythrocytes'] ?? null,
        $file_request['file'],
        $file_request['user_id'],
        $type_id,
        $phone_id,
    ]);
    
    $delete_stmt = $pdo->prepare("DELETE FROM FileRequest WHERE file_request_id = ?");
    $delete_stmt->execute([$filerequest_id]);

    $_SESSION['ok_message'] = "Запись успешно добавлена!";
    header("Location: master.php");
    exit();
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

<link rel="stylesheet" type="text/css" href="../source/fileprocessingstyles.css">
<img src="../source/exit.png" class="exit" onclick="document.location.href = 'master.php'">
<img src="<?php echo $file_path;?>">
<form method="POST">
    <table>
        <tr>
            <th>Удельный вес:</th>
            <th>Нитриты:</th>
            <th>pH:</th>
            <th>Кетоновые тела:</th>
            <th>Глюкоза:</th>
            <th>Белок:</th>
            <th>Лейкоциты:</th>
            <th>Билирубин:</th>
            <th>Эритроциты:</th>
        </tr>
        <tr>
            <td><input type="text" name="specific_weight" value="<?= htmlspecialchars($data['specific_weight'] ?? '') ?>"></td>
            <td><input type="number" name="nitrites" value="<?= htmlspecialchars($data['nitrites'] ?? '') ?>"></td>
            <td><input type="text" name="pH" value="<?= htmlspecialchars($data['pH'] ?? '') ?>"></td>
            <td><input type="text" name="ketone_bodies" value="<?= htmlspecialchars($data['ketone_bodies'] ?? '') ?>"></td>
            <td><input type="text" name="glucose" value="<?= htmlspecialchars($data['glucose'] ?? '') ?>"></td>
            <td><input type="text" name="protein" value="<?= htmlspecialchars($data['protein'] ?? '') ?>"></td>
            <td><input type="number" name="leukocytes" value="<?= htmlspecialchars($data['leukocytes'] ?? '') ?>"></td>
            <td><input type="number" name="bilirubin" value="<?= htmlspecialchars($data['bilirubin'] ?? '') ?>"></td>
            <td><input type="number" name="erythrocytes" value="<?= htmlspecialchars($data['erythrocytes'] ?? '') ?>"></td>
        </tr>
        <tr>
            <td colspan="3">
                Название типа:
                <select name="type_name" required>
                    <?php
                    foreach ($pdo->query("SELECT company_name FROM Test_Strips_Types") as $row) {
                        echo "<option>{$row['company_name']}</option>";
                    }
                    ?>
                </select>
            </td>
            <td colspan="3">
                Название телефона:
                <select name="phone_name" required>
                    <?php
                    foreach ($pdo->query("SELECT name FROM Smartphones") as $row) {
                        echo "<option>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </td>
            <td colspan="3"><input type="submit" value="Добавить"></td>
        </tr>
    </table>
</form>
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
