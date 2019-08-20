/**
 * EmailScrambler class
 *
 * Static class, to scramble an email, use:
 * EmailScrambler.scramble('me@mydomain.com'); // Returns R&1R.s,R:)O?q,R
 *
 * Then to restore the email, use:
 * EmailScrambler.restore('R&1R.s,R:)O?q,R'); // Returns me@mydomain.com
 *
 * So, use EmailScrambler.scramble() once when you're building the site,
 * then only use EmailScrambler.restore() in production.
 */
class EmailScrambler {

	static getMap() {

		// We optimize the hash table for decoding, since it's
		// what we're gonna do most of the time
		return {
			':': 'a', '$': 'A', '[': '0',
			'}': 'b', 'w': 'B', '`': '1',
			'q': 'c', 'x': 'C', '5': '2',
			's': 'd', '-': 'D', 'v': '3',
			'&': 'e', '7': 'E', 'V': '4',
			'j': 'f', 'G': 'F', '2': '5',
			'h': 'g', '3': 'G', 't': '6',
			'~': 'h', 'S': 'H', 'Z': '7',
			')': 'i', 'c': 'I', '=': '8',
			'H': 'j', '|': 'J', '_': '9',
			'r': 'k', 'F': 'K', '1': '@',
			']': 'l', 'U': 'L', '!': '-',
			'R': 'm', 'P': 'M', '{': '_',
			'O': 'n', 'Q': 'N', '?': '.',
			',': 'o', 'y': 'O', 'z': '+',
			'K': 'p', 'k': 'P', '(': "'",
			'l': 'q', '0': 'Q', '@': '!',
			'6': 'r', 'g': 'R', 'E': '#',
			'A': 's', '^': 'S', '+': '&',
			'I': 't', '*': 'T', '4': '*',
			'8': 'u', '>': 'U', '%': '?',
			'J': 'v', 'L': 'V', '/': '=',
			'f': 'w', 'd': 'W', 'T': '^',
			'M': 'x', '9': 'X', 'n': '~',
			'.': 'y', ';': 'Y', 'Y': '€',
			'#': 'z', '<': 'Z', 'b': '$'
		};
	}

	/**
	 * Restore a scrambled email string.
	 *
	 * @param scrambledEmail
	 */
	static restore(scrambledEmail) {

		let email = '';

		let map = this.getMap();

		for (let char of scrambledEmail) {
			// If unknown char, just keep it as it is
			email += map.hasOwnProperty(char) ? map[char] : char;
		}

		return email;
	}

	/**
	 * Scrambles the email & outputs the code.
	 *
	 * @param email
	 */
	static scramble(email) {

		let scrambledEmail = '';

		let map = this.getMap();

		for (let char of email) {

			let newChar = null;

			for (let [x, y] of Object.entries(map)) {

				if (char == y) {
					newChar = x;
					break;
				}
			}

			// If unknown char, just keep it as it is
			scrambledEmail += newChar !== null ? newChar : char;
		}

		return scrambledEmail;
	}
}
