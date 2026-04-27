<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    protected $fillable = [
        'user_id', 'aksi', 'tabel_terkait', 'record_id',
        'data_lama', 'data_baru', 'keterangan', 'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper untuk mencatat log
    public static function catat($userId, $aksi, $tabel = null, $recordId = null, $keterangan = null, $dataLama = null, $dataBaru = null)
    {
        return self::create([
            'user_id' => $userId,
            'aksi' => $aksi,
            'tabel_terkait' => $tabel,
            'record_id' => $recordId,
            'data_lama' => $dataLama ? json_encode($dataLama) : null,
            'data_baru' => $dataBaru ? json_encode($dataBaru) : null,
            'keterangan' => $keterangan,
            'ip_address' => request()->ip(),
        ]);
    }
}
