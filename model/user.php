<?php
class User {
    private $id;
    private $username;
    private $nom;
    private $prenom;
    private $email;
    private $passwordHash;
    private $role;
    private $status;
    private $createdAt;
    private $token;
    private $tokenExpires;

    public function __construct(
        $id = null,
        $username = null,
        $nom = null,
        $prenom = null,
        $email = null,
        $passwordHash = null,
        $role = "user",
        $status = "pending",
        $createdAt = null,
        $token = null,
        $tokenExpires = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->token = $token;
        $this->tokenExpires = $tokenExpires;
    }

    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getPasswordHash() { return $this->passwordHash; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getToken() { return $this->token; }
    public function getTokenExpires() { return $this->tokenExpires; }

    public function setId($id) { $this->id = $id; }
    public function setUsername($username) { $this->username = $username; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setPasswordHash($passwordHash) { $this->passwordHash = $passwordHash; }
    public function setRole($role) { $this->role = $role; }
    public function setStatus($status) { $this->status = $status; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }
    public function setToken($token) { $this->token = $token; }
    public function setTokenExpires($tokenExpires) { $this->tokenExpires = $tokenExpires; }
}
