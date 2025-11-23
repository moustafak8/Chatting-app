<?php
require_once(__DIR__ . "/../connection/connection.php");
class Ai_response
{
    public static function generateAIResponse($text)
    {
        global $apiKey;
        $system = "You are an assistant that generates short, clear catch-up summaries for chat conversations. 
                    Your task is to summarize only the unread messages that the user missed.
                    Keep the summary brief, easy to understand, and written in a friendly tone.
                    Do NOT invent information. 
                    Only use the messages provided in the input.";
        $userContent = "Here are the unread messages the user missed:\n\n" . $text . "\n\nGenerate a short catch-up summary of what happened.";
        $payload = [
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role" => "system", "content" => $system],
                ["role" => "user",   "content" => $userContent]
            ],
            "max_tokens" => 1000,
            "temperature" => 0.2
        ];
        $headers = [
            "Authorization: Bearer " . $apiKey,
            "Content-Type: application/json",
        ];
        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!$response) return json_encode(["error" => "No response from AI API"]);

        $result = json_decode($response, true);
        if ($httpCode !== 200) {
            $errorMsg = isset($result['error']) ? $result['error']['message'] : "HTTP $httpCode error";
            return json_encode(["error" => $errorMsg]);
        }

        $content = $result['choices'][0]['message']['content'] ?? "";
        if (empty($content)) return json_encode(["error" => "Empty AI response"]);

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) return json_encode(["error" => "Invalid JSON from AI: " . json_last_error_msg()]);
        return json_encode($decoded);
    }
}
