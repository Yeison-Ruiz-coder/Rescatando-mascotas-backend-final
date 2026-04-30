<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #667eea;">Restablecer contraseña</h2>
        <p>Hola <strong>{{ $user->nombre }}</strong>,</p>
        <p>Recibimos una solicitud para restablecer tu contraseña.</p>
        
        <a href="{{ $resetUrl }}" 
           style="display: inline-block; 
                  background: linear-gradient(135deg, #667eea, #764ba2); 
                  color: white; 
                  padding: 12px 24px; 
                  border-radius: 8px; 
                  text-decoration: none;
                  margin: 20px 0;">
            Restablecer contraseña
        </a>
        
        <p>Este enlace expirará en 60 minutos.</p>
        <p>Si no solicitaste este cambio, ignora este mensaje.</p>
        <hr>
        <p style="color: #999; font-size: 12px;">© {{ date('Y') }} Tu Plataforma</p>
    </div>
</body>
</html>