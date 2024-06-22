<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatesAndTerms extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cycle',
        'start_recv_date_ord',
        'end_recv_date_ord',
        'recom_classif_end_date_ord',
        'recom_eval_end_date_ord',
        'correc_end_date_ord',
        'recom_second_eval_end_date_ord',
        'start_recv_date_ext',
        'end_recv_date_ext',
        'recom_classif_end_date_ext',
        'recom_eval_end_date_ext',
        'correc_end_date_ext',
        'recom_second_eval_end_date_ext',
    ];
}
