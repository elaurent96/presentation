<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentation Studio API - Swagger</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.17.0/swagger-ui.css">
    <style>
        body { margin: 0; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.17.0/swagger-ui-bundle.js" charset="UTF-8"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '/openapi.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis
                ],
                persistAuthorization: true
            });
            
            window.ui = ui;
        };
    </script>
</body>
</html>
