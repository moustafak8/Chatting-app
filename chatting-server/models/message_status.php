<?php
include("Model.php");

class message_status extends Model
{
    private ?int $id;
    private int $message_id;
    private int $user_id;
    protected static string $table = "message_status";
    public function __construct(array $data)
    {
        $this->id = $data["id"] ?? null;
        $this->message_id = $data["message_id"];
        $this->user_id = $data["user_id"];
    }
    public function __toString()
    {
        $idStr = $this->id !== null ? $this->id : "null";
        return  $idStr . " | " . $this->message_id . " | " . $this->user_id . " | ";
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "message_id" => $this->message_id,
            "recipient_id" => $this->user_id,
        ];
    }
}
