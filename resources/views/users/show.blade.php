<x-layout>
    <div class="max-w-3xl mx-auto px-6 py-8">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">
                    Detail Pengguna
                </h1>

                <p class="text-gray-600 mt-1">
                    Informasi lengkap pengguna.
                </p>
            </div>

            <a
                href="{{ route('users.index') }}"
                class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300"
            >
                ← Kembali
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">

            <div class="p-6 border-b">
                <h2 class="text-2xl font-bold">
                    {{ $user->name }}
                </h2>

                <p class="text-gray-600">
                    {{ $user->email }}
                </p>
            </div>

            <div class="divide-y">

                <div class="p-4 flex justify-between">
                    <span class="font-medium">
                        ID
                    </span>

                    <span>
                        {{ $user->id }}
                    </span>
                </div>

                <div class="p-4 flex justify-between">
                    <span class="font-medium">
                        Nama
                    </span>

                    <span>
                        {{ $user->name }}
                    </span>
                </div>

                <div class="p-4 flex justify-between">
                    <span class="font-medium">
                        Email
                    </span>

                    <span>
                        {{ $user->email }}
                    </span>
                </div>

                <div class="p-4 flex justify-between">
                    <span class="font-medium">
                        Role
                    </span>

                    <span>
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                <div class="p-4 flex justify-between">
                    <span class="font-medium">
                        NIM / NIP
                    </span>

                    <span>
                        {{ $user->nim_nip ?? '-' }}
                    </span>
                </div>

                <div class="p-4 flex justify-between">
                    <span class="font-medium">
                        Email Terverifikasi
                    </span>

                    <span>
                        {{ $user->email_verified_at ? 'Ya' : 'Belum' }}
                    </span>
                </div>

                <div class="p-4 flex justify-between">
                    <span class="font-medium">
                        Dibuat
                    </span>

                    <span>
                        {{ $user->created_at?->format('d M Y H:i') }}
                    </span>
                </div>

            </div>

            <div class="p-6 border-t flex gap-3">

                <a
                    href="{{ route('users.edit', $user) }}"
                    class="bg-yellow-400 px-5 py-2 rounded-lg hover:bg-yellow-500"
                >
                    Edit
                </a>

                <form
                    action="{{ route('users.destroy', $user) }}"
                    method="POST"
                    onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700"
                    >
                        Hapus
                    </button>
                </form>

            </div>

        </div>

    </div>
</x-layout>