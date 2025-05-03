<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

   /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sender of the notification.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the booking associated with the notification.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread()
    {
        $this->update(['is_read' => false, 'read_at' => null]);
    }
}