@extends('layouts.shadcn')

@section('title', 'Éditeur')

@section('styles')
<style>
    :root { --primary: #0d6efd; --dark: #212529; }
    .editor-panel { height: calc(100vh - 80px); overflow-y: auto; background: #f8f9fa; border-right: 1px solid #dee2e6; display: flex; flex-direction: column; min-width: 200px; max-width: 500px; }
    .preview-panel { height: calc(100vh - 80px); background: #000; position: relative; display: flex; flex-direction: column; overflow: hidden; flex: 1; min-width: 0; }
    .panel-resizer {
        width: 8px;
        background: #dee2e6;
        cursor: col-resize;
        flex-shrink: 0;
        transition: background 0.2s;
    }
    .panel-resizer:hover, .panel-resizer.dragging {
        background: #0d6efd;
    }
    .editor-panel { height: calc(100vh - 80px); overflow-y: auto; background: #f8f9fa; border-right: 1px solid #dee2e6; display: flex; flex-direction: column; min-width: 0; }
    #preview-container { flex: 1; overflow: hidden; flex-grow: 1; background: #fff; position: relative; }
    .slide-item { cursor: pointer; border-left: 4px solid transparent; transition: all 0.2s; padding: 10px; }
    .slide-item:hover { background: #e9ecef; border-left-color: var(--primary); }
    .slide-item.active-slide { background: #e7f1ff; border-left-color: var(--primary); font-weight: 600; }
    input[type="color"] { padding: 0; height: 38px; cursor: pointer; width: 100%; }
    .btn-float-del { opacity: 0; transition: opacity 0.2s; z-index: 10; }
    .slide-item:hover .btn-float-del { opacity: 1; }
    .row.h-100 { display: flex; flex-wrap: nowrap; margin: 0; width: 100%; }
    .resizer { width: 6px; background: #dee2e6; cursor: col-resize; flex-shrink: 0; }
    .editor-header { background: #1a1a1a; padding: 10px 20px; display: flex; align-items: center; gap: 15px; }
    .editor-header select, .editor-header input { background: #333; border: 1px solid #555; color: white; padding: 6px 12px; border-radius: 4px; }
    .custom-accordion-item { border-bottom: 1px solid #dee2e6; }
    .custom-accordion-button { width: 100%; padding: 15px 20px; background: #f8f9fa; border: none; text-align: left; cursor: pointer; display: flex; align-items: center; justify-content: space-between; font-weight: 600; font-size: 14px; color: #333; }
    .custom-accordion-button:hover { background: #e9ecef; }
    .custom-accordion-button i.chevron { transition: transform 0.3s; }
    .custom-accordion-button.active i.chevron { transform: rotate(180deg); }
    .custom-accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
    .custom-accordion-content.open { max-height: 500px; }
    @media (max-width: 768px) {
        .editor-panel { width: 100% !important; max-width: 100%; min-width: 100%; border-right: none; }
        .preview-panel { display: none; }
        .editor-header { padding: 8px 10px; }
        .editor-header h5 { font-size: 14px; }
    }
</style>
@endsection

@section('content')
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar" id="mainSidebar">
        <div class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-chevron-right"></i>
        </div>
        <div class="sidebar-header">
            <div class="app-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <h3>Presentation Studio</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('editor') }}" class="{{ request()->routeIs('editor') ? 'active' : '' }}">
                <i class="fas fa-edit"></i> <span>Éditeur</span>
            </a>
            <a href="{{ route('presentation.list') }}" class="{{ request()->routeIs('presentation.*') ? 'active' : '' }}">
                <i class="fas fa-play"></i> <span>Présentations</span>
            </a>
            @auth
                @if(Auth::user()->isAdmin())
                <a href="{{ route('playlist.index') }}" class="{{ request()->routeIs('playlist.*') ? 'active' : '' }}">
                    <i class="fas fa-list"></i> <span>Playlists</span>
                </a>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> <span>Utilisateurs</span>
                </a>
                @endif
            @endauth
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <i class="fas fa-user"></i> <span>{{ Auth::user()->name }}</span>
                <span class="badge {{ Auth::user()->isAdmin() ? 'badge-admin' : 'badge-lambda' }} ms-1">
                    {{ Auth::user()->role }}
                </span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span>
                </button>
            </form>
        </div>
    </div>

    <div class="main-content" id="mainContent" style="display: flex; flex-direction: column; width: 100%;">
        <div class="editor-header">
            <h5 class="text-white mb-0" id="projectTitle">{{ $project ?? 'Nouveau Projet' }}</h5>
            <div style="flex: 1;"></div>
            <button class="btn btn-sm btn-outline-light" id="btnViewPresentation">
                <i class="fas fa-play"></i> Voir
            </button>
        </div>
        
        <div style="display: flex; flex: 1; overflow: hidden;">
            <div class="editor-panel" id="editorPanel" style="width: 350px;">
                <div class="custom-accordion-item">
                    <button class="custom-accordion-button" onclick="toggleAccordion('globalSettingsPanel', this)">
                        <span><i class="fas fa-sliders-h me-2"></i> Paramètres Globaux</span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div id="globalSettingsPanel" class="custom-accordion-content open">
                        <div class="p-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Musique de fond</label>
                                <input type="file" id="musicFile" class="form-control form-control-sm" accept="audio/*">
                                <small class="text-muted d-block mt-1" id="musicFileName">Par défaut</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Durée par défaut (ms)</label>
                                <input type="number" class="form-control form-control-sm" id="defaultDuration" value="50000">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="custom-accordion-item">
                    <button class="custom-accordion-button" onclick="toggleAccordion('slidesListPanel', this)">
                        <span><i class="fas fa-images me-2"></i> Diapositives <span class="badge bg-primary ms-2" id="slideCount">0</span></span>
                        <i class="fas fa-chevron-down chevron"></i>
                    </button>
                    <div id="slidesListPanel" class="custom-accordion-content open">
                        <div class="p-3">
                            <button class="btn btn-outline-primary w-100 mb-3" id="btnAddSlide">
                                <i class="fas fa-plus"></i> Nouvelle Slide
                            </button>
                            <div id="slidesList" class="list-group list-group-flush"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-resizer" id="panelResizer"></div>

            <div class="preview-panel" id="previewPanel">
                <div id="preview-container">
                    <div id="preview-slider" class="carousel slide h-100 w-100" data-bs-ride="false">
                        <div class="carousel-inner h-100" id="preview-inner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#preview-slider" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#preview-slider" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
                <div class="bottom-slides-bar" id="bottomSlidesBar">
                    <div id="bottomSlidesList" class="d-flex"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Éditer la Slide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editIndex">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" id="inpTitle">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Taille Titre</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" class="form-range" id="inpTitleSize" min="1" max="6" step="0.1" value="2.5">
                            <small id="titleSizeVal">2.5rem</small>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Contenu</label>
                        <textarea class="form-control" id="inpContent" rows="4"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Taille Contenu</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" class="form-range" id="inpContentSize" min="0.8" max="3" step="0.1" value="1.2">
                            <small id="contentSizeVal">1.2rem</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Disposition</label>
                        <select class="form-select" id="inpLayout">
                            <option value="right">Texte Gauche / Image Droite</option>
                            <option value="left">Texte Droite / Image Gauche</option>
                            <option value="none">Texte Uniquement</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" id="inpImage" accept="image/*">
                        <small class="text-muted" id="currentImageName"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alignement Titre</label>
                        <select class="form-select" id="inpTitleAlign">
                            <option value="flex-start">Gauche</option>
                            <option value="center">Centre</option>
                            <option value="flex-end">Droite</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alignement Contenu</label>
                        <select class="form-select" id="inpContentAlign">
                            <option value="left">Gauche</option>
                            <option value="center">Centre</option>
                            <option value="right">Droite</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Couleur Fond</label>
                        <input type="color" class="form-control" id="inpBgColor">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Couleur Texte</label>
                        <input type="color" class="form-control" id="inpTextColor">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée (ms)</label>
                        <input type="number" class="form-control" id="inpDuration">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnSaveSlide">Appliquer</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteSlideModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 90%;">
        <h3 style="margin-bottom: 15px;">Confirmer la suppression</h3>
        <p style="margin-bottom: 20px; color: #666;">Êtes-vous sûr de vouloir supprimer cette slide ? Cette action est irréversible.</p>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn btn-outline" onclick="document.getElementById('deleteSlideModal').style.display='none'">Annuler</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteSlide">Supprimer</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentProject = { settings: { musicPath: "", defaultDuration: 50000 }, slides: [] };
let currentProjectName = null;
let editModal;
let slideToDelete = null;

console.log('Editor script loaded');

// Setup AJAX to send credentials
$.ajaxSetup({
    xhrFields: {
        withCredentials: true
    },
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

console.log('Editor loaded, jQuery:', typeof $, 'Bootstrap:', typeof bootstrap);

function toggleAccordion(id, btn) {
    console.log('toggleAccordion', id);
    const content = document.getElementById(id);
    if (content.classList.contains('open')) {
        content.classList.remove('open');
        btn.classList.remove('active');
    } else {
        content.classList.add('open');
        btn.classList.add('active');
    }
}

$(document).ready(function() {
    console.log('Document ready');
    
    editModal = new bootstrap.Modal($('#editModal')[0]);
    
    // Get project name from PHP variable first, then URL
    var phpProject = '{{ addslashes($project ?? '') }}';
    currentProjectName = phpProject;
    if (!currentProjectName) {
        var urlParams = new URLSearchParams(window.location.search);
        currentProjectName = urlParams.get('project');
    }
    console.log('Project name:', currentProjectName);
    
    if (currentProjectName) {
        loadProject(currentProjectName);
    } else {
        currentProject = { settings: { musicPath: "", defaultDuration: 50000 }, slides: [] };
        renderSlideList();
        renderPreview();
    }

    $('#btnAddSlide').on('click', addNewSlide);
    $('#btnSaveProject').on('click', saveProjectToServer);
    $('#btnSaveSlide').on('click', applySlideChanges);
    $('#btnViewPresentation').on('click', () => {
        if (!currentProjectName) { alert('Aucun projet sélectionné'); return; }
        window.open('/presentation?project=' + encodeURIComponent(currentProjectName), '_blank');
    });

    $('#inpTitleSize').on('input', function() { $('#titleSizeVal').text($(this).val() + 'rem'); });
    $('#inpContentSize').on('input', function() { $('#contentSizeVal').text($(this).val() + 'rem'); });

    $('#musicFile').on('change', async function() {
        if (!currentProjectName) { alert('Aucun projet sélectionné'); return; }
        const file = this.files[0];
        if (file) {
            console.log('Starting upload for file:', file.name, 'size:', file.size, 'type:', file.type);
            const formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('projectName', currentProjectName);
            formData.append('audio', file);
            console.log('FormData created, projectName:', currentProjectName);
            try {
                console.log('Sending upload request to /api/upload');
                const res = await $.ajax({ 
                    url: '/api/upload', 
                    type: 'POST', 
                    data: formData, 
                    processData: false, 
                    contentType: false,
                    cache: false
                });
                console.log('Upload response received:', res);
                if (res.audioPath) {
                    currentProject.settings.musicPath = res.audioPath;
                    $('#musicFileName').text(file.name);
                    console.log('Music path set to:', res.audioPath);
                    await saveProjectToServer();
                    console.log('Save complete, musicPath in project:', currentProject.settings.musicPath);
                } else {
                    console.error('No audioPath in response:', res);
                    alert('Upload réussi mais chemin non retourné');
                }
            } catch (e) { 
                console.error('Upload error:', e.status, e.statusText);
                console.error('Response:', e.responseJSON);
                alert('Erreur: ' + (e.responseJSON?.error || e.message)); 
            }
        } else {
            console.log('No file selected');
        }
    });
});

// Panel Resizer
const resizer = document.getElementById('panelResizer');
const editorPanel = document.getElementById('editorPanel');

if (resizer && editorPanel) {
    let isResizing = false;
    
    resizer.addEventListener('mousedown', (e) => {
        isResizing = true;
        resizer.classList.add('dragging');
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    });
    
    document.addEventListener('mousemove', (e) => {
        if (!isResizing) return;
        
        const containerRect = editorPanel.parentElement.getBoundingClientRect();
        const newWidth = e.clientX - containerRect.left;
        
        if (newWidth >= 200 && newWidth <= 500) {
            editorPanel.style.width = newWidth + 'px';
        }
    });
    
    document.addEventListener('mouseup', () => {
        if (isResizing) {
            isResizing = false;
            resizer.classList.remove('dragging');
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        }
    });
}

async function loadProject(name) {
    console.log('Loading project:', name);
    try {
        const url = '/api/project/' + encodeURIComponent(name);
        console.log('Calling API:', url);
        const data = await $.get(url);
        console.log('Loaded data:', data);
        currentProject = data;
        currentProjectName = name;
        $('#defaultDuration').val(data.settings.defaultDuration);
        $('#musicFileName').text(data.settings.musicPath ? data.settings.musicPath.split('/').pop() : "Par défaut");
        renderSlideList();
        renderPreview();
    } catch (e) { 
        console.error('Load error:', e);
        console.error('Status:', e.status);
        console.error('Response:', e.responseText);
        alert('Erreur chargement: ' + e.status + ' - ' + e.responseText);
        currentProject = { settings: { musicPath: "", defaultDuration: 50000 }, slides: [] };
        renderSlideList();
        renderPreview();
    }
}

async function saveProjectToServer() {
    if (!currentProjectName) { alert('Aucun projet sélectionné'); return; }
    currentProject.settings.defaultDuration = $('#defaultDuration').val();
    console.log('Saving project:', currentProjectName, 'with', currentProject.slides.length, 'slides');
    try {
        const res = await $.ajax({
            url: '/api/save',
            type: 'POST',
            data: JSON.stringify({ projectName: currentProjectName, data: currentProject }),
            contentType: 'application/json'
        });
        console.log('Save response:', res);
    } catch (e) { 
        console.error('Save error:', e);
        alert('Erreur: ' + e.responseText); 
    }
}

function addNewSlide() {
    if (!currentProjectName) { alert('Aucun projet sélectionné'); return; }
    currentProject.slides.push({
        title: "Titre Slide",
        content: "Texte...",
        layout: "left",
        imagePath: "",
        bgColor: "#ffffff",
        textColor: "#000000",
        titleSize: "2.5rem",
        contentSize: "1.2rem",
        titleAlign: "center",
        contentAlign: "center",
        duration: currentProject.settings.defaultDuration || 50000
    });
    renderSlideList();
    renderPreview();
    openEditModal(currentProject.slides.length - 1);
    saveProjectToServer();
}

function renderSlideList() {
    const $list = $('#slidesList');
    $list.empty();
    $('#slideCount').text(currentProject.slides.length);
    currentProject.slides.forEach((slide, i) => {
        const $item = $('<div class="list-group-item slide-item d-flex justify-content-between align-items-center"><span>' + (i + 1) + '. ' + slide.title + '</span><button class="btn btn-sm btn-link text-danger btn-del" data-index="' + i + '"><i class="fas fa-trash"></i></button></div>');
        $item.on('click', function(e) { if (!$(e.target).closest('.btn-del').length) openEditModal(i); });
        $item.find('.btn-del').on('click', function() {
            slideToDelete = i;
            document.getElementById('deleteSlideModal').style.display = 'flex';
        });
        $list.append($item);
    });
}

$('#confirmDeleteSlide').on('click', function() {
    if (slideToDelete !== null) {
        currentProject.slides.splice(slideToDelete, 1);
        renderSlideList();
        renderPreview();
        saveProjectToServer();
        slideToDelete = null;
    }
    document.getElementById('deleteSlideModal').style.display = 'none';
});

function openEditModal(index) {
    const slide = currentProject.slides[index];
    $('#editIndex').val(index);
    $('#inpTitle').val(slide.title);
    $('#inpContent').val(slide.content);
    $('#inpLayout').val(slide.layout || 'left');
    $('#inpBgColor').val(slide.bgColor || '#ffffff');
    $('#inpTextColor').val(slide.textColor || '#000000');
    $('#inpDuration').val(slide.duration);
    $('#inpTitleSize').val(parseFloat(slide.titleSize || '2.5'));
    $('#titleSizeVal').text(slide.titleSize || '2.5rem');
    $('#inpContentSize').val(parseFloat(slide.contentSize || '1.2'));
    $('#contentSizeVal').text(slide.contentSize || '1.2rem');
    $('#inpTitleAlign').val(slide.titleAlign || 'center');
    $('#inpContentAlign').val(slide.contentAlign || 'center');
    $('#currentImageName').text(slide.imagePath ? slide.imagePath.split('/').pop() : "");
    editModal.show();
}

async function applySlideChanges() {
    const idx = $('#editIndex').val();
    const slide = currentProject.slides[idx];
    slide.title = $('#inpTitle').val();
    slide.content = $('#inpContent').val();
    slide.layout = $('#inpLayout').val();
    slide.bgColor = $('#inpBgColor').val();
    slide.textColor = $('#inpTextColor').val();
    slide.duration = $('#inpDuration').val();
    slide.titleSize = $('#inpTitleSize').val() + 'rem';
    slide.contentSize = $('#inpContentSize').val() + 'rem';
    slide.titleAlign = $('#inpTitleAlign').val();
    slide.contentAlign = $('#inpContentAlign').val();

    const file = $('#inpImage')[0].files[0];
    if (file) {
        const formData = new FormData();
        formData.append('projectName', currentProjectName);
        formData.append('image', file);
        try {
            console.log('Uploading image for project:', currentProjectName);
            const res = await $.ajax({ url: '/api/upload', type: 'POST', data: formData, processData: false, contentType: false });
            console.log('Upload response:', res);
            slide.imagePath = res.imagePath;
        } catch (e) { 
            console.error('Upload error:', e);
            alert('Erreur upload: ' + e.responseText); 
        }
    }

    editModal.hide();
    renderSlideList();
    renderPreview();
    saveProjectToServer();
}

function renderPreview() {
    const $inner = $('#preview-inner');
    const $bottom = $('#bottomSlidesList');
    $inner.empty();
    $bottom.empty();

    currentProject.slides.forEach((slide, i) => {
        const $item = $('<div class="carousel-item h-100 ' + (i === 0 ? 'active' : '') + '"></div>');
        const $wrap = $('<div class="d-flex w-100 h-100"></div>');
        const paragraphs = (slide.content || '').split('\n').filter(p => p.trim() !== '').map(p => '<p style="margin-bottom:1rem; font-size:' + slide.contentSize + '; line-height:1.6;">' + p + '</p>').join('');
        const $text = $('<div class="p-5 flex-grow-1 d-flex flex-column justify-content-center" style="background:' + slide.bgColor + '; color:' + slide.textColor + '; text-align:' + slide.contentAlign + '; align-items:' + slide.titleAlign + '; width:' + ((slide.layout !== 'none' && slide.imagePath) ? '50%' : '100%') + ';"><h1 style="font-size:' + slide.titleSize + '; font-weight:800; margin-bottom:1.5rem;">' + slide.title + '</h1><div style="width:100%">' + paragraphs + '</div></div>');

        let imgPath = slide.imagePath;
        if (imgPath && !imgPath.startsWith('/') && !imgPath.startsWith('http')) { imgPath = '/' + imgPath; }

        if (slide.layout !== 'none' && slide.imagePath) {
            const $imgWrap = $('<div class="w-50 h-100" style="background-image:url(\'' + imgPath + '\'); background-size:cover; background-position:center;"></div>');
            // layout "right" = Text Left / Image Right
            // layout "left" = Text Right / Image Left
            if (slide.layout === 'right') { $wrap.append($text).append($imgWrap); }
            else if (slide.layout === 'left') { $wrap.append($imgWrap).append($text); }
        } else { $wrap.append($text); }
        $item.append($wrap);
        $inner.append($item);

        const $thumb = $('<div class="bottom-slide-thumb ' + (i === 0 ? 'active' : '') + '" data-index="' + i + '">' + (slide.imagePath ? '<img src="' + imgPath + '">' : '<div style="width:100%;height:100%;background:' + slide.bgColor + '"></div>') + '<div class="thumb-title">' + (i + 1) + '. ' + slide.title + '</div></div>');
        $thumb.on('click', function() {
            bootstrap.Carousel.getOrCreateInstance($('#preview-slider')[0]).to(i);
            $('.bottom-slide-thumb').removeClass('active');
            $thumb.addClass('active');
        });
        $bottom.append($thumb);
    });
}
</script>
@endsection
