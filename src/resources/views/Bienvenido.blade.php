<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
</head>
<body>
    <h1>Hola, {{ $name }}!</h1>
    <p>Gracias por registrarte. Por favor, verifica tu correo electrónico haciendo clic en el enlace siguiente:</p>
    <p><a href="{{ $verificationUrl }}">Verificar mi correo</a></p>
    <p>Si no realizaste este registro, ignora este mensaje.</p>
</body>
</html>