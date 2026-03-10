<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recuperación de contraseña</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333333;
            padding: 40px 16px;
        }

        .wrapper {
            max-width: 520px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 36px 40px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            margin-top: 6px;
        }

        /* Body */
        .body {
            padding: 36px 40px;
        }

        .body p {
            font-size: 15px;
            line-height: 1.7;
            color: #555555;
        }

        /* Code badge */
        .code-box {
            margin: 28px 0;
            text-align: center;
        }

        .code-badge {
            display: inline-block;
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            border: 2px solid #a78bfa;
            border-radius: 12px;
            padding: 18px 40px;
        }

        .code-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #7c3aed;
            margin-bottom: 8px;
        }

        .code-value {
            display: block;
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #4f46e5;
            font-family: 'Courier New', Courier, monospace;
        }

        /* Warning */
        .warning {
            background-color: #fef9c3;
            border-left: 4px solid #facc15;
            border-radius: 6px;
            padding: 12px 16px;
            margin-top: 24px;
            font-size: 13px;
            color: #713f12;
            line-height: 1.6;
        }

        /* Ignore note */
        .ignore-note {
            margin-top: 20px;
            font-size: 13px;
            color: #999999;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #aaaaaa;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <h1>🔐 Recuperación de contraseña</h1>
            <p>Sistema de condominios</p>
        </div>

        <!-- Body -->
        <div class="body">
            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. Usa el siguiente código de
                verificación:</p>

            <div class="code-box">
                <div class="code-badge">
                    <span class="code-label">Tu código</span>
                    <span class="code-value">{{ $code }}</span>
                </div>
            </div>

            <div class="warning">
                ⏳ <strong>Este código expira en 15 minutos.</strong> Si no lo usas en ese tiempo, tendrás que solicitar
                uno nuevo.
            </div>

            <p class="ignore-note">
                Si no solicitaste un cambio de contraseña, puedes ignorar este correo con seguridad. Tu cuenta no
                sufrirá ningún cambio.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            Este es un mensaje automático, por favor no respondas a este correo.
        </div>

    </div>
</body>

</html>