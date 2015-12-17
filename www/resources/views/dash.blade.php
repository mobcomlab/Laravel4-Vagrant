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
		<div id="humidTempGraph" class="panel panel-default">
			<div class="panel-body">
				<div id="humidTempGraphHead">
					<img id="graph" src="{{ asset('images/graph-blue.png') }}">
					<b>Temperature & Humidity</b>
					<div class="btn-group pull-right" role="group" aria-label="...">
						<button id="today" type="button" class="btn btn-default {{ $date == 'today' ? 'active' : '' }}" onclick="swapGraph('today')">Last 24 hours</button>
						<button id="week" type="button" class="btn btn-default {{ $date == 'week' ? 'active' : '' }}" onclick="swapGraph('week')">Last 7 days</button>
					</div>
				</div>
				<hr>
				<div id="chart_humid_temp_day_div"></div>
				<div id="chart_humid_temp_week_div"></div>
			</div>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
		<div id="energyGraph" class="panel panel-default">
			<div class="panel-body">
				<div id="energyGraphHead">
					<img id="graph" src="{{ asset('images/graph-green.png') }}">
					<b>Energy Consumption</b>
					Last 24 hours
				</div>
				<hr>
				<div id="chart_energy_graph_div"></div>
			</div>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
		<div id="energyChart" class="panel panel-default">
			<div class="panel-body">
				<div id="energyChartHead">
					<img id="chart" src="{{ asset('images/chart-green.png') }}">
					<b>Energy Consumption</b>
					Last 7 days
				</div>
				<hr>
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
			$('#chart_humid_temp_week_div').hide();
		});

		function swapGraph(date) {
			if (date != $('input:hidden[name=date]').val()) {
				if (date == 'today') {
					$('#chart_humid_temp_week_div').hide();
					$('#chart_humid_temp_day_div').show();
					refreshGraph('today');
					$('#today').addClass("active");
					$('#week').removeClass("active");
					$('input:hidden[name=date]').val("today");
				} else {
					$('#chart_humid_temp_day_div').hide();
					$('#chart_humid_temp_week_div').show();
					refreshGraph('week');
					$('#week').addClass("active");
					$('#today').removeClass("active");
					$('input:hidden[name=date]').val("week");
				}
			}
		}
    </script>
@endsection
