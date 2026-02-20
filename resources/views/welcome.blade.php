<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentation Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-dark">
    <div class="container vh-100 d-flex align-items-center justify-content-center">
        <div class="text-center text-white">
            <h1 class="display-1 fw-bold mb-4">
                <i class="fas fa-layer-group me-3"></i>Presentation Studio
            </h1>
            <p class="lead mb-5">Créez et diffusez vos présentations</p>
            
            @auth
                <a href="{{ route('editor') }}" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-edit me-2"></i>Ouvrir l'éditeur
                </a>
            @else
                <div class="d-flex gap-3 justify-content-center">
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-5">Connexion</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-5">Inscription</a>
                </div>
            @endauth
        </div>
    </div>
</body>
</html>
