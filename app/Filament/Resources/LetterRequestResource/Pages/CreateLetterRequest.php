<?php

namespace App\Filament\Resources\LetterRequestResource\Pages;

use App\Filament\Resources\LetterRequestResource;
use App\Models\LetterType;
use Filament\Resources\Pages\CreateRecord;

class CreateLetterRequest extends CreateRecord
{
    protected static string $resource = LetterRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';

        // Collect dynamic fields into payload_data
        $payloadData = [];
        $letterType = LetterType::find($data['letter_type_id'] ?? null);
        $formSchema = $letterType?->form_schema ?? [];

        foreach ($formSchema as $field) {
            $fieldName = $field['name'] ?? 'field';
            $dynamicKey = 'dynamic_' . $fieldName;

            if (isset($data[$dynamicKey])) {
                $payloadData[$fieldName] = $data[$dynamicKey];
                unset($data[$dynamicKey]); // Remove from main data
            }
        }

        $data['payload_data'] = $payloadData;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
