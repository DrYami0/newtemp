<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/config.php';

class UserRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = obtenirPDO();
    }

    // === Finders ===
    public function findById(int $uid): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE uid = ? LIMIT 1");
        $stmt->execute([$uid]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByUsername(string $username): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUser($row) : null;
    }

    public function findByToken(string $token): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE token = ? AND token_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ? $this->mapRowToUser($row) : null;
    }

    // === Create / Update / Delete ===
    public function create(User $user): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, firstName, lastName, email, phone, password_hash, role, status, creationDate)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $user->getUsername(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
            $user->getPhone(),
            $user->getPasswordHash(),
            $user->getRole(),
            $user->getRole() == 1 ? 'active' : 'pending'
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(User $user): bool {
        $stmt = $this->pdo->prepare("
            UPDATE users SET username=?, firstName=?, lastName=?, email=?, phone=?, 
                password_hash=?, role=?, status=?, token=?, token_expires=?,
                totalScore1=?, totalScore2=?, totalScore3=?, dailyScore1=?, dailyScore2=?, dailyScore3=?,
                streak=?, gamesPlayed1=?, gamesPlayed2=?, gamesPlayed3=?, wins=?, losses=?
            WHERE uid=?
        ");
        return $stmt->execute([
            $user->getUsername(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
            $user->getPhone(),
            $user->getPasswordHash(),
            $user->getRole(),
            $user->getRole() == 1 ? 'active' : $user->getStatus(),
            $user->getToken(),
            $user->getTokenExpires(),
            $user->getTotalScore1(),
            $user->getTotalScore2(),
            $user->getTotalScore3(),
            $user->getDailyScore1(),
            $user->getDailyScore2(),
            $user->getDailyScore3(),
            $user->getStreak(),
            $user->getGamesPlayed1(),
            $user->getGamesPlayed2(),
            $user->getGamesPlayed3(),
            $user->getWins(),
            $user->getLosses(),
            $user->getUid()
        ]);
    }

    public function delete(int $uid): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE uid = ?");
        return $stmt->execute([$uid]);
    }

    // === Lists & Counts ===
    public function listAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY creationDate DESC");
        return array_map([$this, 'mapRowToUser'], $stmt->fetchAll());
    }

    public function listPending(): array {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE status='pending' ORDER BY creationDate DESC");
        return array_map([$this, 'mapRowToUser'], $stmt->fetchAll());
    }

    public function countAll(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function countPending(): int {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();
    }

    public function getTopPlayers(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE role = 0
            ORDER BY (totalScore1 + totalScore2 + totalScore3) DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return array_map([$this, 'mapRowToUser'], $stmt->fetchAll());
    }

    // === Helper: maps DB row to User object ===
    private function mapRowToUser(array $row): User {
        $user = new User(
            $row['uid'] ?? null,
            $row['username'] ?? null,
            $row['firstName'] ?? null,
            $row['lastName'] ?? null,
            $row['email'] ?? null,
            $row['password_hash'] ?? null,
            $row['phone'] ?? null,
            $row['role'] ?? 0,
            $row['totalScore1'] ?? 0,
            $row['totalScore2'] ?? 0,
            $row['totalScore3'] ?? 0,
            $row['dailyScore1'] ?? 0,
            $row['dailyScore2'] ?? 0,
            $row['dailyScore3'] ?? 0,
            $row['streak'] ?? 0,
            $row['gamesPlayed1'] ?? 0,
            $row['gamesPlayed2'] ?? 0,
            $row['gamesPlayed3'] ?? 0,
            $row['wins'] ?? 0,
            $row['losses'] ?? 0,
            $row['creationDate'] ?? null
        );

        if (isset($row['status'])) $user->setStatus($row['status']);
        if (isset($row['token'])) $user->setToken($row['token']);
        if (isset($row['token_expires'])) $user->setTokenExpires($row['token_expires']);

        return $user;
    }
}
