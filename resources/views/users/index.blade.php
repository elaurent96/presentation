@extends('layouts.shadcn')

@section('title', 'Utilisateurs')

@section('styles')
<style>
@media (max-width: 600px) {
    .table { font-size: 12px; }
    .table th, .table td { padding: 6px 4px; }
    .btn-sm { padding: 3px 6px; font-size: 11px; }
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
            <a href="{{ route('playlist.index') }}" class="{{ request()->routeIs('playlist.*') ? 'active' : '' }}">
                <i class="fas fa-list"></i> <span>Playlists</span>
            </a>
            <a href="{{ route('presentation.list') }}" class="{{ request()->routeIs('presentation.*') ? 'active' : '' }}">
                <i class="fas fa-play"></i> <span>Présentations</span>
            </a>
            @auth
                @if(Auth::user()->isAdmin())
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Gestion des utilisateurs</h1>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i> Nouvel utilisateur
                </button>
            </div>
        </div>
        <div class="page-content">
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-lambda' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    @if($user->id !== Auth::id())
                                    <button class="btn btn-sm btn-outline" style="padding: 4px 8px;" onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm" style="padding: 4px 8px; background: #dc2626; color: white;" onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @else
                                    <span style="color: #999; font-size: 12px;">Vous</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div id="createModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 450px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;">Nouvel utilisateur</h3>
        <form id="createForm" action="{{ route('users.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Nom</label>
                <input type="text" name="name" class="input" required minlength="2">
                @error('name')
                <small style="color: #dc2626;">{{ $message }}</small>
                @enderror
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Email</label>
                <input type="email" name="email" class="input" required>
                @error('email')
                <small style="color: #dc2626;">{{ $message }}</small>
                @enderror
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Mot de passe</label>
                <input type="password" name="password" class="input" required minlength="8">
                @error('password')
                <small style="color: #dc2626;">{{ $message }}</small>
                @enderror
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" class="input" required>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Rôle</label>
                <select name="role" class="input">
                    <option value="lambda">Lambda</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeCreateModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 450px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;">Modifier l'utilisateur</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Nom</label>
                <input type="text" name="name" id="editName" class="input" required minlength="2">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Email</label>
                <input type="email" name="email" id="editEmail" class="input" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Nouveau mot de passe (laisser vide pour unchanged)</label>
                <input type="password" name="password" class="input" minlength="8">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Rôle</label>
                <select name="role" id="editRole" class="input">
                    <option value="lambda">Lambda</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 400px; max-width: 90%;">
        <h3 style="margin-bottom: 15px;">Confirmer la suppression</h3>
        <p style="margin-bottom: 20px; color: #666;">Êtes-vous sûr de vouloir supprimer l'utilisateur <strong id="deleteUserName"></strong> ? Cette action est irréversible.</p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Annuler</button>
                <button type="submit" class="btn btn-danger">Supprimer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
}

function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
}

function openEditModal(id, name, email, role) {
    document.getElementById('editForm').action = '/admin/users/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value = role;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function confirmDelete(id, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteForm').action = '/admin/users/' + id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals on outside click
window.onclick = function(event) {
    if (event.target.id === 'createModal') closeCreateModal();
    if (event.target.id === 'editModal') closeEditModal();
    if (event.target.id === 'deleteModal') closeDeleteModal();
}
</script>

@if(session('success'))
<script>showToast("{{ session('success') }}", 'success')</script>
@endif
@if(session('error'))
<script>showToast("{{ session('error') }}", 'error')</script>
@endif
@endsection
