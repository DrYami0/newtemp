<?php
session_start();
require_once __DIR__ . '/../require/config.php';
require_once __DIR__ . '/../require/mailer.php';
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../controller/userC.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/template-login/');
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'dashboard';
$repo = new UserRepository(obtenirPDO());

if ($action === 'approve' || $action === 'reject') {
    $token = $_GET['token'] ?? '';
    if (!$token) die("Token manquant.");

    $user = $repo->findByToken($token);
    if (!$user) die("Lien invalide ou expiré.");

    if ($action === 'approve') {
        $user->setStatus('active');
        $user->setToken(null);
        $user->setTokenExpires(null);
        $repo->update($user);

        $sujet = "Votre compte a été approuvé !";
        $corps = "Félicitations ! Votre compte est maintenant actif. Vous pouvez vous connecter.";
    } else {
        $repo->delete($user->getId());
        $sujet = "Votre inscription a été refusée";
        $corps = "Malheureusement, votre demande d'inscription a été refusée par l'administrateur.";
    }

    envoyerMailAdmin($user->getEmail(), $sujet, $corps);

    echo "<p>Action effectuée avec succès.</p>";
    echo "<p><a href='" . BASE_URL . "view/auth/login.php'>Retour à la page de connexion</a></p>";
    exit;
}

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error'] = "Accès refusé. Administrateur requis.";
    header('Location: ' . BASE_URL . 'view/auth/login.php');
    exit;
}

switch ($action) {

    case 'list_pending':
        $pendings = $repo->listPending();
        $pageTitle = "Utilisateurs en attente d'approbation";
        require __DIR__ . '/../view/BackOffice/pending.php';
        break;

    case 'list_all':
        $users = $repo->listAll();
        $pageTitle = "Gestion des utilisateurs";
        require __DIR__ . '/../view/BackOffice/users_list.php';
        break;

    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        $user = $repo->findById($id);
        if (!$user) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            header('Location: ' . BASE_URL . 'controller/admin.php?action=list_all');
            exit;
        }
        $pageTitle = "Modifier l'utilisateur";
        require __DIR__ . '/../view/BackOffice/user_edit.php';
        break;

    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';

        if ($id <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Données invalides.";
            header('Location: ' . BASE_URL . 'controller/admin.php?action=edit&id=' . $id);
            exit;
        }

        $user = $repo->findById($id);
        if ($user) {
            $user->setEmail($email);
            $user->setRole($role);
            $user->setStatus($status);
            $repo->update($user);
            $_SESSION['message'] = "Utilisateur mis à jour avec succès.";
        }

        header('Location: ' . BASE_URL . 'controller/admin.php?action=list_all');
        exit;
        break;

    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0 && $id !== $_SESSION['user']['id']) {
            $repo->delete($id);
            $_SESSION['message'] = "Utilisateur supprimé.";
        } else {
            $_SESSION['error'] = "Impossible de supprimer cet utilisateur.";
        }
        header('Location: ' . BASE_URL . 'controller/admin.php?action=list_all');
        exit;
        break;

    default:
        $totalUsers = $repo->countAll();
        $pendingCount = $repo->countPending();
        $pageTitle = "Tableau de bord administrateur";
        require __DIR__ . '/../view/BackOffice/dashboard.php';
        break;
}
