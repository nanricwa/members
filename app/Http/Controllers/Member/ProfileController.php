<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $member = Auth::guard('member')->user();
        $customFields = CustomField::active()->ordered()->get();
        $fieldValues = CustomFieldValue::where('member_id', $member->id)
            ->pluck('value', 'custom_field_id')
            ->toArray();

        return view('member.profile.edit', compact('member', 'customFields', 'fieldValues'));
    }

    public function update(Request $request)
    {
        $member = Auth::guard('member')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $member->update([
            'name' => $validated['name'],
            'name_kana' => $validated['name_kana'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $member->update(['password' => Hash::make($validated['password'])]);
        }

        // カスタムフィールド保存
        $customFieldsData = $request->input('custom_fields', []);
        foreach ($customFieldsData as $fieldId => $value) {
            if ($value === null || $value === '') {
                CustomFieldValue::where('member_id', $member->id)
                    ->where('custom_field_id', $fieldId)
                    ->delete();
                continue;
            }

            $storeValue = is_array($value) ? json_encode($value) : $value;

            CustomFieldValue::updateOrCreate(
                ['member_id' => $member->id, 'custom_field_id' => $fieldId],
                ['value' => $storeValue]
            );
        }

        return back()->with('success', 'プロフィールを更新しました。');
    }
}
