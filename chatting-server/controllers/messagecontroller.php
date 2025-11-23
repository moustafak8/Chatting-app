<?php
require_once(__DIR__ . "/../models/message.php");
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../connection/connection.php");

class messagecontroller
{
    function get_message()
    {
        global $connection;
        
        // If requesting a single message by ID
        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $message = message::find($connection, $id);
            echo ResponseService::response(200, $message->toArray());
            return;
        }
        
        // If requesting messages by conversation_id
        if (isset($_GET["conversation_id"])) {
            $conversationId = $_GET["conversation_id"];
            $userId = $_GET["user_id"] ?? null; // Get user_id from query or session
            
            if (!$userId) {
                echo ResponseService::response(401, "User ID required");
                return;
            }
            
            // Verify user is a participant of this conversation
            $checkParticipant = $connection->prepare(
                "SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?"
            );
            $checkParticipant->bind_param("ii", $conversationId, $userId);
            $checkParticipant->execute();
            $participantResult = $checkParticipant->get_result();
            
            if ($participantResult->num_rows === 0) {
                echo ResponseService::response(403, "User is not a participant of this conversation");
                return;
            }
            
            // Start transaction
            $connection->begin_transaction();
            
            try {
                // Mark messages as delivered for this user (where delivered_at IS NULL)
                $updateDelivered = $connection->prepare(
                    "UPDATE message_status ms
                     INNER JOIN messages m ON ms.message_id = m.id
                     SET ms.delivered_at = NOW()
                     WHERE m.conversation_id = ? 
                     AND ms.user_id = ? 
                     AND ms.delivered_at IS NULL"
                );
                $updateDelivered->bind_param("ii", $conversationId, $userId);
                $updateDelivered->execute();
                
                // Get all messages for this conversation with their status for the current user
                $sql = "SELECT 
                            m.id as message_id,
                            m.conversation_id,
                            m.sender_user_id,
                            m.content,
                            m.created_at,
                            ms.delivered_at,
                            ms.read_at
                        FROM messages m
                        LEFT JOIN message_status ms ON m.id = ms.message_id AND ms.user_id = ?
                        WHERE m.conversation_id = ?
                        ORDER BY m.created_at ASC";
                
                $query = $connection->prepare($sql);
                $query->bind_param("ii", $userId, $conversationId);
                $query->execute();
                $result = $query->get_result();
                
                $messages = [];
                while ($row = $result->fetch_assoc()) {
                    $messages[] = [
                        "message_id" => $row["message_id"],
                        "conversation_id" => $row["conversation_id"],
                        "sender_user_id" => $row["sender_user_id"],
                        "content" => $row["content"],
                        "created_at" => $row["created_at"],
                        "delivered_at" => $row["delivered_at"],
                        "read_at" => $row["read_at"]
                    ];
                }
                
                // Commit transaction
                $connection->commit();
                
                echo ResponseService::response(200, $messages);
                return;
                
            } catch (Exception $e) {
                // Rollback on error
                $connection->rollback();
                echo ResponseService::response(500, ["error" => "Failed to fetch messages: " . $e->getMessage()]);
                return;
            }
        }
        
        // Default: return all messages (for backward compatibility)
        $messages = message::findAll($connection);
        $arr = [];
        foreach ($messages as $message) {
            $arr[] = $message->toArray();
        }
        echo ResponseService::response(200, $arr);
        return;
    }
        function new_message()
        {
            global $connection;
            if ($_SERVER["REQUEST_METHOD"] != 'POST') {
                echo ResponseService::response(405, "Method Not Allowed");
                exit;
            }
            $data = json_decode(file_get_contents("php://input"), true);
            $newmessage = ["conversation_id" => $data['conversation_id'], "sender_user_id" => $data['sender_user_id'], "content" => $data['content']];
            $new = new message($newmessage);
            $insertedId = $new->add($connection, $newmessage);
            if ($insertedId) {
                echo ResponseService::response(200, ["message" => "Message added successfully", "id" => $insertedId]);
            } else {
                echo ResponseService::response(500, ["error" => "Failed to add Message"]);
            }
        }
    }

