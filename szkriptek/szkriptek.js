window.onload = init;

var context;
var bufferLoader;
var audioBuffer;
var vca;
var masterGainVol;
var masterGain;
var audioRate;
var audioPan;
var vco;
var vcoamp;
var frequency;
var socket;
var sampleDrive = 4.0;
var scriptNode;
	
// ez egy aranyszam, ami a plusz infok kiiratasat egyre keslelteti, ahogy mar tobbet hasznalod a rendszert;
var hasznaltsag = 8;

// initialize audio context:

function init() {
	try{
		window.AudioContext = window.AudioContext||window.webkitAudioContext;
		context = new AudioContext();
		masterGain = context.createGain();
		masterGainVol = 0.3;
		masterGain.gain.value = masterGainVol;
		masterGain.connect(context.destination);
	}
		catch(e)
		{ alert("Webaudio is not working on your browser... sorry! Try it with a fresh Firefox or Chrome:)"); };

		
	};

// loads and plays back an audio file
// options: file URL, playback rate (1=normal), velocity,
// pan (-1.0 = left, 0: center, 1.0 / right)

function loadplay(file, rate, amp, pan){

var audioURL	=	file;
audioRate		= 	rate;
var audioAmp	=	amp*sampleDrive;
audioPan		=	pan;

var request = new XMLHttpRequest();
request.open("GET",audioURL,true);
request.responseType='arraybuffer';
request.onload=function(){
	context.decodeAudioData(request.response, function(buffer){
	audioBuffer = buffer;
	var source = context.createBufferSource();
	source.buffer=audioBuffer;
	vca = context.createGain();

//gain

	vca.gain.value =audioAmp;

// pan

	var pan = context.createPanner();
	pan.panningModel = 'equalpower';
	pan.setPosition(audioPan,0,1); //bal: -1., kozep: 0, jobb: 1.

	source.connect(vca);

	vca.connect(pan);
	pan.connect(masterGain);

//	masterGain.connect(context.destination);

//rate

	source.playbackRate.value = audioRate;
	source.loop=false;
	source.start(0);
		});

		};
request.send();
};

function loopplay(file, rate, amp, pan, timeout, gombid,instancia){

var audioURL	=	file;
audioRate		= 	rate;
var audioAmp	=	amp*sampleDrive;
audioPan		=	pan;
var timeOut		= 	parseInt(timeout);
var source;
var gombID		= 	gombid;

// console.log(gombID);

var request = new XMLHttpRequest();
request.open("GET",audioURL,true);
request.responseType='arraybuffer';
request.onload=function(){
	context.decodeAudioData(request.response, function(buffer){
	audioBuffer = buffer;
	source = context.createBufferSource();
	source.buffer=audioBuffer;
	vca = context.createGain();

//gain

	vca.gain.value =audioAmp;

// pan

	var pan = context.createPanner();
	pan.panningModel = 'equalpower';
	pan.setPosition(audioPan,0,1); //bal: -1., kozep: 0, jobb: 1.

	source.connect(vca);

	vca.connect(pan);
	pan.connect(masterGain);

//	masterGain.connect(context.destination);

//rate

	source.playbackRate.value = audioRate;
	source.loop=true;
	source.start(0);
	setInterval(function() {
		if(typeof processzek[instancia] != 'undefined') {
			var vajonAlszike = processzek[instancia].sleep;
			if(vajonAlszike==true){
				vca.gain.value = 0;}
				else{
				vca.gain.value =audioAmp;};
			}
	},200);
	document.getElementById(gombID).addEventListener('click', function() {
		if(document.getElementById(gombID).innerHTML = "*") {
			pan.disconnect();
			document.getElementById(gombID).innerHTML = "";
			var melyikProcessz = gombID.substr(4);
//			console.log(melyikProcessz);
			delete processzek[melyikProcessz];
			processzekFiltered = processzek.filter(function (el) {
				return el != null;
				});
//			console.log(processzekFiltered.length);
			}
	});
	if(timeOut > 0) {
		source.stop(context.currentTime + timeOut);
		};
		});

		};
 
request.send();
};

function fadeplay(file, rate, amp, pan, timeout, gombid){

var audioURL	=	file;
audioRate		= 	rate;
var audioAmp	=	amp*sampleDrive;
audioPan		=	pan;
var timeOut		= 	parseInt(timeout);
var source;
var gombID		= 	gombid;

// console.log(gombID);

var request = new XMLHttpRequest();
request.open("GET",audioURL,true);
request.responseType='arraybuffer';
request.onload=function(){
	context.decodeAudioData(request.response, function(buffer){
	audioBuffer = buffer;
	source = context.createBufferSource();
	source.buffer=audioBuffer;
	vca = context.createGain();

//gain

//	vca.gain.value =audioAmp;
	vca.gain.setValueAtTime(audioAmp, context.currentTime);
	vca.gain.linearRampToValueAtTime(0, context.currentTime + timeOut);

// pan

	var pan = context.createPanner();
	pan.panningModel = 'equalpower';
	pan.setPosition(audioPan,0,1); //bal: -1., kozep: 0, jobb: 1.

	source.connect(vca);

	vca.connect(pan);
	pan.connect(masterGain);

//	masterGain.connect(context.destination);

//rate

	source.playbackRate.value = audioRate;
	source.loop=true;
	source.start(0);
	document.getElementById(gombID).addEventListener('click', function() {
		if(document.getElementById(gombID).innerHTML = "stop") {
			pan.disconnect();
			document.getElementById(gombID).innerHTML = "";
			}
	});
	if(timeOut > 0) {
		source.stop(context.currentTime + timeOut);
		};
		});

		};
 
request.send();
};


// synthesize with Webaudio API

function szintiBe(key, amp) {

	frequency = 200 * Math.pow(2, key/12);
	vco = context.createOscillator();
	vco.type = vco.SINE;
	vco.frequency.value = frequency;
	vco.start(0);

	vcoamp = amp;
	vca = context.createGain();
	vca.gain.value = vcoamp;

	vco.connect(vca);
	vca.connect(context.destination);

}

function wall(message) {

	socket.send(message);
}

function szinti(freq, length, velo, attack) {

	var most = context.currentTime;
	var osc = context.createOscillator();
	osc.type = osc.SINE;
	osc.frequency.value = freq;
	osc.start(0);		// mikor kezdje el a hangot? mp-ben kell megadni

	osc.stop(most + length); // mikor hagyja abba?

	var amp = context.createGain();
	amp.gain.setValueAtTime(0.0, most);
	amp.gain.linearRampToValueAtTime(velo, most+attack);
	amp.gain.linearRampToValueAtTime(0.0, most + length);// fade out


	osc.connect(amp);
	amp.connect(masterGain);
//	masterGain.connect(context.destination);
// trying to work around overload problems:
	setTimeout(function() {
		amp.disconnect();
	}, length*1100);
}
 
function fmszinti(freq, length, velo, lfoFreq, lfoGain) {
			var most = context.currentTime;
			var vco = context.createOscillator();
			vco.type = 'sine';
			vco.frequency.value = freq;
			vco.start(0);		// mikor kezdje el a hangot? mp-ben kell megadni

			vco.stop(most + length); // mikor hagyja abba?

			//	modulátor
			var lfo = context.createOscillator();
			lfo.type = 'sine';
//			var lfoFreq = Math.random()*posY1+100;
			lfo.frequency.value = lfoFreq;
			lfo.start(0);		// mikor kezdje el a hangot? mp-ben kell megadni
			var lfa = context.createGain();
			lfa.gain.value = lfoGain;

			var panPos = (Math.random()*2) - 1;
			var pan = context.createPanner();
			pan.panningModel = 'equalpower';
			pan.setPosition(panPos,0,0); //left: -1., center: 0, right: 1.

			vca = context.createGain();
			vca.gain.setValueAtTime(0.0, most);
			vca.gain.linearRampToValueAtTime(velo, most + length/8);// fade out
			vca.gain.linearRampToValueAtTime(0.0, most + length);// fade out

			lfo.connect(lfa);
			lfa.connect(vco.frequency);
			vco.connect(vca);
			vca.connect(pan);
			pan.connect(masterGain);
//			masterGain.connect(context.destination);
			// trying to work around overload problems:
			setTimeout(function() {
				pan.disconnect();
			}, length*1100);

	}

function mathSzinti(freq1, wave1, velo1, lfoFreq1, lfoGain1, modwave1, freq2, wave2, velo2, lfoFreq2, lfoGain2, modwave2, length, expression, buffersize) {

	var most = context.currentTime;

// oscil 1:
	var vco1 = context.createOscillator();
	vco1.type = wave1;
	vco1.frequency.value = freq1;
	vco1.start(0);		// mikor kezdje el a hangot? mp-ben kell megadni
	vco1.stop(most + length); // mikor hagyja abba?
	//	modulátor
	var lfo1 = context.createOscillator();
	lfo1.type = modwave1;
	lfo1.frequency.value = lfoFreq1;
	lfo1.start(0);		// mikor kezdje el a hangot? mp-ben kell megadni
	var lfa1 = context.createGain();
	lfa1.gain.value = lfoGain1;

	vca1 = context.createGain();
	vca1.gain.setValueAtTime(0.0, most);
	vca1.gain.linearRampToValueAtTime(velo1, most + length/8);// fade out
	vca1.gain.linearRampToValueAtTime(0.0, most + length);// fade out

	lfo1.connect(lfa1);
	lfa1.connect(vco1.frequency);
	vco1.connect(vca1);


// scriptproc

	var mathjel;
    var bufferMeret = buffersize;  
	var szkript = context.createScriptProcessor(bufferMeret, 1, 1);
	szkript.onaudioprocess = function(audioProcessingEvent) {
        var inputBuffer = audioProcessingEvent.inputBuffer;
        var outputBuffer = audioProcessingEvent.outputBuffer;
        for (var channel = 0; channel < outputBuffer.numberOfChannels; channel++) {
              var inputData = inputBuffer.getChannelData(channel);
              var outputData = outputBuffer.getChannelData(channel);
//              freki++;

              for (var sample = 0; sample < inputBuffer.length; sample++) {


// phasor w/freki
				mathjel = (sample%freq2)/(bufferMeret/freq2)*velo2;
				
if(expression == '%'){				
				outputData[sample] = inputData[sample] % mathjel;
				}
else if(expression == '/'){
				outputData[sample] = inputData[sample] / mathjel;
				}
else if(expression == '!%'){
				outputData[sample] = mathjel % inputData[sample];
				}
else if(expression == '!/'){
				outputData[sample] = mathjel / inputData[sample];
				}
else if(expression == '+'){
				outputData[sample] = inputData[sample] * mathjel;
				}

else {
				outputData[sample] = inputData[sample] % mathjel;

}


// phasor w/freki
//				outputData[sample] = (sample%freki)/(500-freki);

// phasor
//				outputData[sample] = (sample/512)-0.2;
				
/*

if(eff == 1) {
//	tremolo
outputData[sample] = inputData[sample]*(sample/2000);
}
else if (eff == 2){ 
// backwards-granular
outputData[sample] += inputData[bufferMeret - sample];

}
else if (eff == 3){
// BLACK DEATH
outputData[sample] += inputData[bufferMeret - sample]/inputData[(sample*6)%bufferMeret];
}

else if (eff == 4){
// noisy sunday
outputData[sample] += inputData[bufferMeret - sample]%inputData[(sample*3)%bufferMeret];
}
else if (eff == 5){
// GRAY DEATH
outputData[sample] += inputData[bufferMeret - sample]/inputData[(sample*1.2)%bufferMeret];
}
else if (eff == 6){
// degrade?
outputData[sample] = inputData[sample] +  inputData[sample-param2]*param1;
}

else {
// bitshift
outputData[sample] += inputData[sample]/inputData[(sample*param1)%bufferMeret];
}
*/

              }

              
            }
      }



vca1.connect(szkript);
szkript.connect(masterGain);
//masterGain.connect(szkript);

			setTimeout(function() {
				szkript.disconnect();
			}, length*1100);




/*

// pan
	var panPos = (Math.random()*2) - 1;
	var pan = context.createPanner();
	pan.panningModel = 'equalpower';
	pan.setPosition(panPos,0,0); //left: -1., center: 0, right: 1.

	vca1.connect(pan);
	vca2.connect(pan);
	pan.connect(masterGain);
//	masterGain.connect(context.destination);
// 	trying to work around overload problems:
	setTimeout(function() {
		pan.disconnect();
	}, length*1100);

*/
}


function tone(freq, length, velo, waveform, filterType, cutoffRatio, lfoFreq, lfoGain) {
			var most = context.currentTime;
			var vco = context.createOscillator();
			vco.type = waveform;
			vco.frequency.value = freq;
			vco.start(0);		// mikor kezdje el a hangot? mp-ben kell megadni

			vco.stop(most + length); // mikor hagyja abba?

			//	modulátor
			var lfo = context.createOscillator();
			lfo.type = 'sine';
//			var lfoFreq = Math.random()*posY1+100;
			lfo.frequency.value = lfoFreq;
			lfo.start(0);		// mikor kezdje el a hangot? mp-ben kell megadni
			var lfa = context.createGain();
			lfa.gain.value = lfoGain;

			var filter = context.createBiquadFilter();
			filter.type = filterType;
			filter.frequency.setValueAtTime(freq, most);
			filter.frequency.linearRampToValueAtTime(freq*cutoffRatio, most+length);
			filter.gain.setValueAtTime(125, context.currentTime);

			var panPos = (Math.random()*2) - 1;
			var pan = context.createPanner();
			pan.panningModel = 'equalpower';
			pan.setPosition(panPos,0,0); //left: -1., center: 0, right: 1.

			vca = context.createGain();
			vca.gain.setValueAtTime(0.0, most);
			vca.gain.linearRampToValueAtTime(velo, most + length/8);// fade out
			vca.gain.linearRampToValueAtTime(0.0, most + length);// fade out

			lfo.connect(lfa);
			lfa.connect(vco.frequency);
			vco.connect(vca);
			vca.connect(filter);
			filter.connect(pan);
			pan.connect(masterGain);
/*
			document.getElementById('turnoff').addEventListener('click', function(){
				pan.disconnect();
				});
*/
	}


function szintiKi() {
	vca.gain.value = 0;
	}


// let change volume on master:

function hangeroMod(vol) {
	masterGain.gain.value = vol;
	document.getElementById("gain").innerHTML = vol.toFixed(3);
	}

// or simple raise it:

function hangeroFel() {
	masterGainVol = masterGainVol * 1.2;
	hangeroMod(masterGainVol)
}

// or lower it:

function hangeroLe() {
	masterGainVol = masterGainVol * 0.8;
	hangeroMod(masterGainVol)
}


function szaggato(buffersize, param1, eff, param2) {

    var bufferMeret = buffersize;  
	scriptNode = context.createScriptProcessor(bufferMeret, 1, 1);
	scriptNode.onaudioprocess = function(audioProcessingEvent) {
        var inputBuffer = audioProcessingEvent.inputBuffer;
        var outputBuffer = audioProcessingEvent.outputBuffer;
        for (var channel = 0; channel < outputBuffer.numberOfChannels; channel++) {
              var inputData = inputBuffer.getChannelData(channel);
              var outputData = outputBuffer.getChannelData(channel);

              for (var sample = 0; sample < inputBuffer.length; sample=sample+2) {
//                for (var sample = inputBuffer.length; sample > 0; sample--) {
              // make output equal to the same as the input
// HPF
//                outputData[sample] = inputData[sample]-inputData[sample-1];
// convolucio
//			outputData[sample] += inputData[Math.floor(Math.random()*inputBuffer.length)];


if(eff == 1) {
//	tremolo
outputData[sample] = inputData[sample]*(sample/2000);
}
else if (eff == 2){ 
// backwards-granular
outputData[sample] += inputData[bufferMeret - sample];

}
else if (eff == 3){
// BLACK DEATH
outputData[sample] += inputData[bufferMeret - sample]/inputData[(sample*6)%bufferMeret];
}

else if (eff == 4){
// noisy sunday
outputData[sample] += inputData[bufferMeret - sample]%inputData[(sample*3)%bufferMeret];
}
else if (eff == 5){
// GRAY DEATH
outputData[sample] += inputData[bufferMeret - sample]/inputData[(sample*1.2)%bufferMeret];
}
else if (eff == 6){
// degrade?
outputData[sample] = inputData[sample] +  inputData[sample-param2]*param1;
}

else {
// bitshift
outputData[sample] += inputData[sample]/inputData[(sample*param1)%bufferMeret];
}



//                outputData[sample] += inputData[Math.floor(Math.random()*inputBuffer.length)];

                // add noise to each output sample
//                outputData[sample] += ((Math.random() * 2) - 1) * 0.2;         
              }
            }
      }

//      getData();

      // wire up play button

masterGain.disconnect();
masterGain.connect(scriptNode);
scriptNode.connect(context.destination);

}