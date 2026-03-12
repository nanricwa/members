<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // カスタムフィールドをフォームに読み込み
        $customFields = [];
        $values = CustomFieldValue::where('member_id', $this->record->id)
            ->with('customField')
            ->get();

        foreach ($values as $cfv) {
            if (!$cfv->customField) continue;

            $slug = $cfv->customField->slug;
            $fieldType = $cfv->customField->type->value;

            if ($fieldType === 'checkbox') {
                $customFields[$slug] = json_decode($cfv->value, true) ?? [];
            } else {
                $customFields[$slug] = $cfv->value;
            }
        }

        $data['custom_fields'] = $customFields;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['password']) && filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->saveCustomFields();
    }

    protected function saveCustomFields(): void
    {
        $customFieldsData = $this->data['custom_fields'] ?? [];

        foreach ($customFieldsData as $slug => $value) {
            $field = CustomField::where('slug', $slug)->first();
            if (!$field) continue;

            if ($value === null || $value === '' || $value === []) {
                CustomFieldValue::where('member_id', $this->record->id)
                    ->where('custom_field_id', $field->id)
                    ->delete();
                continue;
            }

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
