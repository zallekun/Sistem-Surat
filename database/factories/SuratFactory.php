<?php

namespace Database\Factories;

use App\Models\Surat;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StatusSurat;
use App\Models\JenisSurat;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Prodi;
use App\Models\Fakultas;

class SuratFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Surat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $statusDraft = StatusSurat::where('kode_status', 'draft')->first();
        $jenisBiasa = JenisSurat::first();
        $staffProdi = User::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Staff Program Studi'))->first();
        $prodi = Prodi::first();
        $fakultas = Fakultas::first();
        $tujuanJabatan = Jabatan::where('nama_jabatan', 'Dekan')->first();

        return [
            'nomor_surat' => $this->faker->unique()->numerify('###/ABC/DEF/###'),
            'tanggal_surat' => $this->faker->date(),
            'perihal' => $this->faker->sentence(),
            'isi_surat' => $this->faker->paragraph(),
            'tipe_surat' => $this->faker->randomElement(['masuk', 'keluar']),
            'sifat_surat' => $this->faker->randomElement(['biasa', 'segera', 'sangat_segera', 'rahasia']),
            'lampiran' => $this->faker->word() . '.pdf',
            'keterangan' => $this->faker->sentence(),
            'jenis_id' => $jenisBiasa ? $jenisBiasa->id : null,
            'status_id' => $statusDraft ? $statusDraft->id : null,
            'created_by' => $staffProdi ? $staffProdi->id : User::factory()->create()->id,
            'tujuan_jabatan_id' => $tujuanJabatan ? $tujuanJabatan->id : null,
            'prodi_id' => $prodi ? $prodi->id : null,
            'fakultas_id' => $fakultas ? $fakultas->id : null,
            'file_surat' => 'surat_pdfs/' . $this->faker->uuid() . '.pdf',
        ];
    }
}