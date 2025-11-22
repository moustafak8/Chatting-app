<?php
include("../connection/connection.php");

$sql = "CREATE TABLE message_status (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  message_id INT(11) UNSIGNED NOT NULL,
  user_id INT(11) UNSIGNED NOT NULL,   
  delivered_at DATETIME NULL,
  read_at DATETIME NULL,
  CONSTRAINT fk_ms_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  CONSTRAINT fk_ms_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_message_recipient (message_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$query = $connection->prepare($sql);
$query->execute();

echo "Table Created!";

?>