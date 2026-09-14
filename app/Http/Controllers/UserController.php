<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'dosen', 'mahasiswa']),
            ],

            'nim_nip' => [
                'nullable',
                'string',
                'max:255',
                'unique:users,nim_nip',
            ],
        ]);

        // Hanya data yang ada di $fillable yang dimasukkan lewat mass assignment.
        $user = new User();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->nim_nip = $validated['nim_nip'] ?? null;

        // role sengaja diisi secara eksplisit karena tidak ada di $fillable.
        $user->role = $validated['role'];

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail pengguna.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Menampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'dosen', 'mahasiswa']),
            ],

            'nim_nip' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'nim_nip')->ignore($user->id),
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->nim_nip = $validated['nim_nip'] ?? null;

        // role diisi secara eksplisit karena tidak ada di $fillable.
        $user->role = $validated['role'];

        // Password hanya diubah jika pengguna mengisi password baru.
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Menghapus pengguna.
     *
     * Karena User menggunakan SoftDeletes,
     * data tidak langsung dihapus secara permanen.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}