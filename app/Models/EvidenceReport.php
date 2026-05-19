<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvidenceReport extends Model
{
    protected $fillable = [
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'category',
        'source_url',
        'description',
        'attachment',
        'status',
        'admin_note',
    ];
}
