<?php
include("../connection/connection.php");

$sql = "CREATE TABLE conversation_participants (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT(11) UNSIGNED NOT NULL,
  user_id INT(11) UNSIGNED NOT NULL,
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cp_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
  CONSTRAINT fk_cp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_conv_user (conversation_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$query = $connection->prepare($sql);
$query->execute();

echo "Table Created!";

?>