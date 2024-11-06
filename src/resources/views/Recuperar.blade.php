<!DOCTYPE html>
<html>
<head>
    <title>Recuperación de Contraseña</title>
</head>
<body>
<h1>Hola, {{ $user->name }}</h1>
<p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
<p>Puedes restablecer tu contraseña usando el siguiente enlace:</p>
<a href="{{ $resetUrl }}">Recuperar contraseña</a>
<p>Si no has solicitado restablecer tu contraseña, por favor, ignora este correo.</p>
<p>Gracias.</p>
</body>
</html>
