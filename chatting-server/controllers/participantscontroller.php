<?php
require_once(__DIR__ . "/../models/participants.php");
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../connection/connection.php");

class participantscontroller
{
     function get_participant()
    {
        global $connection;
        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $message = participants::find($connection, $id);
            echo ResponseService::response(200, $message->toArray());
            return;
        } else {
            $messages = participants::findAll($connection);
            $arr = [];
            foreach ($messages as $message) {
                $arr[] = $message->toArray();
            }
            echo ResponseService::response(200, $arr);
            return;
        }
    }
    function newparticipants()
    {
        global $connection;
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $newuser = ["conversation_id" => $data['conversation_id'] , "user_id" => $data['user_id']];
        $new = new participants($newuser);
        $insertedId = $new->add($connection, $newuser);
        if ($insertedId) {
            echo ResponseService::response(200, ["message" => "participant added successfully", "id" => $insertedId]);
        } else {
            echo ResponseService::response(500, ["error" => "Failed to add participant"]);
        }
    }
}