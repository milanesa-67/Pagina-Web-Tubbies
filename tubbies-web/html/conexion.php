<?php
$host = 'localhost';
$dbname = 'tubbies_web_2';
$usuario = 'root';
$password = '';

try {
    $conexion = new PDO("mysql:host=localhost;dbname=tubbies_web_2;charset=utf8", "root", "");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>