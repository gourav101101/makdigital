<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $fillable = ['submission_id', 'name', 'email', 'phone', 'service', 'message', 'status', 'notes', 'notification_status', 'notification_error', 'notified_at'];

    protected function casts(): array
    {
        return ['notified_at' => 'datetime'];
    }
}
