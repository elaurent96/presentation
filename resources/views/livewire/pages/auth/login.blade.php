<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="login">
        @csrf
        
        <div class="mb-3">
            <label style="color: #a0a0a0; font-size: 14px;">Email</label>
            <input type="email" wire:model="form.email" class="form-control" style="background: #333; border: 1px solid #444; color: white; padding: 10px 15px;" placeholder="Email" required autofocus>
            @error('form.email')
                <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label style="color: #a0a0a0; font-size: 14px;">Mot de passe</label>
            <input type="password" wire:model="form.password" class="form-control" style="background: #333; border: 1px solid #444; color: white; padding: 10px 15px;" placeholder="Mot de passe" required>
            @error('form.password')
                <span style="color: #dc3545; font-size: 12px;">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" wire:model="form.remember" id="remember" class="form-check-input" style="background: #333; border-color: #444;">
            <label class="form-check-label" for="remember" style="color: #a0a0a0; font-size: 14px;">
                Se souvenir de moi
            </label>
        </div>

        <div class="d-flex justify-content-between align-items-center" style="flex-wrap: wrap; gap: 10px;">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate style="color: #0d6efd; font-size: 14px; text-decoration: none;">
                    Mot de passe oublié ?
                </a>
            @endif
            
            <button type="submit" class="btn btn-primary" style="background: #0d6efd; border: none; padding: 8px 20px; font-size: 14px;">
                Connexion
            </button>
        </div>
    </form>
    
    <div class="text-center mt-4" style="border-top: 1px solid #444; padding-top: 20px;">
        <a href="{{ route('register') }}" wire:navigate style="color: #a0a0a0; font-size: 14px; text-decoration: none;">
            Pas de compte ? <span style="color: #0d6efd;">Créer un compte</span>
        </a>
    </div>
</div>
