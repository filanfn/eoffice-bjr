<?php

namespace App\Filament\Resources\LetterArchiveResource\Pages;

use App\Filament\Resources\LetterArchiveResource;
use Filament\Resources\Pages\ListRecords;

class ListLetterArchives extends ListRecords
{
    protected static string $resource = LetterArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
