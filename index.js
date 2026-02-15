let dataConfig = null;
let carouselInstance = null;
let slideTimer = null;

const dom = {
    slidesContainer: document.getElementById('slides-container'),
    indicatorsContainer: document.getElementById('indicators-container'),
    audio: document.getElementById('bg-music'),
    overlay: document.getElementById('start-overlay'),
    progressBar: document.getElementById('progress-fill')
};

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const projectParam = urlParams.get('project');
        const jsonPath = projectParam ? `assets/${projectParam}/data.json` : 'data.json';

        if (window.location.protocol === "file:" && !projectParam) {
            throw new Error("Mode fichier détecté sans paramètre 'project'. Passage au chargement manuel.");
        }

        const response = await fetch(jsonPath);
        if (!response.ok) throw new Error(`Erreur ${response.status} : Fichier ${jsonPath} introuvable.`);

        dataConfig = await response.json();
        startPresentationFlow();

    } catch (error) {
        dom.overlay.style.cursor = 'default';
        dom.overlay.innerHTML = `
            <div style="max-width: 500px; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                <h3 style="color:#333; margin-bottom:15px;">Fichier introuvable</h3>
                <p style="color:#666; margin-bottom:20px;">
                    Le fichier <b>data.json</b> n'a pas pu être chargé automatiquement.
                </p>
                
                <div class="alert alert-info text-start small" style="font-size: 0.9rem;">
                    <strong>Option 1 :</strong><br>
                    Assurez-vous que le fichier <b>data.json</b> est dans le même dossier que <b>index.html</b>.
                    <hr style="margin:10px 0;">
                    <strong>Option 2 (Rapide) :</strong><br>
                    Sélectionnez manuellement votre fichier <b>data.json</b> ci-dessous :
                </div>

                <div class="mb-3">
                    <label for="manualJsonInput" class="form-label fw-bold">Choisir le fichier JSON :</label>
                    <input type="file" id="manualJsonInput" class="form-control" accept=".json">
                </div>
            </div>
        `;

        document.getElementById('manualJsonInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (evt) {
                try {
                    dataConfig = JSON.parse(evt.target.result);
                    dom.overlay.innerHTML = '<div class="spinner-border text-primary"></div>';
                    setTimeout(startPresentationFlow, 500);
                } catch (err) {
                    alert("Fichier JSON invalide.");
                }
            };
            reader.readAsText(file);
        });
    }
});

function startPresentationFlow() {
    buildSlider();
    dom.audio.src = dataConfig.settings.musicPath;
    dom.audio.volume = 0.5;

    dom.audio.onerror = () => { };

    dom.overlay.removeEventListener('click', startShow);
    dom.overlay.addEventListener('click', startShow);

    document.getElementById('btn-fullscreen').addEventListener('click', toggleFullScreen);
    document.getElementById('btn-mute').addEventListener('click', toggleMute);
    document.getElementById('main-slider').addEventListener('slid.bs.carousel', (e) => {
        resetTimer(e.to);
    });
}

function buildSlider() {
    if (!dataConfig || !dataConfig.slides) return;

    const slides = dataConfig.slides;
    dom.slidesContainer.innerHTML = '';
    dom.indicatorsContainer.innerHTML = '';

    slides.forEach((slideData, index) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('data-bs-target', '#main-slider');
        btn.setAttribute('data-bs-slide-to', index);
        if (index === 0) btn.classList.add('active');
        dom.indicatorsContainer.appendChild(btn);

        const item = document.createElement('div');
        item.classList.add('carousel-item');
        if (index === 0) item.classList.add('active');

        item.dataset.duration = slideData.duration || dataConfig.settings.defaultDuration;

        const wrapper = document.createElement('div');
        wrapper.classList.add('slide-content-wrapper');
        wrapper.style.display = 'flex';
        wrapper.style.width = '100%';
        wrapper.style.height = '100%';
        wrapper.style.flexDirection = 'row';

        const textCol = document.createElement('div');
        textCol.classList.add('col-text');

        const bgColor = slideData.bgColor || '#ffffff';
        const textColor = slideData.textColor || '#000000';
        const titleAlign = slideData.titleAlign || 'flex-start';
        const contentAlign = slideData.contentAlign || 'left';
        const titleSize = slideData.titleSize || '2.5rem';
        const contentSize = slideData.contentSize || '1.2rem';

        textCol.style.backgroundColor = bgColor;
        textCol.style.color = textColor;

        textCol.innerHTML = `
            <h1 class="slide-title" style="font-weight:800; margin-bottom:1.5rem; color:${textColor}; font-size:${titleSize}">${slideData.title}</h1>
            <div class="slide-desc" style="font-weight:400; color:${textColor}; max-width:800px; line-height:1.8; text-align:${contentAlign}; font-size:${contentSize}">
                ${slideData.content.split('\n').map(paragraph => `<p style="margin-bottom:1rem;">${paragraph}</p>`).join('')}
            </div>
        `;

        let imageCol = null;
        if (slideData.layout !== 'none' && slideData.imagePath) {
            imageCol = document.createElement('div');
            imageCol.classList.add('col-image');

            imageCol.style.position = 'relative';
            imageCol.style.overflow = 'hidden';
            imageCol.style.width = '50%';
            imageCol.style.height = '100%';
            imageCol.style.display = 'block';
            imageCol.style.backgroundColor = '#000';

            const img = document.createElement('img');
            img.src = slideData.imagePath;
            img.alt = slideData.title;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.position = 'absolute';
            img.style.top = '0';
            img.style.left = '0';

            img.onerror = function () {
                this.style.display = 'none';
                this.parentElement.style.backgroundColor = '#333';
                this.parentElement.innerHTML = '<div style="display:flex;height:100%;align-items:center;justify-content:center;color:#fff;">Image Absente</div>';
            };

            imageCol.appendChild(img);
        }

        textCol.style.width = (slideData.layout !== 'none' && slideData.imagePath) ? '50%' : '100%';
        textCol.style.height = '100%';
        textCol.style.display = 'flex';
        textCol.style.flexDirection = 'column';
        textCol.style.justifyContent = 'center';
        textCol.style.alignItems = titleAlign;
        textCol.style.padding = '4rem';

        if (slideData.layout === 'right' && imageCol) {
            textCol.style.order = '2';
            imageCol.style.order = '1';
        } else {
            textCol.style.order = '1';
            if (imageCol) imageCol.style.order = '2';
        }

        wrapper.appendChild(textCol);
        if (imageCol) wrapper.appendChild(imageCol);

        item.appendChild(wrapper);
        dom.slidesContainer.appendChild(item);
    });

    const mainSlider = document.getElementById('main-slider');

    const prevBtn = document.createElement('button');
    prevBtn.className = 'carousel-control-prev';
    prevBtn.type = 'button';
    prevBtn.setAttribute('data-bs-target', '#main-slider');
    prevBtn.setAttribute('data-bs-slide', 'prev');
    prevBtn.innerHTML = '<span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>';
    mainSlider.appendChild(prevBtn);

    const nextBtn = document.createElement('button');
    nextBtn.className = 'carousel-control-next';
    nextBtn.type = 'button';
    nextBtn.setAttribute('data-bs-target', '#main-slider');
    nextBtn.setAttribute('data-bs-slide', 'next');
    nextBtn.innerHTML = '<span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span>';
    mainSlider.appendChild(nextBtn);

    const style = document.createElement('style');
    style.innerHTML = `
        .carousel-control-prev, .carousel-control-next {
            opacity: 0 !important;
            transition: opacity 0.3s ease;
        }
        #main-slider:hover .carousel-control-prev,
        #main-slider:hover .carousel-control-next {
            opacity: 1 !important;
        }
    `;
    document.head.appendChild(style);

    carouselInstance = new bootstrap.Carousel(document.getElementById('main-slider'), {
        interval: false,
        wrap: true
    });
}

function startShow() {
    dom.overlay.style.opacity = 0;
    setTimeout(() => dom.overlay.remove(), 600);

    toggleFullScreen();
    dom.audio.play().catch(e => { });

    resetTimer(0);
}

function resetTimer(index) {
    const activeItem = dom.slidesContainer.children[index];
    const duration = parseInt(activeItem.dataset.duration) || 50000;

    dom.progressBar.style.transition = 'none';
    dom.progressBar.style.width = '0%';
    void dom.progressBar.offsetWidth;
    dom.progressBar.style.transition = `width ${duration}ms linear`;
    dom.progressBar.style.width = '100%';

    if (slideTimer) clearTimeout(slideTimer);

    slideTimer = setTimeout(() => {
        carouselInstance.next();
    }, duration);
}

function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => { });
        document.querySelector('#btn-fullscreen i').className = "fas fa-compress";
    } else {
        document.exitFullscreen();
        document.querySelector('#btn-fullscreen i').className = "fas fa-expand";
    }
}

function toggleMute() {
    dom.audio.muted = !dom.audio.muted;
    const icon = document.querySelector('#btn-mute i');
    icon.className = dom.audio.muted ? "fas fa-volume-mute" : "fas fa-volume-up";
}