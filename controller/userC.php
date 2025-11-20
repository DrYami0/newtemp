<?php
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/config.php';

class UserC {

    private string $pendingDir;
    private string $approvedDir;

    public function __construct() {
        $this->pendingDir  = __DIR__ . '/pending/';
        $this->approvedDir = __DIR__ . '/approved/';

        if (!is_dir($this->pendingDir))  mkdir($this->pendingDir, 0777, true);
        if (!is_dir($this->approvedDir)) mkdir($this->approvedDir, 0777, true);
    }

    /* ============================================================
       =============== FINDERS / SELECT ============================
       ============================================================ */

    // Find user by username (look in approved first)
    public function findByUsername(string $username): ?array {
        $pendingFile  = $this->pendingDir  . $username . '.json';
        $approvedFile = $this->approvedDir . $username . '.json';

        if (file_exists($approvedFile)) {
            return json_decode(file_get_contents($approvedFile), true);
        }

        if (file_exists($pendingFile)) {
            return json_decode(file_get_contents($pendingFile), true);
        }

        return null;
    }

    // Find user by email (search both folders)
    public function findByEmail(string $email): ?array {
        foreach ([$this->approvedDir, $this->pendingDir] as $folder) {
            foreach (glob($folder . '*.json') as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['email']) && $data['email'] === $email) {
                    return $data;
                }
            }
        }
        return null;
    }

    // Login: returns user array if password OK, otherwise null
    public function login(string $email, string $password): ?array {
        $user = $this->findByEmail($email);

        if (!$user || !isset($user['password'])) {
            return null;
        }

        if (password_verify($password, $user['password'])) {
            return $user; // Login successful
        }

        return null; // Wrong password
    }

    /* ============================================================
       ================== CREATE / UPDATE / DELETE ================
       ============================================================ */

    // Create pending user (Signup)
    public function create(array $userData): bool {

        // Hash password before saving
        if (isset($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
        }

        $file = $this->pendingDir . $userData['username'] . '.json';

        return file_put_contents($file, json_encode($userData, JSON_PRETTY_PRINT)) !== false;
    }

    // Update user (can be in pending or approved)
    public function update(string $username, array $userData, bool $approved = false): bool {

        // If updating password, re-hash it
        if (isset($userData['password']) && substr($userData['password'], 0, 4) !== '$2y$') {
            $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
        }

        $dir = $approved ? $this->approvedDir : $this->pendingDir;
        $file = $dir . $username . '.json';

        return file_put_contents($file, json_encode($userData, JSON_PRETTY_PRINT)) !== false;
    }

    // Delete user
    public function delete(string $username, bool $approved = false): bool {
        $dir = $approved ? $this->approvedDir : $this->pendingDir;
        $file = $dir . $username . '.json';

        return file_exists($file) ? unlink($file) : false;
    }

    /* ============================================================
       ====================== APPROVAL =============================
       ============================================================ */

    // Approve user → move from pending to approved
    public function approveUser(string $username): bool {
        $pendingFile  = $this->pendingDir  . $username . '.json';
        $approvedFile = $this->approvedDir . $username . '.json';

        if (!file_exists($pendingFile)) {
            return false;
        }

        return rename($pendingFile, $approvedFile);
    }

    /* ============================================================
       =========================== LISTS ===========================
       ============================================================ */

    public function listPending(): array {
        return $this->listFromDir($this->pendingDir);
    }

    public function listApproved(): array {
        return $this->listFromDir($this->approvedDir);
    }

    private function listFromDir(string $dir): array {
        $users = [];

        foreach (glob($dir . '*.json') as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $users[] = $data;
            }
        }

        return $users;
    }
}
