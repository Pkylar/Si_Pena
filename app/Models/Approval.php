<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = ['fund_request_id', 'user_id', 'role', 'status', 'catatan'];

    public function fundRequest()
    {
        return $this->belongsTo(FundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
