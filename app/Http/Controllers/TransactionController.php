<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseNotificationService;

class TransactionController extends Controller
{
    protected $firebaseNotificationService;

    public function __construct(FirebaseNotificationService $firebaseNotificationService)
    {
        $this->firebaseNotificationService = $firebaseNotificationService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['product', 'user'])->orderBy('created_at', 'desc');

        // Filter berdasarkan nama produk jika ada
        if ($request->has('product') && $request->get('product') != '') {
            $query->where('product_id', $request->get('product'));
        }

        // Filter berdasarkan status jika ada
        if ($request->has('status') && $request->get('status') != '') {
            $query->where('status', $request->get('status'));
        }

        $transaction = $query->paginate(10);
        $products = Product::all(); // Mengambil daftar produk untuk filter

        return view('transaction.index', compact('transaction', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        return view('transaction.detail', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transaction.index')->with('success', 'Transaksi berhasil dihapus');
    }

    public function changeStatus(Request $request, $id, $status)
    {
        $transaction = Transaction::findOrFail($id);

        $deviceToken = $transaction->user->device_token;
        $transaction->status = $status;
        $transaction->save();

        $title = $transaction->user->name;
        $body = match ($status) {
            'PENDING' => "SELAMAT!\nPesanan Anda berhasil dipesan, silahkan selesaikan pembayaran agar segera diproses\nTerimakasih",
            'SUCCESS' => "SELAMAT\nPembayaran Anda berhasil, pesanan Anda akan segera kami proses\nTerimakasih",
            'PACKED' => "SELAMAT\nPembayaran Anda berhasil, pesanan Anda akan segera kami proses\nTerimakasih",
            'ON_DELIVERY' => "Pesanan Anda sedang dalam pengiriman\nMohon tunggu pihak pengantaran pesanan menghubungi Anda ketika sudah sampai tujuan\nTerimakasih",
            'DELIVERED' => "Pesanan Anda telah sampai, mohon di cek terlebih dahulu sebelum diterima\nTerimakasih",
            'CANCELLED' => "Pesanan Anda telah dibatalkan\nSilahkan cari produk yang Anda inginkan\nTerimakasih",
            default => "Status pesanan Anda telah diperbarui.",
        };
        $data = [
            'order_id' => $transaction->id
        ];
        $this->firebaseNotificationService->sendNotification($deviceToken, $title, $body, $data);

        return redirect()->route('transaction.index')->with('success', 'Status berhail diganti');
    }

    public function salesHistory()
    {
        $history = Transaction::with('product') // Relasi dengan produk
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(total) as total_revenue')
            ->where('status', 'delivered') // Hanya transaksi selesai
            ->groupBy('product_id')
            ->get()
            ->map(function ($transaction) {
                return [
                    'name' => $transaction->product->name, // Nama produk
                    'total_sold' => $transaction->total_sold, // Jumlah terjual
                    'total_revenue' => $transaction->total_revenue, // Total pendapatan
                ];
            });

        return view('transaction.sales-history', compact('history'));
    }

    public function printReceipt($id)
    {
        $transaction = Transaction::with('product', 'user')->findOrFail($id);

        return view('transaction.receipt', compact('transaction'));
    }

}
