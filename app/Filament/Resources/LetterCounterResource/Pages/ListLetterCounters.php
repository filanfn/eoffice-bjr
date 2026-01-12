<?php

namespace App\Filament\Resources\LetterCounterResource\Pages;

use App\Filament\Resources\LetterCounterResource;
use Filament\Resources\Pages\ListRecords;

class ListLetterCounters extends ListRecords
{
    protected static string $resource = LetterCounterResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
