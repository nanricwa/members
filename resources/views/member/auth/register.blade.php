@extends('member.layouts.auth')

@section('title', $form->name)

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    @if($form->header_image)
        <img src="{{ Storage::url($form->header_image) }}" alt="" class="w-full rounded-lg mb-6">
    @endif

    <h2 class="text-xl font-bold text-gray-800 mb-2 text-center">{{ $form->name }}</h2>

    @if($form->description)
        <p class="text-gray-600 text-sm mb-6 text-center">{{ $form->description }}</p>
    @endif

    @if($form->body_html)
        <div class="prose prose-sm mb-6">{!! $form->body_html !!}</div>
    @endif

    <form method="POST" action="{{ route('registration.store', $form->slug) }}">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">氏名 <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>

        <div class="mb-4">
            <label for="name_kana" class="block text-sm font-medium text-gray-700 mb-1">氏名（カナ）</label>
            <input type="text" name="name_kana" id="name_kana" value="{{ old('name_kana') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">パスワード <span class="text-red-500">*</span></label>
            <input type="password" name="password" id="password"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">パスワード（確認） <span class="text-red-500">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>

        @foreach($customFields as $field)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $field->name }}
                    @if($field->pivot->is_required || $field->is_required)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @switch($field->type->value)
                    @case('text')
                    @case('number')
                        <input type="{{ $field->type->value === 'number' ? 'number' : 'text' }}"
                               name="custom_fields[{{ $field->slug }}]"
                               value="{{ old("custom_fields.{$field->slug}") }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               {{ ($field->pivot->is_required || $field->is_required) ? 'required' : '' }}>
                        @break
                    @case('textarea')
                        <textarea name="custom_fields[{{ $field->slug }}]"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  {{ ($field->pivot->is_required || $field->is_required) ? 'required' : '' }}>{{ old("custom_fields.{$field->slug}") }}</textarea>
                        @break
                    @case('select')
                        <select name="custom_fields[{{ $field->slug }}]"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                {{ ($field->pivot->is_required || $field->is_required) ? 'required' : '' }}>
                            <option value="">選択してください</option>
                            @foreach($field->options ?? [] as $option)
                                <option value="{{ $option }}" {{ old("custom_fields.{$field->slug}") === $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                        @break
                    @case('radio')
                        <div class="space-y-1">
                            @foreach($field->options ?? [] as $option)
                                <label class="flex items-center">
                                    <input type="radio" name="custom_fields[{{ $field->slug }}]" value="{{ $option }}"
                                           {{ old("custom_fields.{$field->slug}") === $option ? 'checked' : '' }}
                                           class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        @break
                    @case('date')
                        <input type="date" name="custom_fields[{{ $field->slug }}]"
                               value="{{ old("custom_fields.{$field->slug}") }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @break
                @endswitch
            </div>
        @endforeach

        <button type="submit"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition font-medium text-lg mt-4">
            {{ $form->button_text }}
        </button>
    </form>
</div>

@if($form->custom_css)
    <style>{!! $form->custom_css !!}</style>
@endif
@endsection
