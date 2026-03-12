@extends('emails.layout')

@section('content')
<h2>{{ $memberName }} 様、ご登録ありがとうございます！</h2>

{!! nl2br(e($bodyText)) !!}

<p style="margin-top: 20px;">
    <a href="{{ $loginUrl }}" class="btn">マイページにログイン</a>
</p>

@if($planName)
<p style="margin-top: 15px; color: #666;">
    付与プラン: {{ $planName }}
</p>
@endif
@endsection
