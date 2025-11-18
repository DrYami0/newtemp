<?php
session_start();
require_once __DIR__ . '/../controller/userC.php';
require_once __DIR__ . '/../require/mailer.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$userRepo = new UserController();

switch ($action) {

    case 'signup':
        $username = trim($_POST['username'] ?? '');
        $nom      = trim($_POST['nom'] ?? '');
        $prenom   = trim($_POST['prenom'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$nom || !$prenom || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password)<6) {
            $_SESSION['signup_error'] = 'Données invalides.';
            header('Location: ' . BASE_URL . 'view/auth/login.php');
            exit;
        }

        if ($userRepo->findByEmail($email)) {
            $_SESSION['signup_error'] = 'Email ou nom d\'utilisateur déjà utilisé.';
            header('Location: ' . BASE_URL . 'view/auth/login.php');
            exit;
        }

        $user = new Utilisateur(null, $username, $nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT));
        $userId = $userRepo->create($user);

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $userRepo->updateToken($userId, $token, $expires);

        $acceptLink = BASE_URL . "controller/admin.php?action=approve&token=$token";
        $rejectLink = BASE_URL . "controller/admin.php?action=reject&token=$token";

        $sujet = "Nouvelle inscription en attente d'approbation";
        $corps = "<p>Nom d'utilisateur: $username</p><p>Email: $email</p>
                  <a href='$acceptLink'>Approuver</a> | <a href='$rejectLink'>Refuser</a>";
        envoyerMailAdmin(ADMIN_EMAIL, $sujet, $corps);

        $_SESSION['message'] = 'Inscription réussie ! En attente de l’approbation.';
        header('Location: ' . BASE_URL . 'view/auth/login.php');
        exit;

    case 'login':
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $userRepo->findByEmail($email);
        if (!$user || !password_verify($password, $user->getPasswordHash()) || $user->getStatus()!=='active') {
            $_SESSION['error'] = 'Email ou mot de passe incorrect / compte non actif.';
            header('Location: ' . BASE_URL . 'view/auth/login.php');
            exit;
        }

        $_SESSION['user'] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'role' => $user->getRole()
        ];

        header('Location: ' . BASE_URL . 'frontoffice/dashboard.php');
        exit;

    case 'logout':
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'view/auth/login.php');
        exit;

    default:
        http_response_code(400);
        echo 'Action invalide.';
        exit;
}
