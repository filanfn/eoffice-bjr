<?php

namespace App\Filament\Resources\LetterCounterResource\Pages;

use App\Filament\Resources\LetterCounterResource;
use Filament\Resources\Pages\EditRecord;

class EditLetterCounter extends EditRecord
{
    protected static string $resource = LetterCounterResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
