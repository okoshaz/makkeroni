<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8" /> 
    <title>Makkeróni</title>
    <meta name="Description" content="Live webaudio operating system."/>
    <script src="jcubic/js-2.0.0/jquery-1.7.1.min.js"></script>
    <script src="jcubic/js-2.0.0/jquery.mousewheel-min.js"></script>
    <script src="jcubic/js-2.0.0/jquery.terminal-2.0.0.js"></script> 
    <script src="szkriptek/socket.io-1.4.5.js"></script>
	<script src="szkriptek/szkriptek.js"></script>
	<script src="szkriptek/polyfill.js"></script>
	<script>keyboardeventKeyPolyfill.polyfill();</script>
        
    <meta name="description" content="Webaudio operating system embedded in a linux-like shell. Can be used for live performance and generating algorhythmic music. Developed for the Makker makerspace in Pécs." />
	<meta property="og:title" content="Makkeróni webaudio system" />
	<meta property="og:type" content="article" />
	<meta property="og:image" content="http://makker.hu/makkeroni/images/makkeroni4.jpg" />
	<meta property="og:url" content="http://makker.hu/makkeroni/" />
	<meta property="og:description" content="Webaudio performance system embedded in a linux-like shell. Can be used for live coding and generating algorhythmic music. Developed for the Makker makerspace in Pécs." />

<link rel="apple-touch-icon" sizes="57x57" href="../favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="../favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="../favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="../favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="../favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="../favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="../favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="../favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="../favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="../favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
<link rel="manifest" href="../favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="../favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">

    
    <link href="jcubic/css-2.0.0/jquery.terminal-2.0.0.css" rel="stylesheet"/>
    <script>

// Welcome!
// this is the everything-on-the table version of the sources of Makkeróni.
// the scripts in use for audio processing are included above - szkriptek/szkriptek.js
// the processing of the commands and their arguments are below
// enjoy browsing or modding it!
// Balázs Kovács, 2019.


	var user = Math.floor(Math.random()*1000); // used for network communication
	var instancia = 0; // actual process number
	var hasznaltsag = 8;
	var ruleIndex = 11; // fontsize-nak kellhet
	var processzek = new Array; // process list
	var processzekFiltered = new Array; // i dont't know
	var szekvenciak = new Array; // sequence list
	var melyikSeq = 0; // actual sequence slot to store
	var statusLine = 7; // status lines for ps
	var socket; 
	var connected = false; // boolean for connection to the chat server 
	
    jQuery(document).ready(function($) {
     

$('body').terminal(function(command, term) {


// és akkor ide jönnek azok a szkriptek, amiknek kell a term:

	var parancs = $.terminal.parse_command(command);
	var opciok = $.terminal.parse_options(parancs.args,command);
	// filters nbsps from auto-completed names
	var parancsName = parancs.name.replace(/&nbsp;/g, '');
	var fullCommand = parancs;
	
    function doOnClick(mit) {
        $('#'+mit).click();
//		term.echo(mit);
    }

	function save(mit,hova) {
	
		$.get("save.php", { file: hova, content:mit });

	}


///////////////////////////////////
// general functions
///////////////////////////////////


// splits multiple commands with &&
// added space too (i don't know if it's o.k. or not...)
// added some tweaks because parancs variable confused the interpreter
// later (it tried to run both seperatedly and alltogether, generating
// interesting error messages)

	if(command.includes('&&')){
		parancs.args = [];
		parancs.name= 'reallynothing';
		parancsName= 'reallynothing';
		
		var parancsok = command.split('&&');
		var mennyiParancs = parancsok.length-1;
		for (var f=0;f<=mennyiParancs;f++){
				term.exec(parancsok[f], true);
			}
		}



// checking asterisk expressions v1.
// * is for random between 0-1
// *400+700 like expressions are multiplied and shifted

function csillagvizsgalo(param) {

	var ertek;
	var ertekek;
	var masikertek;

	if(param.startsWith('*')){		
		ertek = param.substring(1,param.length);
		ertekek = ertek.split('+');
		if(ertekek[1]){masikertek = parseFloat(ertekek[1])}else{masikertek=0};
		ertek = Math.random()*parseFloat(ertek)+masikertek; 
	}
	else {
		ertek = 100;
	} 
		return ertek;
}

// csillagvizsgalo2(parameterek[1],3,4);
// asterisk parser v2. with customizable defaults

function csillagvizsgalo2(param,mul,offset) {

	var ertek;
	var ertekek;
	var masikertek;

	if(param=='*'){		
		ertek = (Math.random()*mul)+offset; 
	}
	else if(param.length>1 && param.startsWith('*')){		
		ertek = param.substring(1,param.length);
		ertekek = ertek.split('+');
		if(ertekek[1]){masikertek = parseFloat(ertekek[1])}else{masikertek=0};
		ertek = Math.random()*parseFloat(ertek)+masikertek; 
	}
	else {
		ertek = 100;
	} 
		return ertek;
}


// selecting waveforms randomly. for makkeróni and tone commands

function waveformSelect() {

		var waveforms = ['sine','sawtooth','square','triangle'];
		var melyikWaveform = waveforms[Math.floor(Math.random()*waveforms.length)];
		return (melyikWaveform);
}

// selecting operator randomly. for makkeróni command

function expressionSelect() {

		var expressions = ['%','/','!/','!%','+'];
		var melyikExpression = expressions[Math.floor(Math.random()*expressions.length)];
		return (melyikExpression);
}

function expressionSelectLight() {

		var expressions = ['%','!%','+'];
		var melyikExpression = expressions[Math.floor(Math.random()*expressions.length)];
		return (melyikExpression);
}


///////////////////////////////////
// audio command parsing functions
///////////////////////////////////


// some audio commands like play, freq, fmfreq etc are organized into 
// meta-commands, because of their multiple purposes: they behave differently
// when called individually or called by an automated process.
// the process number is forwarded to these functions everytime with
// the thisInstancia variable.

	function playCommand(parameterek,thisInstancia) {

		// loads and plays back an audio file
		// options: file URL, playback rate (1=normal), velocity,
		// pan (-1.0 = left, 0: center, 1.0 / right)
		var file;
		var rate;
		var amp;
		var pan;

		var pid = ' ';
		if(thisInstancia) {
			pid = '[[;#fff;]pid '+thisInstancia+':] ';
			}
			else{
			pid = '';
			}
			
		if (parameterek.length >= 4) {
			file= parameterek[0];
			if(file=='*') {			
				var fileok = ['surr.wav','szlotty1.wav','bong1.wav','karc1.wav'];
				var melyikfile = Math.floor(Math.random()*fileok.length);
				file= fileok[melyikfile];
				}
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			if(parameterek[3]=="*"){pan=Math.random()*2-1.0} else {
				pan= parameterek[3]};
			filenev = "home/soundfiles/" + file;
			}

		else if (parameterek.length == 3) {
			file= parameterek[0];
			if(file=='*') {			
				var fileok = ['surr.wav','szlotty1.wav','bong1.wav','karc1.wav'];
				var melyikfile = Math.floor(Math.random()*fileok.length);
				file= fileok[melyikfile];
				}
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;
			}

		else if (parameterek.length == 2) {
			file= parameterek[0];
			if(file=='*') {			
				var fileok = ['surr.wav','szlotty1.wav','bong1.wav','karc1.wav'];
				var melyikfile = Math.floor(Math.random()*fileok.length);
				file= fileok[melyikfile];
				}
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			amp = Math.random();
			amp = (Math.round(amp*100))/100;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;
			}

		else if (parameterek.length == 1) {
			file= parameterek[0];
			if(file=='*') {			
				var fileok = ['surr.wav','szlotty1.wav','bong1.wav','karc1.wav'];
				var melyikfile = Math.floor(Math.random()*fileok.length);
				file= fileok[melyikfile];
				}
			if(typeof file == "string") {
			rate = Math.random();
			rate = (Math.round(rate*100))/100+0.3;

			amp = Math.random();
			amp = (Math.round(amp*100))/100;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;
			}
			else
			{
			term.echo("Please give me a filename to play instead of a number! You can choose a file by asking the directory contents with 'ls'. I could recommend 'szlotty1.wav'.");
			}
			
			}

		else {
			
			var fileok = ['surr.wav','szlotty1.wav','bong1.wav','karc1.wav'];
			var melyikfile = Math.floor(Math.random()*fileok.length);

			file= fileok[melyikfile];

			rate = Math.random();
			rate = (Math.round(rate*100))/100+0.3;

			amp = Math.random();
			amp = (Math.round(amp*100))/100+0.4;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;

			var legyene = Math.floor(Math.random()*hasznaltsag);
			if(legyene==1 && thisInstancia == ''){
				
			term.echo("NB: play needs some parameters:  1)  file name (check your soundfile with ls commmand!), 2) playback rate (1=normal), 3) velocity, and 4) pan (-1.0 = left, 0: center, 1.0 / right)");
		}
	}

// which is better? scrolling events or fixed-to-top? -1, 0 or last_index()?
// result: this...
				
if (thisInstancia && processzek[thisInstancia]) {
	var csillag = document.getElementById('gomb' + thisInstancia);
	csillag.style.opacity = 0.6;
	if(processzek[thisInstancia].sleep == true) {amp = 0} else {
		term.update(thisInstancia%statusLine+1, pid + "file: " +file+", rate: "+rate.toFixed(2)+", velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2));
			setTimeout(function(){csillag.style.opacity = 1.0},100)
		}
} else
{		
			term.echo(pid + "file: " +file+", rate: "+rate.toFixed(2)+", velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2));
}

			loadplay(filenev,rate,amp,pan);
}


function freqCommand(parameterek,thisInstancia) {
		// loads and plays back an audio file
		// options: file URL, playback rate (1=normal), velocity,
		// pan (-1.0 = left, 0: center, 1.0 / right)
		// timeout (sec)
		
		// console.log(parameterek,thisInstancia);

		var frekvencia;
		var hanghossz;
		var hangero;
		var felfutas;
		
		var pid = ' ';
		if(thisInstancia) {
			pid = '[[;#fff;]pid '+thisInstancia+':] ';
			}
			else{
			pid = '';
			}

		if (parameterek.length >= 4) {
 
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};
		if(parameterek[1]=="*"){hanghossz=Math.random()*16} else {
				hanghossz= parameterek[1]};
		if(parameterek[2]=="*"){hangero=Math.random()*0.8+0.2} else {
				hangero= parameterek[2]};
		if(parameterek[3]=="*"){felfutas=Math.random()*(hanghossz/2)} else {
				felfutas= parameterek[3]};
		}
		else if (parameterek.length == 3) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};
		if(parameterek[1]=="*"){hanghossz=Math.random()*16} else {
			hanghossz= parameterek[1]};
		if(parameterek[2]=="*"){hangero=Math.random()*0.8+0.2} else {
			hangero= parameterek[2]};
			felfutas= Math.random()*(hanghossz / 2);
}

		else if (parameterek.length == 2) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};
		if(parameterek[1]=="*"){hanghossz=Math.random()*16} else {
			hanghossz= parameterek[1]};
			hangero=Math.random()*0.8+0.2;
			felfutas= Math.random()*(hanghossz / 2);
			}

		else if (parameterek.length == 1) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};
			hanghossz=Math.random()*16;
			hangero=Math.random()*0.8+0.2;
			felfutas= Math.random()*(hanghossz / 2);
			}

		else {
			frekvencia=Math.random()*1399+66;
			hanghossz=Math.random()*16;
			hangero=Math.random()*0.8+0.2;
			felfutas= Math.random()*(hanghossz / 2);
			var legyene = Math.floor(Math.random()*hasznaltsag);
			if(legyene==1 && thisInstancia==''){
				term.echo("freq needs some parameters:  1)  frequency in Hz, 2) length in secundum, 3) velocity (1=normal), and 4) attack time (try less than the length of the sound) / for example: freq 440 3.2 0.8 1.6");
				}
			}


if (thisInstancia && processzek[thisInstancia]) {
			var csillag = document.getElementById('gomb' + thisInstancia);
			csillag.style.opacity = 0.6;
			if(processzek[thisInstancia].sleep == true) {hangero = 0} else {
			term.update(thisInstancia%statusLine+1, pid+"freq: " +frekvencia.toFixed(1)+", length: "+hanghossz.toFixed(2)+", velocity: "+hangero.toFixed(2)+",  attack: "+felfutas.toFixed(2));
			setTimeout(function(){csillag.style.opacity = 1.0},100)
			}
} else
{		
//			console.log(pid+"freq: " +frekvencia.toFixed(1)+", length: "+hanghossz.toFixed(2)+", velocity: "+hangero.toFixed(2)+",  attack: "+felfutas.toFixed(2));

			term.echo(pid+"freq: " +frekvencia.toFixed(1)+", length: "+hanghossz.toFixed(2)+", velocity: "+hangero.toFixed(2)+",  attack: "+felfutas.toFixed(2));
}

		
szinti(frekvencia,hanghossz,hangero, felfutas); //args: freq, length, velo, attacktime 
		
	}

function fmfreqCommand(parameterek,thisInstancia) {

		// loads and plays back an audio file
		// options: file URL, playback rate (1=normal), velocity,
		// pan (-1.0 = left, 0: center, 1.0 / right)
		// timeout (sec)

		var frekvencia;
		var hanghossz;
		var hangero;
		var lfoFreq;
		var lfoGain;
		var felfutas;

		var pid = ' ';
		if(thisInstancia) {
			pid = '[[;#fff;]pid '+thisInstancia+':] ';
			}
			else{
			pid = '';
			}

//		var parameterek = parancs.args;
		if (parameterek.length >= 5) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};			if(parameterek[1]=="*"){hanghossz=Math.random()*16} else {
				hanghossz= parameterek[1]};
			if(parameterek[2]=="*"){hangero=Math.random()*0.8+0.2} else {
				hangero= parameterek[2]};
			if(parameterek[3]=="*"){lfoFreq=Math.random()*23} else {
				lfoFreq= parameterek[3]};
			if(parameterek[4]=="*"){lfoGain=Math.random()*frekvencia} else {
				lfoGain= parameterek[4]};
			}

		else if (parameterek.length == 4) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};
			if(parameterek[1]=="*"){hanghossz=Math.random()*16} else {
				hanghossz= parameterek[1]};
			if(parameterek[2]=="*"){hangero=Math.random()*0.8+0.2} else {
				hangero= parameterek[2]};
			if(parameterek[3]=="*"){lfoFreq=Math.random()*23} else {
				lfoFreq= parameterek[3]};
			lfoGain=Math.random()*frekvencia;
			}

		else if (parameterek.length == 3) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};			if(parameterek[1]=="*"){hanghossz=Math.random()*16} else {
				hanghossz= parameterek[1]};
			if(parameterek[2]=="*"){hangero=Math.random()*0.8+0.2} else {
				hangero= parameterek[2]};
			lfoFreq=Math.random()*23;
			lfoGain=Math.random()*frekvencia;
			}

		else if (parameterek.length == 2) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};			if(parameterek[1]=="*"){hanghossz=Math.random()*16} else {
				hanghossz= parameterek[1]};
			hangero=Math.random()*0.8+0.2;
			lfoFreq=Math.random()*23;
			lfoGain=Math.random()*frekvencia;
			}

		else if (parameterek.length == 1) {
		if(parameterek[0]=="*"){frekvencia=Math.random()*999+66} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){frekvencia = csillagvizsgalo(parameterek[0])}
			else {frekvencia= parameterek[0]};			hanghossz=Math.random()*16;
			hangero=Math.random()*0.8+0.2;
			lfoFreq=Math.random()*23;
			lfoGain=Math.random()*frekvencia;
			}
		else 
		{
			frekvencia=Math.random()*666+66;
			hanghossz=Math.random()*16;
			hangero=Math.random()*0.8+0.2;
			lfoFreq=Math.random()*23;
			lfoGain=Math.random()*frekvencia;
			}

if (thisInstancia && processzek[thisInstancia]) {
			var csillag = document.getElementById('gomb' + thisInstancia);
			csillag.style.opacity = 0.6;
			if(processzek[thisInstancia].sleep == true) {hangero = 0} else {
			term.update(thisInstancia%statusLine+1, pid+"carrier freq: " +frekvencia.toFixed(1)+", length: "+hanghossz.toFixed(2)+", velocity: "+hangero.toFixed(2)+", mod freq: "+lfoFreq.toFixed(2)+", mod depth: "+ lfoGain.toFixed(1));
			setTimeout(function(){csillag.style.opacity = 1.0},100)
			}
} 
else
{	
			term.echo(pid+"carrier freq: " +frekvencia.toFixed(1)+", length: "+hanghossz.toFixed(2)+", velocity: "+hangero.toFixed(2)+", mod freq: "+lfoFreq.toFixed(2)+", mod depth: "+ lfoGain.toFixed(1));
}

fmszinti(frekvencia,hanghossz,hangero, lfoFreq,lfoGain);
						
}

// // function tone(freq, length, velo, waveform, filterType, cutoffRatio, lfoFreq, lfoGain)

// it's much easier to implement a command like this than the other ways...

function tonecommand(parameterek,thisInstancia){

	var freq=440;
	var length=5;
	var velo=0.3;
	var waveform = 'sawtooth';
	var filterType = 'lowpass';
	var cutoffRatio = 1.5;
	var lfoFreq = 0.0;
	var lfoGain = 0.0;

	var pid = ' ';
	if(thisInstancia) {
		pid = '[[;#fff;]pid '+thisInstancia+':] ';
	}
	else{
		pid = '';
	}

	if(parameterek.length >= 6){
		if(parameterek[0]=="*"){freq=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq = csillagvizsgalo(parameterek[0])}
			else {freq= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[1]=="string" && parameterek[1].includes('*')){length = csillagvizsgalo(parameterek[1])}
			else {length= parseFloat(parameterek[1])};
		if(parameterek[2]=="*"){velo=Math.random()*0.8+0.2} else {
			velo= parameterek[2]};
		if(parameterek[3]=="*"){waveform = waveformSelect()} 
			else {waveform= parameterek[3]};
		filterType = parameterek[4];
		cutoffRatio = parameterek[5];
	}	

	else if(parameterek.length == 5){
		if(parameterek[0]=="*"){freq=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq = csillagvizsgalo(parameterek[0])}
			else {freq= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[1]=="string" && parameterek[1].includes('*')){length = csillagvizsgalo(parameterek[1])}
			else {length= parseFloat(parameterek[1])};
		if(parameterek[2]=="*"){velo=Math.random()*0.8+0.2} else {
			velo= parameterek[2]};
		if(parameterek[3]=="*"){waveform = waveformSelect()} 
			else {waveform= parameterek[3]};
		filterType = parameterek[4];
	}	

	else if(parameterek.length == 4){
		if(parameterek[0]=="*"){freq=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq = csillagvizsgalo(parameterek[0])}
			else {freq= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[1]=="string" && parameterek[1].includes('*')){length = csillagvizsgalo(parameterek[1])}
			else {length= parseFloat(parameterek[1])};
		if(parameterek[2]=="*"){velo=Math.random()*0.8+0.2} else {
			velo= parameterek[2]};
		if(parameterek[3]=="*"){waveform = waveformSelect()} 
			else {waveform= parameterek[3]};
	}	

	else if(parameterek.length == 3){
		if(parameterek[0]=="*"){freq=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq = csillagvizsgalo(parameterek[0])}
			else {freq= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[1]=="string" && parameterek[1].includes('*')){length = csillagvizsgalo(parameterek[1])}
			else {length= parseFloat(parameterek[1])};
		if(parameterek[2]=="*"){velo=Math.random()*0.8+0.2} else {
			velo= parameterek[2]};
	}		

	else if(parameterek.length == 2){
		if(parameterek[0]=="*"){freq=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq = csillagvizsgalo(parameterek[0])}
			else {freq= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[1]=="string" && parameterek[1].includes('*')){length = csillagvizsgalo(parameterek[1])}
			else {length= parseFloat(parameterek[1])};
		velo=Math.random()*0.8+0.2;
	}

	else if(parameterek.length == 1){
		if(parameterek[0]=="*"){freq=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq = csillagvizsgalo(parameterek[0])}
			else {freq= parseFloat(parameterek[0])};
		length= Math.random()*2+6;
		velo=Math.random()*0.8+0.2;
	}

	else{
		freq=Math.floor(Math.random()*999+66);
		length= Math.random()*3+9;
		velo=Math.random()*0.8+0.2;
	}

if (thisInstancia && processzek[thisInstancia]) {
			var csillag = document.getElementById('gomb' + thisInstancia);
			csillag.style.opacity = 0.6;
			if(processzek[thisInstancia].sleep == true) {velo = 0;console.log('sleep...')} 
				else {
			term.update(thisInstancia%statusLine+1, pid+"freq: " +freq.toFixed(2)+", length: "+ length.toFixed(2)+", velocity: "+velo.toFixed(2)+", waveform: "+waveform+", filter type: "+filterType+", cutoff ratio: "+ cutoffRatio);

			tone(freq, length, velo, waveform, filterType, cutoffRatio, lfoFreq, lfoGain);	

			setTimeout(function(){csillag.style.opacity = 1.0},100)
			}
} 
else
{	
			term.echo(pid+"freq: " +freq.toFixed(2)+", length: "+ length.toFixed(2)+", velocity: "+velo.toFixed(2)+", waveform: "+waveform+", filter type: "+filterType+", cutoff ratio: "+ cutoffRatio);
			tone(freq, length, velo, waveform, filterType, cutoffRatio, lfoFreq, lfoGain);	

}


// term.echo("freq: " +freq.toFixed(2)+", length: "+ length.toFixed(2)+", velocity: "+velo.toFixed(2)+", waveform: "+waveform+", filter type: "+filterType+", cutoff ratio: "+ cutoffRatio);

} 

function makkeronicommand(parameterek,thisInstancia) {

//		mathSzinti(freq1, wave1, velo1, lfofFreq1, lfoGain1, modwave1, freq2, wave2, velo2, lfofFreq2, lfoGain2, modwave2, length, expression, buffersize)

// ujj de hosszu:
// mathSynth 20 sine 0.8 0 0 sine 100 phasor 0.7 0 0 sine 0.8 / 512 
// az utolsó 3 oké, előtte 3-at nem használja a mathszinti

		

		var freq1;
		var wave1;
		var velo1;
		var lfoFreq1;
		var lfoGain1;
		var modwave1; 
		var freq2;
		var wave2 = 'sawtooth';
		var velo2;
		var lfoFreq2 = 0;
		var lfoGain2 = 0;
		var modwave2 = 'sine';
		var length;
		var expression;
		var buffersize;


	if(parameterek.length >= 11) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		if(parameterek[4]=="*"){lfoGain1=Math.floor(Math.random()*freq1)} 
			else if(typeof parameterek[4]=="string" && parameterek[4].includes('*')){lfoGain1 = csillagvizsgalo(parameterek[4])}
			else {lfoGain1= parameterek[4]};
		if(parameterek[5]=="*"){modwave1 = waveformSelect()} 
			else {modwave1= parameterek[5]};
//		freq2 = parseFloat(parameterek[6]);
		if(parameterek[6]=="*"){freq2=Math.floor(Math.random()*20+6)} 
			else if(typeof parameterek[6]=="string" && parameterek[6].includes('*')){freq2 = csillagvizsgalo(parameterek[6])}
			else {freq2= parseFloat(parameterek[6])};
		if(parameterek[7]=="*"){velo2=Math.random()*30+0.1} else {
			velo2= parseFloat(parameterek[7])};
		if(parameterek[8]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[8]=="string" && parameterek[8].includes('*')){length = csillagvizsgalo(parameterek[8])}
			else {length= parseFloat(parameterek[8])};
		if(parameterek[9]=="*"){expression = expressionSelect()} 
			else {expression= parameterek[9]};
		if(parameterek[10]=="*"){buffersize = 512}
			else {buffersize = parseInt(parameterek[10])};		
		}

	else if(parameterek.length == 10) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		if(parameterek[4]=="*"){lfoGain1=Math.floor(Math.random()*freq1)} 
			else if(typeof parameterek[4]=="string" && parameterek[4].includes('*')){lfoGain1 = csillagvizsgalo(parameterek[4])}
			else {lfoGain1= parameterek[4]};
		if(parameterek[5]=="*"){modwave1 = waveformSelect()} 
			else {modwave1= parameterek[5]};
//		freq2 = parseFloat(parameterek[6]);
		if(parameterek[6]=="*"){freq2=Math.floor(Math.random()*20+6)} 
			else if(typeof parameterek[6]=="string" && parameterek[6].includes('*')){freq2 = csillagvizsgalo(parameterek[6])}
			else {freq2= parseFloat(parameterek[6])};
		if(parameterek[7]=="*"){velo2=Math.random()*30+0.1} else {
			velo2= parseFloat(parameterek[7])};
		if(parameterek[8]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[8]=="string" && parameterek[8].includes('*')){length = csillagvizsgalo(parameterek[8])}
			else {length= parseFloat(parameterek[8])};
		if(parameterek[9]=="*"){expression = expressionSelect()} 
			else {expression= parameterek[9]};
		buffersize = 512;		
		}

	else if(parameterek.length == 9) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		if(parameterek[4]=="*"){lfoGain1=Math.floor(Math.random()*freq1)} 
			else if(typeof parameterek[4]=="string" && parameterek[4].includes('*')){lfoGain1 = csillagvizsgalo(parameterek[4])}
			else {lfoGain1= parameterek[4]};
		if(parameterek[5]=="*"){modwave1 = waveformSelect()} 
			else {modwave1= parameterek[5]};
//		freq2 = parseFloat(parameterek[6]);
		if(parameterek[6]=="*"){freq2=Math.floor(Math.random()*20+6)} 
			else if(typeof parameterek[6]=="string" && parameterek[6].includes('*')){freq2 = csillagvizsgalo(parameterek[6])}
			else {freq2= parseFloat(parameterek[6])};
		if(parameterek[7]=="*"){velo2=Math.random()*30+0.1} else {
			velo2= parseFloat(parameterek[7])};
		if(parameterek[8]=="*"){length=Math.random()*2+0.1} 
			else if(typeof parameterek[8]=="string" && parameterek[8].includes('*')){length = csillagvizsgalo(parameterek[8])}
			else {length= parseFloat(parameterek[8])};
		expression = expressionSelectLight();
		buffersize = 512;		
		}

	else if(parameterek.length == 8) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		if(parameterek[4]=="*"){lfoGain1=Math.floor(Math.random()*freq1)} 
			else if(typeof parameterek[4]=="string" && parameterek[4].includes('*')){lfoGain1 = csillagvizsgalo(parameterek[4])}
			else {lfoGain1= parameterek[4]};
		if(parameterek[5]=="*"){modwave1 = waveformSelect()} 
			else {modwave1= parameterek[5]};
//		freq2 = parseFloat(parameterek[6]);
		if(parameterek[6]=="*"){freq2=Math.floor(Math.random()*20+6)} 
			else if(typeof parameterek[6]=="string" && parameterek[6].includes('*')){freq2 = csillagvizsgalo(parameterek[6])}
			else {freq2= parseFloat(parameterek[6])};
		if(parameterek[7]=="*"){velo2=Math.random()*30+0.1} else {
			velo2= parseFloat(parameterek[7])};
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}

	else if(parameterek.length == 7) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		if(parameterek[4]=="*"){lfoGain1=Math.floor(Math.random()*freq1)} 
			else if(typeof parameterek[4]=="string" && parameterek[4].includes('*')){lfoGain1 = csillagvizsgalo(parameterek[4])}
			else {lfoGain1= parameterek[4]};
		if(parameterek[5]=="*"){modwave1 = waveformSelect()} 
			else {modwave1= parameterek[5]};
//		freq2 = parseFloat(parameterek[6]);
		if(parameterek[6]=="*"){freq2=Math.floor(Math.random()*20+6)} 
			else if(typeof parameterek[6]=="string" && parameterek[6].includes('*')){freq2 = csillagvizsgalo(parameterek[6])}
			else {freq2= parseFloat(parameterek[6])};
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}

	else if(parameterek.length == 6) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		if(parameterek[4]=="*"){lfoGain1=Math.floor(Math.random()*freq1)} 
			else if(typeof parameterek[4]=="string" && parameterek[4].includes('*')){lfoGain1 = csillagvizsgalo(parameterek[4])}
			else {lfoGain1= parameterek[4]};
		if(parameterek[5]=="*"){modwave1 = waveformSelect()} 
			else {modwave1= parameterek[5]};
		freq2=Math.floor(Math.random()*20+6);
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}

	else if(parameterek.length == 5) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		if(parameterek[4]=="*"){lfoGain1=Math.floor(Math.random()*freq1)} 
			else if(typeof parameterek[4]=="string" && parameterek[4].includes('*')){lfoGain1 = csillagvizsgalo(parameterek[4])}
			else {lfoGain1= parameterek[4]};
		modwave1 = waveformSelect();
		freq2=Math.floor(Math.random()*20+6);
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}

	else if(parameterek.length == 4) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		if(parameterek[3]=="*"){lfoFreq1=Math.floor(Math.random()*9999)} 
			else if(typeof parameterek[3]=="string" && parameterek[3].includes('*')){lfoFreq1 = csillagvizsgalo(parameterek[3])}
			else {lfoFreq1= parameterek[3]};
		lfoGain1=Math.floor(Math.random()*freq1);
		modwave1 = waveformSelect();
		freq2=Math.floor(Math.random()*20+6);
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}

	else if(parameterek.length == 3) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		if(parameterek[2]=="*"){velo1=Math.random()*0.8+0.2} else {
			velo1= parameterek[2]};
		lfoFreq1=Math.floor(Math.random()*9999);
		lfoGain1=Math.floor(Math.random()*freq1);
		modwave1 = waveformSelect();
		freq2=Math.floor(Math.random()*20+6);
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}

	else if(parameterek.length == 2) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		if(parameterek[1]=="*"){wave1 = waveformSelect()} 
			else {wave1= parameterek[1]};
		velo1=Math.random()*0.8+0.2;
		lfoFreq1=Math.floor(Math.random()*9999);
		lfoGain1=Math.floor(Math.random()*freq1);
		modwave1 = waveformSelect();
		freq2=Math.floor(Math.random()*20+6);
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}		

	else if(parameterek.length == 1) {
		if(parameterek[0]=="*"){freq1=Math.floor(Math.random()*999+66)} 
			else if(typeof parameterek[0]=="string" && parameterek[0].includes('*')){freq1 = csillagvizsgalo(parameterek[0])}
			else {freq1= parseFloat(parameterek[0])};
		wave1 = waveformSelect();
		velo1=Math.random()*0.8+0.2;
		lfoFreq1=Math.floor(Math.random()*9999);
		lfoGain1=Math.floor(Math.random()*freq1);
		modwave1 = waveformSelect();
		freq2=Math.floor(Math.random()*20+6);
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}	


	else {
		freq1=Math.floor(Math.random()*999+66);
		wave1 = waveformSelect();
		velo1=Math.random()*0.8+0.2;
		lfoFreq1=Math.floor(Math.random()*9999);
		lfoGain1=Math.floor(Math.random()*freq1);
		modwave1 = waveformSelect();
		freq2=Math.floor(Math.random()*20+6);
		velo2=Math.random()*30+0.1;
		length=Math.random()*2+0.1;
		expression = expressionSelectLight();
		buffersize = 512;		
		}			

var pid = 0;
 
term.echo("freq1: " +freq1+", wave1: "+wave1+", velo1: "+velo1.toFixed(2)+", mod freq: "+lfoFreq1.toFixed(1)+", mod depth: "+ lfoGain1.toFixed(1)+", mod wave: "+ modwave1 + ", freq2: "+freq2+", velo2: "+velo2.toFixed(2)+", length: "+ length.toFixed(2)+", expr: "+expression+", buffer: "+buffersize);

//		console.log(freq1, wave1, velo1, lfoFreq1, lfoGain1, modwave1, freq2, wave2, velo2, lfoFreq2, lfoGain2, modwave2, length, expression, buffersize);
	mathSzinti(freq1, wave1, velo1, lfoFreq1, lfoGain1, modwave1, freq2, wave2, velo2, lfoFreq2, lfoGain2, modwave2, length, expression, buffersize);

/* eredetileg ez vót itt:
		freq1 = parseFloat(parameterek[0]);
		wave1 = parameterek[1];
		velo1 = parseFloat(parameterek[2]);
		lfoFreq1 = parseFloat(parameterek[3]);
		lfoGain1 = parseFloat(parameterek[4]);
		modwave1 = parameterek[5]; 
		freq2 = parseFloat(parameterek[6]);
		wave2 = parameterek[7];
		velo2 = parseFloat(parameterek[8]);
		lfoFreq2 = parseFloat(parameterek[9]);
		lfoGain2 = parseFloat(parameterek[10]);
		modwave2 = parameterek[11];
		length = parseFloat(parameterek[12]);
		expression = parameterek[13];
		buffersize = parseInt(parameterek[14]);	
*/
}


///////////////////////////////////
// audio command parsing
///////////////////////////////////

// play

	if (parancs.name == 'play') {

		if(parancs.args[0] == '--help') {
			term.echo('Plays a soundfile');			
			term.echo('arguments: soundfile name (string) playback rate (float, 1=normal) velocity (float)');			
		}
		else {
		
		playCommand(parancs.args);
		}
}

// loopplay

	else if (parancsName == 'loopplay') {


			if(parancs.args[0] == '--help') {
			term.echo('Loop-plays a soundfile.');			
			term.echo('arguments: soundfile name (string) playback rate (float, 1=normal) velocity (float) pan (-1.0...1.0) timeout (seconds, float)');			
			term.echo('example: loopplay surr.wav 0.6 0.9 -0.4 4');			
		}
		else {
		
		// loads and plays back an audio file
		// options: file URL, playback rate (1=normal), velocity,
		// pan (-1.0 = left, 0: center, 1.0 / right)
		// timeout (sec)

		var file;
		var filenev;
		var rate;
		var amp;
		var pan;
		var timeout;
		var gombID;
		instancia++;
		gombID = "gomb"+instancia;
		term.echo("Thread no. " + instancia + " started. To stop it, type [[gb;#fff;]stop " + instancia +"]!");		

		var parameterek = parancs.args;
		if (parameterek.length >= 5) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			if(parameterek[3]=="*"){pan=Math.random()*2-1.0} else {
				pan= parameterek[3]};
			timeout = parameterek[4];
			filenev = "home/soundfiles/" + file;
//			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
//			loopplay(filenev,rate,amp,pan,timeout,gombID);
			}

		else if (parameterek.length == 4) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			timeout = 0;
//			term.echo("timeout wasn't set, i set it to infinity. you can turn it off by <span id='"+gombID+"'>clicking here</span>.",{raw:true});
			filenev = "home/soundfiles/" + file;
//			loopplay(filenev,rate,amp,pan,timeout,gombID);
			}


		else if (parameterek.length == 3) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			timeout = 0;
			filenev = "home/soundfiles/" + file;
//			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
//			loopplay(filenev,rate,amp,pan,timeout,gombID);
//			term.echo("panning: "+pan.toFixed(2));
			}

		else if (parameterek.length == 2) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			amp = Math.random();
			amp = (Math.round(amp*100))/100;
			timeout = 0;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			timeout = 0;
			filenev = "home/soundfiles/" + file;
//			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
//			loopplay(filenev,rate,amp,pan,timeout,gombID);
//			term.echo("velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2));
			}

		else if (parameterek.length == 1) {
			file= parameterek[0];
			rate = Math.random();
			rate = (Math.round(rate*100))/100+0.4;
			timeout = 0;

			amp = Math.random();
			amp = (Math.round(amp*100))/100;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;
//			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
//			loopplay(filenev,rate,amp,pan,timeout,gombID);

//			term.echo("rate: "+rate.toFixed(2)+", velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2));

			}

		else {
		
			var fileok = ['surr.wav','szlotty1.wav','bong1.wav','karc1.wav'];
			var melyikfile = Math.floor(Math.random()*fileok.length);

			file= fileok[melyikfile];

			rate = Math.random();
			rate = (Math.round(rate*100))/100+0.3;
			
			timeout = 0;

			amp = Math.random();
			amp = (Math.round(amp*100))/100;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;
//			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
			
//			term.echo("file: " +file+", rate: "+rate.toFixed(2)+", velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2));

			var legyene = Math.floor(Math.random()*hasznaltsag);
			if(legyene==1){
		
			term.echo("Nota bene: loopplay needs some parameters:  1)  file name (check the soundfiles on the server with the 'ls' commmand!), 2) playback rate (1=normal), 3) velocity, and 4) pan (-1.0 = left, 0: center, 1.0 / right), 5) timeout (sec)");
			}
		}


// add process data to the process list

		var d = new Date();
		processzek[instancia] = {
			pid: instancia,
			uid: user,
			time: d.getTime(),
			command: 'loopplay ' + file + ' ' + rate + ' ' + amp + ' ' + pan,
			sleep: false};


			var posX1 = Math.floor(Math.random()*90);
			var posY1 = Math.floor(Math.random()*90);

			var r=document.createElement('div'); 
			r.className=('objekt');
			r.setAttribute("id",gombID);
			// choose position
			r.style.left = posX1 + "%";
			r.style.bottom = posY1 + "%";	
			r.innerHTML = "*";
			document.body.appendChild(r);

			term.echo("file: " +file+", rate: "+rate.toFixed(2)+", velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2));
			loopplay(filenev,rate,amp,pan,timeout,gombID,instancia);
		}
	}

// fadeplay

	else if (parancsName == 'fadeplay') {

			if(parancs.args[0] == '--help') {
			term.echo('Loop-plays a soundfile with linear fade-out.');			
			term.echo('arguments: soundfile name (string) playback rate (float, 1=normal) velocity (float) pan (-1.0...1.0) timeout (seconds, float)');			
			term.echo('example: loopplay surr.wav 0.6 0.9 -0.4 4');			
		}
		else {

		// loads and plays back an audio file with linear fade out
		// options: file URL, playback rate (1=normal), velocity,
		// pan (-1.0 = left, 0: center, 1.0 / right)
		// timeout (sec)


		var file;
		var filenev;
		var rate;
		var amp;
		var pan;
		var timeout;
		var gombID;
		instancia++;
		gombID = "gomb"+instancia;
		term.echo("Thread no. " + instancia + " started. To stop it, type [[gb;#fff;]stop " + instancia +"]!");		

		var parameterek = parancs.args;
		if (parameterek.length >= 5) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			if(parameterek[3]=="*"){pan=Math.random()*2-1.0} else {
				pan= parameterek[3]};
			timeout = parameterek[4];
			filenev = "home/soundfiles/" + file;
			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
			fadeplay(filenev,rate,amp,pan,timeout,gombID);
			}

		else if (parameterek.length == 4) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			timeout = (Math.random() * 10) + 1;
			term.echo("timeout: "+timeout.toFixed(1) + ". You can turn it off by <span id='"+gombID+"'>clicking here</span>.",{raw:true});
			filenev = "home/soundfiles/" + file;
			fadeplay(filenev,rate,amp,pan,timeout,gombID);
			}


		else if (parameterek.length == 3) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			if(parameterek[2]=="*"){amp=Math.random()*0.8+0.2} else {
				amp= parameterek[2]};
			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			timeout = (Math.random() * 10) + 1;
			filenev = "home/soundfiles/" + file;
			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
			fadeplay(filenev,rate,amp,pan,timeout,gombID);
			term.echo("panning: "+pan.toFixed(2)+", timeout: "+timeout.toFixed(1));
			}

		else if (parameterek.length == 2) {
			file= parameterek[0];
			if(parameterek[1]=="*"){rate=Math.random()+0.5} else {
				rate= parameterek[1]};
			amp = Math.random();
			amp = (Math.round(amp*100))/100;
			timeout = 0;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			timeout = (Math.random() * 10) + 1;
			filenev = "home/soundfiles/" + file;
			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
			fadeplay(filenev,rate,amp,pan,timeout,gombID);
			term.echo("velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2)+", timeout: "+timeout.toFixed(1));
			}

		else if (parameterek.length == 1) {
			file= parameterek[0];
			rate = Math.random();
			rate = (Math.round(rate*100))/100+0.4;
			timeout = (Math.random() * 10) + 1;

			amp = Math.random();
			amp = (Math.round(amp*100))/100;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;
			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
			fadeplay(filenev,rate,amp,pan,timeout,gombID);

			term.echo("rate: "+rate.toFixed(2)+", velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2)+", timeout: "+timeout.toFixed(1));

			}

		else {
		
			var fileok = ['surr.wav','szlotty1.wav','bong1.wav','karc1.wav'];
			var melyikfile = Math.floor(Math.random()*fileok.length);

			file= fileok[melyikfile];

			rate = Math.random();
			rate = (Math.round(rate*100))/100+0.3;
			
			timeout = (Math.random() * 10) + 1;

			amp = Math.random();
			amp = (Math.round(amp*100))/100;

			pan=(Math.random() * 2) - 1;
			pan = (Math.round(pan*100))/100;
			filenev = "home/soundfiles/" + file;
			term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});
			fadeplay(filenev,rate,amp,pan,timeout,gombID);
			
			term.echo("file: " +file+", rate: "+rate.toFixed(2)+", velocity: "+amp.toFixed(2)+", panning: "+pan.toFixed(2)+", timeout: "+timeout.toFixed(1));

			var legyene = Math.floor(Math.random()*hasznaltsag);
			if(legyene==1){
		
			term.echo("Nota bene: fadeplay needs some parameters:  1)  file name (check the soundfiles on the server with the 'ls' commmand!), 2) playback rate (1=normal), 3) velocity, and 4) pan (-1.0 = left, 0: center, 1.0 / right), 5) timeout (sec)");
			}
		}

	 }
	}


// synth 1

	else if (parancsName == 'freq') {
		if(parancs.args[0] == '--help') {
			term.echo('Sine wave generator');
			term.echo('arguments: frequency (int) length (int) velocity (float) attack time (secundum, float)');
			term.echo('example 1: freq 666 3.4');
			term.echo('example 2: freq * 3.4 <- with random frequency');
			
		}
		else {
		freqCommand(parancs.args)
		}
	}

// fmsynth

	else if (parancsName == 'fmfreq') {
	
		if(parancs.args[0] == '--help') {
			term.echo('FM tone generator');
			term.echo('arguments: carrier frequency (int) length (int) velocity (float) modulation frequency (int) modulation depth (int)');
			term.echo('example 1: fmfreq <- random everything');
			term.echo('example 2: fmfreq *1000+777 <- generates frequencies between 777-1776 Hz');
			term.echo('example 3: fmfreq 555 3.4 0.8 * 5 <- with random mod. freq');
			
		}
		else {
		fmfreqCommand(parancs.args)
		}	
	}

// effect line experiment...
	else if (parancsName == 'bitshift') {

		if(parancs.args[0] == '--help') {
			term.echo('Digital bitshift effect. Extremely loud!');			
			term.echo('arguments: buffer size (int, 256, 512, 1024 and so on), float (ratio)');
		}
		else {

			var param1;
			var param2;
			
			if(parancs.args[1]){
				param1 = parancs.args[0];
				param2 = parancs.args[1];
				} 
			else if (parancs.args[0]){
				param1 = 256;
				param2 = parancs.args[0];
				}
			else {
				param1 = 256;
				param2 = 0.97;
				}

			term.echo('Welcome to Bitshiftónia! Params for buffersize: ' + param1 + ', bitshiftindex: ' +param2);
			szaggato(param1.toFixed(2), param2, 7);
		  }
		}

	else if (parancsName == 'blackdeath') {

		if(parancs.args[0] == '--help') {
			term.echo('Digital effect. Extremely loud!');			
			term.echo('arguments: buffer size (int, 256, 512, 1024 and so on), float (ratio)');
		}
		else {


			var param1;
			var param2;
			
			if(parancs.args[1]){
				param1 = parancs.args[0];
				param2 = parancs.args[1];
				} 
			else if (parancs.args[0]){
				param1 = 256;
				param2 = parancs.args[0];
				}
			else {
				param1 = 256;
				param2 = 0.8;
				}

			szaggato(param1, param2, 3);
			term.echo("Type 'deffect' to turn it off....");

		 }
		}

	else if (parancsName == 'degrade') {
	
		if(parancs.args[0] == '--help') {
			term.echo('Digital effect. Very loud.');			
			term.echo('arguments: buffer size (int, 256, 512, 1024 and so on), float (ratio)');
		}
		else {


			var param1;
			var param2;
			var param3;
			
			if(parancs.args[2]){
				param1 = parancs.args[0];
				param2 = parancs.args[1];
				param3 = parancs.args[2];
				} 
			else if(parancs.args[1]){
				param1 = parancs.args[0];
				param2 = parancs.args[1];
				param3 = Math.ceil(Math.random()*param1);
				} 
			else if (parancs.args[0]){
				param1 = 1024;
				param2 = parancs.args[0];
				param3 = Math.ceil(Math.random()*param1);
				}
			else {
				param1 = 1024;
				param2 = 0.8;
				param3 = Math.ceil(Math.random()*param1);
				}

			szaggato(param1, param2, 6, param3);
		}
		}


	else if (parancsName == 'effect') {

			var param1;
			var param2;
			var param3;
			var param4 = 0;
			
			if(parancs.args[2]){
				param1 = parancs.args[0];
				param2 = parancs.args[1];
				param3 = parancs.args[2];
				param4 = parancs.args[3];
				} 
			if(parancs.args[2]){
				param1 = parancs.args[0];
				param2 = parancs.args[1];
				param3 = parancs.args[2];
				} 
			else if(parancs.args[1]){
				param1 = parancs.args[0];
				param2 = parancs.args[1];
				param3 = Math.ceil(Math.random()*5);
				console.log(param3);
				} 
			else if (parancs.args[0]){
				param1 = 1024;
				param2 = parancs.args[0];
				param3 = Math.ceil(Math.random()*5);
				console.log(param3);
				}
			else {
				param1 = 1024;
				param2 = 1.001;
				param3 = Math.ceil(Math.random()*5);
				console.log(param3);
				}

			szaggato(param1, param2, param3, param4);

		}


// effect line experiment...
	else if (parancsName == 'debitshift' || parancsName == 'deffect') {
		if(parancs.args[0] == '--help') {
			term.echo('Remove effect from the chain. No argument needed.');			
		}
		else {

		masterGain.disconnect(scriptNode);
		scriptNode.disconnect(context.destination);
		masterGain.connect(context.destination);
		}
		}

	else if (parancsName == 'multi') {
		console.log(parancs.args[0]);
		console.log(typeof parancs.args[0]);
	}


	else if (parancsName == 'makkeróni') {
	
		if(parancs.args[0] == '--help') {
			term.echo('Complex wave generator with 1 osc, 1 LFO, 1 sawtooth osc and digital operators');
			term.echo('arguments: 1: carrier frequency (float), 2: waveform (string), 3: velocity (float), 4: lfo frequency (float), 5: lfo depth (float), 6: lfo waveform (string), 7: 2nd osc frequency (float), 8: 2nd osc velocity (float), 9: length (float), 10: operator (*,/,!/,%,!% or +), 11: buffersize (256,512,1024,2014 or *)');
			term.echo('\nexample 1: makkeróni 777 sine 0.3');
			term.echo('example 2: makkeróni 666 sawtooth 0.3 689 0.4 sine 15 0.1 0.8 % 512 ');

		
		}
		else
		{
		makkeronicommand(parancs.args);
		}
	
}

// function tone(freq, length, velo, waveform, filterType, cutoffRatio, lfoFreq, lfoGain)

	else if (parancsName == 'tone') {
	
		if(parancs.args[0] == '--help') {
			term.echo('Simple tone generator with filter');
			term.echo('Arguments: freq, length, velocity, waveform (square, triangle, sine or sawtooth), filter type (lowpass, bandpass, lowshelf), cutoff ratio (1 = equal to freq)');
			term.echo('\nexample 1: tone 490 10 0.5 sawtooth lowpass 1.4');
			term.echo('tone <- recommended for long tones...');
		
		}
		else
		{
		tonecommand(parancs.args);
		}
		
	}


///////////////////////////////////
// other commands
///////////////////////////////////


	else if (parancsName == 'teszt') {
		var param1 = parancs.args[0];
		term.echo(parancs.args.length);
		}

// pipe please...
	else if (command.includes("|")==true){
		console.log("pipe currently doesn't work")
		}

    else if (parancsName == 'test') {
        term.echo("you just typed 'test'");
    } 

// about
    else if (parancsName == 'about') {
        term.echo("Based on Jcubic (https://terminal.jcubic.pl/) & Webaudio.");
        term.echo("Name coin to the Makker makerspace and low-tech lab in Pécs. And also, 'makker' is the hungarian name for 'gibberish'...");
        term.echo("Updated: 21th mai 2018.");
    } 

// help

	else if (parancsName == 'help') {
		term.echo("Makkeróni is a linux shell-like live coding system. At least these commands does something:" +
		"\n- [[b;#fff;]freq] + frequency (int) length (int) velocity (float) attack time (secundum, float): a tone (pl. \"freq 666 3.4\")"+
		"\n- [[b;#fff;]fmfreq] + carrier frequency (int) length (int) velocity (float) modulation frequency (int) modulation depth (int): a frequency modulated tone (for ex. \"fmfreq 555 3.4 0.8 3 5\")"+
		"\n- [[b;#fff;]tone] + frequency (int) length (int) velocity (float) waveform (sawtooth, square etc), filter type (lowpass, bandpass etc), cutoff ratio (1=freq): a simple tone generator"+
		"\n- [[b;#fff;]makkeróni]: a complex waveform with a lot of parameters... please see reference or on-line help with 'makkeróni --help'!"+
		"\n- [[b;#fff;]ls]: list contents of the home, soundfiles & saved presets folders" +
		"\n- [[b;#fff;]play]: play a soundfile" +
		"\n- [[b;#fff;]loopplay]: loop-play a soundfile" +
		"\n- [[b;#fff;]fadeplay]: loop-play a soundfile with fade-out" +
		"\n- [[b;#fff;]watch]: repeatedly starts any command; use with -n (int) argument to set the delay time" +
		"\n- [[b;#fff;]batch]: starts any command multiple times" +
		"\n- [[b;#fff;]upload]: upload a sample (wav,mp3 or ogg) into the soundfile folder" +
		"\n- [[b;#fff;]ps]: list of running processes"+
		"\n- [[b;#fff;]stop] + thread id (int): stop a loop-play or watch thread" +
		"\n- [[b;#fff;]sleep] + thread id (int, region or all): sleep (mute) a process" +
		"\n- [[b;#fff;]resume] + thread id (int, region or all): resume (unmute) a process" +
		"\n- [[b;#fff;]remakker] + thread id (int): restart a loop-play or watch thread" +
		"\n- [[b;#fff;]replace] + thread id (int) + new command: replace a loop-play or watch thread with a different one" +
		"\n- [[b;#fff;]seq] -s int (0-3) list (numbers separated with comma): stores a list of values for playback with stepseq"+		
		"\n- [[b;#fff;]stepseq] + int(0-3) -t (int) > target (string: freq, fmfreq, tone, makkeróni or drum): starts sequence playback. -t specify time in ms between steps"+
		"\n- [[b;#fff;]seqlist]: prints out stored sequences"+
		"\n- [[b;#fff;]help]: this screen"+
		"\n- [[b;#fff;]clear]: clear window"+
		"\n- [[b;#fff;]cat]: print the contents of a textfile (see list of files with the 'ls' command!"+
		"\n- [[b;#fff;]connect]: open up the connection with the chat server" +	
		"\n- [[b;#fff;]wall] + message: send a message to the other users or send remote commands with the -c option" +	
		"\n- [[b;#fff;]disconnect]: close the connection with the chat server" +	
		"\n- [[b;#fff;]fontsize] + int: set fontsize on the console (normal: 12)" +				
		"\n- [[b;#fff;]statuslength] + int: set number of lines in status bar (default: 7)" +				
		"\n- [[b;#fff;]degrade, bitshift, blackdeath]: extremely loud digital audio effects. Please use them without headphones and be careful for the loudspeakers and the environment!"+
		"\n- [[b;#fff;]deffect]: remove audio effect from the chain"+
		"\n- [[b;#fff;]something else]: not implemented yet :)"+
 		"\n\n General syntax:"+
		"\n- [[b;#fff;]*]: random number (randomize parameters on play, loopplay, fadeplay, freq, fmfreq, and makkeróni)"+
		"\n- [[b;#fff;]&uarr;] and [[gb;#fff;]&darr;] arrows: browse command history"+
		"\n- something and [[gb;#fff;]TAB] key: autocomplete command (for. ex 'lo' + TAB gives 'loopplay' back)"+
		"\n\n For full reference and examples, please type '[[b;#fff;]cat reference.txt]'!"
				
		);

		fmszinti(66,0.7,0.6, Math.random()*8,200);

	}
	
// ls
	
	else if (parancsName == 'ls') {

		if(parancs.args[0] == '--help') {
			term.echo('Lists the contents of a folder. Currently it aggregates the content of all folders inside "home"');			
		}
		else {
		function drawOutput(responseText) {
		    term.echo(responseText);
		    fmszinti(111,0.1,0.8, Math.random()*80,1000);
		}

		function drawError() {
    		term.echo("Bummer: there was an error!");
		}

		function getRequest(url, success, error) {
    		var req = false;
    		try{
        		// most browsers
        		req = new XMLHttpRequest();
    		} catch (e){
        		// IE
        		try{
            	req = new ActiveXObject("Msxml2.XMLHTTP");
        	} catch(e) {
            	// try an older version
            	try{
                	req = new ActiveXObject("Microsoft.XMLHTTP");
            		} catch(e) {
                	return false;
            		}
				}
			  }
			req.open("GET",url,true);
  			req.send();
			req.onreadystatechange = function() {
    			if (this.readyState === 4) {
        			if (this.status === 200) {
            		drawOutput(this.responseText);
        		}
    		 }
			}	
		}
		getRequest('lekerdezo.php');
	 }
	}

// cat means 'macska' in hungarian

	else if (parancsName == 'cat') {

		if(parancs.args[0] == '--help') {
			term.echo('Prints the contents of a textfile');			
			term.echo('arguments: textfile name (string)');			
		}
		else {

		var parameterek = parancs.args;
		var hova = 0; // 0: -> term, 1 -> ps

		if (parameterek[1] == '>' && parameterek[2] == 'ps') {
		
		hova = 1;
		getRequest('cat.php?file=home/saved/'+parameterek[0]);		
		}
		
		else
		
		{
		hova = 0;
		getRequest('cat.php?file=home/'+parameterek[0]);

		}

		function drawOutput(responseText) {

			if(hova==0) {
			    term.echo(responseText);
				}
			else if (hova==1) {
				var sorok = responseText.split('"');
				var mennyiSor = sorok.length;
				for (var f = 0; f<mennyiSor; f=f+2){
					if(sorok[f+1]){
//						term.echo(sorok[f+1]);
						term.exec(sorok[f+1]);
					}
					}
			}	    
		}

		function drawError() {
    		term.echo("Bummer: there was an error!");
		}

		function getRequest(url, success, error) {
    		var req = false;
    		try{
        		// most browsers
        		req = new XMLHttpRequest();
    		} catch (e){
        		// IE
        		try{
            	req = new ActiveXObject("Msxml2.XMLHTTP");
        	} catch(e) {
            	// try an older version
            	try{
                	req = new ActiveXObject("Microsoft.XMLHTTP");
            		} catch(e) {
                	return false;
            		}
				}
			  }
			req.open("GET",url,true);
  			req.send();
			req.onreadystatechange = function() {
    			if (this.readyState === 4) {
        			if (this.status === 200) {
            		drawOutput(this.responseText);
        		}
    		 }
			}	
		}
	  }
	}


// watch
	else if (parancs.name == 'watch') {
	
			if(parancs.args[0] == '--help') {
			term.echo('Repeatedly starts play, freq, fmfreq or any other commands');			
			term.echo('arguments: -n (float) <- repeat time; -e (float) <- erode ratio');			
			term.echo('example: example: watch -n 0.3 -e 1.1 fmfreq * 0.2 0.1 140 13');			
		}
		else {
		
	
		var parameterek = parancs.args;

		var teljesparancs = parancs.name; 	

		for (var p = 0; p<parameterek.length; p++) {
			teljesparancs = teljesparancs + ' ' +  parameterek[p];
		}
		var intervalSec;
		var randomVal;
		var erode = 1.0;
		var intervalRandom = false;
		instancia++;
	
// ide kéne valami visszafelé kompatibilis randommegoldás...
// pl. csillagvizsgalo 2 stb.

		if (parameterek[0]=='-n'){
			if(typeof parameterek[1]=="string" && parameterek[1].includes('*')){
				intervalRandom = true;
				intervalSec = csillagvizsgalo2(parameterek[1],3,4);
				randomVal = parameterek[1];
				}
			else {
				intervalSec = parseFloat(parameterek[1]);
			}
			parameterek.splice(0,2);
			}
		else {
			intervalSec = 2;
		}

		if (parameterek[0]=='-e'){
			erode = parseFloat(parameterek[1]);
			parameterek.splice(0,2);
			}

		var commandToRepeat = parameterek[0];

// store thread data

	    var d = new Date();
		processzek[instancia] = {
			pid: instancia,
			uid: user,
			time: d.getTime(),
			command: teljesparancs,
			sleep: false};
//		console.log(processzek[instancia]);

		parameterek.splice(0,1);
		var gombID;
		var running = true;
		gombID = "gomb"+instancia;
		var emeInstancia = instancia;
		term.echo("Thread no. " + instancia + " started. To stop it, type [[gb;#fff;]stop " + instancia +"]!");

		var posX1 = Math.floor(Math.random()*90);
		var posY1 = Math.floor(Math.random()*90);

		var r=document.createElement('div'); 
		r.className=('objekt');
		r.setAttribute("id",gombID);
		// choose position
		r.style.left = posX1 + "%";
		r.style.bottom = posY1 + "%";	
		r.style.opacity = "1.0";
		r.innerHTML = "*";
		document.body.appendChild(r);


//		term.echo("<span id='"+gombID+"'>or click here to stop me</span>",{raw:true});			
		document.getElementById(gombID).addEventListener('click', function() {
			if(document.getElementById(gombID).innerHTML = "stop") {
				running = false;
				document.getElementById(gombID).innerHTML = "";
				var melyikProcessz = gombID.substr(4);
//				console.log(melyikProcessz);
				delete processzek[melyikProcessz];
				processzekFiltered = processzek.filter(function (el) {
					return el != null;
				});

			}
		});		
		if(commandToRepeat == 'play') {
			playCommand(parameterek,emeInstancia);
		}
		else if(commandToRepeat == 'freq') {
			freqCommand(parameterek,emeInstancia);
		}
		else if(commandToRepeat == 'fmfreq') {
			fmfreqCommand(parameterek,emeInstancia);
		}
		else if(commandToRepeat == 'tone') {
			tonecommand(parameterek,emeInstancia);
		}
		else {
			var totalCommand = commandToRepeat + ' ' + parameterek.join(' ');
			term.exec(totalCommand,true);
		}
		
			function timeout() {
				setTimeout(function() {
					if(commandToRepeat == 'play') {
						if(running==true){
							playCommand(parameterek,emeInstancia);
						}
					}
					else if(commandToRepeat == 'freq') {
						if(running==true){
							freqCommand(parameterek,emeInstancia);
						}
					}
					else if(commandToRepeat == 'fmfreq') {
						if(running==true){
							fmfreqCommand(parameterek,emeInstancia);
							}
					}
					else if(commandToRepeat == 'tone') {
						if(running==true){
						tonecommand(parameterek,emeInstancia);
						}
					}
					else {

// better solution with running / sleeping check
// works for any command:

					 if(running==true){
					  if(processzek[emeInstancia].sleep == false) {
						var totalCommand = commandToRepeat + ' ' + parameterek.join(' ');
						term.exec(totalCommand,true);
					   }
					  }
						}					
					if(running==true){
						if(intervalRandom == true) {

// ide lehetne még egy olyan, ami az erode-val egyutt noveli
// a random merteket esetleg...

//							intervalSec = (Math.random()*2 )+ 0.1;
							intervalSec = csillagvizsgalo2(randomVal,3,4);
							}
						timeout();
						intervalSec *= erode;
						}
					}, intervalSec*1000)};
			timeout();
		
 	}
}

// batch
	else if (parancs.name == 'batch') {

			if(parancs.args[0] == '--help') {
			term.echo('Starts a command multiple times.');			
			term.echo('arguments: -n (int) <- number of instances');			
			term.echo('example: batch -n 8 freq : starts a random freq 8-times');			
		}
		else {
	
		var parameterek = parancs.args;

		var teljesparancs = parancs.name; 	

		for (var p = 0; p<parameterek.length; p++) {
			teljesparancs = teljesparancs + ' ' +  parameterek[p];
		}
		var howMuch;

		if (parameterek[0]=='-n'){
			if (parameterek[1] == '*') {
				howMuch = Math.ceil(Math.random()*10 )+ 1;
			}
			else {
				howMuch = parseInt(parameterek[1]);
			}
			parameterek.splice(0,2);
			}
		else {
			howMuch = 3;
		}


		var commandToStart = parameterek.join(' ');

//		console.log(commandToStart);
		var g;
		for(g = 0; g<howMuch; g++){
			term.exec(commandToStart, true);
		}
	}
}

// communication

	else if (parancsName == 'connect') {

		var usermin = 0;
		var usermax = 999;
		
		if(parancs.args[0] == '--help') {
			term.echo('Opens up the connection to the chat server');			
		}
		else {
	 if (connected == false) {
		socket = io("http://mumia.art.pte.hu:6096");
		socket.on('connect', function () {
		socket.send('user'+ user + ' connected.');
		term.echo('connecting to the chat server as user'+user+'...');
		connected = true;
		fmszinti(user,0.5,0.4, 0.9,user*4);
		});

		socket.on('message', function (uzenet) {
		
		  if(connected==true) {

//			console.log(uzenet);
			document.title = uzenet + ' [Makkeróni]';

			var message = uzenet.split(' ');
			if( uzenet == message[0] + ' connected.' || uzenet == message[0] + ' disconnected.') {
				term.echo('server says: ' + uzenet);				
			}

// parse messages
// command to all
			else if (message[1] == '-c') {
				if(message[0]!='user'+user+':'){
					message.splice(0,2);
					var command = message.join(' ');
					var filteredCommand = command.replace('%user',user);
					term.exec(filteredCommand, true);
				}
				else{
//					term.echo('command sent succesfully to the other users');
				}
			}
// command to selected region of users			
			else if (message[1] == '-min') {
				usermin = parseInt(message[2]);
				if (usermin<0){usermin=0;}
				if (message[3] == '-max') {
					usermax = parseInt(message[4]);
					if (usermax>999){usermin=999;}
// if -min, -max and -c set:
					if (message[5] == '-c') {
						if (user>usermin && user<usermax){
						message.splice(0,6);
						var command = message.join(' ');
						var filteredCommand = command.replace('%user',user);
						term.exec(filteredCommand, true);	
						}
					}
				}
// if -min and -c set:
				else if (message[3] == '-c') {
					if (user>usermin && user<usermax){
					message.splice(0,4);
					var command = message.join(' ');
					var filteredCommand = command.replace('%user',user);
					term.exec(filteredCommand, true);	
					}
				}
			}
			
			else if (message[1] == '-max') {
				usermax = parseInt(message[2]);
				if (usermax>999){usermax=999;}
// if -max and -c set:
				if (message[3] == '-c') {
					if (user>usermin && user<usermax){
					message.splice(0,4);
					var command = message.join(' ');
					var filteredCommand = command.replace('%user',user);
					term.exec(filteredCommand, true);	
					}
				}
			
			}

			{
			if(message[0]!='user'+user+':'){
				fmszinti((uzenet.length*30)% 500,uzenet.length/30,0.4, Math.random()*4080,Math.random()*4080);
				term.echo(uzenet);
			}
			}
		   }
		});

		}
		else
		{
			term.echo('already connected.');
			}

	}
}

	else if (parancsName == 'disconnect') {
		if(parancs.args[0] == '--help') {
			term.echo('Closes the connection to the chat server');			
		}
		else {
		socket.send('user'+user+' disconnected.');
		socket.disconnect();
		connected=false;
		fmszinti(user,0.5,0.4, 0.5,user*2);
	 }
		
	}

//  (string) or -c command (string

	else if (parancsName == 'wall') {
		if(parancs.args[0] == '--help') {
			term.echo('Send messages and commands to the other users. Open the connection first with "connect"!');			
			term.echo('Arguments:');
			term.echo('-min [integer between 0-999]: set user number region minimum who receives the command to start');
			term.echo('-max [integer between 0-999]: set user number region maximum who receives the command to start');
			term.echo('-c [string]: command to start (if min and max is unset, it sends to everyone)');
			term.echo('or simply:');
			term.echo('[string]: message to send for everyone');
			term.echo('\nexample 1: wall -min 300 -max 600 -c freq <- freq command sent to connected users no. 300-600');
			term.echo('example 2: wall -c fmfreq * 0.4 <- fmfreq command sent to all of the connected users');
			term.echo('example 3: wall hey Makkeróni people <- message to everyone connected');
		}
		else {
	  		if(connected == true){
			var allmessage = parancs.args;
			var message = allmessage.join(' ');
			message = 'user' + user + ': ' + message;
			socket.send(message);
		}
		else
		{
			term.echo('please connect first to the chat server with the "connect" command!');
		}
		}
		
	}

// fontsize


	else if (parancsName == 'fontsize') {
		if(parancs.args[0] == '--help') {
			term.echo('Changes font size on the terminal.');			
			term.echo('arguments: fontsize in pixels (int)');
		}
		else {

		var sheet = document.styleSheets[0];
		var param1 = parancs.args[0];
		if(param1==""){param1="14"};
		var sorkozErtek = parseInt(param1)+2;
		sheet.deleteRule(0);
		sheet.insertRule(".terminal, .terminal-wrapper *, .cmd, .cmd * { font-family: monospace; color: rgb(170, 170, 170); background-color: rgb(0, 0, 0); font-size: "+param1+"px; line-height: "+sorkozErtek+"px; }", 0);
				
		}
		}

// set status bar size
	else if (parancsName == 'statuslength') {
		if(parancs.args[0] == '--help') {
			term.echo('Changes status area size for displaying tasks.');			
			term.echo('arguments: area size in lines (int)');
		}
		else {
		var param1 = parancs.args[0];
		if(param1==""){param1="7"};
		statusLine = parseInt(param1);

		}
		}

// refresh processes
	else if (parancsName == 'remakker') {
		if(parancs.args[0] == '--help') {
			term.echo('Restarts a process');			
			term.echo('argument: process ID (int)');
		}
		else {
		
		var param1 = parseInt(parancs.args[0]);
		if(param1){
			var commandToStart = processzek[param1].command;
			var mit = "gomb"+param1;
			doOnClick(mit);	
			term.exec(commandToStart, true);
			}
		else {
			var commandToStart = processzek[instancia].command;
			var mit = "gomb"+instancia;
			doOnClick(mit);	
			term.exec(commandToStart, true);
		}
		}
		}

// replace processes with new ones
	else if (parancsName == 'replace') {

		if(parancs.args[0] == '--help') {
			term.echo('Replaces a process to a new one');			
			term.echo('argument: process ID (int) new command (string)');
			term.echo('example: replace 2 watch fmfreq 999 0.5  <- replace process #2 to a different one.')
		}
		else {

		var parameterek = parancs.args;
		var param1 = parseInt(parameterek[0]);
		if(param1){
			var mit = "gomb"+param1;
			doOnClick(mit);	
			var teljesparancs = parameterek[1]; 	

			for (var p = 2; p<parameterek.length; p++) {
			teljesparancs = teljesparancs + ' ' +  parameterek[p];
			}
		}
		else {
			var mit = "gomb"+instancia;
			doOnClick(mit);	
			var teljesparancs = parameterek[0]; 	

			for (var p = 1; p<parameterek.length; p++) {
			teljesparancs = teljesparancs + ' ' +  parameterek[p];
			}
			
		}
			term.exec(teljesparancs, true);
		}
		}



	else if (parancsName == 'kill') {
		term.echo("I don't kill anything or anybody even a process too... Try 'stop' instead!");
		}


	else if (parancsName == 'stop') {

		if(parancs.args[0] == '--help') {
			term.echo('Stops a process or multiple processes.');			
			term.echo('argument: process ID (int), region of IDs or "all"');
			term.echo('example: stop 2-9 <- stops processes #2-#9.')
		}
		else {
	
	 	if(parancs.args[0]) {
			var param1 = parancs.args[0];
			if(typeof param1 == 'number'){
				var mit = "gomb"+param1;
				doOnClick(mit);
			}

// remove all process:
		else if(param1 =='all')
		{
/*			processzekFiltered = processzek.filter(function (el) {
				return el != null;
			});
			console.log(processzekFiltered);
*/			
			var mennyiprocessz = processzek.length;
			for(var p = 0; p<mennyiprocessz; p++) {				
			  	if(processzek[p]) {
					var k = processzek[p].pid;
					var mit = "gomb"+k;
					doOnClick(mit);
				}
			} 
		}

// define pid region to remove
		
		else if(param1.includes('-')){
				var regio = param1.split("-");
				var balfele = parseInt(regio[0]);
				var jobbfele = parseInt(regio[1]);
				for(var p = balfele; p<=jobbfele; p++) {				
					if(processzek[p]){
						var mit = "gomb"+p;
						doOnClick(mit);
						}
				} 
			}
	   }
// if no attribute set, choose the last started thread...
		else {
			var mit = "gomb"+instancia;
			doOnClick(mit);
			console.log
			}

//		var param1 = parancs.args[0];
//		var mit = "gomb"+param1;
//		doOnClick(mit);
// based on http://stackoverflow.com/questions/906486/how-can-i-programmatically-invoke-an-onclick-event-from-a-anchor-tag-while-kee
		}
		}

	else if (parancsName == 'sleep') {
		if(parancs.args[0] == '--help') {
			term.echo('Sleeps a process or multiple processes.');			
			term.echo('argument: process ID (int), region of IDs or "all"');
			term.echo('example: sleep 5 <- sleeps process #5.')
			term.echo('example: resume 5 <- resumes the previously paused process #5.')
		}
		else {
	
		var param1 = parancs.args[0];
		if(typeof param1 == 'number'){
			processzek[param1].sleep = true;
			}

// set all pid to sleep
		else if(param1 =='all')
		{
			processzekFiltered = processzek.filter(function (el) {
				return el != null;
			});

			var mennyiprocessz = processzekFiltered.length;
			for(var p = 0; p<mennyiprocessz; p++) {				
				var k = processzekFiltered[p].pid;
				processzek[k].sleep = true;
			} 
		}

// define pid region to sleep
		
		else if(param1.includes('-')){
				var regio = param1.split("-");
				var balfele = parseInt(regio[0]);
				var jobbfele = parseInt(regio[1]);
				for(var p = balfele; p<=jobbfele; p++) {				
					if(processzek[p]){
						processzek[p].sleep = true;
						}
				} 
			}
		  }
		}

	else if (parancsName == 'resume' || parancsName == 'awake') {
		if(parancs.args[0] == '--help') {
			term.echo('Resumes a process or multiple processes.');			
			term.echo('argument: process ID (int), region of IDs or "all"');
			term.echo('example: resume all <- resumes all of the previously paused processes.')
		}
		else {
	
		var param1 = parancs.args[0];
		if(typeof param1 == 'number'){
			processzek[param1].sleep = false;
			}
		else if(param1 =='all')
		{
			processzekFiltered = processzek.filter(function (el) {
				return el != null;
			});

			var mennyiprocessz = processzekFiltered.length;
			for(var p = 0; p<mennyiprocessz; p++) {				
				var k = processzekFiltered[p].pid;
				processzek[k].sleep = false;
			} 
		}
// define pid region to awake
		else if(param1.includes('-')){
				var regio = param1.split("-");
				var balfele = parseInt(regio[0]);
				var jobbfele = parseInt(regio[1]);
				for(var p = balfele; p<=jobbfele; p++) {				
					if(processzek[p]){
						processzek[p].sleep = false;
						}
				} 
			}		
			}
		}


//////////////////////////////
// seq
//////////////////////////////


	else if (parancsName == 'seq') {
	  if(parancs.args[0] == '--help' || typeof parancs.args[0] == 'undefined'){
	  	term.echo('Store a sequence of numbers into one of the 4 sequence slots. For use with the "stepseq" command.');
	  	term.echo('argument: -s int (0,1,2 or 3) <- select sequencer slot number to store.');	  	
	  	term.echo('\nexample: seq 440,500,0,200,* <- where numbers are frequencies, zero is empty note, * is random number.');
	  }
	  else {
	  // if -s option set:
	   if(parancs.args[0] == '-s') {
	   		var melyikSeqLocal = parseInt(parancs.args[1])%4;
			var szekvencia = parancs.args[2];
			if(szekvencia.includes(',')){
			// it's a series of numbers:
			szekvenciak[melyikSeqLocal] = szekvencia;
			term.echo("Sequence no. "+melyikSeqLocal+" stored.");
			//	var lepesek = szekvencia.split(',');				
			}
			else
			{
			// it's a series of characters:
			console.log('betuk');
			console.log(szekvencia.length);	
			}
		}
		// if not set:
		else {
	   		var szekvencia = parancs.args[0];
			if(szekvencia.includes(',')){
			// it's a series of numbers:
			szekvenciak[melyikSeq] = szekvencia;
			term.echo("Sequence no. "+melyikSeq+" stored.");
			melyikSeq = (melyikSeq + 1) % 4;		
			//var lepesek = szekvencia.split(',');
			}
			else
			{
			// it's a series of characters:
			console.log('betuk');
			console.log(szekvencia.length);
			}
	   	}
	   }
	}		
		
	else if (parancsName == 'seqlist') {
	  if(parancs.args[0] == '--help'){
	  term.echo('Seqlist, without any parameter, displays the contents of the stored sequences. With the "--help" parameter, it shows this message:)')
	  }
	  else
	  {
		var mennyiSeq = szekvenciak.length;
		for (var v = 0; v<mennyiSeq; v++) {
			term.echo(v + ': ' + szekvenciak[v]);
			}
		if (mennyiSeq == 0) {term.echo("No sequence stored yet. You can add one with the 'seq' command!");}
	  }
	}

	else if (parancsName == 'stepseq') {
	
	 if(parancs.args[0] == '--help'){
	  term.echo('Simple stepsequencer for lists of numbers stored by the "seq" command');
	  term.echo('arguments: (int) slot number (between 0-3), -t (int) delay time between steps, "> string" target command (freq or fmfreq) and their parameters.');	
	  term.echo('if target is set to "drum", stepseq drives a basic drum set, where 0: silence, 1= hang.mp3, 2=aine.wav, 3=hihat.wav, 4=lewwder.wav, 5=ccbassd.wav');	
	  term.echo('\nstepseq 0 -t 300 > fmfreq 0.3 <- starts a sequence stored at slot #1 with 300 msec speed, and passing the data to the fmfreq command with 0.3 sec note length');
	  term.echo('stepseq <- simply starts the sequence stored in the last used slot with random chosen delay time, and passing the data to the fmfreq command');	 
	 }
	 else{

		var parameterek = parancs.args;
		var seqTarget = 'fmfreq';

		var teljesparancs = parancs.name + ' ' + parameterek.join(' '); 	

		// store thread data
		instancia++;
	    var d = new Date();
		processzek[instancia] = {
			pid: instancia,
			uid: user,
			time: d.getTime(),
			command: teljesparancs,
			sleep: false};

		// create asterisk
		var gombID;
		gombID = "gomb"+instancia;
		var emeInstancia = instancia;

		var posX1 = Math.floor(Math.random()*90);
		var posY1 = Math.floor(Math.random()*90);

		var r=document.createElement('div'); 
		r.className=('objekt');
		r.setAttribute("id",gombID);
		
		// choose position
		r.style.left = posX1 + "%";
		r.style.bottom = posY1 + "%";	
		r.style.opacity = "1.0";
		r.innerHTML = "*";
		document.body.appendChild(r);


		var melyikInstancia = instancia;

		var melyik;
		var delay = Math.floor(Math.random()*600)+200;
		var randomDelay = false;
		
		// ez jobb arra, hogy megnézzük, van-e x paraméter vagy sem:
		
		// figure out the sequence number to start:
		if (typeof parameterek[0] !== 'undefined'){
			melyik = parameterek[0];
		}
		else {
			melyik = (melyikSeq - 1) % 4;
		}
		
		var pointer = 0;
		// figure out the speed of seq or target data:
		if (typeof parameterek[1] !== 'undefined'){
			if (parameterek[1] == '-t') {
				if (parameterek[2] == "*") {
						randomDelay = true;
						delay = Math.floor(Math.random()*600)+400;
					}
					else { 
						delay = parseInt(parameterek[2]);
					}
				pointer = 2;	
				if (parameterek[3] == '>' ){
					seqTarget = parameterek[4];
					pointer = 4;
				}
				else {
					seqTarget = 'fmfreq';
				}
					
				}
			else if (parameterek[1] == '>' ){
				seqTarget = parameterek[2];
				pointer = 2;
				}
		}

		// figure out the target data:
		if (typeof parameterek[3] !== 'undefined'){
			if (parameterek[3] == '>' ){
				seqTarget = parameterek[4];
				pointer = 4;
				}
		}

// parameterek array contains now all the info behind > sign
// string created from them all too

		parameterek.splice(0,pointer);
		var commandToForward = parameterek.join(' ');

// only parameters left
		parameterek.splice(0,1);
		if(parameterek.length==0){parameterek = [0.1]}
		var parametersToForward = parameterek.join(' ');

		console.log(szekvenciak[melyik]);
		var lepesek = szekvenciak[melyik].split(',');
		var mennyiLepes = lepesek.length;
		var melyikLepes = 0;
		var seqParameterek = new Array;
		var dataToForward;
		var melyikHang = lepesek[melyikLepes];

// first step:
// parse data
				if (melyikHang.includes('*')) {
					dataToForward = csillagvizsgalo2(melyikHang,1000,0);
					console.log(dataToForward);
//					dataToForward = Math.floor(Math.random)*1000;
				}
				else {
					dataToForward =  parseFloat(melyikHang);
				}

				if(dataToForward != 0){
				seqParameterek = [dataToForward];
				seqStepper(seqParameterek.concat(parameterek),melyikInstancia);
				}

		
		melyikLepes = (melyikLepes + 1) % mennyiLepes;

		var sleeping = true;

		document.getElementById(gombID).addEventListener('click', function() {
			if(document.getElementById(gombID).innerHTML = "stop") {
			running = false;
			document.getElementById(gombID).innerHTML = "";
			var melyikProcessz = gombID.substr(4);
			delete processzek[melyikProcessz];
			processzekFiltered = processzek.filter(function (el) {
				return el != null;
				});
			};
			});

// sequencer steps:
		function timeout() {
			setTimeout(function() {

				var lepesek = szekvenciak[melyik].split(',');
				var mennyiLepes = lepesek.length;
				var melyikHang = lepesek[melyikLepes];

				// parse data
				if (melyikHang.includes('*')) {
					dataToForward = csillagvizsgalo2(melyikHang,1000,0);
				}
				else {
					dataToForward =  parseFloat(melyikHang);
				}
				sleeping = processzek[melyikInstancia].sleep;
				if(sleeping==false){

				if(dataToForward != 0){
				seqParameterek = [dataToForward];
				seqStepper(seqParameterek.concat(parameterek),melyikInstancia);
				}

				}
	
					melyikLepes = (melyikLepes + 1) % mennyiLepes;

// if * is set for delay time, a random value is chosen:

				if (randomDelay==true) {
					delay = Math.floor(Math.random()*600)+400;
				}

				timeout();
				}, delay)};
				
		timeout();

		function seqStepper(value,melyikInstancia){
//			var parametersToForward = [value,0.3];
			if(seqTarget=='fmfreq'){
//				console.log(value,melyikInstancia);
				fmfreqCommand(value, melyikInstancia);
			}
			else if(seqTarget=='freq'){
				freqCommand(value, melyikInstancia);

			}
			else if(seqTarget=='makkeróni'){
					makkeronicommand(value,melyikInstancia);

			}
			else if(seqTarget=='tone'){
					tonecommand(value,melyikInstancia);
			}
			else if(seqTarget=='drum'){
					var melyikDob = Math.floor((value[0]-1)%5);
					var dobok = ['hang.mp3','aine.wav','hihat.wav','lewwder.wav','ccbassd.wav'];
					var dobFile = dobok[melyikDob];
					value[0] = dobFile;
			
					playCommand(value,melyikInstancia);
			}
			else{
				freqCommand(value, melyikInstancia);
			}		
			}

		term.echo("Thread no. " + instancia + " started. To stop it, type [[gb;#fff;]stop " + instancia +"]! Metronome is set to " + delay + "ms");

     }
	}

	else if (parancsName == 'upload') {

		if(parancs.args[0] == '--help') {
			term.echo('Uploads a sound sample (wav,mp3 or ogg) into the soundfiles folder].');			
		}
		else {

		term.echo('<form action="upload.php" method="post" enctype="multipart/form-data" target="hiddenFrame">Select soundfile to upload (mp3,ogg and wav format, max 500kB):<input type="file" name="fileToUpload" id="fileToUpload"><input type="submit" value="Upload soundfile" name="submit"></form>',{raw:true});

// info about submit without redirecting: http://stackoverflow.com/questions/25983603/how-to-submit-html-form-without-redirection

		term.echo('After uploading, check the results with an ls command!')
	  }
	}


	else if (parancsName == 'save') {
		var filenev = parancs.args[0];
		var tartalom = parancs.args[1];
	
		$.get("save.php", { file: filenev, content:tartalom });
		
	}

	else if (parancsName == 'somethingelse' || parancsName == 'something') {
		term.echo('[[;#fff;]Try something else, not exactly something else :)]');
		fmszinti(1111,0.9,0.8, Math.random()*4080,Math.random()*4080);
	}

	else if (parancsName == 'kill') {
		term.echo("[[;#fff;]We don't kill animals, plants and processes! :) use 'stop' or 'sleep' instead...]");
		fmszinti(11111,0.1,0.8, Math.random()*4080,Math.random()*4080);
	}


	else if (parancsName == 'ps') {

		if(parancs.args[0] == '--help') {
			term.echo('Prints the list of running loopplay and watch processes, including thread ID, user ID, time since start and the command');
			term.echo('Can be used with pipe to store process informations in a textfile for future load.');
			term.echo('\nexample: ps > something.txt <- writes out the process list to a textfile for further loading with the "cat something.txt > ps" command');

		
		}
		else
		{

		var texttosave = '';

		processzekFiltered = processzek.filter(function (el) {
			return el != null;
		});

		var mennyiProcessz = processzekFiltered.length;

		var fejlec ='[[;#fff;]PID UID TIME COMMAND]';
		texttosave = fejlec;
		
		for(var u = 0; u<mennyiProcessz; u++){
			var mostido = new Date();
			var elteltido = Math.round((mostido - processzekFiltered[u].time)/1000);
			var sleeping;
			if (processzekFiltered[u].sleep) {sleeping = '-'} else {sleeping = ' '};
			var cucc = processzekFiltered[u].pid + sleeping + '  ' + processzekFiltered[u].uid + ' ' + elteltido + ' "' + processzekFiltered[u].command + '"';
			texttosave = texttosave + "\n" + cucc;
		}
		
	// pipe!! the first one
	
	if(parancs.args[0]=='>'){		
		save(texttosave,parancs.args[1]);
		term.echo('I think the process list is saved to a textfile. If You need it, you can load with the cat command. Anyway the files are stored here: https://makker.hu/makkeroni/home/saved/ .');
		}
	else {
		term.echo(texttosave); 				
		}
	 }
	}

	else if (parancsName == 'man') {
        term.echo('This command is currently not implemented. Try "help" instead!');
	}


	else if (parancsName == '') {

		var phrases=['No problem:)','No input - no output:)','It was a clear Enter key'];
		var howmuch = phrases.length;
		var whichtext = Math.floor(Math.random()*howmuch);
		var text = phrases[whichtext]; 
		
        term.echo('[[;#fff;]'+text+']');
	}

	else if (parancsName == 'reallynothing') {
		// do nothing...
	}

	else if (parseInt(parancsName) > 0) {
        term.echo("oh no, this is a number!");
		}

	else {
		var phrases=['Type "help" to get some instructions!','This is maybe a new command? Could You implement it? contact me!','Feel free to type to me! this is a random answer also','Ladybugs are known as "katica" in hungarian','Maybe You tried to type "reboot"?',"I can't understand, because my code is written by artists for programmers",'Code is NOT poetry','To be marginal in a marginal scene...'];
		var howmuch = phrases.length;
		var whichtext = Math.floor(Math.random()*howmuch);
		var text = phrases[whichtext]; 

        term.echo('[[;#fff;]'+text+']');
		fmszinti(333,3.9,0.3, Math.random()*11,Math.random()*408);

    };




    
}, { 


            greetings:  
"[[gb;#0f0;]           *     *           ]\n"+
"[[gb;#0f0;]       *             *       ]\n"+
"[[gb;#0f0;]    *   **   **   **   *     ][[gb;#fff;]MAKKERÓNI]\n"+
"[[gb;#0f0;]   *       ** ** **     *    ]A live webaudio operating system.\n"+
"[[gb;#0f0;]  *        ******        *   ]Type [[b;#fff;]help] for more instructions!\n"+
"[[gb;#0f0;]  *         ****         *   ]News: [[b;#fff;]cat changelog.txt],\n"+
"[[gb;#0f0;]  *        ******        *   ]Reference: [[b;#fff;]cat reference.txt].\n"+
"[[gb;#0f0;]   *      ** ** **      *    ]\n"+
"[[gb;#0f0;]     *  **   **   **   *     ]Suggestions and comments welcome:\n"+
"[[gb;#0f0;]       *             *       ]Balázs Kovács - kovacs.balazs@pte.hu\n"+
"[[gb;#0f0;]           *     *      ]\n"+
						"____________________________________________________________________________\n\n",
			prompt: '[[;#0f0;]user'+user+']@[[;#0f0;]Makkeróni:~ $] ',
//			completion: ['help','ls','play','loopplay&nbsp;','fadeplay','upload','stop ','clear','freq','fmfreq','wall','cat','something&nbsp;else','fontsize','watch&nbsp;-n&nbsp;2','batch&nbsp;-n&nbsp;3','ps','statuslength&nbsp;','remakker&nbsp;','replace&nbsp;','sleep&nbsp;','resume&nbsp;','deffect','makkeróni','connect','disconnect'],
			completion: function(string,callback) {
				if (this.get_command().match(/^play /) || this.get_command().match(/^loopplay /) || this.get_command().match(/^fadeplay /)) {
					callback([
					<?php

	foreach (glob("home/soundfiles/*.wav") as $filename) {
		$filenameFiltered = substr($filename, 16);
    	echo("'$filenameFiltered',");
}
	foreach (glob("home/soundfiles/*.mp3") as $filename) {
		$filenameFiltered = substr($filename, 16);
    	echo("'$filenameFiltered',");
}
?>
					]);
					}
					else if (this.get_command().match(/^cat /)) {
						callback([
					

<?php

	foreach (glob("home/*.txt") as $filename) {
		$filenameFiltered = substr($filename, 5);
    	echo("'$filenameFiltered',");
}

	foreach (glob("home/saved/*.txt") as $filename) {
		$filenameFiltered = substr($filename, 11);
    	echo("'$filenameFiltered',");
}

?>
])	
					
					}
					else {
					callback(['help','ls','play','loopplay','fadeplay','upload','stop ','clear','freq','fmfreq','wall','cat','something&nbsp;else','fontsize','watch&nbsp;-n&nbsp;2','batch&nbsp;-n&nbsp;3','ps','statuslength&nbsp;','remakker&nbsp;','replace&nbsp;','sleep&nbsp;','resume&nbsp;','deffect','makkeróni','connect','disconnect','seq','seqlist','stepseq','tone']);
					}},
			name: 'test',
			keymap: {
				'CTRL+C': function(e, original) {
						console.log('megy');
				}
				},
			exit: false
});

})
    </script>
  </head>
<body>
<iframe name="hiddenFrame" width="0" height="0" border="0" style="display: none;"></iframe>
</body>

<script>

// ez korábban a fejlécben volt documentready-vel. próbálom minimalizálni a jquery-t

		

</script>
</html>
