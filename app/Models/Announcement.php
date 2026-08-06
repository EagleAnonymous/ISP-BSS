<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'body', 'icon', 'announcement_date'])]
class Announcement extends Model
{
    protected function casts(): array
    {
        return [
            'announcement_date' => 'date',
        ];
    }
}
