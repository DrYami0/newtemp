<?php
// Include the controller that fetches all the dashboard data
require_once __DIR__ . '/../../controller/userC-dashboard.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - PerfRan</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>view/FrontOffice/assets/css/style.css">
</head>
<body>
<div class="dashboard-container">

    <!-- Header -->
    <div class="dashboard-header">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($userData['username'], 0, 2)) ?>
            </div>
            <div class="user-details">
                <h1>Bienvenue, <?= htmlspecialchars($userData['username']) ?> !</h1>
                <p>
                    <?php if($userData['firstName'] && $userData['lastName']): ?>
                        <?= htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']) ?> • 
                    <?php endif; ?>
                    <?= htmlspecialchars($userData['email']) ?>
                </p>
            </div>
        </div>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>view/FrontOffice/account-profile.php" class="btn btn-secondary">
                <i class="fas fa-user"></i> Mon Profil
            </a>
            <a href="<?= BASE_URL ?>controller/auth.php?action=logout" class="btn btn-primary">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-gamepad"></i></div>
            <div class="stat-value"><?= $totalGames ?></div>
            <div class="stat-label">Parties Jouées</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-trophy"></i></div>
            <div class="stat-value"><?= $wins ?></div>
            <div class="stat-label">Victoires</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-star"></i></div>
            <div class="stat-value"><?= number_format($totalScore) ?></div>
            <div class="stat-label">Score Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-fire"></i></div>
            <div class="stat-value"><?= $streak ?> 🔥</div>
            <div class="stat-label">Série en Cours</div>
        </div>
    </div>

    <!-- Game Categories -->
    <div class="game-categories">
        <h2><i class="fas fa-th-large"></i> Catégories de Jeux</h2>
        <div class="categories-grid">
            <div class="category-card" onclick="location.href='<?= BASE_URL ?>view/games/game1.php'">
                <div class="category-icon">🎯</div>
                <div class="category-name">Jeu 1 - Dictée</div>
                <div class="category-games"><?= $gamesPlayed1 ?> parties</div>
                <div class="category-games">Score: <?= number_format($totalScore1) ?></div>
            </div>
            <div class="category-card" onclick="location.href='<?= BASE_URL ?>view/games/game2.php'">
                <div class="category-icon">📝</div>
                <div class="category-name">Jeu 2 - Quiz</div>
                <div class="category-games"><?= $gamesPlayed2 ?> parties</div>
                <div class="category-games">Score: <?= number_format($totalScore2) ?></div>
            </div>
            <div class="category-card" onclick="location.href='<?= BASE_URL ?>view/games/game3.php'">
                <div class="category-icon">🧩</div>
                <div class="category-name">Jeu 3 - Textes</div>
                <div class="category-games"><?= $gamesPlayed3 ?> parties</div>
                <div class="category-games">Score: <?= number_format($totalScore3) ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Games -->
    <div class="recent-games">
        <h2><i class="fas fa-history"></i> Parties Récentes</h2>
        <?php if(empty($recentGames)): ?>
            <div class="no-games">
                <i class="fas fa-gamepad"></i>
                <p>Aucune partie jouée pour le moment.</p>
                <p>Commencez à jouer pour voir vos statistiques ici !</p>
            </div>
        <?php else: ?>
            <table class="games-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Adversaire</th>
                        <th>Durée</th>
                        <th>Résultat</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentGames as $game): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($game['timestamp'])) ?></td>
                            <td><?= htmlspecialchars($game['player1id']==$user['uid']?$game['player2_name']:$game['player1_name']) ?></td>
                            <td><?= gmdate("i:s", $game['duration']) ?></td>
                            <td>
                                <span class="badge <?= $game['result']=='Gagné'?'badge-success':'badge-danger' ?>">
                                    <?= $game['result'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>view/games/game-detail.php?gid=<?= $game['gid'] ?>" class="btn btn-secondary btn-small">Détails</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Include other account sections here if needed -->

</div>
</body>
</html>
