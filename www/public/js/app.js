$.fn.datepicker.defaults.format = "dd/mm/yyyy";
$.fn.datepicker.defaults.autoclose = true;
$.fn.datepicker.defaults.orientation = "top left";
var isLoading = false;
var intervalRefreshContent;
var intervalRefreshGraph;


function refreshContent() {
	if (isLoading) {
		return;
	}

	// Show loading ui
	isLoading = true;

	
	// Start load data
	var json = '/api/now';
	$.getJSON(json).done(function (data) {

		$("#tempNow").text(formatTemperature(data.temperature.value));
		$("#tempNowUpdated").text(formatDateTime(data.temperature.recorded_at));
		$("#extTempNow").text(formatTemperature(data.external_temperature.value));
		$("#humidNow").text(formatHumidity(data.humidity.value));
		$("#humidNowUpdated").text(formatDateTime(data.humidity.recorded_at));
		$("#extHumidNow").text(formatHumidity(data.external_humidity.value));
		$("#powerNow").text(formatPower(data.power.value));
		$("#powerAverage").text(formatPower(data.power.day.average_kw));
		$("#powerNowUpdated").text(formatDateTime(data.power.recorded_at));
		$("#energyNowUpdated").text(formatDateTime(data.power.recorded_at));
		$("#powerHoursUsed").text(formatHours(data.power.day.hours_used));
		$("#energyNow").text(formatEnergy(data.power.day.energy_kwh));
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

function dayHumidTemp() {
	$.getJSON("/api/day/humidtemp").done(function (data) {

		var humidtemp_results = data.results;
		$.each(humidtemp_results, function (index, value) {
			if (index == 0) {
				return;
			}
			humidtemp_results[index][0] = formatTime(humidtemp_results[index][0]);
			humidtemp_results[index][1] = parseFloat(humidtemp_results[index][1]);
			humidtemp_results[index][2] = parseInt(humidtemp_results[index][2]);
		});

		var humidtemp_chartData = google.visualization.arrayToDataTable(humidtemp_results);

		var humidtemp_chartOptions = {
			height: "100%",
			width: "100%",
			vAxis: {
				title: "Temperature (°C)",
				minValue: 0,
				maxValue: 40
			},
			hAxis: {
				title: "Hour"
			},
			seriesType: "bars",
			series: {
				0: {type: "line", color: '#f44336'},
				1: {type: "line", color: '#2196F3', targetAxisIndex: 1},
				2: {type: "line", targetAxisIndex: 2}
			},
			vAxes: {
				1: {
					title: 'Humidity (%)',
					minValue: 30,
					maxValue: 70
				}
			},
			legend: {position: 'top'},
			animation: {startup: true, duration: 500},
			fontName: 'Roboto',
			lineWidth: 4
		};

		var humidtemp_chart = new google.visualization.ComboChart(document.getElementById('chart_humid_temp_div'));
		humidtemp_chart.draw(humidtemp_chartData, humidtemp_chartOptions);

		google.visualization.events.addListener(humidtemp_chart, 'ready', readyHandler);

		function readyHandler() {
			$('#loading').hide();
		}

	}).fail(function (jqxhr, textStatus, error) {
		var err = textStatus + ", " + error;
		console.log("Request Failed: " + err);
	});
}

function weekHumidTemp() {
	$.getJSON("/api/week/humidtemp").done(function(data) {

		var humidtemp_results = data.results;
		$.each(humidtemp_results, function(index, value) {
			if (index == 0) {
				return;
			}
			humidtemp_results[index][0] = dateName(humidtemp_results[index][0]);
			humidtemp_results[index][1] = parseFloat(humidtemp_results[index][1]);
			humidtemp_results[index][2] = parseInt(humidtemp_results[index][2]);
		});

		var humidtemp_chartData = google.visualization.arrayToDataTable(humidtemp_results);

		var humidtemp_chartOptions = {
			height: "100%",
			width: "100%",
			vAxis: {
				title: "Temperature (°C)",
				minValue: 0,
				maxValue: 40
			},
			hAxis: {title: "Day", gridlines: {count: 7}},
			seriesType: "bars",
			series: {0: {type: "line", color: '#f44336'}, 1: {type: "line",color: '#2196F3', targetAxisIndex: 1}, 2: {type: "line", targetAxisIndex: 2}},
			vAxes:{1:{
				title:'Humidity (%)',
				minValue: 30,
				maxValue: 70
			}},
			legend: { position: 'top' },
			animation: {startup: true, duration: 500},
			fontName: "Roboto",
			lineWidth: 4
		};
		var humidtemp_chart = new google.visualization.ComboChart(document.getElementById('chart_humid_temp_div'));
		humidtemp_chart.draw(humidtemp_chartData, humidtemp_chartOptions);

		google.visualization.events.addListener(humidtemp_chart, 'ready', readyHandler);

		function readyHandler() {
			$('#loading').hide();
		}


	}).fail(function(jqxhr, textStatus, error) {
		var err = textStatus + ", " + error;
		console.log("Request Failed: " + err);
	});
}

function refreshGraph(check) {
	if (check) {
		if($('input:hidden[name=date]').val() == 'today') {
			dayHumidTemp();
		} else {
			weekHumidTemp();
		}
	} else {
		if($('input:hidden[name=date]').val() == 'today') {
			dayHumidTemp();
		} else {
			weekHumidTemp();
		}
		$.getJSON("/api/day/power").done(function(data) {

			var power_results = data.results;
			$.each(power_results, function(index, value) {
				if (index == 0) {
					return;
				}
				power_results[index][0] = formatTime(power_results[index][0]);
				power_results[index][1] = parseFloat(power_results[index][1]);
			});

			var power_chartData = google.visualization.arrayToDataTable(power_results);

			var power_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Energy consumption (kWh)"},
				hAxis: {title: "Hour"},
				seriesType: "bars",
				series: {0: {type: "line", color: '#43a047'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: "Roboto",
				lineWidth: 4
			};

			var power_chart = new google.visualization.ComboChart(document.getElementById('chart_energy_graph_div'));
			power_chart.draw(power_chartData, power_chartOptions);


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
				power_results[index][0] = dateName(power_results[index][0]);
				power_results[index][1] = parseFloat(power_results[index][1]);
			});

			var power_chartData = google.visualization.arrayToDataTable(power_results);

			var power_chartOptions = {
				height: "100%",
				width: "100%",
				vAxis: {title: "Energy consumption (kWh)"},
				hAxis: {title: "Day", gridlines: {count: 7}},
				seriesType: "bars",
				bar: {
					groupWidth: '100%'
				},
				series: {0: { color: '#43a047'}},
				legend: { position: 'none' },
				animation: {startup: true, duration: 500},
				fontName: 'Roboto'
			};

			var power_chart = new google.visualization.ComboChart(document.getElementById('chart_energy_div'));
			power_chart.draw(power_chartData, power_chartOptions);


		}).fail(function(jqxhr, textStatus, error) {
			var err = textStatus + ", " + error;
			console.log("Request Failed: " + err);
		});
	}
}

function startUpdates() {
	refreshContent();
	refreshGraph(false);
    intervalRefreshContent = setInterval(refreshContent, 60000);
	setTimeout(function() {
		intervalRefreshGraph = setInterval(refreshGraph, 60000);
	}, 30000);
}
function stopUpdates() {
	window.clearInterval(intervalRefreshContent);
	window.clearInterval(intervalRefreshGraph);
}
function dateName(subDay) {
	var days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

	var now = new Date();
	if (now.getDate() == 1 || now.getDate() == 21 || now.getDate() == 31) {
		var extension = 'st';
	} else if (now.getDate() == 2 || now.getDate() == 22) {
		extension = 'nd';
	} else if (now.getDate() == 3 || now.getDate() == 23) {
		extension = 'rd';
	} else {
		extension = 'th';
	}
	switch (now.getDay()-subDay) {
		case -1: var day = 6;break;
		case -2: day = 5;break;
		case -3: day = 4;break;
		case -4: day = 3;break;
		case -5: day = 2;break;
		case -6: day = 1;break;
		default: day = now.getDay()-subDay;
	}
	if (now.getDate()-subDay < 0) {
		var month = now.getMonth();
		if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 0) {
			var end_of_month = 31;
		} else if (month == 4 || month == 6 || month == 9 || month == 11) {
			end_of_month = 30;
		} else {
			if (now.getFullYear()%4 == 0) {
				end_of_month = 29;
			} else {
				end_of_month = 28;
			}

		}
		switch (now.getDate()-subDay) {
			case -1: var date = end_of_month;break;
			case -2: date = end_of_month-1;break;
			case -3: date = end_of_month-2;break;
			case -4: date = end_of_month-3;break;
			case -5: date = end_of_month-4;break;
			case -6: date = end_of_month-5;break;
			default: date = now.getDate()-subDay;
		}
	} else {
		date = now.getDate()-subDay;
	}
	day = days[day];
	return day+' '+date+extension;
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
	return moment.utc(dt).add(7,'hours').format('HH');
}

function startTime() {
	var today = new Date();
	var h = addZero(today.getHours());
	var m = addZero(today.getMinutes());
	var day = checkDay(today.getDay());
	var date = addZero(today.getDate());
	var month = checkMonth(today.getMonth());
	var year = today.getFullYear();
	document.getElementById('date').innerHTML = h+":"+m+" "+day+" "+date+" "+month+" "+year;
	var t = setTimeout(startTime, 60000);
}
function addZero(i) {
	if (i < 10) {i = "0" + i}  // add zero in front of numbers < 10
	return i;
}
function checkDay(day) {
	switch (day) {
		case 0 : day = "Sun";
			break;
		case 1 : day = "Mon";
			break;
		case 2 : day = "Tue";
			break;
		case 3 : day = "Wed";
			break;
		case 4 : day = "Thu";
			break;
		case 5 : day = "Fri";
			break;
		case 6 : day = "Sat";
			break;
	}
	return day;
}
function checkMonth(month) {
	switch (month) {
		case 0 : month = "Jan";
			break;
		case 1 : month = "Feb";
			break;
		case 2 : month = "Mar";
			break;
		case 3 : month = "Apr";
			break;
		case 4 : month = "May";
			break;
		case 5 : month = "Jun";
			break;
		case 6 : month = "Jul";
			break;
		case 7 : month = "Aug";
			break;
		case 8 : month = "Sep";
			break;
		case 9 : month = "Oct";
			break;
		case 10 : month = "Nov";
			break;
		case 11 : month = "Dec";
			break;
	}
	return month;
}

