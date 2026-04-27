<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    // HAPUS __construct() - JANGAN PAKAI MIDDLEWARE DI SINI

    public function index(Request $request)
    {
        // Cek apakah admin (opsional, tapi route sudah di-protect)
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengakses halaman ini');
        }

        $query = Log::with('user')->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->aksi) {
            $query->where('aksi', $request->aksi);
        }

        // Filter by date
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(50);
        $users = User::all();
        $actions = Log::distinct()->pluck('aksi');

        return view('logs.index', compact('logs', 'users', 'actions'));
    }

    public function show($id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengakses halaman ini');
        }

        $log = Log::with('user')->findOrFail($id);

        return view('logs.show', compact('log'));
    }
}
