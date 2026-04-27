<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Log;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    // Proses simpan transaksi

    public function store(Request $request)
    {
        $request->validate([
            'bayar' => 'required|integer|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'diskon_persen' => 'nullable|integer|min:0|max:100',
            'diskon_nominal' => 'nullable|integer|min:0',
        ]);

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong'], 400);
        }

        // Hitung subtotal
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['subtotal'];
        }

        // Hitung diskon
        $diskonPersen = $request->diskon_persen ?? 0;
        $diskonNominal = $request->diskon_nominal ?? 0;

        // Diskon dari member (jika ada)
        $customer = null;
        if ($request->customer_id) {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                $diskonMember = $customer->hitungDiskon($subtotal);
                if ($customer->jenis_diskon === 'persen') {
                    $diskonPersen += $customer->nilai_diskon;
                } else {
                    $diskonNominal += $diskonMember;
                }
            }
        }

        $totalDiskon = ($subtotal * $diskonPersen / 100) + $diskonNominal;
        $total = $subtotal - $totalDiskon;

        if ($total < 0) {
            $total = 0;
        }

        if ($request->bayar < $total) {
            return response()->json(['success' => false, 'message' => 'Uang bayar kurang'], 400);
        }

        $kembalian = $request->bayar - $total;

        // Mulai transaction database
        DB::beginTransaction();

        try {
            // Validasi stok ulang
            foreach ($cart as $item) {
                $product = Product::find($item['id']);
                if (! $product || $product->stok < $item['qty']) {
                    throw new \Exception("Stok {$item['nama_produk']} tidak mencukupi");
                }
            }

            // Buat kode transaksi unik
            $kodeTransaksi = 'INV/' . date('Ymd') . '/' . strtoupper(Str::random(6));

            // Simpan transaksi
            $transaction = Transaction::create([
                'kode_transaksi' => $kodeTransaksi,
                'tanggal' => now(),
                'subtotal' => $subtotal,
                'diskon_persen' => $diskonPersen,
                'diskon_nominal' => $diskonNominal,
                'total' => $total,
                'bayar' => $request->bayar,
                'kembalian' => $kembalian,
                'status' => 'selesai',
                'user_id' => Auth::id(),
                'customer_id' => $request->customer_id,
            ]);

            // Simpan item transaksi dan kurangi stok
            foreach ($cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['id'],
                    'qty' => $item['qty'],
                    'harga_saat_transaksi' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Kurangi stok
                $product = Product::find($item['id']);
                $product->kurangiStok($item['qty']);
            }

            // Catat log
            Log::catat(
                Auth::id(),
                'transaksi',
                'transactions',
                $transaction->id,
                "Transaksi {$kodeTransaksi} sebesar Rp " . number_format($total)
            );

            // Kirim struk ke email jika member
            if ($customer && $customer->email) {
                $this->kirimStrukEmail($transaction, $customer);
            }

            DB::commit();

            // Hapus keranjang
            Session::forget('cart');

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'transaction_id' => $transaction->id,
                'kode_transaksi' => $kodeTransaksi,
                'kembalian' => $kembalian,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Kirim struk ke email
    private function kirimStrukEmail($transaction, $customer)
    {
        try {
            Mail::send('pos.email-struk', [
                'transaction' => $transaction,
                'customer' => $customer,
                'items' => $transaction->items()->with('product')->get(),
            ], function ($message) use ($customer, $transaction) {
                $message->to($customer->email)
                    ->subject('Struk Pembayaran - ' . $transaction->kode_transaksi);
            });
        } catch (\Exception $e) {
            Log::catat(Auth::id(), 'email_gagal', 'transactions', $transaction->id, 'Gagal kirim email: ' . $e->getMessage());
        }
    }

    // Ambil data transaksi untuk struk
    public function getReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user', 'customer'])->findOrFail($id);

        return response()->json($transaction);
    }

    // Cetak struk (view untuk print)
    public function printReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'user', 'customer'])->findOrFail($id);

        Log::catat(Auth::id(), 'cetak_struk', 'transactions', $id, 'Mencetak struk transaksi');

        return view('pos.receipt', compact('transaction'));
    }

    // History transaksi (untuk report)
    public function history(Request $request)
    {
        $query = Transaction::with(['user', 'customer'])
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc');

        if ($request->start_date) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $transactions = $query->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'data' => $transactions->items(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage()
            ]);
        }

        return view('transactions.history', compact('transactions'));
    }
}
