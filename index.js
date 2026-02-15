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
        const response = await fetch('data.json');
        if (!response.ok) throw new Error("Erreur lors du chargement de data.json");
        dataConfig = await response.json();

        buildSlider();

        dom.audio.src = dataConfig.settings.musicPath;
        dom.audio.volume = 0.5;

        setupEventListeners();

    } catch (error) {
        console.error("Erreur critique:", error);
        dom.overlay.innerHTML = `<h1 style="color:red">Erreur de chargement</h1><p>Vérifiez que le fichier data.json est présent à la racine.</p>`;
    }
});

function setupEventListeners() {
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

        textCol.innerHTML = `
            <h1 class="slide-title" style="font-weight:800; font-size:3.5rem; margin-bottom:1.5rem; color:#000;">${slideData.title}</h1>
            <div class="slide-desc" style="font-size:1.4rem; font-weight:400; color:#555; max-width:800px; line-height:1.8;">
                ${slideData.text.split('\n').map(paragraph => `<p style="margin-bottom:1rem;">${paragraph}</p>`).join('')}
            </div>
        `;

        let imageCol = null;
        if (slideData.hasImage) {
            imageCol = document.createElement('div');
            imageCol.classList.add('col-image');

            imageCol.style.position = 'relative';
            imageCol.style.overflow = 'hidden';
            imageCol.style.width = '50%';
            imageCol.style.height = '100%';
            imageCol.style.display = 'block';
            imageCol.style.backgroundColor = '#000';

            const img = document.createElement('img');
            const imgPath = slideData.imagePath;

            img.src = imgPath;
            img.alt = slideData.title;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.position = 'absolute';
            img.style.top = '0';
            img.style.left = '0';

            img.onerror = function () {
                console.error('Image introuvable : ' + imgPath);
                this.style.display = 'none';
                this.parentElement.style.backgroundColor = '#333';
                this.parentElement.innerHTML = '<div style="display:flex;height:100%;align-items:center;justify-content:center;color:#fff;">Image Absente</div>';
            };

            imageCol.appendChild(img);
        }

        textCol.style.width = slideData.hasImage ? '50%' : '100%';
        textCol.style.height = '100%';
        textCol.style.display = 'flex';
        textCol.style.flexDirection = 'column';
        textCol.style.justifyContent = 'center';
        textCol.style.alignItems = slideData.titleAlign || 'flex-start';
        textCol.style.padding = '4rem';
        textCol.style.backgroundColor = '#ffffff';
        textCol.style.textAlign = slideData.contentAlign || 'left';

        if (slideData.alignSlide === 'right' && imageCol) {
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

    carouselInstance = new bootstrap.Carousel(document.getElementById('main-slider'), {
        interval: false,
        wrap: true
    });
}

function startShow() {
    dom.overlay.style.opacity = 0;
    setTimeout(() => dom.overlay.remove(), 600);

    toggleFullScreen();
    dom.audio.play().catch(e => console.log("Audio bloqué par le navigateur", e));

    resetTimer(0);
}

function resetTimer(index) {
    const activeItem = dom.slidesContainer.children[index];
    const duration = parseInt(activeItem.dataset.duration) || 5000;

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
        document.documentElement.requestFullscreen().catch(err => console.log(err));
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