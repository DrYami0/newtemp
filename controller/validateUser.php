<?php
session_start();


if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die("Accès refusé : vous devez être admin.");
}

$uid = $_GET['user'] ?? null;  
$action = $_GET['action'] ?? null;

if (!$uid || !$action) {
    die("Paramètres manquants.");
}

$pendingDir  = __DIR__ . '/pending/';
$approvedDir = __DIR__ . '/approved/';

$pendingFile = $pendingDir . $uid . '.json';

if (!file_exists($pendingFile)) {
    die("Aucune demande de validation trouvée pour cet utilisateur.");
}


$userData = json_decode(file_get_contents($pendingFile), true);

if ($action === 'approve') {
    $userData['accepted'] = true;

   
    if (!is_dir($approvedDir)) mkdir($approvedDir, 0777, true);
    file_put_contents($approvedDir . $uid . '.json', json_encode($userData, JSON_PRETTY_PRINT));

   
    unlink($pendingFile);

   
    $subject = "Votre compte a été accepté";
    $body = "<p>Bonjour {$userData['firstName']},</p>
             <p>Votre compte a été validé par l'administrateur. Vous pouvez désormais accéder à votre tableau de bord.</p>
             <p><a href='http://tonsite.com/view/FrontOffice/user-dashboard.php'>Accéder au tableau de bord</a></p>";
    require_once __DIR__ . '/../model/mailer.php';
    queueEmail($userData['email'], $subject, $body, ["Content-type: text/html; charset=UTF-8"]);

    header("Location: ../view/BackOffice/pending.php?message=Utilisateur approuvé");
    exit;

} elseif ($action === 'refuse') {
    $userData['accepted'] = false;
    file_put_contents($pendingFile, json_encode($userData, JSON_PRETTY_PRINT));

   
    $subject = "Votre compte a été refusé";
    $body = "<p>Bonjour {$userData['firstName']},</p>
             <p>Votre demande de création de compte a été refusée. Vous pouvez contacter l'administration pour plus d'informations.</p>";
    require_once __DIR__ . '/../model/mailer.php';
    queueEmail($userData['email'], $subject, $body, ["Content-type: text/html; charset=UTF-8"]);

    header("Location: ../view/BackOffice/pending.php?message=Utilisateur refusé");
    exit;

} else {
    die("Action invalide.");
}
