<x-layout>
    <div class="max-w-3xl mx-auto px-6 py-8">

        <div class="mb-6">
            <h1 class="text-3xl font-bold">Tambah Pengguna</h1>
            <p class="text-gray-600 mt-1">
                Tambahkan admin, dosen, atau mahasiswa baru.
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
            action="{{ route('users.store') }}"
            method="POST"
            class="bg-white p-6 rounded-lg shadow"
        >
            @csrf

            <div class="mb-4">
                <label class="block font-medium mb-2">
                    Nama
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
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
                    value="{{ old('email') }}"
                    required
                    class="w-full border rounded-lg px-3 py-2"
                >
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-2">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    minlength="8"
                    class="w-full border rounded-lg px-3 py-2"
                >

                <p class="text-sm text-gray-500 mt-1">
                    Minimal 8 karakter.
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
                    <option value="">-- Pilih Role --</option>

                    <option
                        value="admin"
                        {{ old('role') === 'admin' ? 'selected' : '' }}
                    >
                        Admin
                    </option>

                    <option
                        value="dosen"
                        {{ old('role') === 'dosen' ? 'selected' : '' }}
                    >
                        Dosen
                    </option>

                    <option
                        value="mahasiswa"
                        {{ old('role') === 'mahasiswa' ? 'selected' : '' }}
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
                    value="{{ old('nim_nip') }}"
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
                    Simpan
                </button>

                <a
                    href="{{ route('users.index') }}"
                    class="bg-gray-200 px-5 py-2 rounded-lg hover:bg-gray-300"
                >
                    Batal
                </a>

            </div>
        </form>

    </div>
</x-layout>