<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    // Disable the auto-incrementing ID
    public $incrementing = false;

    // Specify the primary key as a composite key
    protected $primaryKey = ['protocol_id', 'sinodal_id'];

    // Define the table name explicitly (optional, if different from 'evaluations')
    protected $table = 'evaluations';

    // Set the key type to string since `protocol_id` and `sinodal_id` are UUIDs
    protected $keyType = 'string';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'protocol_id',
        'sinodal_id',
        'current_status',
        'evaluation_response',
    ];

    // Ensure that `evaluation_response` is cast to an array for easier handling
    protected $casts = [
        'evaluation_response' => 'array',
    ];

    // Disable timestamps if not needed
    public $timestamps = true;

    // Override the `getKey` method to handle composite keys
    public function getKey()
    {
        return $this->getAttribute('protocol_id') . '-' . $this->getAttribute('sinodal_id');
    }

    // Disable Laravel's automatic primary key handling
    public function getKeyName()
    {
        return null;
    }
}
