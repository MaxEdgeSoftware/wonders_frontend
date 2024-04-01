@extends('emails.layout')

@section('body')
<!-- content -->
<td valign="top" class="bodyContent" mc:edit="body_content">
    <h1 class="h1">{{$data['title']}}</h1>
    <p>{!! $data['body'] !!}</p>
    @if(isset($data['hasButton']))
    <a href="{{$data['buttonLink']}}" class="btn btn-primary">{{$data["buttonText"]}}</a>
    @endif
    @if(isset($data['hint']))
    <p>{!! $data['hint'] !!}</p>

    @endif
</td>
@endsection
@section("cancel")
<p class="footer">If you have any questions or concerns, please contact our support team at {{env("MAIL_FROM_ADDRESS")}} or visit our website {{env("APP_URL")}}.</p>
@endsection