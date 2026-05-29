<?php
$host = 'localhost';
$port = '3307';
$dbname = 'atendelab';
$user = 'root';
$password = '';
try {
 $pdo = new PDO(
 "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
 $user,
 $password
 );
 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
 die('Erro ao conectar com o banco de dados: ' . $e->getMessage());
}