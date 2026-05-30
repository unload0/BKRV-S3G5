<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Project/PHP/PHPProject.php to edit this template
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>BKRV - S3G5</title>
        <link rel="stylesheet" href="MainStyles.css">
    </head>
    <body>
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        include 'login.php';
//        include 'PageHeader.php';
        ?>
    </body>
</html>
