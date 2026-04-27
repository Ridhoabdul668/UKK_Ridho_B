<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeleteRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteRequestController extends Controller
{
    // ========== REQUEST HAPUS PRODUK (KASIR -> ADMIN) ==========
    public function storeProduct(Request $request)
    {
        $request->validate([
            'tabel_target' => 'required|in:products,customers',
            'target_id' => 'required|integer',
            'alasan' => 'required|string'
        ]);

        $deleteRequest = DeleteRequest::create([
            'tabel_target' => $request->tabel_target,
            'target_id' => $request->target_id,
            'alasan' => $request->alasan,
            'requested_by' => Auth::id(),
            'status' => 'pending'
        ]);

        Log::catat(Auth::id(), 'request_delete_product', $request->tabel_target, $request->target_id, $request->alasan);

        return response()->json(['success' => true, 'message' => 'Request hapus produk sudah dikirim ke admin']);
    }

    // ========== REQUEST HAPUS TRANSAKSI (KASIR -> ADMIN) ==========
    public function storeTransactionRequest(Request $request)
    {
        $request->validate([
            'tabel_target' => 'required|in:transactions',
            'target_id' => 'required|exists:transactions,id',
            'alasan' => 'required|min:10',
        ]);

        $deleteRequest = DeleteRequest::create([
            'tabel_target' => $request->tabel_target,
            'target_id' => $request->target_id,
            'alasan' => $request->alasan,
            'requested_by' => Auth::id(),
            'status' => 'pending',
        ]);

        Log::catat(Auth::id(), 'request_delete_transaction', 'transactions', $request->target_id, $request->alasan);

        return redirect()->route('transaction.history')
            ->with('success', 'Request hapus transaksi telah dikirim ke admin untuk persetujuan.');
    }

    // ========== ADMIN: LIHAT SEMUA REQUEST ==========

    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengakses');
        }

        // Request hapus produk
        $productRequests = DeleteRequest::with(['requester', 'approver'])
            ->where('tabel_target', '!=', 'transactions')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Request hapus transaksi
        $transactionRequests = DeleteRequest::with(['requester', 'approver'])
            ->where('tabel_target', 'transactions')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.delete-requests', compact('productRequests', 'transactionRequests'));
    }

    // ========== APPROVE REQUEST ==========
    public function approveProduct($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengakses');
        }

        $deleteRequest = DeleteRequest::findOrFail($id);

        if ($deleteRequest->tabel_target === 'products') {
            $product = Product::find($deleteRequest->target_id);
            if ($product) {
                // Cek apakah produk pernah dipakai di transaksi
                if ($product->transactionItems()->exists()) {
                    $product->is_active = false;
                    $product->save();
                } else {
                    $product->delete();
                }
            }
        } elseif ($deleteRequest->tabel_target === 'transactions') {
            $transaksi = Transaction::find($deleteRequest->target_id);
            if ($transaksi) {
                DB::beginTransaction();
                try {
                    // Kembalikan stok produk
                    foreach ($transaksi->details as $detail) {
                        $produk = Product::find($detail->product_id);
                        if ($produk) {
                            $produk->stok += $detail->qty;
                            $produk->save();
                        }
                    }

                    // Batalkan transaksi
                    $transaksi->status = 'batal';
                    $transaksi->save();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
                }
            }
        }

        $deleteRequest->update([
            'status' => 'disetujui',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        Log::catat(Auth::id(), 'approve_delete', $deleteRequest->tabel_target, $deleteRequest->target_id, 'Menyetetujui request hapus');

        return back()->with('success', 'Request hapus telah disetujui');
    }

    // ========== REJECT REQUEST ==========
    public function rejectProduct($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengakses');
        }

        $deleteRequest = DeleteRequest::findOrFail($id);
        $deleteRequest->update([
            'status' => 'ditolak',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        Log::catat(Auth::id(), 'reject_delete', $deleteRequest->tabel_target, $deleteRequest->target_id, 'Menolak request hapus');

        return back()->with('success', 'Request hapus ditolak');
    }

    // ========== APPROVE & REJECT TRANSAKSI - DIHAPUS karena sudah digabungkan ke approveProduct/rejectProduct ==========
}
