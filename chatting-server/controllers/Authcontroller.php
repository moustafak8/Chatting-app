<?php
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../services/Authentication.php");

class AuthController{
    function login(){
         global $connection;
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $result= AuthenticationService::authenticate($data['email'],$data['password']);
        if($result['success']){
            echo ResponseService::response(200, ["message" => "Login successful", "user" => $result['user']]);
        }
        else{
            echo ResponseService::response(401, ["message" => $result['message']]);
        }

    }
}