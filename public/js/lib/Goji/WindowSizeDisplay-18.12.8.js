/**
 * Display can be customized using its class ".window-size-display"
 */

window.addEventListener('load', function() {

	let windowSizeDisplay = document.createElement('p');
		windowSizeDisplay.style.position = 'fixed';
		windowSizeDisplay.style.right = '10px';
		windowSizeDisplay.style.bottom = '10px';
		windowSizeDisplay.style.margin = '0';
		windowSizeDisplay.classList.add('window-size-display');
			document.querySelector('body').appendChild(windowSizeDisplay);

	function updateWindowSizeDisplay(w, h) {
		windowSizeDisplay.innerHTML = 'w: ' + window.innerWidth + 'px<br>h: ' + window.innerHeight +  'px';
	}

	window.addEventListener('resize', updateWindowSizeDisplay, false);

	updateWindowSizeDisplay();

}, false);
