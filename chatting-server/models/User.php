<?php
include("Model.php");

class User extends Model {
    private ?int $id;
    private string $username;
    private string $email;
    private string $password;

    protected static string $table = "users";

    public function __construct(array $data){
        $this->id = $data["id"] ?? null;
        $this->username = $data["username"];
        $this->email = $data["email"];
        $this->password = $data["password"];
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getemail(){
        return $this->email;
    }

    public function setemail(string $username){
        $this->username = $username;
    }
    public function __toString(){
        $idStr = $this->id !== null ? $this->id : "null";
        return  $idStr . " | " . $this->username . " | " . $this->email. " | " . $this->password;
    }
    
    public function toArray(){
        return [
            "id" => $this->id,
            "username" => $this->username,
            "email" => $this->email,
            "pass" => $this->password
        ];
    }

}

?>