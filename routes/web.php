<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FakultasController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\PublicSuratController;
use App\Http\Controllers\SuratFSIController;
use App\Http\Controllers\FakultasStaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['verified'])
        ->name('dashboard');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
    

    // Surat (general)
    Route::prefix('surat')->name('surat.')->group(function () {
        Route::get('/', [SuratController::class, 'index'])->name('index');
        Route::get('/{id}', [SuratController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SuratController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SuratController::class, 'update'])->name('update');
        Route::post('/{id}/approve', [SuratController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [SuratController::class, 'reject'])->name('reject');
        Route::post('/{id}/submit', [SuratController::class, 'submit'])->name('submit');
        Route::get('/{id}/tracking', [SuratController::class, 'tracking'])->name('tracking');
        Route::get('/{id}/download', [SuratController::class, 'download'])->name('download');
    });

    // Staff Routes (Prodi & Fakultas)
    Route::middleware(['role:staff_prodi,staff_fakultas'])->prefix('staff')->name('staff.')->group(function () {
        Route::resource('surat', SuratController::class)->except(['index']);
        Route::get('surat', [SuratController::class, 'staffIndex'])->name('surat.index');


                // TAMBAHKAN: Routes untuk surat pengantar
        Route::get('/{id}/pengantar/preview', [App\Http\Controllers\StaffPengajuanController::class, 'previewPengantar'])
            ->name('pengantar.preview');
        Route::post('/{id}/pengantar/store', [App\Http\Controllers\StaffPengajuanController::class, 'storePengantar'])
            ->name('pengantar.store');
        });

        // Staff Prodi only
        Route::middleware(['role:staff_prodi'])->group(function () {
            Route::get('surat/create-from-pengajuan/{id}', [SuratController::class, 'createFromPengajuan'])
                ->name('surat.create-from-pengajuan');
        });

// Route untuk debug - HAPUS SETELAH SELESAI
Route::get('/debug/approval/{id}', function($id) {
    $pengajuan = \App\Models\PengajuanSurat::with(['jenisSurat', 'prodi'])->findOrFail($id);
    
    echo "<h1>DEBUG APPROVAL FLOW</h1>";
    echo "<style>
        body { font-family: monospace; padding: 20px; }
        .section { border: 2px solid #ccc; margin: 10px 0; padding: 10px; }
        .error { background: #ffebee; border-color: #f44336; }
        .success { background: #e8f5e9; border-color: #4caf50; }
        .warning { background: #fff3e0; border-color: #ff9800; }
        h3 { margin-top: 0; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .code { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>";
    
    // 1. Info Pengajuan
    echo "<div class='section'>";
    echo "<h3>📋 Informasi Pengajuan</h3>";
    echo "<table>";
    echo "<tr><td><strong>ID</strong></td><td>{$pengajuan->id}</td></tr>";
    echo "<tr><td><strong>Tracking Token</strong></td><td>{$pengajuan->tracking_token}</td></tr>";
    echo "<tr><td><strong>Mahasiswa</strong></td><td>{$pengajuan->nama_mahasiswa} ({$pengajuan->nim})</td></tr>";
    echo "<tr><td><strong>Jenis Surat</strong></td><td>{$pengajuan->jenisSurat->nama_jenis} ({$pengajuan->jenisSurat->kode_surat})</td></tr>";
    echo "<tr><td><strong>Status Saat Ini</strong></td><td><strong>{$pengajuan->status}</strong></td></tr>";
    echo "<tr><td><strong>Approved By Prodi</strong></td><td>" . ($pengajuan->approved_by_prodi ?? 'NULL') . "</td></tr>";
    echo "<tr><td><strong>Approved At Prodi</strong></td><td>" . ($pengajuan->approved_at_prodi ?? 'NULL') . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // 2. Cek Method needsSuratPengantar()
    echo "<div class='section " . ($pengajuan->needsSuratPengantar() ? 'warning' : 'success') . "'>";
    echo "<h3>🔍 Cek needsSuratPengantar()</h3>";
    $needs = $pengajuan->needsSuratPengantar();
    echo "<p><strong>Result:</strong> " . ($needs ? 'TRUE (Perlu pengantar)' : 'FALSE (Tidak perlu)') . "</p>";
    echo "<div class='code'>";
    echo "// Method di Model PengajuanSurat.php<br>";
    echo "public function needsSuratPengantar() {<br>";
    echo "&nbsp;&nbsp;return in_array(\$this->jenisSurat->kode_surat, ['KP', 'TA']);<br>";
    echo "}<br><br>";
    echo "Kode Surat: {$pengajuan->jenisSurat->kode_surat}<br>";
    echo "in_array('{$pengajuan->jenisSurat->kode_surat}', ['KP', 'TA']) = " . ($needs ? 'TRUE' : 'FALSE');
    echo "</div>";
    echo "</div>";
    
    // 3. Cek Method hasSuratPengantar()
    echo "<div class='section " . ($pengajuan->hasSuratPengantar() ? 'success' : 'error') . "'>";
    echo "<h3>📄 Cek hasSuratPengantar()</h3>";
    $has = $pengajuan->hasSuratPengantar();
    echo "<p><strong>Result:</strong> " . ($has ? 'TRUE (Sudah ada)' : 'FALSE (Belum ada)') . "</p>";
    echo "<table>";
    echo "<tr><td><strong>surat_pengantar_url</strong></td><td>" . ($pengajuan->surat_pengantar_url ?? 'NULL') . "</td></tr>";
    echo "<tr><td><strong>surat_pengantar_nomor</strong></td><td>" . ($pengajuan->surat_pengantar_nomor ?? 'NULL') . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // 4. Simulasi Approval Flow
    echo "<div class='section warning'>";
    echo "<h3>⚙️ Simulasi Approval Flow</h3>";
    echo "<p><strong>Ketika staff prodi klik 'Setujui', ini yang terjadi:</strong></p>";
    echo "<div class='code'>";
    echo "// StaffPengajuanController@processPengajuan (Line 86-96)<br><br>";
    echo "if (\$request->action === 'approve') {<br>";
    echo "&nbsp;&nbsp;\$pengajuan->status = 'approved_prodi'; // ✅ Set status<br>";
    echo "&nbsp;&nbsp;\$pengajuan->approved_by_prodi = \$user->id;<br>";
    echo "&nbsp;&nbsp;\$pengajuan->approved_at_prodi = now();<br>";
    echo "&nbsp;&nbsp;\$pengajuan->save();<br><br>";
    echo "&nbsp;&nbsp;// Pesan berbeda berdasarkan jenis surat<br>";
    echo "&nbsp;&nbsp;if (\$pengajuan->needsSuratPengantar()) {<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;\$message = 'Silakan generate surat pengantar...';<br>";
    echo "&nbsp;&nbsp;} else {<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;\$message = 'Diteruskan ke fakultas...';<br>";
    echo "&nbsp;&nbsp;}<br>";
    echo "}<br><br>";
    echo "<strong>UNTUK PENGAJUAN INI:</strong><br>";
    echo "- Status AKAN diubah ke: <span style='color: green;'>approved_prodi</span><br>";
    echo "- Pesan yang muncul: ";
    if ($pengajuan->needsSuratPengantar()) {
        echo "'<span style='color: orange;'>Silakan generate surat pengantar untuk diteruskan ke fakultas.</span>'";
    } else {
        echo "'<span style='color: blue;'>Pengajuan disetujui dan diteruskan ke fakultas untuk diproses.</span>'";
    }
    echo "</div>";
    echo "</div>";
    
    // 5. Cek Tracking History
    echo "<div class='section'>";
    echo "<h3>📜 Tracking History</h3>";
    $histories = $pengajuan->trackingHistory()->orderBy('created_at', 'desc')->limit(5)->get();
    if ($histories->count() > 0) {
        echo "<table>";
        echo "<tr><th>Waktu</th><th>Status</th><th>Deskripsi</th></tr>";
        foreach ($histories as $h) {
            echo "<tr>";
            echo "<td>{$h->created_at->format('Y-m-d H:i:s')}</td>";
            echo "<td><strong>{$h->status}</strong></td>";
            echo "<td>{$h->description}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada history</p>";
    }
    echo "</div>";
    
    // 6. MASALAH POTENSIAL
    echo "<div class='section error'>";
    echo "<h3>🚨 KEMUNGKINAN MASALAH</h3>";
    echo "<ol>";
    
    // Cek apakah status berubah ke processed
    if ($pengajuan->status === 'processed') {
        echo "<li><strong>STATUS 'processed' TERDETEKSI!</strong><br>";
        echo "Kemungkinan ada kode lain yang mengubah status ke 'processed' setelah approve.<br>";
        echo "Periksa:<br>";
        echo "- PublicSuratController@createSuratFromPengajuan (Line 366-382)<br>";
        echo "- Apakah ada redirect/ajax yang memanggil route lain?<br>";
        echo "- Cek JavaScript di view staff.pengajuan.show</li>";
    }
    
    // Cek route
    echo "<li><strong>Route Check:</strong><br>";
    echo "URL approve seharusnya: <code>/staff/pengajuan/{$pengajuan->id}/process</code><br>";
    echo "Method: POST<br>";
    echo "Action: 'approve'</li>";
    
    // Cek method yang tersedia
    echo "<li><strong>Methods Available:</strong><br>";
    $reflection = new \ReflectionClass($pengajuan);
    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
    $relevantMethods = ['needsSuratPengantar', 'hasSuratPengantar', 'canEditSurat', 'canPrintSurat'];
    echo "<ul>";
    foreach ($methods as $method) {
        if (in_array($method->name, $relevantMethods)) {
            echo "<li>{$method->name}() - Defined in {$method->class}</li>";
        }
    }
    echo "</ul></li>";
    
    echo "</ol>";
    echo "</div>";
    
    // 7. CEK DATABASE LANGSUNG
    echo "<div class='section'>";
    echo "<h3>💾 Query Database Raw</h3>";
    $raw = DB::table('pengajuan_surat')->where('id', $pengajuan->id)->first();
    echo "<pre>" . json_encode($raw, JSON_PRETTY_PRINT) . "</pre>";
    echo "</div>";
    
    // 8. REKOMENDASI
    echo "<div class='section success'>";
    echo "<h3>✅ Rekomendasi Fix</h3>";
    echo "<ol>";
    echo "<li>Pastikan controller StaffPengajuanController line 86 menggunakan:<br>";
    echo "<code>\$pengajuan->status = 'approved_prodi';</code><br>";
    echo "BUKAN <code>'processed'</code></li>";
    
    echo "<li>Cek apakah ada middleware/observer yang mengubah status</li>";
    
    echo "<li>Tambahkan logging di StaffPengajuanController:<br>";
    echo "<code>\\Log::info('After approve', ['status' => \$pengajuan->status]);</code></li>";
    
    echo "<li>Cek JavaScript di view untuk memastikan fetch ke route yang benar</li>";
    echo "</ol>";
    echo "</div>";
    
    // 9. TEST BUTTON
    echo "<div class='section'>";
    echo "<h3>🧪 Test Action</h3>";
    echo "<form method='POST' action='/staff/pengajuan/{$pengajuan->id}/process'>";
    echo csrf_field();
    echo "<input type='hidden' name='action' value='approve'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #4caf50; color: white; border: none; cursor: pointer;'>
        Simulate Approve
    </button>";
    echo "</form>";
    echo "<p style='color: red;'><strong>WARNING:</strong> Ini akan benar-benar approve pengajuan!</p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<p><a href='/staff/pengajuan/{$pengajuan->id}'>← Kembali ke Detail Pengajuan</a></p>";
});
    });

    // Kaprodi Routes
    Route::middleware(['role:kaprodi'])->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('surat/approval', [SuratController::class, 'approvalList'])->name('surat.approval');
    });

    // Pimpinan Routes
    Route::middleware(['role:pimpinan,dekan,wd1,wd2,wd3'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
        Route::get('surat/disposisi', fn() => view('pimpinan.surat.disposisi'))->name('surat.disposisi');
        Route::post('surat/{id}/disposisi', [DisposisiController::class, 'store'])->name('surat.disposisi.store');
        Route::get('surat/ttd', fn() => view('pimpinan.surat.ttd'))->name('surat.ttd');
        Route::post('surat/{id}/ttd', [SuratController::class, 'tandaTangan'])->name('surat.ttd.process');
    });

    // Admin Routes
    Route::middleware(['role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('fakultas', FakultasController::class);
        Route::resource('prodi', ProdiController::class);
        Route::resource('jabatan', JabatanController::class);
    });

    // General Admin Resources
    // Route::resource('tracking', TrackingController::class);


Route::get('/debug-route', function() {
    $routes = Route::getRoutes();
    $trackingRoutes = [];
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'tracking')) {
            $trackingRoutes[] = [
                'uri' => $route->uri(),
                'action' => $route->getActionName(),
                'name' => $route->getName()
            ];
        }
    }
    
    return response()->json($trackingRoutes);
});

/*
|--------------------------------------------------------------------------
| FAKULTAS STAFF ROUTES - CONSOLIDATED
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:staff_fakultas'])->prefix('fakultas')->name('fakultas.')->group(function () {
    // Main surat listing
    Route::get('surat', [FakultasStaffController::class, 'index'])->name('surat.index');
    
    // Standard fakultas routes
    Route::get('surat/preview/{id}', [FakultasStaffController::class, 'previewPengajuan'])->name('surat.preview');
    Route::get('surat/edit/{id}', [FakultasStaffController::class, 'editPengajuan'])->name('surat.edit');
    Route::put('surat/update/{id}', [FakultasStaffController::class, 'updatePengajuan'])->name('surat.update');
    Route::get('surat/generate/{id}', [FakultasStaffController::class, 'generateSuratPDF'])->name('surat.generate');
    Route::post('surat/generate-pdf/{id}', [FakultasStaffController::class, 'generateSuratPDF'])->name('surat.generate-pdf');
    Route::post('surat/kirim-ke-pengaju/{id}', [FakultasStaffController::class, 'kirimKePengaju'])->name('surat.kirim-pengaju');
    
    // Pengajuan processing
    Route::get('pengajuan', [FakultasStaffController::class, 'pengajuanFromProdi'])->name('pengajuan.index');
    Route::post('pengajuan/{id}/process', [FakultasStaffController::class, 'processPengajuanFromProdi'])->name('pengajuan.process');
    Route::post('pengajuan/{id}/generate-surat', [FakultasStaffController::class, 'generateSuratFromPengajuan'])->name('pengajuan.generate');
    
    // FSI Specific Routes - NEW WORKFLOW
    Route::prefix('surat/fsi')->name('surat.fsi.')->group(function () {
        Route::get('preview/{id}', [SuratFSIController::class, 'preview'])->name('preview');
        Route::post('save-edits/{id}', [SuratFSIController::class, 'saveEdits'])->name('save-edits');
        Route::get('print/{id}', [SuratFSIController::class, 'printSurat'])->name('print');
        Route::post('upload-signed/{id}', [SuratFSIController::class, 'uploadSignedLink'])->name('upload-signed');
        Route::post('reject/{id}', [SuratFSIController::class, 'rejectSurat'])->name('reject');
        Route::post('generate-pdf/{id}', [SuratFSIController::class, 'generatePdf'])->name('generate-pdf');
        Route::get('status/{pengajuanId}', [SuratFSIController::class, 'getSuratStatus'])->name('status');
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN BARCODE MANAGEMENT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin/barcode-signatures')->name('admin.barcode-signatures.')->group(function () {
    Route::get('/', function () {
        $barcodes = \App\Models\BarcodeSignature::with('fakultas')->paginate(10);
        return view('admin.barcode-signatures', compact('barcodes'));
    })->name('index');

    Route::post('/', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'fakultas_id' => 'nullable|exists:fakultas,id',
            'pejabat_nama' => 'required|string|max:255',
            'pejabat_jabatan' => 'required|string|max:255',
            'pejabat_nid' => 'nullable|string|max:50',
            'barcode_image' => 'required|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        $path = $request->file('barcode_image')->store('barcode-signatures', 'public');

        \App\Models\BarcodeSignature::create([
            'fakultas_id' => $request->fakultas_id,
            'pejabat_nama' => $request->pejabat_nama,
            'pejabat_jabatan' => $request->pejabat_jabatan,
            'pejabat_nid' => $request->pejabat_nid,
            'barcode_path' => $path,
            'is_active' => true
        ]);

        return redirect()->back()->with('success', 'Barcode berhasil ditambahkan');
    })->name('store');
});
/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES - FIXED
|--------------------------------------------------------------------------
*/
Route::middleware(['throttle:30,1'])->group(function () {
    // Pengajuan routes
    Route::get('/pengajuan-surat', [App\Http\Controllers\PublicSuratController::class, 'create'])->name('public.pengajuan.create');
    Route::post('/pengajuan-surat', [App\Http\Controllers\PublicSuratController::class, 'store'])->name('public.pengajuan.store');

    // Tracking - SPECIFIC ROUTES FIRST
    Route::get('/tracking', [App\Http\Controllers\PublicSuratController::class, 'trackingIndex'])->name('tracking.public');
    Route::post('/tracking/search', [App\Http\Controllers\PublicSuratController::class, 'trackingSearch'])->name('tracking.search');
    Route::post('/tracking/api', [App\Http\Controllers\PublicSuratController::class, 'trackingApi'])->name('tracking.api');
    Route::get('/tracking/download/{id}', [App\Http\Controllers\PublicSuratController::class, 'downloadSurat'])->name('tracking.download')->where('id', '[0-9]+');
    
    // Dynamic route LAST
    Route::get('/tracking/{token}', [App\Http\Controllers\PublicSuratController::class, 'trackingShow'])->name('tracking.show');
});

// Dosen Wali API 
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/api/dosen-wali/{prodi_id}', [App\Http\Controllers\PublicSuratController::class, 'getDosenWali'])->name('api.dosen-wali.get');
    Route::post('/api/dosen-wali/search', [App\Http\Controllers\PublicSuratController::class, 'searchDosenWali'])->name('api.dosen-wali.search');
});

// Public routes (no auth required)
Route::prefix('public')->name('public.')->group(function () {
    // Existing routes...
    Route::get('pengajuan/create', [PublicSuratController::class, 'create'])->name('pengajuan.create');
    Route::post('pengajuan/store', [PublicSuratController::class, 'store'])->name('pengajuan.store');
    
    // Dosen Wali API - with optional parameter
    Route::get('dosen-wali/{prodiId?}', [PublicSuratController::class, 'getDosenWali'])->name('dosen.wali');
});

// Or if you prefer API routes
Route::prefix('api')->group(function () {
    Route::get('dosen-wali/{prodiId}', [PublicSuratController::class, 'getDosenWali']);
});



/*
|--------------------------------------------------------------------------
| DEBUG & INCLUDES
|--------------------------------------------------------------------------
*/
Route::get('/debug-alpine', fn() => view('debug.alpine'));

Route::get('/test-log', function() {
    \Log::info('LOG TEST VIA BROWSER', ['time' => now()]);
    
    $logPath = storage_path('logs/laravel.log');
    
    if (file_exists($logPath)) {
        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -20);
        
        return '<pre>' . implode("\n", $lastLines) . '</pre>';
    }
    
    return 'Log file not found at: ' . $logPath;
});

require __DIR__ . '/auth.php';
require __DIR__ . '/fakultas.php';
require __DIR__ . '/staff.php';