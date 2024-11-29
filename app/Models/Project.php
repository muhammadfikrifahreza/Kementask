<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Parallax\FilamentComments\Models\Traits\HasFilamentComments; 

class Project extends Model
{
    use HasFactory;
    use HasFilamentComments;
    protected $fillable = ['name', 'user_id', 'description', 'due_date', 'attachments'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
