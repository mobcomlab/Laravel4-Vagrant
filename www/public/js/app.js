var isLoading = false;
var intervalRefreshContent;

function refreshContent() {
	if (isLoading) {
		return;
	}
	
	// Show loading ui
	console.log('Loading');
	isLoading = true;
	
	
	// Start load data
	$.getJSON("/api/now").done(function(data) {
		
		if (data.occupancy) {
			$("#occupancy").text(formatPeople(data.occupancy.people));
			$("#occupancyTime").text(formatTime(data.occupancy.start)+'-'+formatTime(data.occupancy.end));
			$('#occupancyDesc').text(data.occupancy.description);
		}
		$("#tempNow").text(formatTemperature(data.temperature.value));
		$("#tempNowUpdated").text(formatDateTime(data.temperature.recorded_at));
		$("#extTempNow").text(formatTemperature(data.external_temperature.value));
		$("#humidNow").text(formatHumidity(data.humidity.value));
		$("#humidNowUpdated").text(formatDateTime(data.humidity.recorded_at));
		$("#extHumidNow").text(formatHumidity(data.external_humidity.value));
		$("#powerNow").text(formatPower(data.power.value));
		$("#powerDayAverage").text(formatPower(data.power.day.average_kw));
		$("#powerNowUpdated").text(formatDateTime(data.power.recorded_at));
		$("#powerDayHoursUsed").text(formatHours(data.power.day.hours_used));
		$("#powerDayEnergy").text(formatEnergy(data.power.day.energy_kwh));
		
		var powerToEmissionConstant = 0.56352;
		var emissions = data.power.value * powerToEmissionConstant;
		$("#emissions").text(parseFloat(emissions).toFixed(1)+"kg/h");
		if (data.occupancy) {
			var emissionsPerPerson = emissions / data.occupancy.people;
			$("#emissionsPerPerson").text(parseFloat(emissionsPerPerson).toFixed(1)+"kg/h");
		}
		else {
			$("#emissionsPerPerson").html("&infin;");
		}
		
		
	}).fail(function(jqxhr, textStatus, error) {
    	var err = textStatus + ", " + error;
    	console.log("Request Failed: " + err);
	}).always(function() {
		// Back to not loading ui
		isLoading = false;
	});
}

function refreshGraph() {
	$.getJSON("/api/day").done(function(data) {
		
		var results = data.results;
		$.each(results, function(index, value) {
			if (index == 0) {
				return;
			}
			results[index][0] = formatTime(results[index][0]);
			results[index][1] = parseInt(results[index][1]);
			results[index][2] = parseFloat(results[index][2]);
			results[index][3] = parseFloat(results[index][3]);
			results[index][4] = parseFloat(results[index][4]);
		});
		
		console.log(results);
		
		var chartData = google.visualization.arrayToDataTable(results);

  	  	var chartOptions = {
			height: "100%",
			width: "100%",
			vAxis: {title: "Humidity/Temp"},
			hAxis: {title: "Hour", gridlines: {count: 6}},
			seriesType: "bars",
			series: {0: {targetAxisIndex: 0}, 1: {type: "line"}, 2: {type: "line"}, 3: {type: "line", targetAxisIndex: 1}},
			vAxes:{1:{title:'Power'}},
			animation: {startup: true, duration: 500},
			fontName: "Roboto"
  	  	};

  	  	var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
  	  	chart.draw(chartData, chartOptions);
		
	}).fail(function(jqxhr, textStatus, error) {
    	var err = textStatus + ", " + error;
    	console.log("Request Failed: " + err);
	});
}


$(document).ready(function() {
	refreshContent();
	refreshGraph();
	intervalRefreshContent = setInterval(refreshContent, 15000);
	intervalRefreshGraph = setInterval(refreshGraph, 29000);
});

function stopUpdates() {
	window.clearInterval(intervalRefreshContent);
	window.clearInterval(intervalRefreshGraph);
}

function formatPeople(people) {
	return people+' people';
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
	return moment.utc(dt).format('HH:mm');
}
