<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\RegistrationForm;
use App\Services\MemberRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function show(RegistrationForm $form)
    {
        if (!$form->is_active || !$form->isAccepting()) {
            abort(404, 'このフォームは現在受付していません。');
        }

        $customFields = $form->customFields()->get();

        return view('member.auth.register', compact('form', 'customFields'));
    }

    public function store(RegistrationForm $form, Request $request, MemberRegistrationService $service)
    {
        if (!$form->is_active || !$form->isAccepting()) {
            abort(404, 'このフォームは現在受付していません。');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|confirmed|min:8',
        ];

        // カスタムフィールドのバリデーション
        foreach ($form->customFields as $field) {
            $fieldRules = [];
            if ($field->pivot->is_required || $field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            $rules["custom_fields.{$field->slug}"] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $member = $service->register($form, $validated);

        Auth::guard('member')->login($member);

        if ($form->redirect_url) {
            return redirect($form->redirect_url);
        }

        return redirect()->route('registration.complete', $form->slug);
    }

    public function complete(RegistrationForm $form)
    {
        return view('member.auth.register-complete', compact('form'));
    }
}
