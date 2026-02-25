<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentation Studio - Diffusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Alegreya&family=Bebas+Neue&family=Bitter&family=Cardo&family=Caveat&family=Comfortaa&family=Crimson+Text&family=Dancing+Script&family=EB+Garamond&family=Fira+Sans&family=Inconsolata&family=Karla&family=Libre+Baskerville&family=Lora&family=Lato&family=Merriweather&family=Montserrat&family=Noto+Sans&family=Nunito&family=Open+Sans&family=Oswald&family=Pacifico&family=Playfair+Display&family=Poppins&family=PT+Sans&family=Quicksand&family=Raleway&family=Rubik&family=Source+Sans+Pro&family=Ubuntu&family=Work+Sans&family=Roboto&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --primary-color: #212529;
            --accent-color: #0d6efd;
            --text-dark: #1a1a1a;
            --bg-color: #ffffff;
            --shadow-elevate: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        body,
        html {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: 'Montserrat', sans-serif;
            background-color: #000;
        }

        #main-slider {
            height: 100vh;
            width: 100vw;
            position: relative;
        }

        .carousel-item {
            height: 100vh !important;
            width: 100%;
            background-color: var(--bg-color);
        }

        .controls-overlay {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            display: flex;
            gap: 15px;
        }

        .controls-overlay button {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.05);
            color: #333;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
            box-shadow: var(--shadow-elevate);
        }

        .controls-overlay button:hover {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.3);
        }

        #start-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            z-index: 2000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #333;
            cursor: pointer;
            transition: opacity 0.5s;
            box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        #start-overlay h1 {
            font-weight: 800;
            margin-bottom: 1rem;
            color: #000;
        }

        #start-overlay i {
            color: var(--accent-color);
        }

        #progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: rgba(0, 0, 0, 0.05);
            z-index: 1040;
        }

        #progress-fill {
            height: 100%;
            background: var(--accent-color);
            width: 0%;
        }

        #bg-music {
            display: none;
        }
    </style>
</head>

<body>

    <div id="start-overlay">
        <div class="text-center">
            <h1 class="display-4 fw-bold mb-4">Prêt pour la présentation ?</h1>
            <p class="lead mb-5">Cliquez n'importe où pour commencer</p>
            <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    </div>

    <div id="main-slider" class="carousel slide h-100" data-bs-ride="false">
        <div class="carousel-inner h-100" id="slides-container"></div>
    </div>

    <div id="progress-bar">
        <div id="progress-fill"></div>
    </div>

    <div class="controls-overlay">
        <button id="btn-mute" title="Couper/Activer le son"><i class="fas fa-volume-up"></i></button>
        <button id="btn-fullscreen" title="Plein écran"><i class="fas fa-expand"></i></button>
    </div>

    <audio id="bg-music" loop></audio>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        let dataConfig = null;
        let carouselInstance = null;
        let slideTimer = null;
        let playlistMode = false;
        let allProjects = [];
        let currentProjectIndex = 0;

        const $dom = {
            slidesContainer: $('#slides-container'),
            audio: $('#bg-music')[0],
            $audio: $('#bg-music'),
            overlay: $('#start-overlay'),
            progressBar: $('#progress-fill')
        };

        $(document).ready(async function () {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const projectParam = urlParams.get('project');
                
                // Mode: all - play all projects
                // Mode: playlist or no param - play only projects in playlist.json
                // Otherwise - play specific project
                
                if (projectParam === 'all') {
                    playlistMode = true;
                    const projects = await $.getJSON('/api/all');
                    if (projects.length === 0) {
                        throw new Error("Aucun projet trouvé.");
                    }
                    allProjects = projects;
                    dataConfig = allProjects[0].data;
                    startPresentationFlow(true);
                } else if (projectParam === 'playlist' || !projectParam) {
                    // Playlist mode - get projects from playlist.json
                    playlistMode = true;
                    const projects = await $.getJSON('/api/playlist');
                    if (projects.length === 0) {
                        // Fallback to all projects if playlist is empty
                        const allProjects = await $.getJSON('/api/all');
                        if (allProjects.length === 0) {
                            throw new Error("Aucun projet trouvé.");
                        }
                        allProjects = allProjects;
                        dataConfig = allProjects[0].data;
                        startPresentationFlow(true);
                        return;
                    }
                    allProjects = projects;
                    dataConfig = allProjects[0].data;
                    startPresentationFlow(true);
                } else {
                    console.log('Loading project:', projectParam);
                    const data = await $.getJSON('/api/project/' + encodeURIComponent(projectParam));
                    console.log('API Response:', data);
                    console.log('Font Family from API:', data.settings.fontFamily);
                    dataConfig = data;
                    startPresentationFlow(false);
                }

            } catch (error) {
                $dom.overlay.css('cursor', 'default');
                $dom.overlay.html(`
                    <div style="max-width: 500px; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                        <h3 style="color:#333; margin-bottom:15px;">Projet introuvable</h3>
                        <p style="color:#666; margin-bottom:20px;">
                            Le projet demandé n'existe pas ou n'a pas pu être chargé.
                        </p>
                        <a href="/editor" class="btn btn-primary">Retour à l'éditeur</a>
                    </div>
                `);
            }
        });

        function startPresentationFlow(isPlaylist = false) {
            buildSlider();
            
            if (isPlaylist && allProjects.length > 0) {
                addProjectIndicator(allProjects[currentProjectIndex].name, allProjects.length, currentProjectIndex + 1);
            }
            
            setMusic(dataConfig.settings.musicPath);
            $dom.audio.volume = 0.5;

            $dom.$audio.on('error', () => { });

            $dom.overlay.off('click').on('click', startShow);

            $('#btn-fullscreen').on('click', toggleFullScreen);
            $('#btn-mute').on('click', toggleMute);

            $('#main-slider').on('slid.bs.carousel', function (e) {
                const totalSlides = $dom.slidesContainer.children().length;
                if (playlistMode && e.to === totalSlides - 1) {
                    if (slideTimer) clearTimeout(slideTimer);
                    
                    currentProjectIndex++;
                    if (currentProjectIndex >= allProjects.length) {
                        currentProjectIndex = 0;
                    }
                    
                    dataConfig = allProjects[currentProjectIndex].data;
                    updateProjectIndicator();
                    setMusic(dataConfig.settings.musicPath);
                    buildSlider();
                    
                    carouselInstance.to(0);
                    resetTimer(0);
                    
                    $dom.audio.play().catch(e => {});
                } else {
                    resetTimer(e.to);
                }
            });
        }

        function setMusic(musicPath) {
            if (musicPath && !musicPath.startsWith('/') && !musicPath.startsWith('http')) {
                musicPath = '/' + musicPath;
            }
            $dom.audio.src = musicPath || '';
            $dom.audio.load();
        }

        function addProjectIndicator(name, total, current) {
            $('#project-indicator').remove();
            const indicator = $('<div>').attr('id', 'project-indicator').css({
                position: 'fixed',
                bottom: '20px',
                right: '20px',
                backgroundColor: 'rgba(0,0,0,0.7)',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '25px',
                fontSize: '14px',
                zIndex: '9999',
                display: 'flex',
                alignItems: 'center',
                gap: '10px',
                opacity: 0,
                transition: 'opacity 0.5s ease'
            }).html(`
                <i class="fas fa-folder-open"></i>
                <span><strong>${name}</strong></span>
                <span class="text-muted">(${current}/${total})</span>
            `);
            $('body').append(indicator);
            
            setTimeout(() => {
                indicator.css('opacity', '1');
                setTimeout(() => {
                    indicator.css('opacity', '0');
                    setTimeout(() => indicator.remove(), 500);
                }, 2000);
            }, 100);
        }

        function updateProjectIndicator() {
            addProjectIndicator(allProjects[currentProjectIndex].name, allProjects.length, currentProjectIndex + 1);
        }

        function buildSlider() {
            if (!dataConfig || !dataConfig.slides) return;

            console.log('buildSlider - settings:', dataConfig.settings);
            console.log('buildSlider - fontFamily:', dataConfig.settings.fontFamily);

            const slides = dataConfig.slides;
            $dom.slidesContainer.empty();

            slides.forEach((slideData, index) => {
                const $item = $('<div>').addClass('carousel-item');
                if (index === 0) $item.addClass('active');

                $item.attr('data-duration', slideData.duration || dataConfig.settings.defaultDuration);

                const $wrapper = $('<div>').addClass('slide-content-wrapper').css({
                    display: 'flex',
                    width: '100%',
                    height: '100%',
                    flexDirection: 'row'
                });

                const $textCol = $('<div>').addClass('col-text');
                const bgColor = slideData.bgColor || '#ffffff';
                const textColor = slideData.textColor || '#000000';
                const titleAlign = slideData.titleAlign || 'flex-start';
                const contentAlign = slideData.contentAlign || 'left';
                const titleSize = slideData.titleSize || '2.5rem';
                const contentSize = slideData.contentSize || '1.2rem';
                const fontFamily = dataConfig.settings.fontFamily || "'Montserrat', sans-serif";

                $textCol.css({
                    backgroundColor: bgColor,
                    color: textColor,
                    width: (slideData.layout !== 'none' && slideData.imagePath) ? '50%' : '100%',
                    height: '100%',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: titleAlign,
                    padding: '4rem'
                });

                const paragraphs = slideData.content.split('\n').map(paragraph => `<p style="margin-bottom:1rem;">${paragraph}</p>`).join('');
                $textCol.html(`
                    <h1 class="slide-title" style="font-weight:800; margin-bottom:1.5rem; color:${textColor}; font-size:${titleSize}; font-family:${fontFamily}">${slideData.title}</h1>
                    <div class="slide-desc" style="font-weight:400; color:${textColor}; max-width:800px; line-height:1.8; text-align:${contentAlign}; font-size:${contentSize}; font-family:${fontFamily}">
                        ${paragraphs}
                    </div>
                `);

                let $imageCol = null;
                if (slideData.layout !== 'none' && slideData.imagePath) {
                    $imageCol = $('<div>').addClass('col-image').css({
                        position: 'relative',
                        overflow: 'hidden',
                        width: '50%',
                        height: '100%',
                        display: 'block',
                        backgroundColor: bgColor
                    });

                    let imgPath = slideData.imagePath;
                    if (imgPath && !imgPath.startsWith('/') && !imgPath.startsWith('http')) {
                        imgPath = '/' + imgPath;
                    }

                    const $img = $('<img>').css({
                        width: '100%',
                        height: '100%',
                        objectFit: 'cover',
                        position: 'absolute',
                        top: '0',
                        left: '0'
                    }).attr('src', imgPath).attr('alt', slideData.title);

                    $img.on('error', function () {
                        $(this).hide();
                        $(this).parent().css('backgroundColor', '#333').html('<div style="display:flex;height:100%;align-items:center;justify-content:center;color:#fff;">Image Absente</div>');
                    });

                    $imageCol.html($img);
                }

                // layout "right" = Text Left / Image Right
                // layout "left" = Text Right / Image Left
                if (slideData.layout === 'right' && $imageCol) {
                    // Text on left, image on right
                    $wrapper.append($textCol).append($imageCol);
                } else if (slideData.layout === 'left' && $imageCol) {
                    // Text on right, image on left
                    $wrapper.append($imageCol).append($textCol);
                } else {
                    // No image or layout "none" - text only
                    $wrapper.append($textCol);
                }

                $item.html($wrapper);
                $dom.slidesContainer.append($item);
            });

            const $mainSlider = $('#main-slider');

            carouselInstance = new bootstrap.Carousel($mainSlider[0], {
                interval: false,
                wrap: true
            });
        }

        function startShow() {
            $dom.overlay.animate({ opacity: 0 }, 600, function () {
                $(this).remove();
            });

            toggleFullScreen();
            $dom.audio.play().catch(e => { });

            resetTimer(0);
        }

        function resetTimer(index) {
            const activeItem = $dom.slidesContainer.children().eq(index);
            const duration = parseInt(activeItem.attr('data-duration')) || 50000;

            $dom.progressBar.css({
                transition: 'none',
                width: '0%'
            });

            $dom.progressBar[0].offsetWidth;

            $dom.progressBar.css({
                transition: `width ${duration}ms linear`,
                width: '100%'
            });

            if (slideTimer) clearTimeout(slideTimer);

            slideTimer = setTimeout(() => {
                carouselInstance.next();
            }, duration);
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => { });
                $('#btn-fullscreen i').attr('class', "fas fa-compress");
            } else {
                document.exitFullscreen();
                $('#btn-fullscreen i').attr('class', "fas fa-expand");
            }
        }

        function toggleMute() {
            $dom.audio.muted = !$dom.audio.muted;
            $('#btn-mute i').attr('class', $dom.audio.muted ? "fas fa-volume-mute" : "fas fa-volume-up");
        }
    </script>
</body>

</html>
