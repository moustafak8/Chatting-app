<?php
require_once(__DIR__ . "/../models/Conversation.php");
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../connection/connection.php");

class conversationcontroller
{
    function newconversation()
    {
        global $connection;
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $newuser = ["title" => $data['title']];
        $new = new Conversation($newuser);
        $insertedId = $new->add($connection, $newuser);
        if ($insertedId) {
            echo ResponseService::response(200, ["message" => "Conversation added successfully", "id" => $insertedId]);
        } else {
            echo ResponseService::response(500, ["error" => "Failed to add Conversation"]);
        }
    }
}

