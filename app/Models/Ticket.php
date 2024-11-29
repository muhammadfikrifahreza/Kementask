<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments; 



class Ticket extends Model
{
    use HasFactory;
    use HasFilamentComments;
    protected $fillable = ['project_id', 'user_id', 'name', 'description', 'status_id', 'priority_id', 'type_id', 'attachments', 'due_date', 'responsible_id',];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    } 
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(user::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class);
    } 

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class);
    } 

    public function type(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    } 

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id')
                    ->whereHas('roles', function($query) {
                        $query->where('name', 'Doctor');
                    });
    }

}
