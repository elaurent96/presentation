<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        @csrf
        
        <div class="mb-3">
            <label style="color: #a0a0a0; font-size: 14px;">Nom</label>
            <input type="text" wire:model="name" class="form-control" style="background: #333; border: 1px solid #444; color: white; padding: 10px 15px;" placeholder="Votre nom" required autofocus>
            @error('name')
                <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label style="color: #a0a0a0; font-size: 14px;">Email</label>
            <input type="email" wire:model="email" class="form-control" style="background: #333; border: 1px solid #444; color: white; padding: 10px 15px;" placeholder="Email" required>
            @error('email')
                <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label style="color: #a0a0a0; font-size: 14px;">Mot de passe</label>
            <input type="password" wire:model="password" class="form-control" style="background: #333; border: 1px solid #444; color: white; padding: 10px 15px;" placeholder="Mot de passe" required>
            @error('password')
                <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label style="color: #a0a0a0; font-size: 14px;">Confirmer le mot de passe</label>
            <input type="password" wire:model="password_confirmation" class="form-control" style="background: #333; border: 1px solid #444; color: white; padding: 10px 15px;" placeholder="Confirmer le mot de passe" required>
            @error('password_confirmation')
                <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4" style="flex-wrap: wrap; gap: 10px;">
            <a href="{{ route('login') }}" wire:navigate style="color: #0d6efd; font-size: 14px; text-decoration: none;">
                Déjà un compte ? Se connecter
            </a>
            
            <button type="submit" class="btn btn-primary" style="background: #0d6efd; border: none; padding: 8px 20px; font-size: 14px;">
                Créer un compte
            </button>
        </div>
    </form>
</div>
