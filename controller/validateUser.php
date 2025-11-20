<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../model/mailer.php';

$action = $_GET['action'] ?? '';
$username = $_GET['user'] ?? '';

if (!$username || !in_array($action, ['approve', 'refuse'])) {
    die("Paramètres invalides.");
}

$pendingDir = __DIR__ . '/pending/';
$approvedDir = __DIR__ . '/approved/';
$refusedDir = __DIR__ . '/refused/';
$userFile = $pendingDir . $username . '.json';

if (!file_exists($userFile)) {
    die("Utilisateur introuvable dans le dossier pending.");
}

$userData = json_decode(file_get_contents($userFile), true);

if ($action === 'approve') {
    // Create approved directory if it doesn't exist
    if (!is_dir($approvedDir)) mkdir($approvedDir, 0777, true);
    
    // ✅ FIX: Update accepted status to TRUE
    $userData['accepted'] = true;
    $userData['approved_at'] = date('Y-m-d H:i:s');
    
    // Move to approved folder with updated data
    $newFile = $approvedDir . $username . '.json';
    file_put_contents($newFile, json_encode($userData, JSON_PRETTY_PRINT));
    unlink($userFile);
    
    // Send approval email to user
    $subject = "Votre compte a été approuvé !";
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #27ae60;'>✓ Compte approuvé</h2>
        <p>Bonjour <strong>" . htmlspecialchars($userData['username']) . "</strong>,</p>
        <p>Bonne nouvelle ! Votre compte a été approuvé par l'administrateur.</p>
        <p>Vous pouvez maintenant vous connecter à la plateforme PerfRan et commencer à jouer.</p>
        <div style='text-align: center; margin: 30px 0;'>
            <a href='" . BASE_URL . "view/FrontOffice/login.php' style='display: inline-block; padding: 12px 30px; background-color: #667eea; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;'>Se connecter maintenant</a>
        </div>
        <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
        <p style='color: #999; font-size: 12px;'>Cordialement,<br>L'équipe PerfRan</p>
    </div>
    ";
    
    envoyerMailUtilisateur($userData['email'], $subject, $body);
    
    $message = "✅ Utilisateur approuvé";
    $details = "L'utilisateur <strong>" . htmlspecialchars($username) . "</strong> a été approuvé avec succès.";
    $color = "#27ae60";
    
} elseif ($action === 'refuse') {
    // Create refused directory if it doesn't exist
    if (!is_dir($refusedDir)) mkdir($refusedDir, 0777, true);
    
    // ✅ FIX: Update accepted status to FALSE
    $userData['accepted'] = false;
    $userData['refused_at'] = date('Y-m-d H:i:s');
    
    // Move to refused folder with updated data
    $newFile = $refusedDir . $username . '.json';
    file_put_contents($newFile, json_encode($userData, JSON_PRETTY_PRINT));
    unlink($userFile);
    
    // Send refusal email to user
    $subject = "Votre inscription a été refusée";
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <h2 style='color: #e74c3c;'>✗ Inscription refusée</h2>
        <p>Bonjour <strong>" . htmlspecialchars($userData['username']) . "</strong>,</p>
        <p>Malheureusement, votre demande d'inscription sur la plateforme PerfRan a été refusée par l'administrateur.</p>
        <p>Si vous pensez qu'il s'agit d'une erreur, veuillez contacter l'administrateur à l'adresse : <a href='mailto:" . ADMIN_EMAIL . "'>" . ADMIN_EMAIL . "</a></p>
        <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
        <p style='color: #999; font-size: 12px;'>Cordialement,<br>L'équipe PerfRan</p>
    </div>
    ";
    
    envoyerMailUtilisateur($userData['email'], $subject, $body);
    
    $message = "❌ Utilisateur refusé";
    $details = "L'utilisateur <strong>" . htmlspecialchars($username) . "</strong> a été refusé.";
    $color = "#e74c3c";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation Utilisateur - PerfRan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            text-align: center;
        }
        h2 { 
            color: <?= $color ?>; 
            margin-bottom: 20px;
            font-size: 28px;
        }
        p { 
            color: #666; 
            line-height: 1.6;
            margin: 15px 0;
        }
        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        a:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= $message ?></h2>
        <div class="details">
            <p><?= $details ?></p>
            <p>Un email de notification a été envoyé à l'utilisateur.</p>
        </div>
        <a href="<?= BASE_URL ?>view/BackOffice/pending.php">← Retour à la liste</a>
        <br>
        <a href="<?= BASE_URL ?>view/FrontOffice/login.php" style="margin-top: 10px; background: #6c757d;">Aller à la connexion</a>
    </div>
</body>
</html>