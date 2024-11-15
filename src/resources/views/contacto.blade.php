<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
        }
        .footer {
            background-color: #e9e9e9;
            color: #555;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Mensaje de Contacto</h1>
    </div>
    <div class="content">
        <p><strong>Nombre:</strong> {{ $name }}</p>
        <p><strong>Email:</strong> {{ $email }}</p>
        <p><strong>Mensaje:</strong></p>
        <p>{{ $messageContent }}</p>
    </div>
    <div class="footer">
        <p>Este mensaje ha sido enviado desde el formulario de contacto de nuestro sitio web.</p>
    </div>
</div>
</body>
</html>
