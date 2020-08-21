<?php

/*
 * Put this in public/ and execute it from the browser
 */

header('Content-Type: text/plain');

$return_var = null;

ob_start();
system('ln -sfv ../../_terminal/public/ ./terminal', $return_var);
$output = ob_get_clean();

if ($return_var === 0) // 0 = no error
	echo "OK";
else
	echo "ERROR: $return_var";

echo PHP_EOL;
echo getcwd();
echo PHP_EOL;
echo $output;
echo PHP_EOL;

ob_start();
system('ls -la');
echo ob_get_clean();
