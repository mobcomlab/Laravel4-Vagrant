@extends('app')

@section('content')

	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">ET Building: Climate Comfort Monitoring</a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="/">SC5-214</a></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ $date == 'today' ? 'Last 24 hours' : 'Last week' }}<span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="{{ route('date',['date' => 'today']) }}">Last 24 hours</a></li>
							<li><a href="{{ route('date',['date' => 'week']) }}">Last week</a></li>
						</ul>
					</li>
				</ul>

				<ul class="nav navbar-nav navbar-right">
					@if (Auth::guest())
						<li><a href="/auth/login">Login</a></li>
					@else
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->name }} <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="{{ route('export') }}">Export</a></li>
								<li role="separator" class="divider"></li>
								<li><a href="/auth/logout">Logout</a></li>
							</ul>
						</li>
					@endif
				</ul>
			</div>
		</div>
	</nav>
<div class="container">
	<div class="row">
		<div class="col-md-2">
			<div class="panel panel-default">
				<div class="panel-heading text-center">Temperature</div>
				<div class="panel-body text-center">
					<div id="tempNow" class="lead">-°C</div>
					<hr>
					<div class="">Outside <span id="extTempNow">-°C</span></div>
					<hr>
					<div class="small">Updated <span id="tempNowUpdated">-</span></div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="panel panel-default">
				<div class="panel-heading text-center">Humidity</div>
				<div class="panel-body text-center">
					<div id="humidNow" class="lead">-%</div>
					<hr>
					<div class="">Outside <span id="extHumidNow">-%</span></div>
					<hr>
					<div class="small">Updated <span id="humidNowUpdated">-</span></div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-body">
					<div id="chart_temperature_div" style="width: 100%; height: 230px;"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2">
			<div class="panel panel-default">
				<div class="panel-heading text-center">Power</div>
				<div class="panel-body text-center">
					<div id="powerNow" class="lead">-kW</div>
					<hr>
					<div class="">{{ $date == 'today' ? "Today's avg" : "Week's avg" }} <span id="powerAverage">-kW</span></div>
					<hr>
					<div class="small">Updated <span id="powerNowUpdated">-</span></div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="panel panel-default">
				<div class="panel-heading text-center">Energy consumption</div>
				<div class="panel-body text-center">
					<div class="small">{{ $date == 'today' ? "Today's usage" : "Week's usage" }}</div>
					<div class="lead" id="powerHoursUsed"></div>
					<hr>
					<div class="small">Total energy</div>
					<div class="lead" id="powerEnergy">-kWh</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-body">
					<div id="chart_humidity_div" style="width: 100%; height: 230px;"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-2">
			<div class="panel panel-default">
				<div class="panel-heading text-center">CO<sub>2</sub> emissions</div>
				<div class="panel-body text-center">
					<div id="emissions" class="lead">-kg/h</div>
					<hr>
					<div id="emissionsPerPerson">-kg/h</div>
					<div>per person</div>
				</div>
			</div>
		</div>
		<div class="col-md-2"></div>
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-body">
					<div id="chart_power_div" style="width: 100%; height: 230px;"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<input type="hidden" name="date" value="{{ old('date',$date) }}">
@endsection

@section('body-close')
	<script type="text/javascript" src="https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.0','packages':['corechart']}]}"></script>
	<script>
        $(document).ready(function() {
            startUpdates();
        });
    </script>
@endsection
