<?php

namespace App\Filament\Resources\LetterRequestResource\Pages;

use App\Enums\LetterRequestStatus;
use App\Filament\Resources\LetterRequestResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewLetterRequest extends ViewRecord
{
    protected static string $resource = LetterRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->outlined()
                ->visible(
                    fn() =>
                    $this->record->status === LetterRequestStatus::Completed &&
                    !empty($this->record->file_path)
                )
                ->url(fn() => route('download.letter', $this->record)),
        ];
    }
}
