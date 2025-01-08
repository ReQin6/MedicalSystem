<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $uploadDir = 'uploads/';
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = generateRandomString() . '.' . $ext;
    $uploadFilePath = $uploadDir . $newFileName;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['message'] = "Ошибка при загрузке файла!";
        exit();
    }

    $fileType = mime_content_type($file['tmp_name']);
    if ($fileType !== 'text/plain' && $fileType !== 'image/png') {
        $_SESSION['message'] = "Допускаются только текстовые файлы и PNG изображения!";
        exit();
    }

    if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
        if ($ext === 'txt') {
            $data = file($uploadFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $input = [];
            foreach ($data as $line) {
                $parts = explode(':', $line);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    if ($key !== 'phone' && $key !== 'type') {
                        $input[$key] = $value;
                    } else {
                        $input[$key] = $value;
                    }
                }
            }

            // Required fields
            if (!isset($input['phone']) || !isset($input['type'])) {
                $_SESSION['message'] = "Ошибка при загрузке файла! Не указаны необходимые параметры.";
                return;
            }

            $specific_weight = isset($input['specific_weight']) ? floatval($input['specific_weight']) : null;
            $nitrites = isset($input['nitrites']) ? intval($input['nitrites']) : null;
            $pH = isset($input['pH']) ? floatval($input['pH']) : null;
            $ketone_bodies = isset($input['ketone_bodies']) ? floatval($input['ketone_bodies']) : null;
            $glucose = isset($input['glucose']) ? floatval($input['glucose']) : null;
            $protein = isset($input['protein']) ? floatval($input['protein']) : null;
            $leukocytes = isset($input['leukocytes']) ? intval($input['leukocytes']) : null;
            $bilirubin = isset($input['bilirubin']) ? intval($input['bilirubin']) : null;
            $erythrocytes = isset($input['erytrocytes']) ? intval($input['erytrocytes']) : null;

            $phone = $input['phone'];
            $phone_id_query = "SELECT phone_id FROM Smartphones WHERE name = :name";
            $stmt = $pdo->prepare($phone_id_query);
            $stmt->execute(['name' => $phone]);
            $phone_id = $stmt->fetchColumn();

            if (!$phone_id) {
                $_SESSION['message'] = "Ошибка при загрузке файла! Не удается найти устройство.";
                return;
            }

            $type = $input['type'];
            $type_id_query = "SELECT type_id FROM Test_Strips_Types WHERE company_name = :name";
            $stmt = $pdo->prepare($type_id_query);
            $stmt->execute(['name' => $type]);
            $type_id = $stmt->fetchColumn();

            if (!$type_id) {
                $_SESSION['message'] = "Ошибка при загрузке файла! Не удается найти тип тестов.";
                return;
            }

            $insert_query = "INSERT INTO Studies (specific_weight, nitrites, pH, ketone_bodies, glucose, protein, leukocytes, bilirubin, erythrocytes, file, user_id, type_id, phone_id) 
                             VALUES (:specific_weight, :nitrites, :pH, :ketone_bodies, :glucose, :protein, :leukocytes, :bilirubin, :erythrocytes, :file, :user_id, :type_id, :phone_id)";
            $stmt = $pdo->prepare($insert_query);
            $stmt->execute([
                'specific_weight' => $specific_weight,
                'nitrites' => $nitrites,
                'pH' => $pH,
                'ketone_bodies' => $ketone_bodies,
                'glucose' => $glucose,
                'protein' => $protein,
                'leukocytes' => $leukocytes,
                'bilirubin' => $bilirubin,
                'erythrocytes' => $erythrocytes,
                'file' => $newFileName,
                'user_id' => $_SESSION['user_id'],
                'type_id' => $type_id,
                'phone_id' => $phone_id,
            ]);

            $_SESSION['message'] = "Файл успешно загружен и обработан!";
        } elseif ($ext === 'png') {
            $userId = $_SESSION['user_id'];

            $sql = "INSERT INTO FileRequest (user_id, file) VALUES (:user_id, :file)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':file', $newFileName, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Файл успешно загружен и в скором времени будет обработан специалистом!";
            } else {
                $_SESSION['message'] = "Ошибка при добавлении запроса!";
            }
        }
    } else {
        $_SESSION['message'] = "Ошибка при загрузке файла!";
    }
} else {
    $_SESSION['message'] = "Необходимо выбрать файл для загрузки!";
}


header("Location: urine.php");
exit();
?>
