<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentation Studio API Documentation</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #1a1a1a; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        h2 { color: #333; margin-top: 40px; }
        h3 { color: #555; }
        .endpoint { background: white; border-radius: 8px; padding: 20px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .method { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; margin-right: 10px; }
        .get { background: #61affe; color: white; }
        .post { background: #49cc90; color: white; }
        .put { background: #fca130; color: white; }
        .delete { background: #f93e3e; color: white; }
        .path { font-family: monospace; font-size: 14px; color: #333; }
        .description { color: #666; margin: 10px 0; }
        .auth { background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin: 20px 0; }
        .code { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; font-family: monospace; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .note { background: #e7f3ff; border-left: 4px solid #0d6efd; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Presentation Studio API Documentation</h1>
    
    <div class="auth">
        <strong>🔐 Authentication:</strong> All admin endpoints require a 94-character API token.<br>
        Add to request header: <code>Authorization: Bearer YOUR_94_CHARACTER_TOKEN</code>
    </div>
    
    <h2>📋 Public Endpoints (No Auth Required)</h2>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/projects</span>
        <p class="description">Get list of projects for the authenticated user</p>
    </div>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/all</span>
        <p class="description">Get all projects (admin only via session)</p>
    </div>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/playlist</span>
        <p class="description">Get projects in playlist</p>
    </div>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/project/{name}</span>
        <p class="description">Get a specific project data</p>
    </div>
    
    <div class="endpoint">
        <span class="method post">POST</span>
        <span class="path">/api/save</span>
        <p class="description">Save project data</p>
        <table>
            <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
            <tr><td>projectName</td><td>string</td><td>Yes</td><td>Name of the project</td></tr>
            <tr><td>data</td><td>object</td><td>Yes</td><td>Project data JSON</td></tr>
        </table>
    </div>
    
    <div class="endpoint">
        <span class="method post">POST</span>
        <span class="path">/api/upload</span>
        <p class="description">Upload audio or image file</p>
        <table>
            <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
            <tr><td>projectName</td><td>string</td><td>Yes</td><td>Name of the project</td></tr>
            <tr><td>audio</td><td>file</td><td>No</td><td>Audio file (music)</td></tr>
            <tr><td>image</td><td>file</td><td>No</td><td>Image file</td></tr>
        </table>
    </div>
    
    <h2>🔧 Admin Endpoints (94-char Token Required)</h2>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/admin/users</span>
        <p class="description">Get all users</p>
    </div>
    
    <div class="endpoint">
        <span class="method post">POST</span>
        <span class="path">/api/admin/users</span>
        <p class="description">Create a new user</p>
        <table>
            <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
            <tr><td>name</td><td>string</td><td>Yes</td><td>User name</td></tr>
            <tr><td>email</td><td>string</td><td>Yes</td><td>User email (unique)</td></tr>
            <tr><td>password</td><td>string</td><td>Yes</td><td>User password (min 8 chars)</td></tr>
            <tr><td>role</td><td>string</td><td>No</td><td>Role: "admin" or "lambda" (default: lambda)</td></tr>
        </table>
        <div class="code">curl -X POST https://yoursite.com/api/admin/users \
  -H "Authorization: Bearer YOUR_94_CHAR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"password123","role":"lambda"}'</div>
    </div>
    
    <div class="endpoint">
        <span class="method put">PUT</span>
        <span class="path">/api/admin/users/{id}</span>
        <p class="description">Update a user</p>
        <table>
            <tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr>
            <tr><td>id</td><td>integer</td><td>Yes</td><td>User ID (URL param)</td></tr>
            <tr><td>name</td><td>string</td><td>No</td><td>User name</td></tr>
            <tr><td>email</td><td>string</td><td>No</td><td>User email</td></tr>
            <tr><td>password</td><td>string</td><td>No</td><td>New password</td></tr>
            <tr><td>role</td><td>string</td><td>No</td><td>Role: "admin" or "lambda"</td></tr>
        </table>
    </div>
    
    <div class="endpoint">
        <span class="method delete">DELETE</span>
        <span class="path">/api/admin/users/{id}</span>
        <p class="description">Delete a user</p>
    </div>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/admin/projects</span>
        <p class="description">Get all projects from all users</p>
    </div>
    
    <div class="endpoint">
        <span class="method delete">DELETE</span>
        <span class="path">/api/admin/projects/{userId}/{name}</span>
        <p class="description">Delete a project</p>
    </div>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/admin/playlist</span>
        <p class="description">Get playlist order</p>
    </div>
    
    <div class="endpoint">
        <span class="method post">POST</span>
        <span class="path">/api/admin/playlist</span>
        <p class="description">Update playlist order</p>
        <div class="code">curl -X POST https://yoursite.com/api/admin/playlist \
  -H "Authorization: Bearer YOUR_94_CHAR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '[{"user_id":1,"name":"Project1"},{"user_id":2,"name":"Project2"}]'</div>
    </div>
    
    <div class="endpoint">
        <span class="method get">GET</span>
        <span class="path">/api/admin/stats</span>
        <p class="description">Get application statistics</p>
        <p class="description">Returns: users_count, projects_count, slides_count</p>
    </div>
    
    <h2>📝 Example: Using the API</h2>
    
    <h3>Get all users:</h3>
    <div class="code">curl -X GET https://yoursite.com/api/admin/users \
  -H "Authorization: Bearer abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqr"</div>
    
    <h3>Create a user:</h3>
    <div class="code">curl -X POST https://yoursite.com/api/admin/users \
  -H "Authorization: Bearer abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqr" \
  -H "Content-Type: application/json" \
  -d '{"name":"New User","email":"new@example.com","password":"secret123","role":"lambda"}'</div>
    
    <h3>Delete a project:</h3>
    <div class="code">curl -X DELETE "https://yoursite.com/api/admin/projects/1/MyProject" \
  -H "Authorization: Bearer abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqrstuvwxyz1234567890abcdefghijklmnopqr"</div>
    
    <div class="note">
        <strong>Note:</strong> Replace "YOUR_94_CHAR_TOKEN" with your actual 94-character API token configured in your .env file as <code>API_TOKEN</code>
    </div>
</body>
</html>
