<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeleteRequest extends Model
{
    protected $table = 'delete_requests';
    
    protected $fillable = [
        'tabel_target',
        'target_id',
        'data_lama',
        'alasan',
        'requested_by',
        'status',
        'approved_by',
        'approved_at'
    ];
    
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaction::class, 'id_transaksi');
    }
}
