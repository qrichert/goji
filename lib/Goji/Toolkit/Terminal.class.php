<?php

	namespace Goji\Toolkit;

	use Exception;

	class Terminal {

		/* <CONSTANTS> */

		const SYSTEM = 'system';
		const PASSTHRU = 'passthru';
		const EXEC = 'exec';
		const SHELL_EXEC = 'shell_exec';

		const E_COMMAND_DOES_NOT_EXIST = 0;
		const E_COMMAND_EXECUTION_NOT_POSSIBLE = 0;

		/**
		 * system()
		 *
		 * From the doc:
		 *
		 * system ( string $command [, int &$return_var ] ) : string
		 *
		 * system() is just like the C version of the function in that it executes the
		 * given command and outputs the result.
		 *
		 * @param string $command
		 * @return string
		 */
		private static function system(string $command): string {
			ob_start();
			system($command);
			return ob_get_clean();
		}

		/**
		 * From the doc:
		 *
		 * passthru ( string $command [, int &$return_var ] ) : void
		 *
		 * The passthru() function is similar to the exec() function in that it executes
		 * a command. This function should be used in place of exec() or system() when the
		 * output from the Unix command is binary data which needs to be passed directly back
		 * to the browser.
		 *
		 * @param string $command
		 * @return string
		 */
		private static function passthru(string $command): string {
			ob_start();
			passthru($command);
			return ob_get_clean();
		}

		/**
		 * From the doc:
		 *
		 * exec ( string $command [, array &$output [, int &$return_var ]] ) : string
		 *
		 * exec() executes the given command.
		 *
		 * @param string $command
		 * @return string
		 */
		private static function exec(string $command): string {
			exec($command , $output);
			return implode("\n" , $output) . "\n";
		}

		/**
		 * From the doc:
		 *
		 * shell_exec ( string $cmd ) : string
		 *
		 * Execute command via shell and return the complete output as a string.
		 *
		 * @param string $command
		 * @return string
		 */
		private static function shell_exec(string $command): string {
			return shell_exec($command);
		}

		/**
		 * @param string $command
		 * @param null $forceCommand
		 * @return string
		 * @throws \Exception
		 */
		protected static function processCommand(string $command, $forceCommand = null): string {

			// Redirect stderr to stdout
			$command .= ' 2>&1';

			if ($forceCommand !== null) {

				switch ($forceCommand) {
					case self::SYSTEM:      return self::system($command);      break;
					case self::PASSTHRU:    return self::passthru($command);    break;
					case self::EXEC:        return self::exec($command);        break;
					case self::SHELL_EXEC:  return self::shell_exec($command);  break;
					default:
						throw new Exception("Command '$forceCommand' doesn't exist", self::E_COMMAND_DOES_NOT_EXIST);
						break;
				}
			}

			if (function_exists('system'))
				return self::system($command);
			else if (function_exists('passthru'))
				return self::passthru($command);
			else if (function_exists('exec'))
				return self::exec($command);
			else if (function_exists('shell_exec'))
				return self::shell_exec($command);
			else
				throw new Exception('Command execution not possible on this system.', self::E_COMMAND_EXECUTION_NOT_POSSIBLE);
		}

		/**
		 * @param string $command
		 * @return string
		 * @throws \Exception
		 */
		public static function execute(string $command, string $forceCommand = null): string {

			$output = '';
			$command = preg_split('#(&&|;)#', $command);

			foreach ($command as $c) {
				$output .= self::processCommand($c, $forceCommand);
			}

			return $output;
		}
	}
