/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	Display can be customized using its class ".window-size-display"
*/

window.addEventListener('load', function() {

	var windowSizeDisplay = document.createElement('p');
		windowSizeDisplay.style.position = 'fixed';
		windowSizeDisplay.style.left = '10px';
		windowSizeDisplay.style.bottom = '10px';
		windowSizeDisplay.style.margin = '0';
		windowSizeDisplay.classList.add('window-size-display');
			document.querySelector('body').appendChild(windowSizeDisplay);

	function updateWindownSizeDisplay(w, h) {
		windowSizeDisplay.innerHTML = 'w: ' + window.innerWidth + 'px<br>h: ' + window.innerHeight +  'px';
	}

	window.addEventListener('resize', updateWindownSizeDisplay, false);

	updateWindownSizeDisplay();
}, false);
