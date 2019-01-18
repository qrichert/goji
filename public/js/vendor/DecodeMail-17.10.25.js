/*
	Copyright © 2017 Quentin Richert <https://www.quentinrichert.com/>
	Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
	See http://creativecommons.org/licenses/by-nc-nd/4.0/
	Please contact the author for commercial use.

	* This script allows to encode and decode an email.
	* Encoding emails prevents robots to find them in the pages they scan, thus preventing spam.

	Instructions:

		1. Call the EncodeMail() function like this:

			alert(EncodeMail("myemail@domain.com"));

				// This will return the decode function you can copy-paste from the alert into your code.
				// The previous call will return:

			"DecodeMail([13, 24, 5, 13, 1, 9, 12, 27, 4, 15, 13, 1, 9, 14, 0, 3, 15, 13]);"

				// You can remove the EncodeMail() function from the file if you don't plan to use it again.

		2. Call DecodeMail() to show you email.

			document.querySelector("p#email").innerHTML = DecodeMail([13, 24, 5, 13, 1, 9, 12, 27, 4, 15, 13, 1, 9, 14, 0, 3, 15, 13]);
*/

function DecodeMail(mail) {
	var chars = new Array(
		'&#046;', // . =  0

		'&#097;', // a =  1
		'&#098;', // b =  2
		'&#099;', // c =  3
		'&#100;', // d =  4
		'&#101;', // e =  5
		'&#102;', // f =  6
		'&#103;', // g =  7
		'&#104;', // h =  8
		'&#105;', // i =  9
		'&#106;', // j = 10
		'&#107;', // k = 11
		'&#108;', // l = 12
		'&#109;', // m = 13
		'&#110;', // n = 14
		'&#111;', // o = 15
		'&#112;', // p = 16
		'&#113;', // q = 17
		'&#114;', // r = 18
		'&#115;', // s = 19
		'&#116;', // t = 20
		'&#117;', // u = 21
		'&#118;', // v = 22
		'&#119;', // w = 23
		'&#120;', // x = 24
		'&#121;', // y = 25
		'&#122;', // z = 26

		'&#064;', // @ = 27
		'&#045;', // - = 28
		'&#095;', // _ = 29

		'&#048;', // 0 = 30
		'&#049;', // 1 = 31
		'&#050;', // 2 = 32
		'&#051;', // 3 = 33
		'&#052;', // 4 = 34
		'&#053;', // 5 = 35
		'&#054;', // 6 = 36
		'&#055;', // 7 = 37
		'&#056;', // 8 = 38
		'&#057;'  // 9 = 39
	);

	var output = "";

	mail.forEach(function(e) {
		output += chars[e];
	});

	return output;
}

function EncodeMail(mail) {
	var chars = new Array(
		'.', // . =  0

		'a', // a =  1
		'b', // b =  2
		'c', // c =  3
		'd', // d =  4
		'e', // e =  5
		'f', // f =  6
		'g', // g =  7
		'h', // h =  8
		'i', // i =  9
		'j', // j = 10
		'k', // k = 11
		'l', // l = 12
		'm', // m = 13
		'n', // n = 14
		'o', // o = 15
		'p', // p = 16
		'q', // q = 17
		'r', // r = 18
		's', // s = 19
		't', // t = 20
		'u', // u = 21
		'v', // v = 22
		'w', // w = 23
		'x', // y = 24
		'y', // y = 25
		'z', // z = 26

		'@', // @ = 27
		'-', // - = 28
		'_', // _ = 29

		'0', // 0 = 30
		'1', // 1 = 31
		'2', // 2 = 32
		'3', // 3 = 33
		'4', // 4 = 34
		'5', // 5 = 35
		'6', // 6 = 36
		'7', // 7 = 37
		'8', // 8 = 38
		'9' // 9 = 39
	);

	var output = "DecodeMail([";

	mail = mail.toLowerCase();
	mail = mail.split("");

	for (var i = 0; i < mail.length; i++) {
		output += chars.indexOf(mail[i]);

		if (i < (mail.length - 1))
			output += ", ";
	}

	output += "]);"

	return output;
}