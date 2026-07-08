<?php

namespace App\Finance\Models;

use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetAllocation extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'income_id',
        'month',
        'income_amount',
        'asrat_amount',
        'needs_amount',
        'emergency_amount',
        'investment_amount',
        'reward_amount',
    ];

    protected function casts(): array
    {
        return [
            'income_amount' => 'decimal:2',
            'asrat_amount' => 'decimal:2',
            'needs_amount' => 'decimal:2',
            'emergency_amount' => 'decimal:2',
            'investment_amount' => 'decimal:2',
            'reward_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
