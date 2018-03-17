<?php
try {
  $pdo = new PDO('mysql:host=localhost;port=3306;dbname=misc','Jon','bears');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
