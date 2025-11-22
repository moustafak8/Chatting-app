<?php
include("../connection/connection.php");

$sql = "CREATE TABLE conversations (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$query = $connection->prepare($sql);
$query->execute();

echo "Table Created!";

?>