<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateOpenApiSpec extends Command
{
    protected $signature = 'openapi:generate';
    protected $description = 'Generate OpenAPI specification file';

    public function handle()
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Presentation Studio API',
                'version' => '1.0.0',
                'description' => 'API for Presentation Studio. Admin endpoints require a 94-character API token in the Authorization header.'
            ],
            'servers' => [
                ['url' => 'http://localhost:8000', 'description' => 'Local server']
            ],
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'TOKEN',
                        'description' => 'Enter your 94-character API token (only for admin endpoints)'
                    ]
                ]
            ],
            'paths' => [
                // Public endpoints - No auth required
                '/api/projects' => [
                    'get' => [
                        'summary' => 'Get user projects',
                        'description' => 'Returns list of projects for authenticated user. No token required.',
                        'tags' => ['Projects'],
                        'responses' => [
                            '200' => ['description' => 'Successful operation']
                        ]
                    ]
                ],
                '/api/all' => [
                    'get' => [
                        'summary' => 'Get all projects',
                        'description' => 'Returns all projects. No token required.',
                        'tags' => ['Projects'],
                        'responses' => [
                            '200' => ['description' => 'Successful operation']
                        ]
                    ]
                ],
                '/api/playlist' => [
                    'get' => [
                        'summary' => 'Get playlist projects',
                        'description' => 'Returns projects in playlist. No token required.',
                        'tags' => ['Playlist'],
                        'responses' => [
                            '200' => ['description' => 'Successful operation']
                        ]
                    ]
                ],
                '/api/project/{name}' => [
                    'get' => [
                        'summary' => 'Get project data',
                        'description' => 'Returns data for a specific project. No token required.',
                        'tags' => ['Projects'],
                        'parameters' => [
                            ['name' => 'name', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'description' => 'Project name']
                        ],
                        'responses' => [
                            '200' => ['description' => 'Successful operation'],
                            '404' => ['description' => 'Project not found']
                        ]
                    ]
                ],
                '/api/save' => [
                    'post' => [
                        'summary' => 'Save project',
                        'description' => 'Save project data. No token required.',
                        'tags' => ['Projects'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['projectName', 'data'],
                                        'properties' => [
                                            'projectName' => ['type' => 'string', 'description' => 'Name of the project'],
                                            'data' => ['type' => 'object', 'description' => 'Project data JSON']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Project saved successfully']
                        ]
                    ]
                ],
                '/api/upload' => [
                    'post' => [
                        'summary' => 'Upload file',
                        'description' => 'Upload audio or image file. No token required.',
                        'tags' => ['Files'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'multipart/form-data' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'projectName' => ['type' => 'string', 'description' => 'Name of the project'],
                                            'audio' => ['type' => 'string', 'format' => 'binary', 'description' => 'Audio file (music)'],
                                            'image' => ['type' => 'string', 'format' => 'binary', 'description' => 'Image file']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => ['description' => 'File uploaded successfully']
                        ]
                    ]
                ],
                
                // Admin endpoints - Token required
                '/api/admin/users' => [
                    'get' => [
                        'summary' => 'Get all users',
                        'description' => 'Returns list of all users. **Requires 94-character API token**.',
                        'tags' => ['Users (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'Successful operation'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token']
                        ]
                    ],
                    'post' => [
                        'summary' => 'Create user',
                        'description' => 'Create a new user. **Requires 94-character API token**.',
                        'tags' => ['Users (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name', 'email', 'password'],
                                        'properties' => [
                                            'name' => ['type' => 'string', 'description' => 'User name'],
                                            'email' => ['type' => 'string', 'format' => 'email', 'description' => 'User email (unique)'],
                                            'password' => ['type' => 'string', 'format' => 'password', 'description' => 'User password (min 8 chars)'],
                                            'role' => ['type' => 'string', 'enum' => ['admin', 'lambda'], 'description' => 'User role (default: lambda)']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => ['description' => 'User created successfully'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token']
                        ]
                    ]
                ],
                '/api/admin/users/{id}' => [
                    'put' => [
                        'summary' => 'Update user',
                        'description' => 'Update an existing user. **Requires 94-character API token**.',
                        'tags' => ['Users (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer'], 'description' => 'User ID']
                        ],
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'name' => ['type' => 'string', 'description' => 'User name'],
                                            'email' => ['type' => 'string', 'format' => 'email', 'description' => 'User email'],
                                            'password' => ['type' => 'string', 'format' => 'password', 'description' => 'New password'],
                                            'role' => ['type' => 'string', 'enum' => ['admin', 'lambda'], 'description' => 'User role']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => ['description' => 'User updated successfully'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token'],
                            '404' => ['description' => 'User not found']
                        ]
                    ],
                    'delete' => [
                        'summary' => 'Delete user',
                        'description' => 'Delete a user. **Requires 94-character API token**.',
                        'tags' => ['Users (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer'], 'description' => 'User ID']
                        ],
                        'responses' => [
                            '200' => ['description' => 'User deleted successfully'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token'],
                            '404' => ['description' => 'User not found']
                        ]
                    ]
                ],
                '/api/admin/projects' => [
                    'get' => [
                        'summary' => 'Get all projects',
                        'description' => 'Returns all projects from all users. **Requires 94-character API token**.',
                        'tags' => ['Projects (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'Successful operation'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token']
                        ]
                    ]
                ],
                '/api/admin/projects/{userId}/{name}' => [
                    'delete' => [
                        'summary' => 'Delete project',
                        'description' => 'Delete a project. **Requires 94-character API token**.',
                        'tags' => ['Projects (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'parameters' => [
                            ['name' => 'userId', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer'], 'description' => 'User ID'],
                            ['name' => 'name', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'description' => 'Project name']
                        ],
                        'responses' => [
                            '200' => ['description' => 'Project deleted successfully'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token'],
                            '404' => ['description' => 'Project not found']
                        ]
                    ]
                ],
                '/api/admin/playlist' => [
                    'get' => [
                        'summary' => 'Get playlist',
                        'description' => 'Returns playlist order. **Requires 94-character API token**.',
                        'tags' => ['Playlist (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'responses' => [
                            '200' => ['description' => 'Successful operation'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token']
                        ]
                    ],
                    'post' => [
                        'summary' => 'Update playlist',
                        'description' => 'Update playlist order. **Requires 94-character API token**.',
                        'tags' => ['Playlist (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'user_id' => ['type' => 'integer', 'description' => 'User ID'],
                                                'name' => ['type' => 'string', 'description' => 'Project name']
                                            ]
                                        ]
                                    ],
                                    'example' => [
                                        ['user_id' => 1, 'name' => 'Project1'],
                                        ['user_id' => 2, 'name' => 'Project2']
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => ['description' => 'Playlist updated successfully'],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token']
                        ]
                    ]
                ],
                '/api/admin/stats' => [
                    'get' => [
                        'summary' => 'Get statistics',
                        'description' => 'Returns application statistics. **Requires 94-character API token**.',
                        'tags' => ['Stats (Admin)'],
                        'security' => [['BearerAuth' => []]],
                        'responses' => [
                            '200' => [
                                'description' => 'Successful operation',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'users_count' => ['type' => 'integer', 'description' => 'Total users'],
                                                'projects_count' => ['type' => 'integer', 'description' => 'Total projects'],
                                                'slides_count' => ['type' => 'integer', 'description' => 'Total slides']
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            '401' => ['description' => 'Unauthorized - Invalid or missing token']
                        ]
                    ]
                ]
            ]
        ];

        $json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(public_path('openapi.json'), $json);
        
        $this->info('OpenAPI spec generated at /openapi.json');
        
        return 0;
    }
}
