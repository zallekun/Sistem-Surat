use App\Models\Prodi;

$prodi = Prodi::where('nama_prodi', 'Kimia S2')->with('fakultas')->first();

if ($prodi) {
    echo "Prodi Found:\n";
    print_r($prodi->toArray());
    if ($prodi->fakultas) {
        echo "Fakultas Found:\n";
        print_r($prodi->fakultas->toArray());
    } else {
        echo "Fakultas not found for this prodi.\n";
    }
} else {
    echo "Prodi 'Kimia S2' not found.\n";
}