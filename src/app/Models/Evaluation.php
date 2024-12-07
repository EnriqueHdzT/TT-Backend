<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evaluations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'protocol_id',
        'sinodal_id',
        'status',
        'current_status',
        'evaluation_response',
    ];

    /**
     * The default attributes for the model.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Relationships.
     */

    // Evaluation belongs to a protocol
    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }

    // Evaluation is created by a sinodal (user)
    public function sinodal()
    {
        return $this->belongsTo(User::class, 'sinodal_id');
    }
}
