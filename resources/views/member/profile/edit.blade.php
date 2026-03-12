@extends('member.layouts.app')

@section('title', 'プロフィール編集')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">プロフィール編集</h1>

    <form method="POST" action="{{ route('member.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-2 mb-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">氏名 <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $member->name) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="name_kana" class="block text-sm font-medium text-gray-700 mb-1">氏名（カナ）</label>
                <input type="text" name="name_kana" id="name_kana" value="{{ old('name_kana', $member->name_kana) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email', $member->email) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="grid gap-4 md:grid-cols-2 mb-4">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード</label>
                <input type="password" name="password" id="password"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">変更する場合のみ入力</p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード（確認）</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        @if($customFields->isNotEmpty())
        <hr class="my-6">
        <h3 class="font-bold text-gray-800 mb-4">追加情報</h3>

        @foreach($customFields as $field)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $field->name }}
                    @if($field->is_required) <span class="text-red-500">*</span> @endif
                </label>

                @php $currentValue = $fieldValues[$field->id] ?? ''; @endphp

                @switch($field->type->value)
                    @case('text')
                    @case('number')
                        <input type="{{ $field->type->value === 'number' ? 'number' : 'text' }}"
                               name="custom_fields[{{ $field->id }}]"
                               value="{{ old("custom_fields.{$field->id}", $currentValue) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @break
                    @case('textarea')
                        <textarea name="custom_fields[{{ $field->id }}]" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old("custom_fields.{$field->id}", $currentValue) }}</textarea>
                        @break
                    @case('select')
                        <select name="custom_fields[{{ $field->id }}]"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">選択してください</option>
                            @foreach($field->options ?? [] as $option)
                                <option value="{{ $option }}" {{ $currentValue === $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                        @break
                    @case('radio')
                        <div class="space-y-1">
                            @foreach($field->options ?? [] as $option)
                                <label class="flex items-center">
                                    <input type="radio" name="custom_fields[{{ $field->id }}]" value="{{ $option }}"
                                           {{ $currentValue === $option ? 'checked' : '' }} class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        @break
                    @case('date')
                        <input type="date" name="custom_fields[{{ $field->id }}]"
                               value="{{ old("custom_fields.{$field->id}", $currentValue) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @break
                @endswitch
            </div>
        @endforeach
        @endif

        <div class="mt-6">
            <button type="submit"
                    class="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition font-medium">
                更新する
            </button>
        </div>
    </form>
</div>
@endsection
