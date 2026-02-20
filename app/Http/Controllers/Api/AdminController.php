<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rules;

/**
 * @OA\Info(
 *      title="Presentation Studio API",
 *      version="1.0.0",
 *      description="API for Presentation Studio - Admin Only",
 *      @OA\Contact(
 *          email="admin@presentation-studio.com"
 *      )
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="Authorization",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="TOKEN",
 *      description="Enter your 94-character API token"
 * )
 */
class AdminController extends Controller
{
    protected $assetsPath;
    protected $playlistFile;
    
    public function __construct()
    {
        $this->assetsPath = public_path('assets');
        $this->playlistFile = public_path('assets/playlist.json');
    }
    
    /**
     * @OA\Get(
     *      path="/api/admin/users",
     *      summary="Get all users",
     *      description="Returns list of all users (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="email", type="string"),
     *                  @OA\Property(property="role", type="string"),
     *                  @OA\Property(property="created_at", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function getUsers()
    {
        $users = User::all(['id', 'name', 'email', 'role', 'created_at']);
        return response()->json($users);
    }
    
    /**
     * @OA\Post(
     *      path="/api/admin/users",
     *      summary="Create a new user",
     *      description="Create a new user (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "email", "password"},
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="role", type="string", enum={"admin", "lambda"})
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User created successfully"
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'in:admin,lambda']
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'lambda'
        ]);
        
        return response()->json($user, 201);
    }
    
    /**
     * @OA\Put(
     *      path="/api/admin/users/{id}",
     *      summary="Update a user",
     *      description="Update an existing user (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="role", type="string", enum={"admin", "lambda"})
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User updated successfully"
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'role' => ['sometimes', 'string', 'in:admin,lambda']
        ]);
        
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('role')) {
            $user->role = $request->role;
        }
        if ($request->has('password') && $request->password) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        return response()->json($user);
    }
    
    /**
     * @OA\Delete(
     *      path="/api/admin/users/{id}",
     *      summary="Delete a user",
     *      description="Delete a user (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User deleted successfully"
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return response()->json(['message' => 'User deleted successfully']);
    }
    
    /**
     * @OA\Get(
     *      path="/api/admin/projects",
     *      summary="Get all projects",
     *      description="Returns list of all projects from all users (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="user_id", type="integer"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="slides", type="integer")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function getProjects()
    {
        $projects = [];
        
        if (File::exists($this->assetsPath)) {
            foreach (File::directories($this->assetsPath) as $userFolder) {
                $userId = str_replace('user_', '', basename($userFolder));
                foreach (File::directories($userFolder) as $projectFolder) {
                    $dataFile = $projectFolder . '/data.json';
                    if (File::exists($dataFile)) {
                        $data = json_decode(File::get($dataFile), true);
                        $projects[] = [
                            'user_id' => (int)$userId,
                            'name' => basename($projectFolder),
                            'slides' => count($data['slides'] ?? [])
                        ];
                    }
                }
            }
        }
        
        return response()->json($projects);
    }
    
    /**
     * @OA\Delete(
     *      path="/api/admin/projects/{userId}/{name}",
     *      summary="Delete a project",
     *      description="Delete a project from a user (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Project deleted successfully"
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only"),
     *      @OA\Response(response=404, description="Project not found")
     * )
     */
    public function deleteProject($userId, $name)
    {
        $projectPath = $this->assetsPath . '/user_' . $userId . '/' . $name;
        
        if (!File::exists($projectPath)) {
            return response()->json(['error' => 'Project not found'], 404);
        }
        
        File::deleteDirectory($projectPath);
        
        return response()->json(['message' => 'Project deleted successfully']);
    }
    
    /**
     * @OA\Get(
     *      path="/api/admin/playlist",
     *      summary="Get playlist",
     *      description="Returns the playlist order (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function getPlaylist()
    {
        $playlist = [];
        if (File::exists($this->playlistFile)) {
            $playlist = json_decode(File::get($this->playlistFile), true) ?? [];
        }
        return response()->json($playlist);
    }
    
    /**
     * @OA\Post(
     *      path="/api/admin/playlist",
     *      summary="Update playlist",
     *      description="Update the playlist order (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="user_id", type="integer"),
     *                  @OA\Property(property="name", type="string")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Playlist updated successfully"
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function updatePlaylist(Request $request)
    {
        $order = $request->all();
        File::put($this->playlistFile, json_encode($order, JSON_PRETTY_PRINT));
        
        return response()->json(['message' => 'Playlist updated successfully']);
    }
    
    /**
     * @OA\Get(
     *      path="/api/admin/stats",
     *      summary="Get statistics",
     *      description="Returns application statistics (Admin only)",
     *      security={{"Authorization": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="users_count", type="integer"),
     *              @OA\Property(property="projects_count", type="integer"),
     *              @OA\Property(property="slides_count", type="integer")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
    public function getStats()
    {
        $usersCount = User::count();
        
        $projectsCount = 0;
        $slidesCount = 0;
        
        if (File::exists($this->assetsPath)) {
            foreach (File::directories($this->assetsPath) as $userFolder) {
                foreach (File::directories($userFolder) as $projectFolder) {
                    $dataFile = $projectFolder . '/data.json';
                    if (File::exists($dataFile)) {
                        $projectsCount++;
                        $data = json_decode(File::get($dataFile), true);
                        $slidesCount += count($data['slides'] ?? []);
                    }
                }
            }
        }
        
        return response()->json([
            'users_count' => $usersCount,
            'projects_count' => $projectsCount,
            'slides_count' => $slidesCount
        ]);
    }
}
