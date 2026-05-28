<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Project/PHP/PHPProject.php to edit this template
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        include 'PageHeader.php';
        include 'HomePage.php';
        ?>
    </body>
</html>
