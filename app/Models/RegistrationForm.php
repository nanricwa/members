<?php

namespace App\Models;

use App\Enums\FormType;
use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RegistrationForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'plan_id',
        'description',
        'header_image',
        'body_html',
        'button_text',
        'custom_css',
        'thanks_message',
        'redirect_url',
        'capacity',
        'opens_at',
        'closes_at',
        'payment_gateway',
        'amount',
        'trial_days',
        'completion_email_subject',
        'completion_email_body',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => FormType::class,
            'payment_gateway' => PaymentGateway::class,
            'amount' => 'decimal:0',
            'is_active' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function customFields(): BelongsToMany
    {
        return $this->belongsToMany(CustomField::class, 'form_custom_fields')
            ->withPivot(['is_required', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isAccepting(): bool
    {
        $now = now();

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        if ($this->capacity !== null) {
            $registeredCount = MemberPlan::where('plan_id', $this->plan_id)
                ->where('granted_by', 'registration')
                ->count();
            if ($registeredCount >= $this->capacity) {
                return false;
            }
        }

        return true;
    }

    public function isFree(): bool
    {
        return $this->type === FormType::Free;
    }
}
