<?php
include("../connection/connection.php");

$sql = "CREATE TABLE messages (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT(11) UNSIGNED NOT NULL,
  sender_user_id INT(11) UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_msg_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_sender FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE SET NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$query = $connection->prepare($sql);
$query->execute();

echo "Table Created!";

?>