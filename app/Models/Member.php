<?php

namespace App\Models;

use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'qr_identifier',
        'status',
        'birth_date',
        'notes',
    ];

    protected $casts = [
        'status' => MemberStatus::class,
        'birth_date' => 'date',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', 'active');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function qrScans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function correctionRequests(): HasMany
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }
}
