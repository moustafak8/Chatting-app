<?php
include("Model.php");

class User extends Model {
    private string $username;
    private string $email;
    private string $password;

    protected static string $table = "users";

    public function __construct(array $data){
        $this->username = $data["username"];
        $this->email = $data["email"];
        $this->password = $data["password"];
    }

    public function getemail(){
        return $this->email;
    }

    public function setemail(string $username){
        $this->username = $username;
    }
    public function __toString(){
        return  $this->username . " | " . $this->email. " | " . $this->password;
    }
    
    public function toArray(){
        return ["username" => $this->username, "email" => $this->email,"pass" => $this->password];
    }

}

?>