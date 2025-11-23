<?php
include("Model.php");

class message extends Model
{
    private ?int $id;
    private int $conversation_id;
    private int $sender_user_id;
    private string $content;

    protected static string $table = "messages";
    public function __construct(array $data)
    {
        $this->id = $data["id"] ?? null;
        $this->conversation_id = $data["conversation_id"];
        $this->sender_user_id = $data["sender_user_id"];
        $this->content = $data["content"];
    }
    public function __toString()
    {
        $idStr = $this->id !== null ? $this->id : "null";
        return  $idStr . " | " . $this->conversation_id . " | " . $this->sender_user_id . " | " . $this->content . " | ";
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "conversation_id" => $this->conversation_id,
            "sender_id" => $this->sender_user_id,
            "content" => $this->content,
        ];
    }
}
