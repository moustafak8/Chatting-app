<?php
require_once(__DIR__ . "/../models/message_status.php");
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../connection/connection.php");

class status_controller
{
    function get_status()
    {
        global $connection;
        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $message = message_status::find($connection, $id);
            echo ResponseService::response(200, $message->toArray());
            return;
        } else {
            $messages = message_status::findAll($connection);
            $arr = [];
            foreach ($messages as $message) {
                $arr[] = $message->toArray();
            }
            echo ResponseService::response(200, $arr);
            return;
        }
    }
    function new_status()
    {
        global $connection;
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $newmessage = ["message_id" => $data['message_id'], "user_id" => $data['user_id']];
        $new = new message_status($newmessage);
        $insertedId = $new->add($connection, $newmessage);
        if ($insertedId) {
            echo ResponseService::response(200, ["message" => "Message_status added successfully", "id" => $insertedId]);
        } else {
            echo ResponseService::response(500, ["error" => "Failed to add status"]);
        }
    }
    function update_status()
    {
        global $connection;
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $newdata = [
            "delivered_at" => $data['delivered_at'],
            "read_at" => $data['read_at'],
        ];
        $row = message_status::update($connection, $id, $newdata);
        if ($row) {
            echo ResponseService::response(200, ["message" => "entry Updated successfully"]);
            return;
        } else {
            echo ResponseService::response(500, ["message" => "failed to update entry"]);
            return;
        }
    }
}
