<?php
include("Model.php");

class participants extends Model
{
    private ?int $id;
    private string $conversation_id;
    private string $user_id;


    protected static string $table = "conversation_participants";

    public function __construct(array $data)
    {
        $this->conversation_id= $data["conversation_id"];
        $this->user_id= $data["user_id"];
    }


    public function __toString()
    {
        $idStr = $this->id !== null ? $this->id : "null";
        return  $idStr . " | " . $this->conversation_id . " | ". $this->user_id;
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "conversation_id" => $this->conversation_id,
            "user_id" => $this->user_id,
        ];
    }
}
