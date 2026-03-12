<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->saveCustomFields();
    }

    protected function saveCustomFields(): void
    {
        $customFieldsData = $this->data['custom_fields'] ?? [];

        foreach ($customFieldsData as $slug => $value) {
            $field = CustomField::where('slug', $slug)->first();
            if (!$field || $value === null) continue;

            $storeValue = is_array($value) ? json_encode($value) : $value;

            CustomFieldValue::updateOrCreate(
                [
                    'member_id' => $this->record->id,
                    'custom_field_id' => $field->id,
                ],
                ['value' => $storeValue]
            );
        }
    }
}
