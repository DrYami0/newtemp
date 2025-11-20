<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../model/mailer.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* ----------------------------- SIGN UP ----------------------------------- */
    case 'signup':

        $username  = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName  = trim($_POST['lastName'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $password  = $_POST['password'] ?? '';

        // Basic validation
        if (!$username || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            $_SESSION['signup_error'] = 'Données invalides.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        if ($phone && (!ctype_digit($phone) || strlen($phone) < 8 || strlen($phone) > 15)) {
            $_SESSION['signup_error'] = 'Numéro de téléphone invalide.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        // Save as pending JSON
        $pendingDir = __DIR__ . '/pending/';
        if (!is_dir($pendingDir)) mkdir($pendingDir, 0777, true);

        $userData = [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 0,
            'accepted' => null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents($pendingDir . $username . '.json', json_encode($userData, JSON_PRETTY_PRINT));

        $_SESSION['uid'] = $username;
        $_SESSION['signup_success'] = true; // Show green message on login page

        // Send email to admin
        $adminEmail = 'louayfkiri06@gmail.com';
        $approveLink = BASE_URL . "controller/validateUser.php?action=approve&user=$username";
        $refuseLink  = BASE_URL . "controller/validateUser.php?action=refuse&user=$username";

        $subject = "Nouvel utilisateur à valider";
        $body = "
        <p>Bonjour Admin,</p>
        <p>Un nouvel utilisateur vient de s'inscrire :</p>
        <ul>
            <li>Nom d'utilisateur: $username</li>
            <li>Email: $email</li>
        </ul>
        <p>Veuillez approuver ou refuser :</p>
        <p>
            <a href='$approveLink' style='padding:10px 20px;background-color:#27ae60;color:#fff;text-decoration:none;border-radius:5px;'>Approuver</a>
            &nbsp;&nbsp;
            <a href='$refuseLink' style='padding:10px 20px;background-color:#e74c3c;color:#fff;text-decoration:none;border-radius:5px;'>Refuser</a>
        </p>
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";

        mail($adminEmail, $subject, $body, $headers);

        header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
        exit;


    /* ----------------------------- LOGIN ----------------------------------- */
    case 'login':

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $_SESSION['error'] = 'Email et mot de passe requis.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        // Folders to search
        $pendingDir  = __DIR__ . '/pending/';
        $approvedDir = __DIR__ . '/approved/';
        $refusedDir  = __DIR__ . '/refused/';
        
        $userFound = null;
        $userStatus = null;

        foreach ([
            'approved' => $approvedDir,
            'pending' => $pendingDir,
            'refused' => $refusedDir
        ] as $status => $folder) {
            if (!is_dir($folder)) continue;
            foreach (glob($folder . "*.json") as $file) {
                $data = json_decode(file_get_contents($file), true);
                if (isset($data['email']) && $data['email'] === $email) {
                    $userFound = $data;
                    $userStatus = $status;
                    break 2;
                }
            }
        }

        if (!$userFound || !password_verify($password, $userFound['passwordHash'])) {
            $_SESSION['error'] = 'Email ou mot de passe incorrect.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        // Handle refused users
        if ($userStatus === 'refused' || (isset($userFound['accepted']) && $userFound['accepted'] === false)) {
            $_SESSION['error'] = 'Votre inscription a été refusée par l\'administrateur.';
            header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
            exit;
        }

        // Handle pending users
        if ($userStatus === 'pending' || (isset($userFound['accepted']) && $userFound['accepted'] === null)) {
            $_SESSION['uid'] = $userFound['username'];
            $_SESSION['user'] = $userFound;
            header('Location: ' . BASE_URL . 'view/FrontOffice/user-status.php');
            exit;
        }

        // Approved users
        $_SESSION['user'] = $userFound;
        $_SESSION['uid'] = $userFound['username'];

        if (isset($userFound['role']) && $userFound['role'] == 1) {
            header('Location: ' . BASE_URL . 'view/BackOffice/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'view/FrontOffice/user-dashboard.php');
        }
        exit;


    /* ----------------------------- LOGOUT ----------------------------------- */
    case 'logout':
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'view/FrontOffice/login.php');
        exit;


    /* ----------------------------- INVALID ACTION ----------------------------------- */
    default:
        http_response_code(400);
        echo 'Action invalide.';
        exit;
}
        