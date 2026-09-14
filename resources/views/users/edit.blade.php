<x-layout>
    <div class="max-w-3xl mx-auto px-6 py-8">

        <div class="mb-6">
            <h1 class="text-3xl font-bold">
                Edit Pengguna
            </h1>

            <p class="text-gray-600 mt-1">
                Perbarui data pengguna.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">

                <p class="font-semibold mb-2">
                    Terdapat kesalahan:
                </p>

                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif

        <form
            action="{{ route('users.update', $user) }}"
            method="POST"
            class="bg-white p-6 rounded-lg shadow"
        >
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block font-medium mb-2">
                    Nama
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    class="w-full border rounded-lg px-3 py-2"
                >
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-2">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    class="w-full border rounded-lg px-3 py-2"
                >
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-2">
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password"
                    minlength="8"
                    class="w-full border rounded-lg px-3 py-2"
                >

                <p class="text-sm text-gray-500 mt-1">
                    Kosongkan jika tidak ingin mengubah password.
                </p>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-2">
                    Role
                </label>

                <select
                    name="role"
                    required
                    class="w-full border rounded-lg px-3 py-2"
                >
                    <option
                        value="admin"
                        {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}
                    >
                        Admin
                    </option>

                    <option
                        value="dosen"
                        {{ old('role', $user->role) === 'dosen' ? 'selected' : '' }}
                    >
                        Dosen
                    </option>

                    <option
                        value="mahasiswa"
                        {{ old('role', $user->role) === 'mahasiswa' ? 'selected' : '' }}
                    >
                        Mahasiswa
                    </option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-2">
                    NIM / NIP
                </label>

                <input
                    type="text"
                    name="nim_nip"
                    value="{{ old('nim_nip', $user->nim_nip) }}"
                    class="w-full border rounded-lg px-3 py-2"
                >

                <p class="text-sm text-gray-500 mt-1">
                    Boleh dikosongkan.
                </p>
            </div>

            <div class="flex gap-3">

                <button
                    type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route('users.show', $user) }}"
                    class="bg-gray-200 px-5 py-2 rounded-lg hover:bg-gray-300"
                >
                    Batal
                </a>

            </div>
        </form>

    </div>
</x-layout>