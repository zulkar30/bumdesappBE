<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <script>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: '{{ session('success') }}',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                </script>
            @endif
            <div class="flex justify-between mb-6">
                <!-- Tombol Histori Penjualan di kiri -->
                <div>
                    <a href="{{ route('transaction.history') }}"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Histori Penjualan
                    </a>
                </div>
                
                <!-- Form Filter di kanan -->
                <div>
                    <form method="GET" action="{{ route('transaction.index') }}" class="flex space-x-4">
                        <div>
                            <label for="product" class="block text-sm font-semibold text-gray-700">Nama Produk</label>
                            <select id="product" name="product" class="bg-gray-100 border border-gray-300 rounded p-2">
                                <option value="">Semua Produk</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                            <select id="status" name="status" class="bg-gray-100 border border-gray-300 rounded p-2">
                                <option value="">Semua Status</option>
                                <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>BELUM BAYAR</option>
                                <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>SUDAH BAYAR</option>
                                <option value="DELIVERED" {{ request('status') == 'DELIVERED' ? 'selected' : '' }}>PESANAN DITERIMA</option>
                                <option value="ON_DELIVERY" {{ request('status') == 'ON_DELIVERY' ? 'selected' : '' }}>SEDANG DIKIRIM</option>
                                <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>DIBATALKAN</option>
                            </select>
                        </div>

                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="border px-6 py-4">ID</th>
                            <th class="border px-6 py-4">Produk</th>
                            <th class="border px-6 py-4">User</th>
                            <th class="border px-6 py-4">Kuantitas</th>
                            <th class="border px-6 py-4">Total</th>
                            <th class="border px-6 py-4">Status</th>
                            <th class="border px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaction as $item)
                            <tr>
                                <td class="border px-6 py-4">{{ $item->id }}</td>
                                <td class="border px-6 py-4 ">{{ $item->product->name }}</td>
                                <td class="border px-6 py-4 ">{{ $item->user->name }}</td>
                                <td class="border px-6 py-4">{{ $item->quantity . ' Item' }}</td>
                                <td class="border px-6 py-4">{{ 'RP ' . number_format($item->total, 0, ',', '.') }}</td>
                                <td class="border px-6 py-4">
                                    @if ($item->status === 'PENDING')
                                        <p class="text-orange-500">{{ 'BELUM BAYAR' }}</p>
                                    @elseif($item->status === 'PACKED')
                                        <p class="text-green-500">{{ 'SEDANG DIKEMAS' }}</p>
                                    @elseif($item->status === 'DELIVERED')
                                        <p class="text-green-800">{{ 'PESANAN DITERIMA' }}</p>
                                    @elseif($item->status === 'ON_DELIVERY')
                                        <p class="text-black-500">{{ 'SEDANG DIKIRIM' }}</p>
                                    @elseif($item->status === 'CANCELLED')
                                        <p class="text-red-500">{{ 'DIBATALKAN' }}</p>
                                    @endif
                                </td>
                                <td class="border px-6 py-4 text-center">
                                    <div class="flex justify-center">
                                        <!-- Lihat -->
                                        <a href="{{ route('transaction.show', $item->id) }}"
                                            class="bg-blue-500 hover:bg-blue-700 text-white p-2 rounded mr-2"
                                            title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                
                                        <!-- Print Resi -->
                                        <a href="{{ route('transaction.printReceipt', $item->id) }}" target="_blank"
                                            class="bg-green-500 hover:bg-green-700 text-white p-2 rounded mr-2"
                                            title="Print Resi">
                                            <i class="fas fa-print"></i>
                                        </a>
                                
                                        <!-- Hapus -->
                                        <form action="{{ route('transaction.destroy', $item->id) }}" method="POST">
                                            {!! method_field('delete') . csrf_field() !!}
                                            <button type="submit"
                                                class="bg-red-500 hover:bg-red-700 text-white p-2 rounded"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="border text-center p-5">
                                    Data Transaksi Tidak Ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-5">
                {{ $transaction->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
