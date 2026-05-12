<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'url',
        'last_status',
        'last_status_code',
        'last_error',
        'last_checked_at',
        'last_alert_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
            'last_alert_sent_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
