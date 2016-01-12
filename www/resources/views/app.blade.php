<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Climate Comfort Monitoring</title>

	<link href="{{ asset('packages/bootstrap-3.3.5/css/bootstrap.min.css') }}" rel="stylesheet">
	<link href="{{ asset('packages/datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

	<!-- Fonts -->
	<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:400,300">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

    @yield('head-close')
</head>
<body>
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/"><span class="hidden-xs">ET Building: </span>Climate Comfort Monitoring</a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a id="btn-navbar" href="/">SC5-214</a></li>
				</ul>

				<ul class="nav navbar-nav navbar-right" id="navbar-right">
					<div class="navbar-time" id="clock" style="display: none"><b id="date"></b></div>
					@if (Auth::guest())
						<li id="login"><a id="btn-navbar" href="/auth/login">Login</a></li>
					@else
						<li class="dropdown">
							<a id="btn-navbar" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->name }} <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a id="btn-menu" href="{{ route('export') }}">Export</a></li>
								<li><a id="btn-menu" href="{{ route('cast') }}">Cast</a></li>
								<li role="separator" class="divider"></li>
								<li><a id="btn-menu" href="/auth/logout">Logout</a></li>
							</ul>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>
	@yield('content')

	<!-- Scripts -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.3.1/moment-timezone.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="{{ asset('packages/bootstrap-3.3.5/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('packages/datepicker/js/bootstrap-datepicker.min.js') }}"></script>
	<script src="{{ asset('packages/datepicker/js/bootstrap-datepicker-thai.js') }}"></script>
	<script src="{{ asset('packages/datepicker/locales/bootstrap-datepicker.th.js') }}"></script>


	<script src="/js/app.js"></script>

    @yield('body-close')
</body>
</html>
