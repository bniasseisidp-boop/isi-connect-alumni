<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue sur ISI Connect</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #0ea5e9; color: #fff; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 20px; }
        .footer { font-size: 11px; color: #999; text-align: center; margin-top: 20px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; background: #0ea5e9; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ISI Suptech Alumni</h1>
        </div>
        <div class="content">
            <p>Bonjour **{{ $name }}**,</p>
            <p>Vous avez été invité à rejoindre la plateforme **ISI Connect**, le réseau social officiel des anciens élèves d'ISI Suptech.</p>
            
            <p>Voici vos identifiants de connexion temporaires :</p>
            <div class="box">
                <p><strong>Email :</strong> {{ $email }}</p>
                <p><strong>Mot de passe :</strong> {{ $password }}</p>
            </div>

            <p><strong>Important :</strong> Lors de votre première connexion, il vous sera demandé de modifier ce mot de passe par mesure de sécurité.</p>
            
            <p style="text-align: center;">
                <a href="http://localhost:5173/login" class="btn">Se connecter maintenant</a>
            </p>

            <p>À bientôt sur la plateforme !</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ISI Suptech Connect. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
