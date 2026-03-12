<?php

namespace App\Services;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Member;
use App\Models\RegistrationForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberRegistrationService
{
    public function register(RegistrationForm $form, array $data): Member
    {
        return DB::transaction(function () use ($form, $data) {
            // 会員作成
            $member = Member::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'name' => $data['name'],
                'name_kana' => $data['name_kana'] ?? null,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // プラン付与
            if ($form->plan_id) {
                $member->plans()->attach($form->plan_id, [
                    'status' => 'active',
                    'started_at' => now(),
                    'granted_by' => 'registration',
                ]);
            }

            // カスタムフィールド保存
            $this->saveCustomFields($member, $form, $data);

            return $member;
        });
    }

    protected function saveCustomFields(Member $member, RegistrationForm $form, array $data): void
    {
        $formFields = $form->customFields()->get();

        foreach ($formFields as $field) {
            $value = $data['custom_fields'][$field->slug] ?? null;
            if ($value === null) continue;

            $storeValue = is_array($value) ? json_encode($value) : $value;

            CustomFieldValue::create([
                'member_id' => $member->id,
                'custom_field_id' => $field->id,
                'value' => $storeValue,
            ]);
        }
    }
}
