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
				<a class="navbar-brand" href="/">ET Building: Climate Comfort Monitoring</a>
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
	<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
		<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
			<div id="temp" class="panel panel-default">
				<div class="panel-body text-center">
					<div id="tempHead">Temperature</div>
					<div id="tempBody"><b id="tempNow">-°C</b></div>
					<div id="tempOut">Outside <span id="extTempNow">-°C</span></div>
					<div id="tempFoot" class="small">Updated <span id="tempNowUpdated">-</span></div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
			<div id="humid" class="panel panel-default">
				<div class="panel-body text-center">
					<div id="humidHead">Humidity</div>
					<div id="humidBody"><b id="humidNow">-%</b></div>
					<div id="humidOut">Outside <span id="extHumidNow">-%</span></div>
					<div id="humidFoot" class="small">Updated <span id="humidNowUpdated">-</span></div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
			<div id="power" class="panel panel-default">
				<div class="panel-body text-center">
					<div id="powerHead">Power</div>
					<div id="powerBody"><b id="powerNow">-kW</b></div>
					<div id="powerAvg">Average <span id="powerAverage">-kW</span></div>
					<div id="powerFoot" class="small">Updated <span id="powerNowUpdated">-</span></div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
			<div id="energy" class="panel panel-default">
				<div class="panel-body text-center">
					<div id="energyHead">Energy Consumption</div>
					<div id="energyBody"><b id="energyNow">-kWh</b></div>
					<div id="energyUsage">Usage <span id="powerHoursUsed">-hours</span></div>
					<div id="energyFoot" class="small">Updated <span id="energyNowUpdated">-</span></div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
		<div id="graph" class="panel panel-default">
			<div class="panel-heading dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Temperature & Humidity ( {{ $date == 'today' ? 'Last 24 hours' : 'Last 7 days' }} )<span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li><a href="{{ route('date',['date' => 'today']) }}">Last 24 hours</a></li>
					<li><a href="{{ route('date',['date' => 'week']) }}">Last 7 days</a></li>
				</ul>
			</div>
			<div class="panel-body">
				<div id="chart_humid_temp_div"></div>
			</div>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
		<div id="graph" class="panel panel-default">
			<div class="panel-heading">Energy Consumption ( Last 24 hours )</div>
			<div class="panel-body">
				<div id="chart_energy_graph_div"></div>
			</div>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
		<div id="energy_graph" class="panel panel-default">
			<div class="panel-heading">Energy Consumption ( Last 7 days )</div>
			<div class="panel-body">
				<div id="chart_energy_div"></div>
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
