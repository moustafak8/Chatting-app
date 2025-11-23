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
}
