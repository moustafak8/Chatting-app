<?php
include("Model.php");

class Conversation extends Model
{
    private ?int $id;
    private string $title;

    protected static string $table = "conversations";

    public function __construct(array $data)
    {
        $this->title = $data["title"];
    }


    public function __toString()
    {
        $idStr = $this->id !== null ? $this->id : "null";
        return  $idStr . " | " . $this->title . " | ";
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
        ];
    }
}
