<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReview extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'aspects' => 'array',
            'item_ratings' => 'array',
            'tags' => 'array',
            'recommend' => 'boolean',
            'anonymous' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
