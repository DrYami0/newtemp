<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}

$user = $_SESSION['user'];

// --- FETCH USER DATA ---
$pdo = obtenirPDO();
$stmt = $pdo->prepare("SELECT * FROM users WHERE uid = ?");
$stmt->execute([$user['uid']]);
$userData = $stmt->fetch();

if (!$userData) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
    exit;
}

// --- CALCULATE STATS ---
$totalGames = $userData['gamesPlayed1'] + $userData['gamesPlayed2'] + $userData['gamesPlayed3'];
$totalScore = $userData['totalScore1'] + $userData['totalScore2'] + $userData['totalScore3'];
$wins = $userData['wins'];
$streak = $userData['streak'];
$gamesPlayed1 = $userData['gamesPlayed1'];
$gamesPlayed2 = $userData['gamesPlayed2'];
$gamesPlayed3 = $userData['gamesPlayed3'];
$totalScore1 = $userData['totalScore1'];
$totalScore2 = $userData['totalScore2'];
$totalScore3 = $userData['totalScore3'];

// --- FETCH RECENT GAMES ---
$stmt = $pdo->prepare("
    SELECT g.*, 
           u1.username as player1_name, 
           u2.username as player2_name,
           CASE 
               WHEN g.winner = ? THEN 'Gagné'
               ELSE 'Perdu'
           END as result
    FROM gamelogs g
    LEFT JOIN users u1 ON g.player1id = u1.uid
    LEFT JOIN users u2 ON g.player2id = u2.uid
    WHERE g.player1id = ? OR g.player2id = ?
    ORDER BY g.timestamp DESC
    LIMIT 10
");
$stmt->execute([$user['uid'], $user['uid'], $user['uid']]);
$recentGames = $stmt->fetchAll();

// --- OPTIONAL: Prepare data for front-end display ---
$userStats = [
    'totalGames' => $totalGames,
    'totalScore' => $totalScore,
    'wins' => $wins,
    'streak' => $streak,
    'gamesPlayed1' => $gamesPlayed1,
    'gamesPlayed2' => $gamesPlayed2,
    'gamesPlayed3' => $gamesPlayed3,
    'totalScore1' => $totalScore1,
    'totalScore2' => $totalScore2,
    'totalScore3' => $totalScore3,
];


require __DIR__ . '/view/FrontOffice/user-dashboard.php';
