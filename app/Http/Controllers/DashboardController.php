<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $assetsPath = public_path('assets');
        
        $stats = [
            'projects' => 0,
            'presentations' => 0,
            'users' => 0,
        ];
        
        $projects = [];
        
        if ($user->isAdmin()) {
            $stats['users'] = User::count();
            
            if (File::exists($assetsPath)) {
                $userFolders = File::directories($assetsPath);
                foreach ($userFolders as $userFolder) {
                    $projectFolders = File::directories($userFolder);
                    foreach ($projectFolders as $projectFolder) {
                        $dataFile = $projectFolder . '/data.json';
                        if (File::exists($dataFile)) {
                            $data = json_decode(File::get($dataFile), true);
                            $projects[] = [
                                'name' => basename($projectFolder),
                                'data' => $data,
                                'user_id' => str_replace('user_', '', basename($userFolder))
                            ];
                            $stats['projects']++;
                            $stats['presentations'] += count($data['slides'] ?? []);
                        }
                    }
                }
            }
        } else {
            $userFolder = $assetsPath . '/user_' . $user->id;
            if (File::exists($userFolder)) {
                $projectFolders = File::directories($userFolder);
                foreach ($projectFolders as $projectFolder) {
                    $dataFile = $projectFolder . '/data.json';
                    if (File::exists($dataFile)) {
                        $data = json_decode(File::get($dataFile), true);
                        $projects[] = [
                            'name' => basename($projectFolder),
                            'data' => $data
                        ];
                        $stats['projects']++;
                        $stats['presentations'] += count($data['slides'] ?? []);
                    }
                }
            }
        }
        
        return view('dashboard', compact('stats', 'projects'));
    }
    
    public function users()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }
    
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,lambda'
        ]);
        
        $user->role = $request->role;
        $user->save();
        
        return redirect()->route('users.index')->with('success', 'Rôle mis à jour avec succès');
    }
    
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,lambda'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role
        ]);
        
        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès');
    }
    
    public function updateUser(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,lambda'
        ];
        
        if ($request->password) {
            $rules['password'] = 'min:8|confirmed';
        }
        
        $request->validate($rules);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        
        if ($request->password) {
            $user->password = $request->password;
        }
        
        $user->save();
        
        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès');
    }
    
    public function destroyUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès');
    }
}
