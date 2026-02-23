let dataConfig = null;
let carouselInstance = null;
let slideTimer = null;
let playlistMode = false;
let allProjects = [];
let currentProjectIndex = 0;

const $dom = {
    slidesContainer: $('#slides-container'),
    indicatorsContainer: $('#indicators-container'),
    audio: $('#bg-music')[0],
    $audio: $('#bg-music'),
    overlay: $('#start-overlay'),
    progressBar: $('#progress-fill')
};

$(document).ready(async function () {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const projectParam = urlParams.get('project');
        playlistMode = projectParam === 'all';

        if (playlistMode) {
            const projects = await $.getJSON('/api/all');
            if (projects.length === 0) {
                throw new Error("Aucun projet trouvé.");
            }
            allProjects = projects;
            dataConfig = allProjects[0].data;
            startPresentationFlow(true);
        } else if (!projectParam) {
            throw new Error("Paramètre 'project' manquant.");
        } else {
            console.log('Loading project:', projectParam);
            const data = await $.getJSON(`/api/project/${projectParam}`);
            console.log('API Response:', data);
            console.log('Settings:', data.settings);
            console.log('Font Family:', data.settings.fontFamily);
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

$(document).on('keydown', function (e) {
    if (!carouselInstance) return;
    
    const totalSlides = $dom.slidesContainer.children().length;
    const currentIndex = $dom.slidesContainer.children().index($dom.slidesContainer.find('.carousel-item.active'));
    
    if (e.key === 'ArrowLeft') {
        e.preventDefault();
        if (slideTimer) clearTimeout(slideTimer);
        carouselInstance.prev();
        setTimeout(() => {
            const newIndex = $dom.slidesContainer.children().index($dom.slidesContainer.find('.carousel-item.active'));
            resetTimer(newIndex);
        }, 50);
    } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        if (slideTimer) clearTimeout(slideTimer);
        carouselInstance.next();
        setTimeout(() => {
            const newIndex = $dom.slidesContainer.children().index($dom.slidesContainer.find('.carousel-item.active'));
            resetTimer(newIndex);
        }, 50);
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

function handleProjectTransition() {
    if (!playlistMode) return;
    
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
}

function buildSlider() {
    if (!dataConfig || !dataConfig.slides) return;

    console.log('buildSlider - dataConfig.settings:', dataConfig.settings);
    console.log('buildSlider - fontFamily:', dataConfig.settings.fontFamily);

    const slides = dataConfig.slides;
    $dom.slidesContainer.empty();
    $dom.indicatorsContainer.empty();

    slides.forEach((slideData, index) => {
        // Supprimé : indicators

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
        console.log('Font family:', fontFamily);

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

        if (slideData.layout === 'right' && $imageCol) {
            $textCol.css('order', '2');
            $imageCol.css('order', '1');
            $wrapper.append($imageCol).append($textCol);
        } else {
            $textCol.css('order', '1');
            $wrapper.append($textCol);
            if ($imageCol) {
                $imageCol.css('order', '2');
                $wrapper.append($imageCol);
            }
        }

        $item.html($wrapper);
        $dom.slidesContainer.append($item);
    });

    const $mainSlider = $('#main-slider');
    // Supprimé : prev/next buttons

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

    $dom.progressBar[0].offsetWidth; // trigger reflow

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
