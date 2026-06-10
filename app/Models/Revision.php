<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $fillable = ['fund_request_id', 'user_id', 'catatan'];

    public function fundRequest()
    {
        return $this->belongsTo(FundRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
