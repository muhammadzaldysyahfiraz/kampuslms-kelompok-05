<x-layout>
    <div class="max-w-6xl mx-auto px-6 py-8">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold">Daftar Pengguna</h1>
                <p class="text-gray-600 mt-1">
                    Kelola data admin, dosen, dan mahasiswa.
                </p>
            </div>

            <a
                href="{{ route('users.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
            >
                + Tambah Pengguna
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">NIM/NIP</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t">
                                <td class="px-4 py-3">
                                    {{ $users->firstItem() + $loop->index }}
                                </td>

                                <td class="px-4 py-3 font-medium">
                                    {{ $user->name }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $user->email }}
                                </td>

                                <td class="px-4 py-3">
                                    @if ($user->role === 'admin')
                                        <span class="px-2 py-1 text-sm rounded bg-red-100 text-red-700">
                                            Admin
                                        </span>
                                    @elseif ($user->role === 'dosen')
                                        <span class="px-2 py-1 text-sm rounded bg-blue-100 text-blue-700">
                                            Dosen
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-sm rounded bg-green-100 text-green-700">
                                            Mahasiswa
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    {{ $user->nim_nip ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex gap-2">

                                        <a
                                            href="{{ route('users.show', $user) }}"
                                            class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300"
                                        >
                                            Detail
                                        </a>

                                        <a
                                            href="{{ route('users.edit', $user) }}"
                                            class="px-3 py-1 bg-yellow-400 rounded hover:bg-yellow-500"
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
                                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                                            >
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-4 py-8 text-center text-gray-500"
                                >
                                    Belum ada pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t">
                    {{ $users->links() }}
                </div>
            @endif

        </div>

    </div>
</x-layout>