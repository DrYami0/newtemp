<?php
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../require/config.php';

class UserRepository {

    private $pdo;

    public function __construct() {
        $this->pdo = obtenirPDO();
    }

    public function findById(int $id): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByToken(string $token): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE token = ? AND token_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function create(User $user): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, nom, prenom, email, password_hash, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user->getUsername(),
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $user->getPasswordHash(),
            $user->getRole(),
            $user->getStatus()
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(User $user): bool {
        $stmt = $this->pdo->prepare("
            UPDATE users SET username=?, nom=?, prenom=?, email=?, password_hash=?, role=?, status=?, token=?, token_expires=?
            WHERE id=?
        ");
        return $stmt->execute([
            $user->getUsername(),
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $user->getPasswordHash(),
            $user->getRole(),
            $user->getStatus(),
            $user->getToken(),
            $user->getTokenExpires(),
            $user->getId()
        ]);
    }

    public function updateStatus(User $user, string $status): bool {
        $user->setStatus($status);
        $user->setToken(null);
        $user->setTokenExpires(null);
        return $this->update($user);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function listPending(): array {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE status='pending' ORDER BY created_at DESC");
        return array_map([$this, 'mapRowToUser'], $stmt->fetchAll());
    }

    public function listAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return array_map([$this, 'mapRowToUser'], $stmt->fetchAll());
    }

    public function countAll(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function countPending(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
    }

    private function mapRowToUser(array $row): User {
        return new User(
            $row['id'] ?? null,
            $row['username'] ?? null,
            $row['nom'] ?? null,
            $row['prenom'] ?? null,
            $row['email'] ?? null,
            $row['password_hash'] ?? null,
            $row['role'] ?? 'user',
            $row['status'] ?? 'pending',
            $row['created_at'] ?? null,
            $row['token'] ?? null,
            $row['token_expires'] ?? null
        );
    }
}
