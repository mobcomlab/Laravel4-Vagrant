@extends('app')

@section('content')
    <a class="btn btn-primary" id="castme">Cast Start</a>
    <a class="btn btn-danger" id="stop">Cast Stop</a>
@endsection

@section('body-close')
    <script type="text/javascript" src="//www.gstatic.com/cv/js/sender/v1/cast_sender.js"></script>
    <script type="text/javascript" src="/js/chromecast-sender.js"></script>
@endsection