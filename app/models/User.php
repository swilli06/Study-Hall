<?php
class User {
    private $db;
    public ?int $lastInsertId = null;
    public function __construct($db) {
        $this->db = $db;
    }
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM user_account WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findByUsername($user) {
        $stmt = $this->db->prepare("
            SELECT ua.*, up.*
            FROM user_account ua
            JOIN user_profile up ON ua.id = up.user_id
            WHERE LOWER(up.username) = LOWER(:username)
        ");
        $stmt->execute(['username' => $user]);
        return $stmt->fetch();
    }

    public function create(string $email, string $password): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO user_account (email, password_hash) VALUES (:email, :hash)");
           $success = $stmt->execute(['email' => $email, 'hash' => $hash]);
        if ($success) {
            $this->lastInsertId = (int) $this->db->lastInsertId();
        }
        return $success;
    }

}
?>
