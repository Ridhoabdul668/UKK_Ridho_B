<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function bestSellers(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now();

        $bestSellers = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.tanggal', [$startDate, $endDate])
            ->where('transactions.status', 'selesai')
            ->select(
                'products.id',
                'products.nama_produk',
                'products.barcode',
                DB::raw('SUM(transaction_items.qty) as total_terjual'),
                DB::raw('SUM(transaction_items.subtotal) as total_omset')
            )
            ->groupBy('products.id', 'products.nama_produk', 'products.barcode')
            ->orderBy('total_terjual', 'desc')
            ->limit(10)
            ->get();

        return response()->json($bestSellers);
    }

    public function slowMovers()
    {
        $slowMovers = Product::leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', function ($join) {
                $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                    ->where('transactions.status', 'selesai');
            })
            ->select(
                'products.id',
                'products.nama_produk',
                'products.stok',
                DB::raw('COALESCE(SUM(transaction_items.qty), 0) as total_terjual')
            )
            ->groupBy('products.id', 'products.nama_produk', 'products.stok')
            ->having('total_terjual', '<', 5)
            ->orderBy('total_terjual', 'asc')
            ->get();

        return response()->json($slowMovers);
    }

    public function damagedProducts()
    {
        $damaged = Product::whereIn('status', ['jelek', 'kadaluarsa'])->get();

        return response()->json($damaged);
    }

    public function productsByStatus($status)
    {
        $products = Product::where('status', $status)->get();

        return response()->json($products);
    }

    public function salesByCashier(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now();

        $sales = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->whereBetween('transactions.tanggal', [$startDate, $endDate])
            ->where('transactions.status', 'selesai')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(transactions.id) as jumlah_transaksi'),
                DB::raw('SUM(transactions.total) as total_omset')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_omset', 'desc')
            ->get();

        return response()->json($sales);
    }

    public function salesByDate(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now();

        $sales = Transaction::whereBetween('tanggal', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->select(
                DB::raw('DATE(tanggal) as tanggal'),
                DB::raw('COUNT(*) as jumlah_transaksi'),
                DB::raw('SUM(total) as total_omset')
            )
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json($sales);
    }

    public function dashboardStats()
    {
        $totalProduk = Product::count();
        $produkHabis = Product::where('stok', 0)->count();
        $produkKadaluarsa = Product::where('status', 'kadaluarsa')->count();
        $produkJelek = Product::where('status', 'jelek')->count();

        $penjualanHariIni = Transaction::whereDate('tanggal', now())
            ->where('status', 'selesai')
            ->sum('total');

        $jumlahTransaksiHariIni = Transaction::whereDate('tanggal', now())
            ->where('status', 'selesai')
            ->count();

        $penjualanBulanIni = Transaction::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->where('status', 'selesai')
            ->sum('total');

        return response()->json([
            'total_produk' => $totalProduk,
            'produk_habis' => $produkHabis,
            'produk_kadaluarsa' => $produkKadaluarsa,
            'produk_jelek' => $produkJelek,
            'penjualan_hari_ini' => $penjualanHariIni,
            'jumlah_transaksi_hari_ini' => $jumlahTransaksiHariIni,
            'penjualan_bulan_ini' => $penjualanBulanIni,
        ]);
    }
}
