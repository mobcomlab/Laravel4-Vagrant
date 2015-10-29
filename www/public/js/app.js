var isLoading = false;
var intervalRefreshContent;
var intervalRefreshGraph;

function refreshContent() {
	if (isLoading) {
		return;
	}

	// Show loading ui
	console.log('Loading');
	isLoading = true;

	
	// Start load data
	var date = $('input:hidden[name=date]').val();
	if (date == 'today') {
		var json = '/api/day/content/';
	} else {
		json = '/api/week/content';
	}
	$.getJSON(json).done(function (data) {

		$("#tempNow").text(formatTemperature(data.temperature.value));
		$("#tempNowUpdated").text(formatDateTime(data.temperature.recorded_at));
		$("#extTempNow").text(formatTemperature(data.external_temperature.value));
		$("#humidNow").text(formatHumidity(data.humidity.value));
		$("#humidNowUpdated").text(formatDateTime(data.humidity.recorded_at));
		$("#extHumidNow").text(formatHumidity(data.external_humidity.value));
		$("#powerNow").text(formatPower(data.power.value));
		if (date == 'today') {
			$("#powerAverage").text(formatPower(data.power.day.average_kw));
			$("#powerNowUpdated").text(formatDateTime(data.power.recorded_at));
			$("#powerHoursUsed").text(formatHours(data.power.day.hours_used));
			$("#powerEnergy").text(formatEnergy(data.power.day.energy_kwh));
		} else {
			$("#powerAverage").text(formatPower(data.power.week.average_kw));
			$("#powerNowUpdated").text(formatDateTime(data.power.recorded_at));
			$("#powerHoursUsed").text(formatHours(data.power.week.hours_used));
			$("#powerEnergy").text(formatEnergy(data.power.week.energy_kwh));
		}
		var powerToEmissionConstant = 0.56352;
		var emissions = data.power.value * powerToEmissionConstant;
		$("#emissions").text(parseFloat(emissions).toFixed(1) + "kg/h");
		if (data.occupancy) {
			var emissionsPerPerson = emissions / data.occupancy.people;
			$("#emissionsPerPerson").text(parseFloat(emissionsPerPerson).toFixed(1) + "kg/h");
		}
		else {
			$("#emissionsPerPerson").html("&infin;");
		}

	}).fail(function (jqxhr, textStatus, error) {
		var err = textStatus + ", " + error;
		console.log("Request Failed: " + err);
	}).always(function () {
		// Back to not loading ui
		isLoading = false;
	});
}

function refreshGraph() {
	if ($('input:hidden[name=date]').val() == 'today') {
		// today
		$.getJSON("/api/day/temperature").done(function(data) {

			var temperature_results = data.results;
			$.each(temperature_results, function(index, value) {
				if (index == 0) {
					return;
				}
				temperature_results[index][0] = formatTime(temperature_results[index][0]);
				temperature_results[index][1] = parseFloat(temperature_results[index][1]);
			});

			console.log(temperature_results);

			var temperature_chartData = google.visualization.arrayToDataTable(temperature_results);

			var temperature_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Temp"},
				hAxis: {title: "Hour"},
				seriesType: "bars",
				series: {0: {type: "line", color: '#ff0000'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: "Roboto"
			};

			var temperature_chart = new google.visualization.ComboChart(document.getElementById('chart_temperature_div'));
			temperature_chart.draw(temperature_chartData, temperature_chartOptions);


		}).fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ", " + error;
			console.log("Request Failed: " + err);
		});

		$.getJSON("/api/day/humidity").done(function(data) {

			var humidity_results = data.results;
			$.each(humidity_results, function(index, value) {
				if (index == 0) {
					return;
				}
				humidity_results[index][0] = formatTime(humidity_results[index][0]);
				humidity_results[index][1] = parseFloat(humidity_results[index][1]);
			});

			console.log(humidity_results);

			var humidity_chartData = google.visualization.arrayToDataTable(humidity_results);

			var humidity_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Humidity"},
				hAxis: {title: "Hour"},
				seriesType: "bars",
				series: {0: {type: "line", color: '#1e90ff'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: "Roboto"
			};

			var humidity_chart = new google.visualization.ComboChart(document.getElementById('chart_humidity_div'));
			humidity_chart.draw(humidity_chartData, humidity_chartOptions);


		}).fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ", " + error;
			console.log("Request Failed: " + err);
		});

		$.getJSON("/api/day/power").done(function(data) {

			var power_results = data.results;
			$.each(power_results, function(index, value) {
				if (index == 0) {
					return;
				}
				power_results[index][0] = formatTime(power_results[index][0]);
				power_results[index][1] = parseFloat(power_results[index][1]);
			});

			console.log(power_results);

			var power_chartData = google.visualization.arrayToDataTable(power_results);

			var power_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Power"},
				hAxis: {title: "Hour"},
				seriesType: "bars",
				series: {0: {type: "line", color: '#00ff00'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: "Roboto"
			};

			var power_chart = new google.visualization.ComboChart(document.getElementById('chart_power_div'));
			power_chart.draw(power_chartData, power_chartOptions);


		}).fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ", " + error;
			console.log("Request Failed: " + err);
		});
	} else {
		// week
		$.getJSON("/api/week/temperature").done(function(data) {

			var temperature_results = data.results;
			$.each(temperature_results, function(index, value) {
				if (index == 0) {
					return;
				}
				temperature_results[index][0] = temperature_results[index][0];
				temperature_results[index][1] = parseFloat(temperature_results[index][1]);
			});

			console.log(temperature_results);

			var temperature_chartData = google.visualization.arrayToDataTable(temperature_results);

			var temperature_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Temp"},
				hAxis: {title: "Day", gridlines: {count: 7}},
				seriesType: "bars",
				series: {0: {type: "line", color: '#ff0000'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: "Roboto"
			};

			var temperature_chart = new google.visualization.ComboChart(document.getElementById('chart_temperature_div'));
			temperature_chart.draw(temperature_chartData, temperature_chartOptions);


		}).fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ", " + error;
			console.log("Request Failed: " + err);
		});

		$.getJSON("/api/week/humidity").done(function(data) {

			var humidity_results = data.results;
			$.each(humidity_results, function(index, value) {
				if (index == 0) {
					return;
				}
				humidity_results[index][0] = humidity_results[index][0];
				humidity_results[index][1] = parseFloat(humidity_results[index][1]);
			});

			console.log(humidity_results);

			var humidity_chartData = google.visualization.arrayToDataTable(humidity_results);

			var humidity_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Humidity"},
				hAxis: {title: "Day", gridlines: {count: 7}},
				seriesType: "bars",
				series: {0: {type: "line", color: '#1e90ff'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: "Roboto"
			};

			var humidity_chart = new google.visualization.ComboChart(document.getElementById('chart_humidity_div'));
			humidity_chart.draw(humidity_chartData, humidity_chartOptions);


		}).fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ", " + error;
			console.log("Request Failed: " + err);
		});

		$.getJSON("/api/week/power").done(function(data) {

			var power_results = data.results;
			$.each(power_results, function(index, value) {
				if (index == 0) {
					return;
				}
				power_results[index][0] = power_results[index][0];
				power_results[index][1] = parseFloat(power_results[index][1]);
			});

			console.log(power_results);

			var power_chartData = google.visualization.arrayToDataTable(power_results);

			var power_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Power"},
				hAxis: {title: "Day", gridlines: {count: 7}},
				seriesType: "bars",
				series: {0: {type: "line", color: '#00ff00'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: "Roboto"
			};

			var power_chart = new google.visualization.ComboChart(document.getElementById('chart_power_div'));
			power_chart.draw(power_chartData, power_chartOptions);


		}).fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ", " + error;
			console.log("Request Failed: " + err);
		});
	}
}

function startUpdates() {
	refreshContent();
	refreshGraph();
    intervalRefreshContent = setInterval(refreshContent, 15000);
    intervalRefreshGraph = setInterval(refreshGraph, 29000);
}
function stopUpdates() {
	window.clearInterval(intervalRefreshContent);
	window.clearInterval(intervalRefreshGraph);
}

function formatTemperature(temp) {
	return parseFloat(temp).toFixed(1)+'°C';
}

function formatHumidity(humid) {
	return parseFloat(humid).toFixed()+'%';
}

function formatPower(power) {
	return parseFloat(power).toFixed(1)+'kW';
}

function formatEnergy(energy) {
	return parseFloat(energy).toFixed(1)+'kWh';
}

function formatHours(hours) {
	return parseFloat(hours).toFixed(1)+' hours';
}

function formatDateTime(dt) {
	return moment.utc(dt).fromNow();
}

function formatTime(dt) {
	return moment.utc(dt).add(7,'hours').format('HH:mm');
}

