<?php
session_start();
require_once __DIR__ . '/../../require/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css" />
<title>Connexion - Votre Application</title>
<style>

.sign-up h1, .sign-up span, .sign-up input, .sign-up .error-message, .sign-up button {
    margin: 0;
    padding: 0;
}
.sign-up h1 {
    margin-bottom: 8px;
    font-size: 22px;
}
.sign-up span {
    display: block;
    font-size: 13px;
    margin-bottom: 8px;
}
.sign-up input {
    display: block;
    width: 100%;
    padding: 8px 10px;
    margin-bottom: 2px;
    box-sizing: border-box;
}
.sign-up .error-message {
    color: #e74c3c;
    font-size: 12px;
    margin: 2px 0 6px 0;
    line-height: 1.1;
}
.sign-up button {
    margin-top: 10px;
    padding: 10px;
}
</style>
</head>
<body>
<div class="container" id="container">
<div class="form-container sign-up">
<form id="signupForm" action="<?= BASE_URL ?>controller/auth.php" method="POST" novalidate>
<input type="hidden" name="action" value="signup">
<h1>Créer un Compte</h1>
<div class="social-icons">
<a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
<a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
<a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
<a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
</div>
<span>ou utilisez vos informations pour l'inscription</span>
<input type="text" name="username" placeholder="Nom d'utilisateur" maxlength="50" />
<p class="error-message"></p>
<input type="text" name="nom" placeholder="Nom" />
<p class="error-message"></p>
<input type="text" name="prenom" placeholder="Prénom" />
<p class="error-message"></p>
<input type="email" name="email" placeholder="Email" />
<p class="error-message"></p>
<input type="password" name="password" placeholder="Mot de passe (min 6 caractères)" minlength="6" />
<p class="error-message"></p>
<?php if(isset($_SESSION['signup_error'])): ?>
<p class="error-message"><?= htmlspecialchars($_SESSION['signup_error']) ?></p>
<?php unset($_SESSION['signup_error']); endif; ?>
<button type="submit">S'inscrire</button>
</form>
</div>

<div class="form-container sign-in">
<form action="<?= BASE_URL ?>controller/auth.php" method="POST">
<input type="hidden" name="action" value="login">
<h1>Se Connecter</h1>
<div class="social-icons">
<a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
<a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
<a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
<a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
</div>
<span>ou utilisez votre email et mot de passe</span>
<input type="email" name="email" placeholder="Email" required />
<input type="password" name="password" placeholder="Mot de passe" required />
<?php if(isset($_SESSION['error'])): ?>
<p style="color:#e74c3c;font-size:13px;margin:5px 0;text-align:center;font-weight:500;"><?= htmlspecialchars($_SESSION['error']) ?></p>
<?php unset($_SESSION['error']); endif; ?>
<?php if(isset($_SESSION['message'])): ?>
<p style="color:#27ae60;font-size:13px;margin:5px 0;text-align:center;font-weight:500;"><?= htmlspecialchars($_SESSION['message']) ?></p>
<?php unset($_SESSION['message']); endif; ?>
<a href="#">Mot de passe oublié ?</a>
<button type="submit">Se Connecter</button>
</form>
</div>

<div class="toggle-container">
<div class="toggle">
<div class="toggle-panel toggle-left">
<h1>Bon retour !</h1>
<p>Entrez vos informations pour accéder à toutes les fonctionnalités</p>
<button class="hidden" id="login">Se Connecter</button>
</div>
<div class="toggle-panel toggle-right">
<h1>Salut, ami !</h1>
<p>Inscrivez-vous pour découvrir toutes les fonctionnalités du site</p>
<button class="hidden" id="register">S'inscrire</button>
</div>
</div>
</div>
</div>

<script src="<?= BASE_URL ?>assets/js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded',()=>{
const form=document.querySelector('#signupForm');if(!form)return;
form.addEventListener('submit',(e)=>{
e.preventDefault();
document.querySelectorAll('#signupForm .error-message').forEach(p=>p.textContent='');
let valid=true;
const username=form.username.value.trim();
const nom=form.nom.value.trim();
const prenom=form.prenom.value.trim();
const email=form.email.value.trim();
const password=form.password.value;
if(!username){form.username.nextElementSibling.textContent="Le nom d'utilisateur est requis.";valid=false;}
else if(username.length>50){form.username.nextElementSibling.textContent="Le nom d'utilisateur doit contenir maximum 50 caractères.";valid=false;}
if(!nom){form.nom.nextElementSibling.textContent="Le nom est requis.";valid=false;}
if(!prenom){form.prenom.nextElementSibling.textContent="Le prénom est requis.";valid=false;}
const emailPattern=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
if(!email){form.email.nextElementSibling.textContent="L'email est requis.";valid=false;}
else if(!emailPattern.test(email)){form.email.nextElementSibling.textContent="Email invalide.";valid=false;}
if(!password){form.password.nextElementSibling.textContent="Le mot de passe est requis.";valid=false;}
else if(password.length<6){form.password.nextElementSibling.textContent="Le mot de passe doit contenir au moins 6 caractères.";valid=false;}
if(valid)form.submit();
});
});
</script>
</body>
</html>
