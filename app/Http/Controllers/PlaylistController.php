<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class PlaylistController extends Controller
{
    protected $playlistFile;
    
    public function __construct()
    {
        $this->playlistFile = public_path('assets/playlist.json');
    }
    
    public function index()
    {
        $user = Auth::user();
        $assetsPath = public_path('assets');
        
        // Get all projects
        $projects = [];
        
        if ($user->isAdmin()) {
            // Admin sees all
            if (File::exists($assetsPath)) {
                foreach (File::directories($assetsPath) as $userFolder) {
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
            // Lambda sees only their
            $userFolder = $assetsPath . '/user_' . $user->id;
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
        
        // Get playlist order
        $playlist = [];
        if (File::exists($this->playlistFile)) {
            $playlist = json_decode(File::get($this->playlistFile), true) ?? [];
        }
        
        // Filter projects based on user
        $userProjectNames = array_map(function($p) { 
            return $p['user_id'] . '/' . $p['name']; 
        }, $projects);
        
        // Sort by playlist order
        $orderedProjects = [];
        $remainingProjects = $projects;
        
        foreach ($playlist as $item) {
            $key = $item['user_id'] . '/' . $item['name'];
            foreach ($remainingProjects as $idx => $p) {
                $pKey = $p['user_id'] . '/' . $p['name'];
                if ($pKey === $key) {
                    $orderedProjects[] = $p;
                    unset($remainingProjects[$idx]);
                    break;
                }
            }
        }
        
        // Add remaining projects
        $orderedProjects = array_merge($orderedProjects, array_values($remainingProjects));
        
        // Mark which are in playlist
        $playlistUserProject = array_map(function($p) { 
            return $p['user_id'] . '/' . $p['name']; 
        }, $orderedProjects);
        
        return view('playlist.index', compact('orderedProjects', 'playlist'));
    }
    
    public function reorder(Request $request)
    {
        $order = $request->input('order', []);
        File::put($this->playlistFile, json_encode($order, JSON_PRETTY_PRINT));
        return response()->json(['success' => true]);
    }
    
    public function play(Request $request)
    {
        $mode = $request->query('mode', 'playlist');
        $user = Auth::user();
        $assetsPath = public_path('assets');
        
        // Get playlist
        $playlist = [];
        if (File::exists($this->playlistFile)) {
            $playlist = json_decode(File::get($this->playlistFile), true) ?? [];
        }
        
        $projectsData = [];
        
        if ($user->isAdmin()) {
            if (File::exists($assetsPath)) {
                foreach (File::directories($assetsPath) as $userFolder) {
                    $userId = str_replace('user_', '', basename($userFolder));
                    foreach (File::directories($userFolder) as $projectFolder) {
                        $projectName = basename($projectFolder);
                        $dataFile = $projectFolder . '/data.json';
                        
                        if ($mode === 'all') {
                            // Mode all: get all projects in order
                            if (File::exists($dataFile)) {
                                $data = json_decode(File::get($dataFile), true);
                                $projectsData[] = [
                                    'name' => $projectName,
                                    'user_id' => $userId,
                                    'data' => $data
                                ];
                            }
                        } else {
                            // Mode playlist: only get projects in playlist
                            $inPlaylist = false;
                            foreach ($playlist as $item) {
                                if ($item['user_id'] == $userId && $item['name'] === $projectName) {
                                    $inPlaylist = true;
                                    break;
                                }
                            }
                            
                            if ($inPlaylist && File::exists($dataFile)) {
                                $data = json_decode(File::get($dataFile), true);
                                $projectsData[] = [
                                    'name' => $projectName,
                                    'user_id' => $userId,
                                    'data' => $data
                                ];
                            }
                        }
                    }
                }
            }
        } else {
            $userFolder = $assetsPath . '/user_' . $user->id;
            if (File::exists($userFolder)) {
                foreach (File::directories($userFolder) as $projectFolder) {
                    $projectName = basename($projectFolder);
                    $dataFile = $projectFolder . '/data.json';
                    
                    if ($mode === 'all') {
                        if (File::exists($dataFile)) {
                            $data = json_decode(File::get($dataFile), true);
                            $projectsData[] = [
                                'name' => $projectName,
                                'user_id' => $user->id,
                                'data' => $data
                            ];
                        }
                    } else {
                        $inPlaylist = false;
                        foreach ($playlist as $item) {
                            if ($item['user_id'] == $user->id && $item['name'] === $projectName) {
                                $inPlaylist = true;
                                break;
                            }
                        }
                        
                        if ($inPlaylist && File::exists($dataFile)) {
                            $data = json_decode(File::get($dataFile), true);
                            $projectsData[] = [
                                'name' => $projectName,
                                'user_id' => $user->id,
                                'data' => $data
                            ];
                        }
                    }
                }
            }
        }
        
        // Sort by playlist order if mode=playlist
        if ($mode !== 'all' && !empty($playlist)) {
            $sorted = [];
            foreach ($playlist as $item) {
                foreach ($projectsData as $idx => $p) {
                    if ($p['user_id'] == $item['user_id'] && $p['name'] === $item['name']) {
                        $sorted[] = $p;
                        unset($projectsData[$idx]);
                        break;
                    }
                }
            }
            $projectsData = array_merge($sorted, array_values($projectsData));
        }
        
        return view('playlist.play', compact('projectsData', 'mode'));
    }
}
