<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMember extends Pivot
{
    use HasUlids;

    protected $table = 'project_user';

    protected $fillable = [
        'project_id',
        'user_id',
        'metadata',

    ];
}
