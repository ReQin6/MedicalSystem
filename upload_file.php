<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

?>

<style type="text/css">

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f0f4f8;
    font-family: 'Arial', sans-serif;
    transition: background-color 0.5s;
}

h1 {
    color: #333;
    font-size: 28px;
    margin-bottom: 20px;
    text-align: center;
}

form {
    background-color: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: left;
}

label {
    font-size: 16px;
    color: #555;
    margin-bottom: 10px;
    display: block;
}

input[type="file"] {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    width: 100%;
    margin-bottom: 15px;
    cursor: pointer;
}

input[type="file"]:hover {
    border-color: #aaa;
}

button {
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    background-color: #007bff;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
    width: 100%;
}

button:hover {
    background-color: #0056b3;
}

@media (max-width: 500px) {
    h1 {
        font-size: 24px;
    }

    form {
        padding: 15px;
    }
}

.exit {
    position: absolute;
    right: 40px;
    top: 40px;
    width: 40px;
    height: 40px;
    cursor: pointer;
}
.exit.dark-theme {
    filter: invert(1);
    background: transparent;
}

.dark-theme {
    color: #e0e0e0;
}

body.dark-theme {
    background-color: #121212;
}

.dark-theme h1 {
    color: #ffffff;
}

.dark-theme form {
    background-color: #1e1e1e;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.dark-theme label {
    color: #b0b0b0;
}

.dark-theme input[type="file"] {
    background-color: #2c2c2c;
    border: 1px solid #444;
    color: #e0e0e0;
}

.dark-theme input[type="file"]:hover {
    border-color: #666;
}

.dark-theme button {
    background-color: #bb86fc;
    color: #000;
}

.dark-theme button:hover {
    background-color: #9e69d3;
}

input[type="file"] {
    display: none;
}

.file-upload {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    border: 2px dashed #007bff;
    border-radius: 8px;
    background-color: #f7f9fc;
    color: #007bff;
    cursor: pointer;
    transition: background-color 0.3s, border-color 0.3s;
    margin-bottom: 15px;
    text-align: center;
}

.file-upload:hover {
    background-color: #e7f1ff;
    border-color: #0056b3;
}

.file-upload .file-name {
    margin-left: 10px;
    font-weight: 500;
    color: #555;
}

.file-icon {
    width: 24px;
    height: 24px;
    fill: #007bff;
    margin-right: 10px;
}

.dark-theme .file-upload {
    border-color: #007bff;
    background-color: #1e1e1e;
    color: #007bff;
}

.dark-theme .file-upload:hover {
    background-color: #292929;
    border-color: #0056b3;
}

.dark-theme .file-upload .file-name {
    color: #ffffff;
}

.dark-theme button {
    background-color: #007bff;
    color: white;
}

.dark-theme button:hover {
    background-color: #0056b3;
}

.dark-theme .file-icon {
    fill: #007bff;
}


</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка файла</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <img src="source/exit.png" class="exit" onclick="document.location.href = 'urine.php'">
    <h1>Загрузка файла</h1>

    <form action="upload.php" method="post" enctype="multipart/form-data">
        <label for="fileInput" class="file-upload">
            <input type="file" name="file" accept=".txt,.png" onchange="updateFileName()" required id="fileInput">
            <i class="fas fa-upload"></i>
            <span class="file-name">Выберите файл для загрузки</span>
        </label>
        <button type="submit">Загрузить</button>
    </form>

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
        function updateFileName() {
            const fileInput = document.getElementById('fileInput');
            const fileNameLabel = document.querySelector('.file-name');

            if (fileInput.files.length > 0) {
                fileNameLabel.textContent = fileInput.files[0].name;
            } else {
                fileNameLabel.textContent = 'Выберите файл для загрузки';
            }
        }
    </script>
</body>
</html>
