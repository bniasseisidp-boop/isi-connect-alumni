<!DOCTYPE html>
<html>
<head>
    <title>Récupération de compte - ISI Connect</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #f43f5e; color: #fff; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { padding: 20px; }
        .footer { font-size: 11px; color: #999; text-align: center; margin-top: 20px; }
        .box { background: #fff1f2; border: 1px solid #fecdd3; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; background: #f43f5e; color: #fff !important; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ISI Connect Alumni</h1>
            <p>SÉCURITÉ SYSTÈME</p>
        </div>
        <div class="content">
            <p>Bonjour **{{ $name }}**,</p>
            <p>Vous avez demandé la réinitialisation de votre matrice d'accès à la plateforme **ISI Connect**.</p>
            
            <p>Voici votre nouveau code d'accès temporaire :</p>
            <div class="box">
                <p><strong>Matrice Email :</strong> {{ $email }}</p>
                <p><strong>Code Temporaire :</strong> <span style="font-family: monospace; font-size: 18px; color: #e11d48; letter-spacing: 2px;">{{ $password }}</span></p>
            </div>

            <p><strong>ALERTE SÉCURITÉ :</strong> Cet accès est temporaire. Dès votre connexion, le système vous demandera obligatoirement de définir une nouvelle clef d'accès personnelle.</p>
            
            <p style="text-align: center;">
                <a href="http://localhost:5173/login" class="btn">RÉACTIVER MON COMPTE</a>
            </p>

            <p>Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.</p>
            <p>À bientôt sur le réseau !</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ISI Suptech Connect. Matrix Security Protocol.</p>
        </div>
    </div>
</body>
</html>
