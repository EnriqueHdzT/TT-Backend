<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'protocol_id',
        'title',
        'resume',
        'period',
        'status',
        'keywords',
        'pdf',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pdf',
    ];

    public function datesAndTerms()
    {
        return $this->belongsTo(DatesAndTerms::class, 'period');
    }
}
