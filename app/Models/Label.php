<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasUuids;

    protected $fillable = ['uuid', 'project_id', 'name', 'color'];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
