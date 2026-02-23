<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlist - Presentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Alegreya&family=Bebas+Neue&family=Bitter&family=Cardo&family=Caveat&family=Comfortaa&family=Crimson+Text&family=Dancing+Script&family=EB+Garamond&family=Fira+Sans&family=Inconsolata&family=Karla&family=Libre+Baskerville&family=Lora&family=Lato&family=Merriweather&family=Montserrat&family=Noto+Sans&family=Nunito&family=Open+Sans&family=Oswald&family=Pacifico&family=Playfair+Display&family=Poppins&family=PT+Sans&family=Quicksand&family=Raleway&family=Rubik&family=Source+Sans+Pro&family=Ubuntu&family=Work+Sans&family=Roboto&display=swap" rel="stylesheet">
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; font-family: 'Montserrat', sans-serif; background-color: #000; }
    </style>
</head>
<body>
    @if(count($projectsData) > 0)
    <div id="playlist-slider" class="carousel slide h-100" data-bs-ride="false">
        <div class="carousel-inner h-100" id="slides-container"></div>
    </div>

    <div id="progress-bar"><div id="progress-fill"></div></div>

    <div class="controls-overlay">
        <button id="btn-mute" title="Couper/Activer le son"><i class="fas fa-volume-up"></i></button>
        <button id="btn-fullscreen" title="Plein écran"><i class="fas fa-expand"></i></button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const playlistData = {!! json_encode($projectsData) !!};
    </script>
    <script src="/js/playlist.js"></script>
    @else
    <div style="display:flex;align-items:center;justify-content:center;height:100vh;color:white;">
        <div class="text-center">
            <h1>Aucune présentation dans la playlist</h1>
            <p>Ajoutez des présentations depuis la page Playlist</p>
            <a href="{{ route('playlist.index') }}" class="btn btn-primary mt-3">Aller à la Playlist</a>
        </div>
    </div>
    @endif
</body>
</html>
