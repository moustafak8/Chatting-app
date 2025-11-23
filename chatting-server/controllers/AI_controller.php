<?php
require_once(__DIR__ . "/../services/ResponsiveService.php");
require_once(__DIR__ . "/../services/AI_summary.php");
class AI_controler{
    function catchup(){
        if ($_SERVER["REQUEST_METHOD"] != 'POST') {
            echo ResponseService::response(405, "Method Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $response = Ai_response::generateAIResponse($data['text']);
        $res=json_decode($response, true);
        if (isset($res['error'])) {
            echo ResponseService::response(500, ["error" => $res['error']]);
            return;
        }

        echo ResponseService::response(200, [
            "message" => "Entry processed successfully",
            "summary" => $res,
        ]);
    }
}
