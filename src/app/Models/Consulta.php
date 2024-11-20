<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    use HasFactory;

    protected $table = 'consultas';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'Question',
        'Answer',
        'Date_query',
        'Last_update',
        'Category',
        'support_email',
        'user_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
