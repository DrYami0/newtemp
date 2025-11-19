<?php
session_start();
require_once __DIR__ . '/../../controller/userC.php';
require_once __DIR__ . '/../../controller/mailer.php';

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['uid'];
$userRepo = new UserRepository();
$user = $userRepo->findById($uid);

if (!$user) {
    die("Utilisateur introuvable.");
}

// Pending JSON folder
$pendingDir = __DIR__ . '/../../controller/pending/';
if (!is_dir($pendingDir)) {
    mkdir($pendingDir, 0775, true);
}
$jsonFile = $pendingDir . $uid . '.json';

// Create JSON if it doesn't exist
if (!file_exists($jsonFile)) {
    $data = [
        'uid' => $uid,
        'username' => $user->getUsername(),
        'firstName' => $user->getFirstName(),
        'lastName' => $user->getLastName(),
        'email' => $user->getEmail(),
        'role' => $user->getRole(),
        'status' => 'en_attente', // pending by default
        'createdAt' => time(),
        'expiresAt' => time() + 48 * 3600 // 48 hours countdown
    ];
    file_put_contents($jsonFile, json_encode($data));
} else {
    $data = json_decode(file_get_contents($jsonFile), true);
}

// Update status if countdown expired
$remaining = $data['expiresAt'] - time();
if ($remaining <= 0 && $data['status'] === 'en_attente') {
    $data['status'] = 'refuse';
    file_put_contents($jsonFile, json_encode($data));
}

// Redirect if accepted
if ($data['status'] === 'accepte') {
    header('Location: user-dashboard.php');
    exit;
}

// Format countdown timer
$hours = floor($remaining / 3600);
$minutes = floor(($remaining % 3600) / 60);
$seconds = $remaining % 60;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statut du compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
        }
        .container {
            background:#fff;
            padding:30px;
            border-radius:8px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
            text-align:center;
        }
        h1 { color:#333; }
        .status { font-size:1.2em; margin:20px 0; }
        .timer { font-size:1.5em; color:#555; }
        .refuse { color:red; font-weight:bold; }
        .attente { color:orange; font-weight:bold; }
        .accepte { color:green; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>Bonjour <?= htmlspecialchars($user->getFullName()) ?> !</h1>
    <div class="status">
        Statut du compte :
        <?php
        switch($data['status']) {
            case 'en_attente': echo '<span class="attente">En attente de validation</span>'; break;
            case 'accepte': echo '<span class="accepte">Accepté</span>'; break;
            case 'refuse': echo '<span class="refuse">Refusé</span>'; break;
        }
        ?>
    </div>
    <?php if ($data['status'] === 'en_attente'): ?>
    <div class="timer" id="timer">
        <?= sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds) ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($data['status'] === 'en_attente'): ?>
<script>
    let remaining = <?= $remaining ?>;
    const timerEl = document.getElementById('timer');
    const interval = setInterval(() => {
        remaining--;
        if (remaining <= 0) {
            clearInterval(interval);
            timerEl.textContent = "00:00:00 (Compte refusé)";
            location.reload(); // update status
            return;
        }
        let h = Math.floor(remaining / 3600);
        let m = Math.floor((remaining % 3600)/60);
        let s = remaining % 60;
        timerEl.textContent = `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
    }, 1000);
</script>
<?php endif; ?>
</body>
</html>
