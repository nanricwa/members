<?php

namespace App\Models;

use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'name',
        'name_kana',
        'status',
        'email_verified_at',
        'last_login_at',
        'login_count',
        'note',
        'stripe_customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => MemberStatus::class,
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'member_plan')
            ->using(MemberPlan::class)
            ->withPivot(['id', 'status', 'started_at', 'expires_at', 'granted_by', 'note'])
            ->withTimestamps();
    }

    public function activePlans(): BelongsToMany
    {
        return $this->plans()->wherePivot('status', 'active');
    }

    public function memberPlans(): HasMany
    {
        return $this->hasMany(MemberPlan::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(MemberDownload::class);
    }

    public function hasActivePlan(Plan $plan): bool
    {
        return $this->activePlans()->where('plans.id', $plan->id)->exists();
    }

    public function hasAnyActivePlan(array $planIds): bool
    {
        return $this->activePlans()->whereIn('plans.id', $planIds)->exists();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === MemberStatus::Active;
    }
}
