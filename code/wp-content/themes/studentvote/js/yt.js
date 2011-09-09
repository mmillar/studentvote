
function updateytplayerInfo() {
  var seconds = ytplayer.getCurrentTime();
  var length = ytplayer.getDuration();
  var percent = 100.0 * seconds / length;
  $('#videotime').text(Math.round(seconds));
  $('#videolength').text(Math.round(length));
  $('#videopercent').text(Math.round(percent));
  
  $('#volume').text(ytplayer.getVolume());
}

function onYouTubePlayerReady(playerId) {
  var volumeLevels = new Array(0,10,20,30,40,50,60,70,80,90,100);
  ytplayer = document.getElementById("myytplayer");
  setInterval(updateytplayerInfo, 250);

  $('#play').click(function() {
    ytplayer.playVideo();
	$('#play').hide();
	$('#pause').show()
	$('#pause').focus();
  });
  $('#pause').click(function() {
    ytplayer.pauseVideo();
	$('#pause').hide();
	$('#play').show();
	$('#play').focus();
  });
  $('#fwd').click(function() {
	ytplayer.seekTo(ytplayer.getCurrentTime() + 15);	
  });
  $('#rewind').click(function() {
	time = ytplayer.getCurrentTime();
	time -= 15;
	if (time < 0) {
		time = 0;
	}
	ytplayer.seekTo(time);
  });
  $('#mute').click(function() {
	ytplayer.mute();
	$('#mute').hide();
	$('#unmute').show();
	$('#unmute').focus();
  });
  $('#unmute').click(function() {
	ytplayer.unMute();
	$('#unmute').hide();
	$('#mute').show();
	$('#mute').focus();
  });
  $('#softer').click(function() {
    vol = ytplayer.getVolume();

	for (var i = volumeLevels.length - 1; i >= 0; i--){
		if (vol > volumeLevels[i]) {
			vol = volumeLevels[i];
			break;
		}
	};

    ytplayer.setVolume(vol);
  });
  $('#louder').click(function() {
    vol = ytplayer.getVolume();
	for (var i=0; i < volumeLevels.length; i++) {
		if (vol < volumeLevels[i]) {
			vol = volumeLevels[i];
			break;
		}
	};
    ytplayer.setVolume(vol);
	
  });
  $('#reset').click(function() {
	ytplayer.pauseVideo();
	ytplayer.seekTo(0);
	$('#pause').hide();
	$('#play').show();
  });

  ytplayer.cueVideoById('jSjaxbRYRV0'); /*CHANGE THIS*/
  ytplayer.setVolume(100);
  resetControls();
  ytplayer.pauseVideo();
  $('#pause').hide();
  $('#play').show();
}


function load(id) {
    ytplayer.loadVideoById(id, 0);
	resetControls();
}

function resetControls() {
	$('#play').hide();
	$('#pause').show();
	$('#unmute').hide();
	$('#mute').show();
}
