@extends('app')

@section('content')
    <div class="text-center">
        <img id="cast" src="{{ asset('images/cast.png') }}">
    </div>
    <div class="text-center">
        <a class="btn-lg btn-primary" id="castme" data-cast="{{ asset('images/cast.png') }}" data-loaded="{{ asset('images/cast_loaded.png') }}" data-connected="{{ asset('images/cast_connected.png') }}">Cast Start</a>
    </div>

@endsection

@section('body-close')
    <script type="text/javascript" src="//www.gstatic.com/cv/js/sender/v1/cast_sender.js"></script>
    <script type="text/javascript" src="/js/chromecast-sender.js"></script>
@endsection