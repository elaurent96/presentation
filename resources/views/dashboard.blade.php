@extends('layouts.shadcn')

@section('title', 'Dashboard')

@section('styles')
<style>
@media (max-width: 480px) {
    .table { font-size: 12px; }
    .table th, .table td { padding: 6px 4px; }
    .btn-sm { padding: 4px 8px; font-size: 11px; }
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
            <h1>Dashboard</h1>
        </div>
        <div class="page-content">
            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 50px; height: 50px; background: #1a1a1a; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div>
                            <div style="color: #666; font-size: 14px;">Présentations</div>
                            <div style="font-size: 24px; font-weight: 600;">{{ $stats['projects'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 50px; height: 50px; background: #1a1a1a; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <div style="color: #666; font-size: 14px;">Slides</div>
                            <div style="font-size: 24px; font-weight: 600;">{{ $stats['presentations'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                @if(Auth::user()->isAdmin())
                <div class="card">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 50px; height: 50px; background: #1a1a1a; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div style="color: #666; font-size: 14px;">Utilisateurs</div>
                            <div style="font-size: 24px; font-weight: 600;">{{ $stats['users'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="card" style="margin-bottom: 30px;">
                <h3 style="margin-bottom: 20px;">Actions rapides</h3>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('presentation.list') }}" class="btn btn-outline">
                        <i class="fas fa-eye"></i> Voir les présentations
                    </a>
                    <a href="/presentation?project=all" class="btn btn-primary" target="_blank">
                        <i class="fas fa-play"></i> Lancer tout
                    </a>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 20px;">Mes projets</h3>
                @if(count($projects) > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Slides</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr>
                                <td>{{ $project['name'] }}</td>
                                <td>{{ count($project['data']['slides'] ?? []) }}</td>
                                <td>
                                    <a href="{{ route('editor') }}?project={{ $project['name'] }}" class="btn btn-outline btn-sm">
                                        Éditer
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="color: #666; text-align: center; padding: 40px;">Aucun projet. Créez votre premier projet !</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>showToast("{{ session('success') }}", 'success')</script>
@endif
@if(session('error'))
<script>showToast("{{ session('error') }}", 'error')</script>
@endif
@endsection
