<?php

namespace App\Models;

use App\Enums\LetterRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'letter_type_id',
        'status',
        'payload_data',
        'letter_number',
        'file_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LetterRequestStatus::class,
            'payload_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class);
    }
}
