<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: admin_login.php");
    exit;
}

$inactive = 1200 * 1000;
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_unset();
        session_destroy();
        header("location: admin_login.php");
        exit;
    }
}

if (!isset($_SESSION['hrefchansky'])) {
    $_SESSION['hrefchansky'] = 'yesiamtheadmin';
}

if ($_SESSION['hrefchansky'] !== 'yesiamtheadmin') {
    session_unset();
    session_destroy();
    header("location: admin_login.php");
    exit;
}

$_SESSION['timeout'] = time();

$host = 'localhost';
$db = 'urine';
$user = 'root';
$password = '';

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = ['Users', 'Researchers', 'Test_Strips_Types', 'Smartphones', 'Analysis', 'Studies', 'Requests', 'FileRequest'];

if (isset($_POST['table'])) {
    $selected_table = $_POST['table'];
    if (!(empty($selected_table))){
        $result = $conn->query("SELECT * FROM $selected_table");
    }
}

?>

<style>
</style>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="sources\admin.css">
    <title>Table Redactor</title>
</head>
<body>
    <a href="admin_logout.php" id="in">Выйти</a>
    <form method="POST" style="height: 7%; overflow: hidden;">
        <label for="table">Choose a table:</label>
        <select name="table" id="table" onchange="submitForm()">
            <?php 
                if (empty($selected_table)) {
                    echo '<option value="">Select table</option>';
                }
            ?>
            <?php foreach ($tables as $table): ?>
                <option value="<?php echo $table; ?>" <?php if (!(empty($selected_table))) {if ($table === $selected_table) {echo 'selected';}} ?>><?php echo $table; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" id="submitBtn" value="View" style="display: none;">
    </form>

<div id="sql-terminal">
    <form method="POST">
        <textarea name="sql_query" rows="4" placeholder="Введите SQL-запрос здесь..." id='sql_area'></textarea><br>
        <input type="submit" name="execute_query" value="Run" style="position: absolute; bottom: 25px; right: 25px;">
    </form>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('sql_area');
        const submitButton = document.querySelector('input[name="execute_query"]');

        textarea.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.key === 'Enter') {
                event.preventDefault();
                submitButton.click();
            }
        });
    });

    function submitForm() {
        var submitButton = document.getElementById('submitBtn');
        submitButton.click();
    }
</script>

<?php

if (isset($_POST['execute_query'])) {
    $sql_query = $_POST['sql_query'];

    if ($result_query = $conn->query($sql_query)) {
        if ($result_query->num_rows > 0) {
            echo "<h2 style='height: 4%;'>Результат запроса:</h2>";
            echo "<div class='table_show'><table border='1'><tr>";
            while ($field = $result_query->fetch_field()) {
                echo "<th>{$field->name}</th>";
            }
            echo "</tr>";
            while ($row = $result_query->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table></div>";
        } else {
            echo "<p>Запрос выполнен успешно, но результат пуст.</p>";
        }
        $result_query->free();
    } else {
        echo "<p>Ошибка выполнения запроса: " . $conn->error . "</p>";
    }
}

if (empty($selected_table)) {
    $selected_table = 'Users';
}



if (isset($_POST['save'])) {
    $has_changes = false;
    $table = $_POST['table'];

    if (isset($_POST['data'])) {
        foreach ($_POST['data'] as $id => $data) {
            $name = $selected_table === 'Users' ? 'user_id' : ($selected_table === 'Researchers' ? 'master_id' : ($selected_table === 'Test_Strips_Types' ? 'type_id' : ($selected_table === 'Smartphones' ? 'phone_id' : ($selected_table === 'Studies' ? 'study_id' : ($selected_table === 'FileRequest' ? 'file_request_id' : ($selected_table === 'Requests' ? 'request_id' : 'analysis_id'))))));
            if (empty($data[$name])) {
                $conn->query("DELETE FROM $table WHERE $name = $id");
                $has_changes = true;
            } else {
                $set = [];
                foreach ($data as $column => $value) {
                    if ($column !== "id") {
                        $set[] = "$column = '" . $conn->real_escape_string($value) . "'";
                    }
                }
                if (!empty($set)) {
                    $set_string = implode(", ", $set);
                    $name = $selected_table === 'Users' ? 'user_id' : ($selected_table === 'Researchers' ? 'master_id' : ($selected_table === 'Test_Strips_Types' ? 'type_id' : ($selected_table === 'Smartphones' ? 'phone_id' : ($selected_table === 'Studies' ? 'study_id' : ($selected_table === 'FileRequest' ? 'file_request_id' : ($selected_table === 'Requests' ? 'request_id' : 'analysis_id'))))));
                    $conn->query("UPDATE $table SET $set_string WHERE $name = $id");
                    $has_changes = true;
                }
            }
        }
    }

    if (isset($_POST['new_data']) && !empty($_POST['new_data'])) {
        $new_data = $_POST['new_data'];
        $columns = implode(", ", array_keys($new_data));
        $values = implode(", ", array_map(function($value) use ($conn) {
            return $value === null ? "NULL" : "'" . $conn->real_escape_string($value) . "'";
        }, $new_data));
        
        if (explode(',', str_replace("'", "", $values))[0] !== '') {
            $conn->query("INSERT INTO $table ($columns) VALUES ($values)");
        }
        $has_changes = true;
    }


    if (!$has_changes) {
        echo "<script>alert('No changes made or no new record to add.');</script>";
    }

    $result = $conn->query("SELECT * FROM $selected_table");
}


?>

    <?php if (isset($result)): ?>
    <form method="POST" style="overflow: hidden;">
        <div class='table_show'>
        <input type="hidden" name="table" value="<?php echo $selected_table; ?>">
        <table border="1">
            <tr>
                <?php
                $fields = $result->fetch_fields();
                foreach ($fields as $field) {
                    echo "<th>{$field->name}</th>";
                }
                ?>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <input type="hidden" name="data[<?php echo $row[$selected_table === 'Users' ? 'user_id' : ($selected_table === 'Researchers' ? 'master_id' : ($selected_table === 'Test_Strips_Types' ? 'type_id' : ($selected_table === 'Smartphones' ? 'phone_id' : ($selected_table === 'Studies' ? 'study_id' : ($selected_table === 'FileRequest' ? 'file_request_id' : ($selected_table === 'Requests' ? 'request_id' : 'analysis_id'))))))]; ?>][id]" value="<?php echo $row[$selected_table === 'Users' ? 'user_id' : ($selected_table === 'Researchers' ? 'master_id' : ($selected_table === 'Test_Strips_Types' ? 'type_id' : ($selected_table === 'Smartphones' ? 'phone_id' : ($selected_table === 'Studies' ? 'study_id' : ($selected_table === 'FileRequest' ? 'file_request_id' : ($selected_table === 'Requests' ? 'request_id' : 'analysis_id'))))))]; ?>">
                    <?php foreach ($row as $column => $cell): ?>
                        <?php if ($column != 'id'): ?>
                            <td>
                                <input type="text" name="data[<?php echo $row[$selected_table === 'Users' ? 'user_id' : ($selected_table === 'Researchers' ? 'master_id' : ($selected_table === 'Test_Strips_Types' ? 'type_id' : ($selected_table === 'Smartphones' ? 'phone_id' : ($selected_table === 'Studies' ? 'study_id' : ($selected_table === 'FileRequest' ? 'file_request_id' : ($selected_table === 'Requests' ? 'request_id' : 'analysis_id'))))))]; ?>][<?php echo $column; ?>]" value="<?php echo htmlspecialchars($cell); ?>">
                            </td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
            <tr>
                <?php foreach ($fields as $field): ?>
                    <td>
                        <input type="text" name="new_data[<?php echo $field->name; ?>]" placeholder="Введите <?php echo htmlspecialchars($field->name); ?>">
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>
        <input type="submit" name="save" value="Save" style="position: fixed; left: 10px; bottom: 10px;">
    </form>
<?php endif; ?>


<?php
$conn->close();
?>
</body>
</html>
