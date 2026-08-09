<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpRequest extends Model
{
    protected $table = 'help_requests';
    protected $primaryKey = 'help_request_id';
    
    protected $fillable = [
        'student_id',
        'message',
        'status',
        'admin_response',
        'resolved_by',
        'resolved_at',
        'responded_at',
    ];
    
    protected $casts = [
        'resolved_at' => 'datetime',
        'responded_at' => 'datetime',
    ];
    
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
    
    public function markAsResolved($adminId = null): void
    {
        $this->status = 'resolved';
        $this->resolved_at = now();
        $this->resolved_by = $adminId;
        $this->save();
    }
    
    public function markAsInProgress(): void
    {
        $this->status = 'in_progress';
        $this->save();
    }
}