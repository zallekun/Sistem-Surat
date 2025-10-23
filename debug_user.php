use App\Models\User;

$user = User::where('email', 'staff.kim2@sistemsurat.com')->with(['roles', 'permissions', 'prodi', 'prodi.fakultas', 'jabatan'])->first();

if ($user) {
    echo "User Found:\n";
    print_r($user->toArray());
} else {
    echo "User with email 'staff.kim2@sistemsurat.com' not found.\n";
}

