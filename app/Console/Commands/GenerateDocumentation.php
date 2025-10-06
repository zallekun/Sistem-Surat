<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionMethod;

class GenerateDocumentation extends Command
{
    protected $signature = 'docs:generate {type=all : Type of documentation (sdd|srs|erd|all)}';
    protected $description = 'Generate SDD, SRS, and ERD documentation for the project';

    private $output_dir;

    public function handle()
    {
        $type = $this->argument('type');
        $this->output_dir = storage_path('app/documentation');
        
        if (!File::exists($this->output_dir)) {
            File::makeDirectory($this->output_dir, 0755, true);
        }

        $this->info('🚀 Starting Documentation Generation...');
        
        switch ($type) {
            case 'sdd':
                $this->generateSDD();
                break;
            case 'srs':
                $this->generateSRS();
                break;
            case 'erd':
                $this->generateERD();
                break;
            case 'all':
                $this->generateSDD();
                $this->generateSRS();
                $this->generateERD();
                $this->generateUseCaseDiagram();
                $this->generateActivityDiagram();
                break;
            default:
                $this->error('Invalid type. Use: sdd, srs, erd, or all');
                return 1;
        }

        $this->newLine();
        $this->info('✅ Documentation generated successfully!');
        $this->info("📁 Output directory: {$this->output_dir}");
        
        return 0;
    }

    /**
     * Generate Software Design Document (SDD)
     */
    private function generateSDD()
    {
        $this->info('📝 Generating SDD...');
        
        $sdd = [
            'metadata' => $this->getMetadata(),
            'introduction' => $this->getSDDIntroduction(),
            'architecture' => $this->getArchitecture(),
            'system_context' => $this->getSystemContext(),
            'database' => $this->getDatabaseSchema(),
            'controllers' => $this->getControllers(),
            'models' => $this->getModels(),
            'routes' => $this->getRoutes(),
            'middleware' => $this->getMiddleware(),
            'views' => $this->getViews(),
            'security' => $this->getSecurityArchitecture(),
            'deployment' => $this->getDeploymentArchitecture(),
            'error_handling' => $this->getErrorHandling(),
        ];

        $markdown = $this->formatSDDMarkdown($sdd);
        $json = json_encode($sdd, JSON_PRETTY_PRINT);

        File::put($this->output_dir . '/SDD.md', $markdown);
        File::put($this->output_dir . '/SDD.json', $json);

        $this->line('  ✓ SDD.md created');
        $this->line('  ✓ SDD.json created');
    }

    /**
     * Generate Software Requirements Specification (SRS)
     */
    private function generateSRS()
    {
        $this->info('📝 Generating SRS...');
        
        $srs = [
            'metadata' => $this->getMetadata(),
            'introduction' => $this->getSRSIntroduction(),
            'overview' => $this->getProjectOverview(),
            'features' => $this->getFeatures(),
            'user_roles' => $this->getUserRoles(),
            'functional_requirements' => $this->getFunctionalRequirements(),
            'non_functional_requirements' => $this->getNonFunctionalRequirements(),
            'interface_requirements' => $this->getInterfaceRequirements(),
            'data_requirements' => $this->getDataRequirements(),
            'constraints' => $this->getConstraints(),
            'assumptions' => $this->getAssumptions(),
            'use_cases' => $this->getUseCases(),
            'user_stories' => $this->getUserStories(),
            'acceptance_criteria' => $this->getAcceptanceCriteria(),
            'traceability_matrix' => $this->getTraceabilityMatrix(),
        ];

        $markdown = $this->formatSRSMarkdown($srs);
        $json = json_encode($srs, JSON_PRETTY_PRINT);

        File::put($this->output_dir . '/SRS.md', $markdown);
        File::put($this->output_dir . '/SRS.json', $json);

        $this->line('  ✓ SRS.md created');
        $this->line('  ✓ SRS.json created');
    }

    /**
     * Generate ERD (Entity Relationship Diagram) in PlantUML format
     */
    private function generateERD()
    {
        $this->info('📝 Generating ERD...');
        
        $tables = $this->getDatabaseSchema();
        $relationships = $this->detectRelationships();
        
        $plantuml = $this->generatePlantUMLERD($tables, $relationships);
        $mermaid = $this->generateMermaidERD($tables, $relationships);
        
        File::put($this->output_dir . '/ERD.puml', $plantuml);
        File::put($this->output_dir . '/ERD_mermaid.md', $mermaid);
        
        $this->line('  ✓ ERD.puml created (PlantUML format)');
        $this->line('  ✓ ERD_mermaid.md created (Mermaid format)');
        $this->line('  ℹ Use https://plantuml.com or https://mermaid.live to visualize');
    }

    /**
     * Generate Use Case Diagram
     */
    private function generateUseCaseDiagram()
    {
        $this->info('📝 Generating Use Case Diagram...');
        
        $plantuml = "@startuml\nleft to right direction\n\n";
        
        // Actors
        $plantuml .= "actor Mahasiswa\n";
        $plantuml .= "actor \"Staff Prodi\" as StaffProdi\n";
        $plantuml .= "actor \"Staff Fakultas\" as StaffFakultas\n";
        $plantuml .= "actor Kaprodi\n";
        $plantuml .= "actor Dekan\n";
        $plantuml .= "actor Admin\n\n";
        
        // System boundary
        $plantuml .= "rectangle \"Sistem Persuratan FSI\" {\n";
        
        // Use cases
        $useCases = [
            'Mahasiswa' => [
                'Submit Pengajuan Surat',
                'Track Status Pengajuan',
                'Download Surat',
            ],
            'StaffProdi' => [
                'Review Pengajuan',
                'Generate Surat Pengantar',
                'Approve/Reject Pengajuan',
                'Manage Arsip',
            ],
            'StaffFakultas' => [
                'Process Surat',
                'Generate Surat Final',
                'Manage Arsip Fakultas',
            ],
            'Kaprodi' => [
                'Approve Pengajuan',
            ],
            'Dekan' => [
                'Sign Surat',
                'Disposisi Surat',
            ],
            'Admin' => [
                'Manage Users',
                'Manage Master Data',
                'Admin Intervention',
                'View Audit Trail',
                'Export Reports',
            ],
        ];
        
        foreach ($useCases as $actor => $cases) {
            foreach ($cases as $case) {
                $caseId = str_replace([' ', '/'], '', $case);
                $plantuml .= "  usecase ($case) as UC{$caseId}\n";
            }
        }
        
        $plantuml .= "}\n\n";
        
        // Relationships
        foreach ($useCases as $actor => $cases) {
            foreach ($cases as $case) {
                $caseId = str_replace([' ', '/'], '', $case);
                $plantuml .= "{$actor} --> UC{$caseId}\n";
            }
        }
        
        $plantuml .= "\n@enduml";
        
        File::put($this->output_dir . '/UseCase.puml', $plantuml);
        $this->line('  ✓ UseCase.puml created');
    }

    /**
     * Generate Activity Diagram for Pengajuan Workflow
     */
    private function generateActivityDiagram()
    {
        $this->info('📝 Generating Activity Diagram...');
        
        $plantuml = "@startuml\nstart\n\n";
        $plantuml .= ":Mahasiswa Submit Pengajuan;\n";
        $plantuml .= ":System Generate Tracking Token;\n";
        $plantuml .= ":Notifikasi ke Staff Prodi;\n\n";
        $plantuml .= "if (Staff Prodi Review?) then (Approve)\n";
        $plantuml .= "  :Update Status: approved_prodi;\n";
        $plantuml .= "  if (Need Surat Pengantar?) then (Yes)\n";
        $plantuml .= "    :Generate Surat Pengantar;\n";
        $plantuml .= "  endif\n";
        $plantuml .= "  :Forward to Fakultas;\n";
        $plantuml .= "  if (Staff Fakultas Review?) then (Approve)\n";
        $plantuml .= "    :Generate Surat Final;\n";
        $plantuml .= "    if (Need Signature?) then (Yes)\n";
        $plantuml .= "      :Send to Dekan/WD;\n";
        $plantuml .= "      :Dekan Sign;\n";
        $plantuml .= "    endif\n";
        $plantuml .= "    :Update Status: completed;\n";
        $plantuml .= "    :Notify Mahasiswa;\n";
        $plantuml .= "    stop\n";
        $plantuml .= "  else (Reject)\n";
        $plantuml .= "    :Update Status: rejected_fakultas;\n";
        $plantuml .= "    :Notify Mahasiswa with Reason;\n";
        $plantuml .= "    stop\n";
        $plantuml .= "  endif\n";
        $plantuml .= "else (Reject)\n";
        $plantuml .= "  :Update Status: rejected_prodi;\n";
        $plantuml .= "  :Notify Mahasiswa with Reason;\n";
        $plantuml .= "  stop\n";
        $plantuml .= "endif\n\n";
        $plantuml .= "@enduml";
        
        File::put($this->output_dir . '/ActivityDiagram.puml', $plantuml);
        $this->line('  ✓ ActivityDiagram.puml created');
    }

    // ===== HELPER METHODS =====

    private function getMetadata()
    {
        return [
            'project_name' => config('app.name'),
            'version' => '1.0.0',
            'generated_at' => now()->toDateTimeString(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'author' => 'Development Team',
        ];
    }

    private function getSDDIntroduction()
    {
        return [
            'purpose' => 'Dokumen ini menjelaskan desain sistem Persuratan FSI secara teknis dan arsitektural',
            'scope' => 'Mencakup arsitektur sistem, database design, komponen aplikasi, dan deployment strategy',
            'intended_audience' => ['Developer', 'System Architect', 'Database Administrator', 'DevOps Engineer'],
        ];
    }

    private function getSRSIntroduction()
    {
        return [
            'purpose' => 'Dokumen ini mendefinisikan kebutuhan fungsional dan non-fungsional sistem Persuratan FSI',
            'scope' => 'Mencakup semua fitur, use cases, user stories, dan acceptance criteria',
            'intended_audience' => ['Stakeholders', 'Project Manager', 'Developer', 'QA Engineer', 'End Users'],
        ];
    }

    private function getSystemContext()
    {
        return [
            'external_systems' => [
                'Email Server' => 'Untuk notifikasi ke mahasiswa dan staff',
                'File Storage' => 'Penyimpanan dokumen PDF surat',
                'Database Server' => 'MySQL/MariaDB untuk persistent data',
            ],
            'actors' => [
                'Mahasiswa' => 'Mengajukan surat',
                'Staff Prodi' => 'Review dan approve pengajuan',
                'Staff Fakultas' => 'Generate dan approve surat final',
                'Kaprodi' => 'Approval surat tertentu',
                'Dekan/WD' => 'Tanda tangan surat',
                'Admin' => 'System administration dan intervention',
            ],
        ];
    }

    private function getArchitecture()
    {
        return [
            'pattern' => 'MVC (Model-View-Controller)',
            'framework' => 'Laravel ' . app()->version(),
            'frontend' => [
                'framework' => 'Blade Templates',
                'css' => 'Tailwind CSS',
                'js' => 'Alpine.js, Livewire',
                'icons' => 'Font Awesome',
            ],
            'backend' => [
                'language' => 'PHP ' . PHP_VERSION,
                'server' => 'Apache/Nginx',
                'authentication' => 'Laravel Breeze',
                'authorization' => 'Spatie Laravel Permission',
            ],
            'database' => [
                'type' => config('database.default'),
                'driver' => 'MySQL/MariaDB',
            ],
            'storage' => [
                'documents' => 'Local Storage / S3',
                'cache' => 'File / Redis',
            ],
        ];
    }

    private function getSecurityArchitecture()
    {
        return [
            'authentication' => [
                'method' => 'Session-based authentication',
                'library' => 'Laravel Breeze',
                'features' => ['Login', 'Logout', 'Password Reset', 'Remember Me'],
            ],
            'authorization' => [
                'method' => 'Role-based Access Control (RBAC)',
                'library' => 'Spatie Laravel Permission',
                'roles' => ['admin', 'staff_prodi', 'staff_fakultas', 'kaprodi', 'pimpinan'],
            ],
            'data_protection' => [
                'csrf' => 'Laravel CSRF Protection',
                'xss' => 'Blade auto-escaping',
                'sql_injection' => 'Eloquent ORM prepared statements',
                'password_hashing' => 'bcrypt',
            ],
            'audit_logging' => [
                'admin_actions' => 'Logged to audit_trails table',
                'tracked_actions' => ['force_complete', 'reopen', 'change_status', 'delete'],
            ],
        ];
    }

    private function getDeploymentArchitecture()
    {
        return [
            'recommended_stack' => [
                'Web Server' => 'Nginx 1.18+',
                'PHP' => 'PHP 8.2+',
                'Database' => 'MySQL 8.0+ or MariaDB 10.6+',
                'Cache' => 'Redis (optional)',
            ],
            'server_requirements' => [
                'CPU' => '2 cores minimum',
                'RAM' => '4GB minimum',
                'Storage' => '20GB minimum (depends on document volume)',
            ],
            'php_extensions' => [
                'Required' => ['PDO', 'Mbstring', 'OpenSSL', 'Tokenizer', 'XML', 'Ctype', 'JSON', 'BCMath'],
            ],
        ];
    }

    private function getErrorHandling()
    {
        return [
            'strategy' => 'Exception-based error handling dengan Laravel exception handler',
            'logging' => [
                'method' => 'Laravel Log facade',
                'channels' => ['single', 'daily', 'slack', 'stack'],
                'location' => 'storage/logs/laravel.log',
            ],
            'user_feedback' => [
                'validation_errors' => 'Displayed inline pada form',
                'system_errors' => 'Generic error message to user, detailed log to file',
                'success_messages' => 'Flash session messages',
            ],
        ];
    }

    private function getDatabaseSchema()
    {
        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.mysql.database');
        $schema = [];

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$database}"};
            
            $columns = DB::select("DESCRIBE {$tableName}");
            $indexes = DB::select("SHOW INDEXES FROM {$tableName}");
            
            $schema[$tableName] = [
                'columns' => $columns,
                'indexes' => $indexes,
            ];
        }

        return $schema;
    }

    private function detectRelationships()
    {
        $relationships = [];
        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.mysql.database');

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$database}"};
            $columns = DB::select("DESCRIBE {$tableName}");
            
            foreach ($columns as $column) {
                // Detect foreign keys by naming convention
                if (str_ends_with($column->Field, '_id') && $column->Field !== 'id') {
                    $relatedTable = str_replace('_id', '', $column->Field);
                    
                    // Check if related table exists (plural form)
                    $pluralTable = $relatedTable . 's';
                    if ($this->tableExists($pluralTable)) {
                        $relationships[] = [
                            'from' => $tableName,
                            'to' => $pluralTable,
                            'type' => 'many_to_one',
                            'fk' => $column->Field,
                        ];
                    } elseif ($this->tableExists($relatedTable)) {
                        $relationships[] = [
                            'from' => $tableName,
                            'to' => $relatedTable,
                            'type' => 'many_to_one',
                            'fk' => $column->Field,
                        ];
                    }
                }
            }
        }

        return $relationships;
    }

    private function tableExists($tableName)
    {
        try {
            DB::select("SHOW TABLES LIKE '{$tableName}'");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function generatePlantUMLERD($tables, $relationships)
    {
        $puml = "@startuml\n!define Table(name,desc) class name as \"desc\" << (T,#FFAAAA) >>\n";
        $puml .= "hide methods\nhide stereotypes\n\n";
        
        foreach ($tables as $tableName => $schema) {
            $puml .= "entity \"{$tableName}\" {\n";
            foreach ($schema['columns'] as $col) {
                $key = '';
                if ($col->Key === 'PRI') $key = '* ';
                elseif ($col->Key === 'UNI') $key = '+ ';
                
                $puml .= "  {$key}{$col->Field} : {$col->Type}\n";
            }
            $puml .= "}\n\n";
        }
        
        foreach ($relationships as $rel) {
            $puml .= "{$rel['from']} }|--|| {$rel['to']} : {$rel['fk']}\n";
        }
        
        $puml .= "\n@enduml";
        
        return $puml;
    }

private function generateMermaidERD($tables, $relationships)
{
    $mermaid = "```mermaid\nerDiagram\n";
    
    foreach ($tables as $tableName => $schema) {
        $mermaid .= "  {$tableName} {\n";
        
        foreach ($schema['columns'] as $col) {
            // Skip jika tidak ada type atau field
            if (empty($col->Field) || empty($col->Type)) {
                continue;
            }
            
            // Clean up type
            $cleanType = $this->cleanMermaidType($col->Type);
            
            // Escape special characters di field name
            $fieldName = str_replace(['(', ')', ',', ' '], '_', $col->Field);
            
            $mermaid .= "    {$cleanType} {$fieldName}\n";
        }
        
        $mermaid .= "  }\n";
    }
    
    $mermaid .= "\n";
    
    foreach ($relationships as $rel) {
        $mermaid .= "  {$rel['from']} ||--o{ {$rel['to']} : \"{$rel['fk']}\"\n";
    }
    
    $mermaid .= "```";
    
    return $mermaid;
}

private function cleanMermaidType($type)
{
    // Handle null/empty type
    if (empty($type)) {
        return 'string';
    }
    
    // Convert to string untuk safety
    $type = (string) $type;
    
    // Handle enum - convert to string type
    if (stripos($type, 'enum') === 0) {
        return 'string';
    }
    
    // Handle set
    if (stripos($type, 'set') === 0) {
        return 'string';
    }
    
    // Extract base type without size/parameters
    // varchar(255) -> varchar
    // decimal(10,2) -> decimal
    if (preg_match('/^([a-z]+)/i', $type, $matches)) {
        $baseType = strtolower($matches[1]);
        
        // Map MySQL types to common types
        $typeMap = [
            'tinyint' => 'int',
            'smallint' => 'int',
            'mediumint' => 'int',
            'bigint' => 'bigint',
            'varchar' => 'string',
            'char' => 'string',
            'text' => 'text',
            'longtext' => 'text',
            'datetime' => 'timestamp',
            'timestamp' => 'timestamp',
            'date' => 'date',
        ];
        
        return $typeMap[$baseType] ?? $baseType;
    }
    
    return 'string';
}

    private function getControllers()
    {
        $controllers = [];
        $path = app_path('Http/Controllers');
        
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $className = 'App\\Http\\Controllers\\' . str_replace(
                ['/', '.php'],
                ['\\', ''],
                $file->getRelativePathname()
            );

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                $methods = [];

                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($method->class === $className && !$method->isConstructor()) {
                        $methods[] = [
                            'name' => $method->getName(),
                            'parameters' => array_map(function($param) {
                                return $param->getName();
                            }, $method->getParameters()),
                        ];
                    }
                }

                $controllers[$className] = [
                    'file' => $file->getRelativePathname(),
                    'methods' => $methods,
                ];
            }
        }

        return $controllers;
    }

    private function getModels()
    {
        $models = [];
        $path = app_path('Models');
        
        if (File::exists($path)) {
            $files = File::allFiles($path);
            
            foreach ($files as $file) {
                $className = 'App\\Models\\' . str_replace('.php', '', $file->getFilename());

                if (class_exists($className)) {
                    $reflection = new ReflectionClass($className);
                    
                    try {
                        $instance = new $className;
                        $table = $instance->getTable();
                    } catch (\Exception $e) {
                        $table = 'unknown';
                    }
                    
                    $models[$className] = [
                        'file' => $file->getFilename(),
                        'table' => $table,
                        'fillable' => $reflection->hasProperty('fillable') 
                            ? $reflection->getProperty('fillable')->getDefaultValue() 
                            : [],
                        'relationships' => $this->getModelRelationships($reflection),
                    ];
                }
            }
        }

        return $models;
    }

    private function getModelRelationships($reflection)
    {
        $relationships = [];
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $returnType = $method->getReturnType();
            
            if ($returnType && in_array($returnType->getName(), [
                'Illuminate\Database\Eloquent\Relations\HasMany',
                'Illuminate\Database\Eloquent\Relations\BelongsTo',
                'Illuminate\Database\Eloquent\Relations\HasOne',
                'Illuminate\Database\Eloquent\Relations\BelongsToMany',
            ])) {
                $relationships[] = [
                    'name' => $method->getName(),
                    'type' => class_basename($returnType->getName()),
                ];
            }
        }

        return $relationships;
    }

    private function getRoutes()
    {
        $routes = [];
        
        foreach (Route::getRoutes() as $route) {
            $routes[] = [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
            ];
        }

        return $routes;
    }

    private function getMiddleware()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        
        return [
            'global' => $kernel->getMiddlewareGroups(),
            'route' => $kernel->getRouteMiddleware(),
        ];
    }

    private function getViews()
    {
        $views = [];
        $path = resource_path('views');
        
        $files = File::allFiles($path);
        
        foreach ($files as $file) {
            $views[] = $file->getRelativePathname();
        }

        return $views;
    }

    private function getProjectOverview()
    {
        return [
            'purpose' => 'Sistem Persuratan Fakultas Sains dan Informatika untuk digitalisasi proses pengajuan dan pembuatan surat mahasiswa',
            'scope' => 'Manajemen pengajuan surat dari submission, approval workflow, hingga generation dan distribution surat',
            'target_users' => [
                'Mahasiswa' => 'Submit pengajuan dan track status',
                'Staff Prodi' => 'Review, approve, dan generate surat',
                'Staff Fakultas' => 'Process dan finalize surat',
                'Kaprodi' => 'Approval surat tertentu',
                'Dekan/WD' => 'Sign surat resmi',
                'Admin' => 'System administration dan intervention',
            ],
            'benefits' => [
                'Efisiensi proses',
                'Transparansi tracking',
                'Paperless documentation',
                'Audit trail lengkap',
                'Reduced processing time',
            ],
        ];
    }

    private function getFeatures()
    {
        return [
            'Authentication & Authorization' => [
                'Login/Logout dengan session-based auth',
                'Role-based Access Control (6 roles)',
                'Profile Management',
                'Password Reset',
            ],
            'Pengajuan Surat (Public/Mahasiswa)' => [
                'Submit Pengajuan tanpa login',
                'Track Status dengan tracking token',
                'Download Surat hasil',
                'Form dinamis per jenis surat',
            ],
            'Staff Prodi Management' => [
                'Review Pengajuan masuk',
                'Approve/Reject dengan reason',
                'Generate Surat Pengantar (untuk KP/TA)',
                'Manage Arsip Surat',
                'Export data ke Excel',
            ],
            'Staff Fakultas Management' => [
                'Process pengajuan dari Prodi',
                'Generate Surat Final',
                'Edit dan Preview sebelum finalisasi',
                'Upload signed document link',
                'Manage Arsip Fakultas',
            ],
            'Admin Features' => [
                'Dashboard dengan Analytics (charts)',
                'Kelola Pengajuan (view, soft delete, restore)',
                'Kelola User (CRUD, reset password, toggle status)',
                'Master Data: Prodi, Jenis Surat, Fakultas',
                'Admin Intervention: Force Complete, Reopen, Change Status',
                'Audit Trail logging semua admin actions',
                'Export Reports (Excel)',
                'Detect stuck pengajuan',
            ],
            'Workflow & Notifications' => [
                'Multi-level approval (Prodi → Fakultas → Pimpinan)',
                'Status tracking realtime',
                'Email notifications (planned)',
                'Approval history timeline',
            ],
        ];
    }

    private function getUserRoles()
    {
        try {
            return DB::table('roles')->pluck('name', 'id')->toArray();
        } catch (\Exception $e) {
            return [
                'admin' => 'System Administrator',
                'staff_prodi' => 'Staff Program Studi',
                'staff_fakultas' => 'Staff Fakultas',
                'kaprodi' => 'Kepala Program Studi',
                'pimpinan' => 'Pimpinan (Dekan/WD)',
            ];
        }
    }

    private function getFunctionalRequirements()
    {
        return [
            'FR-001' => 'System harus dapat menerima pengajuan surat dari mahasiswa tanpa login',
            'FR-002' => 'System harus generate unique tracking token untuk setiap pengajuan',
            'FR-003' => 'System harus dapat men-tracking status pengajuan secara realtime',
            'FR-004' => 'System harus melakukan approval workflow bertingkat (Prodi → Fakultas → Pimpinan)',
            'FR-005' => 'System harus dapat generate surat pengantar untuk jenis surat tertentu (KP, TA)',
            'FR-006' => 'System harus dapat generate surat final dalam format PDF',
            'FR-007' => 'Staff harus dapat approve/reject dengan memberikan alasan',
            'FR-008' => 'Admin harus dapat melakukan intervention: Force Complete, Reopen, Change Status',
            'FR-009' => 'System harus log semua admin intervention ke audit trail',
            'FR-010' => 'System harus dapat soft delete pengajuan dengan wajib input alasan',
            'FR-011' => 'System harus dapat restore pengajuan yang telah di-soft delete',
            'FR-012' => 'System harus detect pengajuan yang stuck lebih dari 3 hari',
            'FR-013' => 'System harus dapat export data pengajuan dan audit trail ke Excel',
            'FR-014' => 'System harus menyimpan approval history lengkap',
            'FR-015' => 'User harus dapat manage Master Data (Prodi, Jenis Surat, Fakultas)',
            'FR-016' => 'Admin harus dapat manage users (CRUD, reset password, toggle status)',
            'FR-017' => 'System harus menampilkan dashboard dengan analytics dan charts',
            'FR-018' => 'System harus validate data input sesuai jenis surat',
            'FR-019' => 'System harus dapat store dan retrieve dokumen PDF surat',
            'FR-020' => 'System harus implement role-based access control',
        ];
    }

    private function getNonFunctionalRequirements()
    {
        return [
            'Performance' => [
                'Response time < 2 seconds untuk operasi CRUD',
                'Page load time < 3 seconds',
                'Support concurrent users minimal 50',
                'Database query optimization dengan indexing',
            ],
            'Security' => [
                'Authentication dengan Laravel Breeze (session-based)',
                'RBAC menggunakan Spatie Laravel Permission',
                'CSRF Protection pada semua form submissions',
                'XSS Protection dengan Blade auto-escaping',
                'SQL Injection Protection dengan Eloquent ORM',
                'Password hashing dengan bcrypt',
                'Audit logging untuk admin actions',
            ],
            'Usability' => [
                'Responsive design dengan Tailwind CSS',
                'Mobile-friendly interface',
                'Intuitive navigation',
                'Clear error messages',
                'Consistent UI/UX patterns',
            ],
            'Scalability' => [
                'Support untuk multiple fakultas dan prodi',
                'Database design yang normalize',
                'Soft delete untuk data recovery',
                'Pagination untuk large datasets',
            ],
            'Reliability' => [
                'Data backup strategy',
                'Error handling dan logging',
                'Soft delete untuk accidental deletion recovery',
                'Transaction rollback pada critical operations',
            ],
            'Maintainability' => [
                'MVC architecture',
                'Following Laravel best practices',
                'Code comments pada logic kompleks',
                'Consistent naming conventions',
                'Modular component structure',
            ],
            'Compatibility' => [
                'Modern browsers: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+',
                'PHP 8.2+',
                'MySQL 8.0+ or MariaDB 10.6+',
                'Desktop dan mobile devices',
            ],
        ];
    }

    private function getInterfaceRequirements()
    {
        return [
            'User Interface' => [
                'Responsive web application',
                'Sidebar navigation untuk authenticated users',
                'Dashboard dengan statistics cards dan charts',
                'Table dengan pagination, search, dan filter',
                'Modal dialogs untuk confirmations',
                'Toast notifications untuk feedback',
                'Form dengan real-time validation',
            ],
            'API Interface' => [
                'RESTful endpoints untuk operations',
                'JSON response format',
                'Standard HTTP status codes',
                'CSRF token di header',
            ],
            'Database Interface' => [
                'MySQL/MariaDB via Eloquent ORM',
                'Migration files untuk version control',
                'Seeder files untuk initial data',
            ],
        ];
    }

    private function getDataRequirements()
    {
        return [
            'Master Data' => [
                'Fakultas' => 'kode_fakultas, nama_fakultas',
                'Prodi' => 'kode_prodi, nama_prodi, fakultas_id',
                'Jenis Surat' => 'kode_surat, nama_jenis, deskripsi',
                'Jabatan' => 'nama_jabatan, deskripsi',
            ],
            'Transactional Data' => [
                'Pengajuan Surat' => 'tracking_token, nim, nama_mahasiswa, prodi_id, jenis_surat_id, status, keperluan, additional_data',
                'Approval History' => 'pengajuan_id, action, performed_by, notes, timestamps',
                'Audit Trail' => 'user_id, action, model_type, model_id, old_data, new_data, reason, ip_address',
            ],
            'User Data' => [
                'Users' => 'nama, email, nip, password, role, prodi_id, jabatan_id, is_active',
                'Roles' => 'name, guard_name',
                'Permissions' => 'name, guard_name',
            ],
            'Data Retention' => [
                'Soft deleted records retained indefinitely',
                'Audit logs retained for 2 years minimum',
                'Completed pengajuan archived but accessible',
            ],
        ];
    }

    private function getConstraints()
    {
        return [
            'Technical Constraints' => [
                'Must use Laravel framework',
                'Must use MySQL/MariaDB database',
                'Must deploy on Linux server',
                'Must use PHP 8.2+',
            ],
            'Business Constraints' => [
                'Budget limitations untuk hosting',
                'Timeline constraints untuk development',
                'Resource constraints (developer availability)',
            ],
            'Legal/Regulatory Constraints' => [
                'Data privacy compliance (GDPR-like principles)',
                'Academic institution policies',
                'Document authenticity requirements',
            ],
            'Operational Constraints' => [
                'Single database server (no clustering initially)',
                'Local file storage (S3 optional)',
                'Manual deployment process',
            ],
        ];
    }

    private function getAssumptions()
    {
        return [
            'Users have stable internet connection',
            'Staff have sufficient technical literacy',
            'Server infrastructure is reliable',
            'Database backups performed regularly by system admin',
            'Email server available for notifications (future feature)',
            'PDF generation library (TCPDF/DomPDF) sufficient for needs',
            'Single institutional deployment (not multi-tenant)',
            'Mahasiswa data validated externally (SIAKAD integration assumed later)',
        ];
    }

    private function getUseCases()
    {
        return [
            'UC-001: Submit Pengajuan Surat' => [
                'Actor' => 'Mahasiswa',
                'Precondition' => 'Mahasiswa memiliki NIM dan data valid',
                'Postcondition' => 'Pengajuan tersimpan dengan tracking token',
                'Main Flow' => [
                    '1. Mahasiswa akses public form pengajuan surat',
                    '2. Mahasiswa pilih jenis surat',
                    '3. System tampilkan form sesuai jenis surat',
                    '4. Mahasiswa isi data (NIM, nama, prodi, keperluan, data tambahan)',
                    '5. System validasi data',
                    '6. System simpan pengajuan ke database',
                    '7. System generate unique tracking token',
                    '8. System tampilkan tracking token ke mahasiswa',
                    '9. System kirim notifikasi ke staff prodi',
                ],
                'Alternative Flow' => [
                    '3a. Jika validasi gagal, tampilkan error message dan kembali ke step 4',
                ],
            ],
            'UC-002: Track Status Pengajuan' => [
                'Actor' => 'Mahasiswa',
                'Precondition' => 'Mahasiswa memiliki tracking token',
                'Postcondition' => 'Status pengajuan ditampilkan',
                'Main Flow' => [
                    '1. Mahasiswa akses halaman tracking',
                    '2. Mahasiswa input tracking token',
                    '3. System cari pengajuan berdasarkan token',
                    '4. System tampilkan detail pengajuan dan timeline status',
                    '5. Jika surat sudah selesai, tampilkan link download',
                ],
            ],
            'UC-003: Review dan Approve Pengajuan (Staff Prodi)' => [
                'Actor' => 'Staff Prodi',
                'Precondition' => 'Staff sudah login, ada pengajuan pending',
                'Postcondition' => 'Pengajuan approved/rejected',
                'Main Flow' => [
                    '1. Staff login ke system',
                    '2. Staff akses list pengajuan pending',
                    '3. Staff klik detail pengajuan',
                    '4. Staff review data mahasiswa dan keperluan',
                    '5. Staff klik "Approve"',
                    '6. System update status menjadi approved_prodi',
                    '7. Jika perlu surat pengantar, system arahkan ke generate surat pengantar',
                    '8. System forward ke staff fakultas',
                    '9. System catat approval history',
                ],
                'Alternative Flow' => [
                    '5a. Staff klik "Reject"',
                    '5b. Staff input alasan reject',
                    '5c. System update status menjadi rejected_prodi',
                    '5d. System notify mahasiswa',
                ],
            ],
            'UC-004: Admin Intervention - Force Complete' => [
                'Actor' => 'Admin',
                'Precondition' => 'Pengajuan dalam status stuck atau approved_prodi/approved_fakultas',
                'Postcondition' => 'Pengajuan status berubah menjadi completed',
                'Main Flow' => [
                    '1. Admin login dan akses kelola pengajuan',
                    '2. Admin identifikasi pengajuan yang stuck',
                    '3. Admin klik detail pengajuan',
                    '4. Admin klik tombol "Force Complete"',
                    '5. Modal muncul dengan form input alasan',
                    '6. Admin input alasan intervention',
                    '7. Admin konfirmasi action',
                    '8. System update status menjadi completed',
                    '9. System set completed_at timestamp',
                    '10. System log action ke audit_trails table',
                    '11. System log ke approval_histories',
                    '12. System tampilkan success message',
                ],
            ],
            'UC-005: Admin Intervention - Reopen' => [
                'Actor' => 'Admin',
                'Precondition' => 'Pengajuan dalam status rejected_prodi atau rejected_fakultas',
                'Postcondition' => 'Pengajuan status reset ke pending atau approved_prodi',
                'Main Flow' => [
                    '1. Admin akses detail pengajuan rejected',
                    '2. Admin klik "Reopen Pengajuan"',
                    '3. Modal muncul dengan pilihan reset status',
                    '4. Admin pilih reset ke: pending atau approved_prodi',
                    '5. Admin input alasan reopen',
                    '6. System update status sesuai pilihan',
                    '7. System clear rejection data',
                    '8. System log ke audit trail',
                    '9. Pengajuan kembali masuk workflow',
                ],
            ],
            'UC-006: Export Report' => [
                'Actor' => 'Admin',
                'Precondition' => 'Admin sudah login',
                'Postcondition' => 'File Excel ter-download',
                'Main Flow' => [
                    '1. Admin akses halaman yang ingin di-export (pengajuan/audit trail)',
                    '2. Admin apply filter jika perlu',
                    '3. Admin klik tombol "Export Excel"',
                    '4. System generate Excel file dengan data sesuai filter',
                    '5. System trigger download file',
                ],
            ],
        ];
    }

    private function getUserStories()
    {
        return [
            'US-001' => [
                'As a' => 'Mahasiswa',
                'I want to' => 'Submit pengajuan surat tanpa perlu login',
                'So that' => 'Saya bisa mengajukan surat dengan cepat dan mudah',
                'Acceptance Criteria' => [
                    'Form accessible tanpa authentication',
                    'Tracking token generated automatically',
                    'Confirmation message dengan tracking token ditampilkan',
                ],
            ],
            'US-002' => [
                'As a' => 'Staff Prodi',
                'I want to' => 'See list of pending pengajuan dengan filter options',
                'So that' => 'Saya bisa prioritize pengajuan yang perlu di-review',
                'Acceptance Criteria' => [
                    'List sortable by date, prodi, jenis surat',
                    'Search functionality available',
                    'Pagination for large datasets',
                ],
            ],
            'US-003' => [
                'As a' => 'Admin',
                'I want to' => 'Force complete stuck pengajuan dengan mandatory reason',
                'So that' => 'Pengajuan tidak terhambat dan ada audit trail',
                'Acceptance Criteria' => [
                    'Reason field mandatory',
                    'Action logged to audit_trails',
                    'Status updated to completed',
                    'Confirmation modal before action',
                ],
            ],
            'US-004' => [
                'As a' => 'Admin',
                'I want to' => 'View comprehensive dashboard dengan charts',
                'So that' => 'Saya bisa monitor performance dan identify bottlenecks',
                'Acceptance Criteria' => [
                    'Statistics cards for key metrics',
                    'Line chart for trend',
                    'Pie chart for status distribution',
                    'Bar chart for per-prodi data',
                    'Alert for stuck pengajuan',
                ],
            ],
            'US-005' => [
                'As a' => 'Staff Fakultas',
                'I want to' => 'Generate final surat dengan preview before finalize',
                'So that' => 'Saya bisa ensure surat correct sebelum distribute',
                'Acceptance Criteria' => [
                    'Preview PDF available',
                    'Edit capability before finalize',
                    'Upload signed document option',
                    'One-click notify mahasiswa',
                ],
            ],
        ];
    }

    private function getAcceptanceCriteria()
    {
        return [
            'Authentication' => [
                'User dapat login dengan email dan password',
                'Session maintained selama user active',
                'Logout menghapus session',
                'Invalid credentials menampilkan error message',
            ],
            'Pengajuan Submission' => [
                'Form validasi all required fields',
                'Tracking token unique dan readable',
                'Confirmation page menampilkan tracking token',
                'Data tersimpan ke database dengan status pending',
            ],
            'Approval Workflow' => [
                'Staff prodi hanya bisa approve/reject pengajuan prodi mereka',
                'Rejection wajib include reason',
                'Approval history tercatat lengkap',
                'Status progression follow defined workflow',
            ],
            'Admin Intervention' => [
                'Force complete only available untuk status tertentu',
                'Reopen only available untuk rejected status',
                'All interventions require reason',
                'All interventions logged ke audit trail',
            ],
            'Master Data Management' => [
                'CRUD operations work correctly',
                'Validation prevent duplicate entries',
                'Foreign key constraints enforced',
                'Cannot delete record yang sedang digunakan',
            ],
            'Export Functionality' => [
                'Export respect active filters',
                'Excel file formatted properly',
                'All relevant columns included',
                'File download triggered automatically',
            ],
        ];
    }

    private function getTraceabilityMatrix()
    {
        return [
            'FR-001' => ['UC-001', 'US-001'],
            'FR-002' => ['UC-001'],
            'FR-003' => ['UC-002'],
            'FR-004' => ['UC-003'],
            'FR-005' => ['UC-003'],
            'FR-006' => ['UC-003', 'US-005'],
            'FR-007' => ['UC-003'],
            'FR-008' => ['UC-004', 'UC-005', 'US-003'],
            'FR-009' => ['UC-004', 'UC-005'],
            'FR-010' => ['UC-004'],
            'FR-011' => ['UC-005'],
            'FR-012' => ['US-004'],
            'FR-013' => ['UC-006'],
            'FR-014' => ['UC-003'],
            'FR-015' => [],
            'FR-016' => [],
            'FR-017' => ['US-004'],
            'FR-018' => ['UC-001'],
            'FR-019' => ['UC-002'],
            'FR-020' => ['All use cases'],
        ];
    }

    // ===== MARKDOWN FORMATTERS =====

    private function formatSDDMarkdown($sdd)
    {
        $md = "# Software Design Document (SDD)\n\n";
        $md .= "## {$sdd['metadata']['project_name']}\n";
        $md .= "**Version:** {$sdd['metadata']['version']}\n\n";
        $md .= "**Generated:** {$sdd['metadata']['generated_at']}\n\n";
        $md .= "**Author:** {$sdd['metadata']['author']}\n\n";
        $md .= "---\n\n";
        
        $md .= "## Table of Contents\n\n";
        $md .= "1. [Introduction](#introduction)\n";
        $md .= "2. [System Context](#system-context)\n";
        $md .= "3. [Architecture Overview](#architecture-overview)\n";
        $md .= "4. [Database Design](#database-design)\n";
        $md .= "5. [Components](#components)\n";
        $md .= "6. [Security Architecture](#security-architecture)\n";
        $md .= "7. [Deployment Architecture](#deployment-architecture)\n";
        $md .= "8. [Error Handling](#error-handling)\n\n";
        $md .= "---\n\n";

        $md .= "## 1. Introduction\n\n";
        $md .= "**Purpose:** {$sdd['introduction']['purpose']}\n\n";
        $md .= "**Scope:** {$sdd['introduction']['scope']}\n\n";
        $md .= "**Intended Audience:** " . implode(', ', $sdd['introduction']['intended_audience']) . "\n\n";

        $md .= "## 2. System Context\n\n";
        $md .= "### External Systems\n\n";
        foreach ($sdd['system_context']['external_systems'] as $system => $desc) {
            $md .= "- **{$system}:** {$desc}\n";
        }
        $md .= "\n### System Actors\n\n";
        foreach ($sdd['system_context']['actors'] as $actor => $desc) {
            $md .= "- **{$actor}:** {$desc}\n";
        }
        $md .= "\n";

        $md .= "## 3. Architecture Overview\n\n";
        $md .= "**Pattern:** {$sdd['architecture']['pattern']}\n\n";
        $md .= "**Framework:** {$sdd['architecture']['framework']}\n\n";
        $md .= "### Frontend Stack\n\n";
        foreach ($sdd['architecture']['frontend'] as $key => $value) {
            $md .= "- **{$key}:** {$value}\n";
        }
        $md .= "\n### Backend Stack\n\n";
        foreach ($sdd['architecture']['backend'] as $key => $value) {
            $md .= "- **{$key}:** {$value}\n";
        }
        $md .= "\n### Database\n\n";
        foreach ($sdd['architecture']['database'] as $key => $value) {
            $md .= "- **{$key}:** {$value}\n";
        }
        $md .= "\n";

        $md .= "## 4. Database Design\n\n";
        $md .= "*See ERD.puml and ERD_mermaid.md for visual representation*\n\n";
        foreach ($sdd['database'] as $table => $schema) {
            $md .= "### Table: `{$table}`\n\n";
            $md .= "| Column | Type | Null | Key | Default | Extra |\n";
            $md .= "|--------|------|------|-----|---------|-------|\n";
            foreach ($schema['columns'] as $col) {
                $md .= "| {$col->Field} | {$col->Type} | {$col->Null} | {$col->Key} | " . ($col->Default ?? 'NULL') . " | {$col->Extra} |\n";
            }
            $md .= "\n";
        }

        $md .= "## 5. Components\n\n";
        $md .= "### Controllers\n\n";
        foreach ($sdd['controllers'] as $class => $data) {
            $shortName = class_basename($class);
            $md .= "#### {$shortName}\n\n";
            $md .= "**File:** `{$data['file']}`\n\n";
            $md .= "**Methods:**\n";
            foreach ($data['methods'] as $method) {
                $params = implode(', $', $method['parameters']);
                if ($params) $params = '$' . $params;
                $md .= "- `{$method['name']}({$params})`\n";
            }
            $md .= "\n";
        }

        $md .= "### Models\n\n";
        foreach ($sdd['models'] as $class => $data) {
            $shortName = class_basename($class);
            $md .= "#### {$shortName}\n\n";
            $md .= "**Table:** `{$data['table']}`\n\n";
            if (!empty($data['fillable'])) {
                $md .= "**Fillable:** `" . implode('`, `', $data['fillable']) . "`\n\n";
            }
            if (!empty($data['relationships'])) {
                $md .= "**Relationships:**\n";
                foreach ($data['relationships'] as $rel) {
                    $md .= "- `{$rel['name']}()` → {$rel['type']}\n";
                }
            }
            $md .= "\n";
        }

        $md .= "## 6. Security Architecture\n\n";
        $md .= "### Authentication\n\n";
        foreach ($sdd['security']['authentication'] as $key => $value) {
            if (is_array($value)) {
                $md .= "**{$key}:** " . implode(', ', $value) . "\n\n";
            } else {
                $md .= "**{$key}:** {$value}\n\n";
            }
        }
        $md .= "### Authorization\n\n";
        foreach ($sdd['security']['authorization'] as $key => $value) {
            if (is_array($value)) {
                $md .= "**{$key}:** " . implode(', ', $value) . "\n\n";
            } else {
                $md .= "**{$key}:** {$value}\n\n";
            }
        }
        $md .= "### Data Protection\n\n";
        foreach ($sdd['security']['data_protection'] as $key => $value) {
            $md .= "- **{$key}:** {$value}\n";
        }
        $md .= "\n";

        $md .= "## 7. Deployment Architecture\n\n";
        $md .= "### Recommended Stack\n\n";
        foreach ($sdd['deployment']['recommended_stack'] as $key => $value) {
            $md .= "- **{$key}:** {$value}\n";
        }
        $md .= "\n### Server Requirements\n\n";
        foreach ($sdd['deployment']['server_requirements'] as $key => $value) {
            $md .= "- **{$key}:** {$value}\n";
        }
        $md .= "\n";

        $md .= "## 8. Error Handling\n\n";
        $md .= "**Strategy:** {$sdd['error_handling']['strategy']}\n\n";
        $md .= "### Logging\n\n";
        foreach ($sdd['error_handling']['logging'] as $key => $value) {
            if (is_array($value)) {
                $md .= "**{$key}:** " . implode(', ', $value) . "\n\n";
            } else {
                $md .= "**{$key}:** {$value}\n\n";
            }
        }

        return $md;
    }

    private function formatSRSMarkdown($srs)
    {
        $md = "# Software Requirements Specification (SRS)\n\n";
        $md .= "## {$srs['metadata']['project_name']}\n";
        $md .= "**Version:** {$srs['metadata']['version']}\n\n";
        $md .= "**Generated:** {$srs['metadata']['generated_at']}\n\n";
        $md .= "---\n\n";

        $md .= "## Table of Contents\n\n";
        $md .= "1. [Introduction](#introduction)\n";
        $md .= "2. [Project Overview](#project-overview)\n";
        $md .= "3. [Features](#features)\n";
        $md .= "4. [User Roles](#user-roles)\n";
        $md .= "5. [Functional Requirements](#functional-requirements)\n";
        $md .= "6. [Non-Functional Requirements](#non-functional-requirements)\n";
        $md .= "7. [Interface Requirements](#interface-requirements)\n";
        $md .= "8. [Data Requirements](#data-requirements)\n";
        $md .= "9. [Constraints](#constraints)\n";
        $md .= "10. [Assumptions & Dependencies](#assumptions--dependencies)\n";
        $md .= "11. [Use Cases](#use-cases)\n";
        $md .= "12. [User Stories](#user-stories)\n";
        $md .= "13. [Acceptance Criteria](#acceptance-criteria)\n";
        $md .= "14. [Traceability Matrix](#traceability-matrix)\n\n";
        $md .= "---\n\n";

        $md .= "## 1. Introduction\n\n";
        $md .= "**Purpose:** {$srs['introduction']['purpose']}\n\n";
        $md .= "**Scope:** {$srs['introduction']['scope']}\n\n";
        $md .= "**Intended Audience:** " . implode(', ', $srs['introduction']['intended_audience']) . "\n\n";

        $md .= "## 2. Project Overview\n\n";
        $md .= "**Purpose:** {$srs['overview']['purpose']}\n\n";
        $md .= "**Scope:** {$srs['overview']['scope']}\n\n";
        $md .= "### Target Users\n\n";
        foreach ($srs['overview']['target_users'] as $role => $desc) {
            $md .= "- **{$role}:** {$desc}\n";
        }
        $md .= "\n### Benefits\n\n";
        foreach ($srs['overview']['benefits'] as $benefit) {
            $md .= "- {$benefit}\n";
        }
        $md .= "\n";

        $md .= "## 3. Features\n\n";
        foreach ($srs['features'] as $category => $features) {
            $md .= "### {$category}\n\n";
            foreach ($features as $feature) {
                $md .= "- {$feature}\n";
            }
            $md .= "\n";
        }

        $md .= "## 4. User Roles\n\n";
        foreach ($srs['user_roles'] as $id => $role) {
            $md .= "- **{$role}**\n";
        }
        $md .= "\n";

        $md .= "## 5. Functional Requirements\n\n";
        foreach ($srs['functional_requirements'] as $id => $req) {
            $md .= "**{$id}:** {$req}\n\n";
        }

        $md .= "## 6. Non-Functional Requirements\n\n";
        foreach ($srs['non_functional_requirements'] as $category => $reqs) {
            $md .= "### {$category}\n\n";
            if (is_array($reqs)) {
                foreach ($reqs as $req) {
                    $md .= "- {$req}\n";
                }
            } else {
                $md .= "{$reqs}\n";
            }
            $md .= "\n";
        }

        $md .= "## 7. Interface Requirements\n\n";
        foreach ($srs['interface_requirements'] as $type => $reqs) {
            $md .= "### {$type}\n\n";
            foreach ($reqs as $req) {
                $md .= "- {$req}\n";
            }
            $md .= "\n";
        }

        $md .= "## 8. Data Requirements\n\n";
        foreach ($srs['data_requirements'] as $category => $data) {
            $md .= "### {$category}\n\n";
            foreach ($data as $entity => $fields) {
                $md .= "**{$entity}:** {$fields}\n\n";
            }
        }

        $md .= "## 9. Constraints\n\n";
        foreach ($srs['constraints'] as $category => $constraints) {
            $md .= "### {$category}\n\n";
            foreach ($constraints as $constraint) {
                $md .= "- {$constraint}\n";
            }
            $md .= "\n";
        }

        $md .= "## 10. Assumptions & Dependencies\n\n";
        foreach ($srs['assumptions'] as $assumption) {
            $md .= "- {$assumption}\n";
        }
        $md .= "\n";

        $md .= "## 11. Use Cases\n\n";
        foreach ($srs['use_cases'] as $title => $uc) {
            $md .= "### {$title}\n\n";
            foreach ($uc as $key => $value) {
                if (is_array($value)) {
                    $md .= "**{$key}:**\n";
                    foreach ($value as $item) {
                        $md .= "  {$item}\n";
                    }
                } else {
                    $md .= "**{$key}:** {$value}\n";
                }
                $md .= "\n";
            }
        }

        $md .= "## 12. User Stories\n\n";
        foreach ($srs['user_stories'] as $id => $story) {
            $md .= "### {$id}\n\n";
            $md .= "**As a** {$story['As a']}, **I want to** {$story['I want to']}, **so that** {$story['So that']}\n\n";
            $md .= "**Acceptance Criteria:**\n";
            foreach ($story['Acceptance Criteria'] as $criterion) {
                $md .= "- {$criterion}\n";
            }
            $md .= "\n";
        }

        $md .= "## 13. Acceptance Criteria\n\n";
        foreach ($srs['acceptance_criteria'] as $category => $criteria) {
            $md .= "### {$category}\n\n";
            foreach ($criteria as $criterion) {
                $md .= "- {$criterion}\n";
            }
            $md .= "\n";
        }

        $md .= "## 14. Traceability Matrix\n\n";
        $md .= "| Functional Requirement | Use Cases | User Stories |\n";
        $md .= "|------------------------|-----------|-------------|\n";
        foreach ($srs['traceability_matrix'] as $fr => $mappings) {
            $md .= "| {$fr} | " . implode(', ', $mappings) . " | |\n";
        }
        $md .= "\n";

        return $md;
    }
}