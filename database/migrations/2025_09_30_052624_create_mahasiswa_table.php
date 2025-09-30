<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create mahasiswa table
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20)->unique();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('phone', 20);
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('restrict');
            $table->enum('status', ['aktif', 'cuti', 'lulus', 'keluar'])->default('aktif');
            $table->year('angkatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['prodi_id', 'status']);
            $table->index('angkatan');
        });
        
        // 2. Migrate existing data from pengajuan_surats to mahasiswa
        $this->migrateExistingMahasiswaData();
        
        // 3. Add mahasiswa_id to pengajuan_surats
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            $table->foreignId('mahasiswa_id')->nullable()->after('id')->constrained('mahasiswa')->onDelete('restrict');
            $table->index('mahasiswa_id');
        });
        
        // 4. Populate mahasiswa_id in pengajuan_surats
        $this->linkPengajuanToMahasiswa();
        
        // 5. Make mahasiswa_id NOT NULL after population
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            $table->foreignId('mahasiswa_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_surats', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->dropColumn('mahasiswa_id');
        });
        
        Schema::dropIfExists('mahasiswa');
    }
    
    /**
     * Migrate existing mahasiswa data from pengajuan_surats
     */
    private function migrateExistingMahasiswaData(): void
    {
        // Get unique mahasiswa from pengajuan_surats
        $uniqueMahasiswa = DB::table('pengajuan_surats')
            ->select('nim', 'nama_mahasiswa', 'email', 'phone', 'prodi_id')
            ->distinct()
            ->get();
        
        foreach ($uniqueMahasiswa as $mhs) {
            // Check if already exists
            $exists = DB::table('mahasiswa')->where('nim', $mhs->nim)->exists();
            
            if (!$exists) {
                DB::table('mahasiswa')->insert([
                    'nim' => $mhs->nim,
                    'nama' => $mhs->nama_mahasiswa,
                    'email' => $mhs->email,
                    'phone' => $mhs->phone,
                    'prodi_id' => $mhs->prodi_id,
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    /**
     * Link pengajuan_surats to mahasiswa table
     */
    private function linkPengajuanToMahasiswa(): void
    {
        DB::table('pengajuan_surats')
            ->join('mahasiswa', 'pengajuan_surats.nim', '=', 'mahasiswa.nim')
            ->update(['pengajuan_surats.mahasiswa_id' => DB::raw('mahasiswa.id')]);
    }
};