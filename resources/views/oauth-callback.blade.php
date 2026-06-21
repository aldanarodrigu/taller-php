<!DOCTYPE html>
<html>
<head>
    <title>Autenticando...</title>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div style="display: flex; justify-content: center; align-items: center; height: 100vh; font-family: Arial, sans-serif;">
        <div style="text-align: center; max-width: 400px;">
            <h2>Completando autenticación...</h2>
            <p>Por favor espera mientras te redirigimos.</p>

            <div style="margin-top: 20px;">
                <p style="color: #666; font-size: 14px;">Si no eres redirigido automáticamente,
                    <a href="{{ $callback_url }}" style="color: #0066cc; text-decoration: none;">haz clic aquí</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        const callbackUrl = "{{ $callback_url }}";

        setTimeout(() => {
            window.location.replace(callbackUrl);
        }, 200);

        setTimeout(() => {
            window.location.href = callbackUrl;
        }, 2000);
    </script>
</body>
</html>
