<?php
require_once(__DIR__ . "/../models/User.php");
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../connection/connection.php");

class usercontroller
{
    function getUsers()
    {
        global $connection;

        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $car = User::find($connection, $id);
            echo ResponseService::response(200, $car->toArray());
            return;
        } else {
            $cars = User::findAll($connection);
            $arr = [];
            foreach ($cars as $car) {
                $arr[] = $car->toArray();
            }
            echo ResponseService::response(200, $arr);
            return;
        }
    }
    function newUser()
    {
        global $connection;
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $password = $data['password'];
        $hashedPassword = hash('sha256', $password);
        $newuser = ["username" => $data['username'], "email" => $data['email'] ,"password" => $hashedPassword];
        $new = new User($newuser);
        $insertedId = $new->add($connection, $newuser);
        if ($insertedId) {
            echo ResponseService::response(200, ["message" => "User added successfully", "id" => $insertedId]);
        } else {
            echo ResponseService::response(500, ["error" => "Failed to add User"]);
        }
    }
    function updateuser()
    {
        global $connection;
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $newdata = ["last_online" => $data['last_online']];
        $row = User::update($connection, $id, $newdata);
        if ($row) {
            echo ResponseService::response(200, ["message" => "row Updated successfully"]);
            return;
        } else {
            echo ResponseService::response(500, ["message" => "failed to update row"]);
            return;
        }
    }
    function deleteuser()
    {
        global $connection;

        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $user = User::Deleterow($connection, $id);
            echo ResponseService::response(200, ["message" => "row deleted successfully"]);
            return;
        } else {
            echo ResponseService::response(500, ["message" => "failed to delete row"]);
            return;
        }
    }
}
