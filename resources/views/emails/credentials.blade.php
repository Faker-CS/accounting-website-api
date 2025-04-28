<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Identifiants</title></head>
<body>
    <p>Bonjour {{ $user->name }},</p>
    <p>Votre compte a été créé avec le rôle « {{ $user->getRoleNames()->first() }} ».</p>
    <ul>
        <li>Email : {{ $user->email }}</li>
        <li>Mot de passe : {{ $password }}</li>
    </ul>
    <p>Merci de changer votre mot de passe après la première connexion.</p>
</body>
</html>