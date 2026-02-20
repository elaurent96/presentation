@extends('layouts.shadcn')

@section('title', 'Playlist')

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

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h1>Playlist</h1>
                <div style="display: flex; gap: 10px;">
                    <a href="/presentation" class="btn btn-outline" target="_blank">
                        <i class="fas fa-list"></i> Mode playlist
                    </a>
                    <a href="/presentation?project=all" class="btn btn-primary" target="_blank">
                        <i class="fas fa-play"></i> Mode tout lire
                    </a>
                </div>
            </div>
        </div>
        <div class="page-content">
            <div class="card">
                <p style="color: #666; margin-bottom: 20px;">
                    Glissez les projets pour définir l'ordre de lecture. 
                    Utilisez le switch pour inclure/exclure un projet de la playlist.
                </p>
                
@php
$playlistKeys = array_map(function($p) { 
    return $p['user_id'] . '/' . $p['name']; 
}, $playlist);
@endphp

<div id="playlist-container">
    @foreach($orderedProjects as $project)
    @php $isInPlaylist = in_array($project['user_id'] . '/' . $project['name'], $playlistKeys); @endphp
    <div class="playlist-item" data-user-id="{{ $project['user_id'] }}" data-name="{{ $project['name'] }}" style="display: flex; align-items: center; gap: 15px; padding: 15px; border-bottom: 1px solid #e5e5e5; cursor: grab;">
        <i class="fas fa-grip-vertical" style="color: #999;"></i>
        <div style="flex: 1;">
            <div style="font-weight: 600;">{{ $project['name'] }}</div>
            <small style="color: #666;">{{ $project['slides'] }} slides</small>
        </div>
        <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
            <input type="checkbox" class="playlist-toggle" 
                data-key="{{ $project['user_id'] . '/' . $project['name'] }}"
                {{ $isInPlaylist ? 'checked' : '' }}
                onchange="togglePlaylist(this)">
            <span class="switch-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: {{ $isInPlaylist ? '#1a1a1a' : '#ccc' }}; transition: .4s; border-radius: 24px;">
                <span class="switch-thumb" style="position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; {{ $isInPlaylist ? 'transform: translateX(26px);' : '' }}"></span>
            </span>
        </label>
    </div>
    @endforeach
</div>
                
                @if(count($orderedProjects) == 0)
                <p style="text-align: center; color: #666; padding: 40px;">
                    Aucun projet disponible. Créez des projets d'abord.
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.switch input { opacity: 0; width: 0; height: 0; }
.playlist-item { user-select: none; transition: transform 0.2s, box-shadow 0.2s; }
.playlist-item:active { cursor: grabbing; }
.playlist-item.dragging { opacity: 0.5; }
.playlist-item.drag-over { border-top: 2px solid #0d6efd; transform: translateY(2px); }
@media (max-width: 480px) {
    .playlist-item { padding: 10px; gap: 10px; }
    .playlist-item .fas.fa-grip-vertical { display: none; }
    .switch { width: 40px; height: 20px; }
    .switch-thumb { height: 14px; width: 14px; left: 3px; bottom: 3px; }
    .switch-thumb[style*="translateX(26px)"] { transform: translateX(20px) !important; }
}
</style>

<script>
var playlist = {!! json_encode($playlistKeys) !!};

function togglePlaylist(checkbox) {
    var key = checkbox.dataset.key;
    var enabled = checkbox.checked;
    var slider = checkbox.nextElementSibling;
    var thumb = slider.querySelector('.switch-thumb');
    
    if (enabled) {
        if (playlist.indexOf(key) === -1) {
            playlist.push(key);
        }
        slider.style.backgroundColor = '#1a1a1a';
        thumb.style.transform = 'translateX(26px)';
    } else {
        playlist = playlist.filter(function(p) { return p !== key; });
        slider.style.backgroundColor = '#ccc';
        thumb.style.transform = 'translateX(0)';
    }
    
    if (Notification.permission === 'granted') {
        new Notification('Playlist', { body: enabled ? 'Projet ajouté' : 'Projet retiré' });
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                new Notification('Playlist', { body: enabled ? 'Projet ajouté' : 'Projet retiré' });
            }
        });
    }
    
    savePlaylist();
}

function savePlaylist() {
    var order = playlist.map(function(key) {
        var parts = key.split('/');
        return { user_id: parseInt(parts[0]), name: parts[1] };
    });
    
    fetch('{!! route("playlist.reorder") !!}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{!! csrf_token() !!}'
        },
        body: JSON.stringify({ order: order })
    });
}

// Simple drag and drop
var draggedItem = null;
var draggedOverItem = null;

document.querySelectorAll('.playlist-item').forEach(function(item) {
    item.setAttribute('draggable', 'true');
    
    item.addEventListener('dragstart', function(e) {
        draggedItem = item;
        e.dataTransfer.effectAllowed = 'move';
        item.style.opacity = '0.5';
        item.classList.add('dragging');
    });
    
    item.addEventListener('dragend', function(e) {
        item.style.opacity = '1';
        item.classList.remove('dragging');
        document.querySelectorAll('.playlist-item').forEach(function(i) {
            i.classList.remove('drag-over');
        });
    });
    
    item.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (draggedItem && draggedItem !== item) {
            item.classList.add('drag-over');
        }
    });
    
    item.addEventListener('dragleave', function(e) {
        item.classList.remove('drag-over');
    });
    
    item.addEventListener('drop', function(e) {
        e.preventDefault();
        item.classList.remove('drag-over');
        if (draggedItem && draggedItem !== item) {
            var container = document.getElementById('playlist-container');
            var items = Array.from(container.querySelectorAll('.playlist-item'));
            var draggedIndex = items.indexOf(draggedItem);
            var dropIndex = items.indexOf(item);
            
            if (draggedIndex < dropIndex) {
                item.parentNode.insertBefore(draggedItem, item.nextSibling);
            } else {
                item.parentNode.insertBefore(draggedItem, item);
            }
            
            if (Notification.permission === 'granted') {
                new Notification('Playlist', { body: 'Ordre mis à jour' });
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission();
            }
            
            savePlaylist();
        }
    });
});
</script>

@if(session('success'))
<script>showToast("{{ session('success') }}", 'success')</script>
@endif
@endsection
