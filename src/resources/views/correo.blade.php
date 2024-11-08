<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4CAF50;
            padding: 20px;
            text-align: center;
            color: #ffffff;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content h1 {
            color: #333;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Encabezado -->
        <div class="header">
            <h1>¡Bienvenido!</h1>
        </div>

        <!-- Contenido -->
        <div class="content">
            <h1>Hola, [Nombre del destinatario]</h1>
            <p>
                Nos complace darte la bienvenida a nuestro servicio. Aquí puedes encontrar toda la información que necesitas para comenzar.
            </p>
            <a href="#" class="button">Comenzar Ahora</a>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p>&copy; 2024 Tu Empresa. Todos los derechos reservados.</p>
            <p>Si tienes alguna pregunta, no dudes en <a href="mailto:soporte@tuempresa.com">contactarnos</a>.</p>
        </div>
    </div>
</body>
</html>