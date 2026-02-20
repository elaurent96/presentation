<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    protected $assetsPath;

    public function __construct()
    {
        $this->assetsPath = public_path('assets');
    }

    public function index()
    {
        $user = Auth::user();
        $folder = $this->assetsPath . '/user_' . $user->id;
        
        if (!File::exists($folder)) {
            return response()->json([]);
        }
        
        $folders = File::directories($folder);
        $projectNames = [];
        
        foreach ($folders as $folder) {
            $dataFile = $folder . '/data.json';
            if (File::exists($dataFile)) {
                $projectNames[] = basename($folder);
            }
        }
        
        return response()->json($projectNames);
    }

    public function all()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $projectsData = [];
            if (File::exists($this->assetsPath)) {
                foreach (File::directories($this->assetsPath) as $userFolder) {
                    if (File::directories($userFolder)) {
                        foreach (File::directories($userFolder) as $projectFolder) {
                            $dataFile = $projectFolder . '/data.json';
                            if (File::exists($dataFile)) {
                                try {
                                    $data = json_decode(File::get($dataFile), true);
                                    $projectsData[] = [
                                        'name' => basename($projectFolder),
                                        'data' => $data
                                    ];
                                } catch (\Exception $e) {}
                            }
                        }
                    }
                }
            }
            return response()->json($projectsData);
        }
        
        return $this->index();
    }
    
    public function playlist()
    {
        $user = Auth::user();
        $playlistFile = public_path('assets/playlist.json');
        
        $playlist = [];
        if (File::exists($playlistFile)) {
            $playlist = json_decode(File::get($playlistFile), true) ?? [];
        }
        
        if (empty($playlist)) {
            return response()->json([]);
        }
        
        $projectsData = [];
        
        if ($user->isAdmin()) {
            if (File::exists($this->assetsPath)) {
                foreach ($playlist as $item) {
                    $userId = $item['user_id'];
                    $projectName = $item['name'];
                    $dataFile = $this->assetsPath . '/user_' . $userId . '/' . $projectName . '/data.json';
                    
                    if (File::exists($dataFile)) {
                        try {
                            $data = json_decode(File::get($dataFile), true);
                            $projectsData[] = [
                                'name' => $projectName,
                                'data' => $data
                            ];
                        } catch (\Exception $e) {}
                    }
                }
            }
        } else {
            foreach ($playlist as $item) {
                if ($item['user_id'] == $user->id) {
                    $projectName = $item['name'];
                    $dataFile = $this->assetsPath . '/user_' . $user->id . '/' . $projectName . '/data.json';
                    
                    if (File::exists($dataFile)) {
                        try {
                            $data = json_decode(File::get($dataFile), true);
                            $projectsData[] = [
                                'name' => $projectName,
                                'data' => $data
                            ];
                        } catch (\Exception $e) {}
                    }
                }
            }
        }
        
        return response()->json($projectsData);
    }

    public function show($name)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $filePath = $this->assetsPath . '/user_' . $user->id . '/' . $name . '/data.json';
        
        if ($user->isAdmin()) {
            if (File::exists($this->assetsPath)) {
                foreach (File::directories($this->assetsPath) as $userFolder) {
                    $testPath = $userFolder . '/' . $name . '/data.json';
                    if (File::exists($testPath)) {
                        $filePath = $testPath;
                        break;
                    }
                }
            }
        }
        
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'Project not found'], 404);
        }
        
        try {
            $data = json_decode(File::get($filePath), true);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid project data'], 500);
        }
    }

    public function save(Request $request)
    {
        $request->validate([
            'projectName' => 'required|string',
            'data' => 'required|array'
        ]);

        $user = Auth::user();
        $projectDir = $this->assetsPath . '/user_' . $user->id . '/' . $request->projectName;
        
        Log::info('Saving project: ' . $request->projectName . ' for user: ' . $user->id . ' to: ' . $projectDir);
        
        File::ensureDirectoryExists($projectDir);
        
        $dataFile = $projectDir . '/data.json';
        File::put($dataFile, json_encode($request->data, JSON_PRETTY_PRINT));
        
        Log::info('Project saved to: ' . $dataFile);
        
        return response()->json(['success' => true]);
    }

    public function upload(Request $request)
    {
        try {
            // Force PHP limits
            ini_set('upload_max_filesize', '64M');
            ini_set('post_max_size', '64M');
            ini_set('memory_limit', '128M');
            
            Log::info('PHP upload limits', [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]);
            
            $user = Auth::user();
            $projectName = $request->projectName;
            
            Log::info('Upload request received', [
                'projectName' => $projectName, 
                'hasAudio' => $request->hasFile('audio'), 
                'hasImage' => $request->hasFile('image'),
                'allKeys' => $request->keys(),
                'files' => array_keys($_FILES),
                'post' => array_keys($_POST)
            ]);
            
            // Try to get file from raw input as fallback
            $audioFile = $request->file('audio');
            $imageFile = $request->file('image');
            
            // If files not found, check $_FILES directly
            if (!$audioFile && isset($_FILES['audio'])) {
                Log::info('Audio in _FILES: ', $_FILES['audio']);
                if ($_FILES['audio']['error'] === UPLOAD_ERR_OK) {
                    $audioFile = new \Illuminate\Http\UploadedFile(
                        $_FILES['audio']['tmp_name'],
                        $_FILES['audio']['name'],
                        $_FILES['audio']['type'],
                        $_FILES['audio']['error']
                    );
                }
            }
            
            if (!$imageFile && isset($_FILES['image'])) {
                Log::info('Image in _FILES: ', $_FILES['image']);
                if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imageFile = new \Illuminate\Http\UploadedFile(
                        $_FILES['image']['tmp_name'],
                        $_FILES['image']['name'],
                        $_FILES['image']['type'],
                        $_FILES['image']['error']
                    );
                }
            }
            
            if (!$projectName) {
                return response()->json(['error' => 'Project name required'], 400);
            }
            
            $response = [];

            if ($audioFile) {
                $type = 'music';
                $projectDir = $this->assetsPath . '/user_' . $user->id . '/' . $projectName;
                File::ensureDirectoryExists($projectDir);
                
                $dir = $projectDir . '/' . $type;
                File::ensureDirectoryExists($dir);
                
                $filename = time() . '-' . $audioFile->getClientOriginalName();
                $audioFile->move($dir, $filename);
                $fullPath = $dir . '/' . $filename;
                $response['audioPath'] = '/assets/user_' . $user->id . '/' . $projectName . '/' . $type . '/' . $filename;
                
                Log::info('Audio uploaded to: ' . $fullPath);
            }

            if ($imageFile) {
                $type = 'images';
                $projectDir = $this->assetsPath . '/user_' . $user->id . '/' . $projectName;
                File::ensureDirectoryExists($projectDir);
                
                $dir = $projectDir . '/' . $type;
                File::ensureDirectoryExists($dir);
                
                $filename = time() . '-' . $imageFile->getClientOriginalName();
                $imageFile->move($dir, $filename);
                $response['imagePath'] = '/assets/user_' . $user->id . '/' . $projectName . '/' . $type . '/' . $filename;
                
                Log::info('Image uploaded to: ' . $dir . '/' . $filename);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
