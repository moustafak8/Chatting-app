<?php
require_once(__DIR__ . "/../models/message.php");
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../services/MessageService.php");
require_once(__DIR__ . "/../connection/connection.php");

class messagecontroller
{
    function get_message()
    {
        global $connection;

        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $message = message::find($connection, $id);
            echo ResponseService::response(200, $message->toArray());
            return;
        }

        if (isset($_GET["conversation_id"])) {
            $conversationId = $_GET["conversation_id"];
            $userId = $_GET["user_id"] ?? null;

            if (!$userId) {
                echo ResponseService::response(401, "User ID required");
                return;
            }

            $messages = MessageService::getMessagesByConversation($conversationId, $userId);
            echo ResponseService::response(200, $messages);
            return;
        }
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
            $messageData = MessageService::getMessageById($insertedId);
            echo ResponseService::response(200, [
                "message" => "Message added successfully",
                "id" => $insertedId,
                "created_at" => $messageData["created_at"]
            ]);
        } else {
            echo ResponseService::response(500, ["error" => "Failed to add Message"]);
        }
    }

    function mark_as_read()
    {
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $conversationId = $data['conversation_id'] ?? null;
        $userId = $data['user_id'] ?? null;

        if (!$conversationId || !$userId) {
            echo ResponseService::response(400, "conversation_id and user_id are required");
            return;
        }

        $result = MessageService::markMessagesAsRead($conversationId, $userId);
        if ($result) {
            echo ResponseService::response(200, ["message" => "Messages marked as read successfully"]);
        } else {
            echo ResponseService::response(500, ["error" => "Failed to mark messages as read"]);
        }
    }
}
