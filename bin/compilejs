#!/usr/bin/env php
<?php

/**
 * Uses Google's Closure Compiler REST API
 * @link https://closure-compiler.appspot.com/
 *
 * How to use it:
 * --------------
 *
 * bin/compilejs.php myJsFile.js                 -> Outputs compiled code to console
 * bin/compilejs.php myJsFile.js myJsFile.min.js -> Saves compiled code into a file (it won't overwrite myJsFile.js if you mistakenly use the same file name as second argument)
 * bin/compilejs.php min                         -> Saves compiled code into a new .min.js file
 * bin/compilejs.php myJsFile.js > myJsFile.js   -> Replace the file with its code compiled
 */

$file = '';

if (empty($argv[1]))
	die("Error: You must at least give a filename as input.\nYou can also directly specify the output as second parameter.\nOr use 'min' as second parameter to created a '.min.js' file.\n");

if (!is_file($argv[1]))
	die("Error: Input file does not exist.\n");
else
	$file = file_get_contents($argv[1]);

if ($file === false)
	die("Error: Cannot read input file.\n");

$data = [
	'js_code' => $file,
	'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
	'output_format' => 'text',
	'output_info' => 'compiled_code'
];

// HTTP POST request

$context = stream_context_create([
	'http' => [
		'method' => 'POST',
		'header' => 'Content-type: application/x-www-form-urlencoded' . "\r\n",
		'content' => http_build_query($data)
	]
]);

$response = file_get_contents('https://closure-compiler.appspot.com/compile', false, $context);

if ($response === false)
	die("Error: Request failed. Try gain.\n");

// Output

if (!empty($argv[2]) && $argv[2] !== $argv[1]) { // Never overwrite input file, use > if you want that

	if ($argv[2] === 'min' && preg_match('#\.js$#i', $argv[1]))
		$argv[2] = preg_replace('#\.js$#i', '.min.js', $argv[1]);
	else if ($argv[2] === 'min')
		$argv[2] = $argv[1] . '.min.js';

	file_put_contents($argv[2], $response);

	echo "Saved output to '{$argv[2]}'.\n";

} else {
	echo $response;
}

