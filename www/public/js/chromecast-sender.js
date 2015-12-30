$( document ).ready(function(){
    var loadCastInterval = setInterval(function(){
        if (chrome.cast.isAvailable) {
            console.log('Cast has loaded.');
            clearInterval(loadCastInterval);
            initializeCastApi();
        } else {
            console.log('Unavailable');
        }
    }, 1000);
});

function initializeCastApi() {
    var applicationID = '7618D4A3';
    var sessionRequest = new chrome.cast.SessionRequest(applicationID);
    var apiConfig = new chrome.cast.ApiConfig(sessionRequest,
        sessionListener,
        receiverListener);
    chrome.cast.initialize(apiConfig, onInitSuccess, onInitError);
}


function sessionListener(e) {
    session = e;
    console.log('New session');
    if (session.media.length != 0) {
        console.log('Found ' + session.media.length + ' sessions.');
    }
}

function receiverListener(e) {
    if( e === 'available' ) {
        console.log("Chromecast was found on the network.");
    }
    else {
        console.log("There are no Chromecasts available.");
    }
}

function onInitSuccess() {
    console.log("Initialization succeeded");
}

function onInitError() {
    console.log("Initialization failed");
}

$('#castme').click(function(){
    $('#error').hide();
    if (document.getElementById('castme').innerHTML == 'Cast Start') {
        launchApp();
    } else {
        stopApp();
    }

});

function launchApp() {
    $('#cast').attr('src', $('#castme').data('loaded'));
    console.log("Launching the Chromecast App...");
    chrome.cast.requestSession(onRequestSessionSuccess, onLaunchError);
}

function onLaunchError() {
    $('#cast').attr('src', $('#castme').data('cast'));
    $('#error').show();
    document.getElementById('error').innerHTML = 'Error connecting to the Chromecast.';
    console.log("Error connecting to the Chromecast.");
}


function onRequestSessionSuccess(e) {
    $('#cast').attr('src', $('#castme').data('connected'));
    $(this).removeClass('btn-primary');
    $(this).addClass('btn-danger');
    document.getElementById('castme').innerHTML = 'Cast Stop';
    console.log("Successfully created session: " + e.sessionId);
    session = e;
}

function stopApp() {
    $('#cast').attr('src', $('#castme').data('loaded'));
    session.stop(onStopAppSuccess, onStopAppError);
}

function onStopAppSuccess() {
    $('#cast').attr('src', $('#castme').data('cast'));
    $(this).removeClass('btn-danger');
    $(this).addClass('btn-primary');
    document.getElementById('castme').innerHTML = 'Cast Start';
    console.log('Successfully stopped app.');
}

function onStopAppError() {
    $('#cast').attr('src', $('#castme').data('connected'));
    $('#error').show();
    document.getElementById('error').innerHTML = 'Error stopping app.';
    console.log('Error stopping app.');
}