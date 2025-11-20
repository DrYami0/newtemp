<?php
session_start();
require_once __DIR__ . '/../../controller/userC.php';
require_once __DIR__ . '/../../model/mailer.php';


// ------------------------------
//  CHECK LOGIN
// ------------------------------
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['user']['username'] ?? null;
if (!$username) {
    die("Utilisateur introuvable.");
}


// ------------------------------
//  LOAD USER FROM REPOSITORY
// ------------------------------
$userRepo = new UserC();
$user = $userRepo->findByUsername($username);

if (!$user) {
    die("Utilisateur introuvable.");
}


// ------------------------------
//  PENDING JSON SYSTEM
// ------------------------------
$pendingDir = __DIR__ . '/../../controller/pending/';
if (!is_dir($pendingDir)) {
    mkdir($pendingDir, 0775, true);
}

$jsonFile = $pendingDir . $username . '.json';

if (!file_exists($jsonFile)) {

    // FIRST TIME — CREATE JSON
    $data = [
        'username'  => $user['username'] ?? '',
        'firstName' => $user['firstName'] ?? '',
        'lastName'  => $user['lastName'] ?? '',
        'email'     => $user['email'] ?? '',
        'role'      => $user['role'] ?? 0,
        'status'    => 'en_attente',
        'createdAt' => time(),
        'expiresAt' => time() + 48 * 3600 // 48 hours
    ];

    file_put_contents($jsonFile, json_encode($data));

} else {
    // ALREADY EXISTS — LOAD JSON
    $data = json_decode(file_get_contents($jsonFile), true);
}


// ------------------------------
//  CHECK STATUS / EXPIRATION
// ------------------------------
$expiresAt = $data['expiresAt'] ?? (time() + 48*3600);
$status    = $data['status']    ?? 'en_attente';
$remaining = $expiresAt - time();

// Auto-refuse if expired
if ($remaining <= 0 && $status === 'en_attente') {
    $status = 'refuse';
    $data['status'] = $status;
    file_put_contents($jsonFile, json_encode($data));
}

// Redirect if accepted
if ($status === 'accepte') {
    header('Location: user-dashboard.php');
    exit;
}


// ------------------------------
//  TIMER CALCULATION
// ------------------------------
$hours   = floor($remaining / 3600);
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
    background:#f2f2f2;
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
    <h1>
        Bonjour
        <?= htmlspecialchars(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?>
        !
    </h1>

    <div class="status">
        Statut du compte :
        <?php
        switch($status) {
            case 'en_attente':
                echo '<span class="attente">En attente de validation</span>';
                break;
            case 'accepte':
                echo '<span class="accepte">Accepté</span>';
                break;
            case 'refuse':
                echo '<span class="refuse">Refusé</span>';
                break;
        }
        ?>
    </div>

    <?php if ($status === 'en_attente'): ?>
    <div class="timer" id="timer">
        <?= sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds) ?>
    </div>
    <?php endif; ?>
</div>


<?php if ($status === 'en_attente'): ?>
<script>
let remaining = <?= $remaining ?>;
const timerEl = document.getElementById('timer');

const interval = setInterval(() => {
    remaining--;

    if (remaining <= 0) {
        clearInterval(interval);
        timerEl.textContent = "00:00:00 (Compte refusé)";
        location.reload();
        return;
    }

    const h = Math.floor(remaining / 3600);
    const m = Math.floor((remaining % 3600) / 60);
    const s = remaining % 60;

    timerEl.textContent =
        h.toString().padStart(2,'0') + ':' +
        m.toString().padStart(2,'0') + ':' +
        s.toString().padStart(2,'0');

}, 1000);
</script>
<?php endif; ?>

</body>
</html>
