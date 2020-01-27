<?php

declare(strict_types=1);
chdir(dirname(__FILE__));
require_once '../lib/Goji/Goji.php';

// <GOJI>

foreach (['Unit', 'System', 'Integration'] as $testType) {

	$files = glob("./Goji/$testType/*Test.class.php");

	foreach ($files as $f) {
		$className = pathinfo($f, PATHINFO_FILENAME); // ./Goji/Unit/SomeTest.class.php -> SomeTest.class
		$className = mb_substr($className, 0, -6); // SomeTest.class -> SomeTest
		$className = "\Test\Goji\\$testType\\$className"; // \Test\Goji\Unit\SomeTest

		require_once $f;

		$object = new $className();
		$reflection = new ReflectionObject($object);

		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($methods as $method) {
			$method = $method->name;
			$object->$method();
		}
	}
}

// If we arrive here, no exception = tests passed successfully
echo "Tests passed successfully! 👏", PHP_EOL;
