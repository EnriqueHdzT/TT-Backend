<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DatesAndTerms extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $keyType = 'string';

    protected $hidden = ['created_at', 'updated_at', 'id'];

    protected $fillable = [
        'cycle',
        'status',
        'ord_start_update_protocols',
        'ord_end_update_protocols',
        'ord_start_sort_protocols',
        'ord_end_sort_protocols',
        'ord_start_eval_protocols',
        'ord_end_eval_protocols',
        'ord_start_change_protocols',
        'ord_end_change_protocols',
        'ord_start_second_eval_protocols',
        'ord_end_second_eval_protocols',
        'ext_start_update_protocols',
        'ext_end_update_protocols',
        'ext_start_sort_protocols',
        'ext_end_sort_protocols',
        'ext_start_eval_protocols',
        'ext_end_eval_protocols',
        'ext_start_change_protocols',
        'ext_end_change_protocols',
        'ext_start_second_eval_protocols',
        'ext_end_second_eval_protocols',
    ];

    public function protocols()
    {
        return $this->hasMany(Protocol::class, 'period');
    }

    public static function latestActiveCycle(): string
    {
        $now = now();

        return static::where('status', true)
            ->where(function ($query) use ($now) {
                $query->where([
                    ['ord_start_update_protocols', '<=', $now],
                    ['ord_end_update_protocols', '>=', $now],
                ])->orWhere([
                    ['ext_start_update_protocols', '<=', $now],
                    ['ext_end_update_protocols', '>=', $now],
                ]);
            })
            ->orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
            ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
            ->value('cycle') ?? '';
    }

    public static function getAllSchoolCyclesAsArray()
    {
        return self::orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
            ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
            ->pluck('cycle')
            ->toArray();
    }
}
