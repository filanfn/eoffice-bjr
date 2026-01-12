<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'form_schema',
    ];

    protected function casts(): array
    {
        return [
            'form_schema' => 'array',
        ];
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }
}
