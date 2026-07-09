<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetSetting extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'asrat_percentage',
        'needs_percentage',
        'emergency_percentage',
        'investment_percentage',
        'reward_percentage',
    ];

    protected function casts(): array
    {
        return [
            'asrat_percentage' => 'decimal:2',
            'needs_percentage' => 'decimal:2',
            'emergency_percentage' => 'decimal:2',
            'investment_percentage' => 'decimal:2',
            'reward_percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
