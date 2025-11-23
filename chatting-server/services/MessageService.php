<?php

require_once(__DIR__ . "/ResponsiveService.php");
require_once(__DIR__ . "/../connection/connection.php");

class MessageService
{
    public static function getMessagesByConversation($conversationId, $userId)
    {
        global $connection;

        if (empty($userId)) {
            return [];
        }
        $sql = "SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("ii", $conversationId, $userId);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows === 0) {
            return [];
        }
        $sql = "UPDATE message_status ms
                INNER JOIN messages m ON ms.message_id = m.id
                SET ms.delivered_at = NOW()
                WHERE m.conversation_id = ? 
                AND ms.user_id = ? 
                AND ms.delivered_at IS NULL";
        $query = $connection->prepare($sql);
        $query->bind_param("ii", $conversationId, $userId);
        $query->execute();

        // Get the other participant's ID
        $sql = "SELECT user_id FROM conversation_participants WHERE conversation_id = ? AND user_id != ?";
        $query = $connection->prepare($sql);
        $query->bind_param("ii", $conversationId, $userId);
        $query->execute();
        $result = $query->get_result();
        $otherUser = $result->fetch_assoc();
        $otherUserId = $otherUser ? $otherUser['user_id'] : null;

        // Get all messages for this conversation
        // For received messages (sender != current user): include status for current user
        // For sent messages (sender = current user): include status for the recipient (other user)
        $sql = "SELECT 
                    m.id as message_id,
                    m.conversation_id,
                    m.sender_user_id,
                    m.content,
                    m.created_at,
                    CASE 
                        WHEN m.sender_user_id = ? THEN ms_recipient.delivered_at
                        ELSE ms_user.delivered_at
                    END as delivered_at,
                    CASE 
                        WHEN m.sender_user_id = ? THEN ms_recipient.read_at
                        ELSE ms_user.read_at
                    END as read_at
                FROM messages m
                LEFT JOIN message_status ms_user ON m.id = ms_user.message_id AND ms_user.user_id = ? AND m.sender_user_id != ?
                LEFT JOIN message_status ms_recipient ON m.id = ms_recipient.message_id AND ms_recipient.user_id = ? AND m.sender_user_id = ?
                WHERE m.conversation_id = ?
                ORDER BY m.created_at";
        $query = $connection->prepare($sql);
        $query->bind_param("iiiiiii", $userId, $userId, $userId, $userId, $otherUserId, $userId, $conversationId);
        $query->execute();
        $result = $query->get_result();

        $objects = [];
        while ($data = $result->fetch_assoc()) {
            $objects[] = $data;
        }

        return $objects;
    }

    public static function getMessageById($messageId)
    {
        global $connection;

        $sql = "SELECT id, conversation_id, sender_user_id, content, created_at FROM messages WHERE id = ?";
        $query = $connection->prepare($sql);
        $query->bind_param("i", $messageId);
        $query->execute();
        $result = $query->get_result();
        $data = $result->fetch_assoc();

        return $data;
    }

    public static function markMessagesAsRead($conversationId, $userId)
    {
        global $connection;

        $sql = "UPDATE message_status ms
                INNER JOIN messages m ON ms.message_id = m.id
                SET ms.read_at = NOW()
                WHERE m.conversation_id = ? 
                AND ms.user_id = ? 
                AND ms.read_at IS NULL
                AND ms.delivered_at IS NOT NULL";
        $query = $connection->prepare($sql);
        $query->bind_param("ii", $conversationId, $userId);
        $query->execute();

        return true;
    }
}
