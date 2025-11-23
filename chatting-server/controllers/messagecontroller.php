<?php
require_once(__DIR__ . "/../models/message.php");
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../services/MessageService.php");
require_once(__DIR__ . "/../connection/connection.php");
require_once(__DIR__ . "/../services/AI_summary.php");
require_once(__DIR__ . "/AI_controller.php");

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
    function get_unread()
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

        $result = MessageService::getunread($conversationId, $userId);
        if ($result !== null) {
            echo ResponseService::response(200, ["unread_count" => $result]);
        } else {
            echo ResponseService::response(500, ["error" => "Failed to load unread messages"]);
        }
    }

    function get_unread_summary()
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

        // Get unread count
        $unreadCount = MessageService::getunread($conversationId, $userId);

        if ($unreadCount === 0) {
            echo ResponseService::response(200, ["summary" => "No unread messages"]);
            return;
        }

        if ($unreadCount < 3) {
            echo ResponseService::response(200, ["summary" => "Less than 3 unread messages, no summary generated"]);
            return;
        }

        // Fetch unread messages content
        $unreadMessagesContent = MessageService::getUnreadMessagesContent($conversationId, $userId);
        $combinedText = implode(" ", $unreadMessagesContent);
        $aiService = new AI_controller();
        ob_start();
        $aiService->catchup($combinedText);
        $response = ob_get_clean();
        $res = json_decode($response, true);

        if (isset($res['error'])) {
            echo ResponseService::response(500, ["error" => $res['error']]);
            return;
        }

        $summaryText = "";
        if (isset($res['data']['summary'])) {
            if (is_array($res['data']['summary']) || is_object($res['data']['summary'])) {
                // Check if inner 'summary' key exists
                if (isset($res['data']['summary']['summary'])) {
                    $summaryText = $res['data']['summary']['summary'];
                } else {
                    $summaryText = json_encode($res['data']['summary']);
                }
            } else {
                $summaryText = $res['data']['summary'];
            }
        } else if (is_string($res)) {
            $summaryText = $res;
        }
        echo ResponseService::response(200, ["summary" => $summaryText]);
    }
}
