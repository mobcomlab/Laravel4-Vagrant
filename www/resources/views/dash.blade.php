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
	<div class="col-sm-12 col-md-4 col-lg-4">
		<div id="now" class="panel panel-default">
			<div class="panel-heading text-center">Now</div>
			<div class="panel-body text-center">
				<div class="pull-left">
					<div>Temperature</div>
					<div id="tempNow" class="lead">-°C</div>
					<div>Outside <span id="extTempNow">-°C</span></div>
				</div>
				<div class="pull-right">
					<div>Humidity</div>
					<div id="humidNow" class="lead">-%</div>
					<div>Outside <span id="extHumidNow">-%</span></div>
				</div>
				<div id="humidtempUpdate" class="small">Updated <span id="humidtempNowUpdated">-</span></div>
				<hr>
				<div class="pull-left">
					<div>Power</div>
					<div id="powerNow" class="lead">-kW</div>
					<div>Average <span id="powerAverage">-kW</span></div>
				</div>
				<div class="pull-right">
					<div>Consumption</div>
					<div class="lead" id="powerEnergy">-kWh</div>
					<div>Usage <span id="powerHoursUsed"></span></div>
				</div>
				<div id="powerUpdate" class="small">Updated <span id="powerNowUpdated">-</span></div>
			</div>
		</div>
			<div id="energy" class="panel panel-default">
				<div class="panel-heading text-center">Last 7 days</div>
				<div class="panel-body">
					<div id="chart_energy_div"></div>
				</div>
			</div>
	</div>
	<div class="col-sm-12 col-md-8 col-lg-8">
		<div id="graph" class="panel panel-default">
			<div class="panel-heading text-center dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ $date == 'today' ? 'Last 24 hours' : 'Last week' }}<span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
						<li><a href="{{ route('date',['date' => 'today']) }}">Last 24 hours</a></li>
						<li><a href="{{ route('date',['date' => 'week']) }}">Last week</a></li>
					</ul>
				</li></div>
			<div class="panel-body">
				<div id="chart_humid_temp_div"></div>
				<div id="chart_power_div"></div>
				<div id="chart_energy_graph_div"></div>
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
