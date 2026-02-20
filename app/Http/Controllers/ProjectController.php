<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProjectController extends Controller
{
    protected $assetsPath;
    
    public function __construct()
    {
        $this->assetsPath = public_path('assets');
    }
    
    public function list()
    {
        $user = Auth::user();
        $projects = [];
        
        if ($user->isAdmin()) {
            // Get all projects
            if (File::exists($this->assetsPath)) {
                foreach (File::directories($this->assetsPath) as $userFolder) {
                    $userId = str_replace('user_', '', basename($userFolder));
                    $userName = \App\Models\User::find($userId)?->name ?? 'Unknown';
                    foreach (File::directories($userFolder) as $projectFolder) {
                        $dataFile = $projectFolder . '/data.json';
                        if (File::exists($dataFile)) {
                            $data = json_decode(File::get($dataFile), true);
                            $projects[] = [
                                'name' => basename($projectFolder),
                                'slides' => count($data['slides'] ?? [])
                            ];
                        }
                    }
                }
            }
        } else {
            $userFolder = $this->assetsPath . '/user_' . $user->id;
            if (File::exists($userFolder)) {
                foreach (File::directories($userFolder) as $projectFolder) {
                    $dataFile = $projectFolder . '/data.json';
                    if (File::exists($dataFile)) {
                        $data = json_decode(File::get($dataFile), true);
                        $projects[] = [
                            'name' => basename($projectFolder),
                            'slides' => count($data['slides'] ?? [])
                        ];
                    }
                }
            }
        }
        
        return view('presentation.list', compact('projects'));
    }
    
    public function create(Request $request)
    {
        $user = Auth::user();
        $projectName = $request->input('name');
        
        $projectDir = $this->assetsPath . '/user_' . $user->id . '/' . $projectName;
        
        if (File::exists($projectDir)) {
            return redirect()->route('presentation.list')->with('error', 'Le projet existe déjà');
        }
        
        File::ensureDirectoryExists($projectDir);
        
        // Create default data.json
        $defaultData = [
            'settings' => [
                'musicPath' => '',
                'defaultDuration' => 50000
            ],
            'slides' => []
        ];
        
        File::put($projectDir . '/data.json', json_encode($defaultData, JSON_PRETTY_PRINT));
        
        return redirect()->route('editor', ['project' => $projectName])->with('success', 'Projet créé');
    }
    
    public function destroy($project)
    {
        $user = Auth::user();
        
        // Find project
        $projectPath = null;
        
        if ($user->isAdmin()) {
            // Search in all folders
            foreach (File::directories($this->assetsPath) as $userFolder) {
                $testPath = $userFolder . '/' . $project;
                if (File::exists($testPath)) {
                    $projectPath = $testPath;
                    break;
                }
            }
        } else {
            $projectPath = $this->assetsPath . '/user_' . $user->id . '/' . $project;
        }
        
        if ($projectPath && File::exists($projectPath)) {
            File::deleteDirectory($projectPath);
            return redirect()->route('presentation.list')->with('success', 'Projet supprimé');
        }
        
        return redirect()->route('presentation.list')->with('error', 'Projet non trouvé');
    }
    
    public function duplicate(Request $request, $project)
    {
        $user = Auth::user();
        $newName = $request->input('new_name');
        
        $sourcePath = $this->assetsPath . '/user_' . $user->id . '/' . $project;
        
        if (!File::exists($sourcePath)) {
            return redirect()->route('presentation.list')->with('error', 'Projet non trouvé');
        }
        
        $destPath = $this->assetsPath . '/user_' . $user->id . '/' . $newName;
        
        if (File::exists($destPath)) {
            return redirect()->route('presentation.list')->with('error', 'Le nom existe déjà');
        }
        
        File::copyDirectory($sourcePath, $destPath);
        
        return redirect()->route('presentation.list')->with('success', 'Projet dupliqué');
    }
    
    public function editor($project = null)
    {
        $user = Auth::user();
        $projects = [];
        
        // Get list of projects for dropdown
        $userFolder = $this->assetsPath . '/user_' . $user->id;
        if (File::exists($userFolder)) {
            foreach (File::directories($userFolder) as $folder) {
                $projects[] = basename($folder);
            }
        }
        
        return view('editor', compact('projects', 'project'));
    }
    
    public function presentation()
    {
        $user = Auth::user();
        $projects = [];
        
        if ($user->isAdmin()) {
            if (File::exists($this->assetsPath)) {
                foreach (File::directories($this->assetsPath) as $userFolder) {
                    $userId = str_replace('user_', '', basename($userFolder));
                    foreach (File::directories($userFolder) as $projectFolder) {
                        $dataFile = $projectFolder . '/data.json';
                        if (File::exists($dataFile)) {
                            $data = json_decode(File::get($dataFile), true);
                            $projects[] = [
                                'name' => basename($projectFolder),
                                'user_id' => $userId,
                                'slides' => count($data['slides'] ?? [])
                            ];
                        }
                    }
                }
            }
        } else {
            $userFolder = $this->assetsPath . '/user_' . $user->id;
            if (File::exists($userFolder)) {
                foreach (File::directories($userFolder) as $projectFolder) {
                    $dataFile = $projectFolder . '/data.json';
                    if (File::exists($dataFile)) {
                        $data = json_decode(File::get($dataFile), true);
                        $projects[] = [
                            'name' => basename($projectFolder),
                            'user_id' => $user->id,
                            'slides' => count($data['slides'] ?? [])
                        ];
                    }
                }
            }
        }
        
        return view('presentation.list', compact('projects'));
    }
    
    public function viewProject($project)
    {
        $user = Auth::user();
        
        $projectPath = null;
        
        if ($user->isAdmin()) {
            foreach (File::directories($this->assetsPath) as $userFolder) {
                $testPath = $userFolder . '/' . $project;
                if (File::exists($testPath)) {
                    $projectPath = $testPath;
                    break;
                }
            }
        } else {
            $projectPath = $this->assetsPath . '/user_' . $user->id . '/' . $project;
        }
        
        if (!$projectPath || !File::exists($projectPath)) {
            abort(404);
        }
        
        $data = json_decode(File::get($projectPath . '/data.json'), true);
        
        return view('presentation.view', compact('project', 'data'));
    }
}
