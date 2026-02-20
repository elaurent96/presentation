@extends('layouts.shadcn')

@section('title', 'Présentations')

@section('styles')
<style>
@media (max-width: 480px) {
    .card .btn { padding: 6px 10px; font-size: 12px; }
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

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="page-header">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <h1>Mes Présentations</h1>
                <div style="display: flex; gap: 10px;">
                    <a href="/presentation" class="btn btn-outline" target="_blank">
                        <i class="fas fa-list"></i> Playlist
                    </a>
                    <a href="/presentation?project=all" class="btn btn-primary" target="_blank">
                        <i class="fas fa-play"></i> Tout lire
                    </a>
                    <button class="btn btn-primary" onclick="document.getElementById('newProjectModal').style.display='flex'">
                        <i class="fas fa-plus"></i>Nouvelle présentation
                    </button>
                </div>
            </div>
        </div>
        <div class="page-content">
            @if(count($projects) > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    @foreach($projects as $project)
                    <div class="card" style="padding: 0; overflow: hidden;">
                        <div style="background: #1a1a1a; padding: 40px 20px; text-align: center;">
                            <i class="fas fa-folder" style="font-size: 48px; color: white;"></i>
                        </div>
                        <div style="padding: 20px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 18px;">{{ $project['name'] }}</h3>
                            <p style="color: #666; margin: 0 0 15px 0; font-size: 14px;">
                                {{ $project['slides'] }} slides
                            </p>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('editor', ['project' => $project['name']]) }}" class="btn btn-primary" style="flex: 1;">
                                    <i class="fas fa-edit me-1"></i> Éditer
                                </a>
                                <a href="/presentation?project={{ $project['name'] }}" target="_blank" class="btn btn-outline" style="padding: 8px 12px;">
                                    <i class="fas fa-play"></i>
                                </a>
                                <button type="button" class="btn btn-danger" style="padding: 8px 12px;" onclick="confirmDelete('{{ addslashes($project['name']) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="card" style="text-align: center; padding: 60px;">
                    <i class="fas fa-folder-open" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                    <p style="color: #666; margin-bottom: 20px;">Aucune présentation. Créez votre première présentation!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- New Project Modal -->
<div id="newProjectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;">Nouvelle Présentation</h3>
        <form action="{{ route('projects.create') }}" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nom de la présentation</label>
                <input type="text" name="name" class="input" placeholder="Ma Présentation" required>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('newProjectModal').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 90%;">
        <h3 style="margin-bottom: 15px;">Confirmer la suppression</h3>
        <p style="margin-bottom: 20px; color: #666;">Êtes-vous sûr de vouloir supprimer la présentation <strong id="deleteProjectName"></strong> ? Cette action est irréversible.</p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('deleteModal').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(projectName) {
    document.getElementById('deleteProjectName').textContent = projectName;
    document.getElementById('deleteForm').action = '/projects/' + encodeURIComponent(projectName);
    document.getElementById('deleteModal').style.display = 'flex';
}

window.onclick = function(event) {
    if (event.target.id === 'newProjectModal') {
        document.getElementById('newProjectModal').style.display = 'none';
    }
    if (event.target.id === 'deleteModal') {
        document.getElementById('deleteModal').style.display = 'none';
    }
}
</script>

@if(session('success'))
<script>showToast("{{ session('success') }}", 'success')</script>
@endif
@if(session('error'))
<script>showToast("{{ session('error') }}", 'error')</script>
@endif
@endsection
