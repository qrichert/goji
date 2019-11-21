<?php

	namespace Goji\Toolkit;

	use Exception;

	class Terminal {

		/* <CONSTANTS> */

		const SYSTEM = 'system';
		const PASSTHRU = 'passthru';
		const EXEC = 'exec';
//		const SHELL_EXEC = 'shell_exec';

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
		 * @param int $return_var
		 * @return string
		 */
		private static function system(string $command, int &$return_var = null): string {
			ob_start();
			system($command, $return_var);
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
		 * @param int $return_var
		 * @return string
		 */
		private static function passthru(string $command, int &$return_var = null): string {
			ob_start();
			passthru($command, $return_var);
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
		 * @param int $return_var
		 * @return string
		 */
		private static function exec(string $command, int &$return_var = null): string {
			exec($command , $output, $return_var);
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
		 * @param bool $success
		 * @param null $forceCommand
		 * @return string
		 * @throws \Exception
		 */
		protected static function processCommand(string $command, bool &$success = null, $forceCommand = null): string {

			// Redirect stderr to stdout
			$command .= ' 2>&1';

			$output = '';
			$return_var = 0;

			if ($forceCommand !== null) {

				switch ($forceCommand) {
					case self::SYSTEM:      $output = self::system($command, $return_var);      break;
					case self::PASSTHRU:    $output = self::passthru($command, $return_var);    break;
					case self::EXEC:        $output = self::exec($command, $return_var);        break;
//					case self::SHELL_EXEC:  $output = self::shell_exec($command);  break;
					default:
						throw new Exception("Command '$forceCommand' doesn't exist", self::E_COMMAND_DOES_NOT_EXIST);
						break;
				}

			} else {

				if (function_exists('system'))
					$output = self::system($command, $return_var);
				else if (function_exists('passthru'))
					$output = self::passthru($command, $return_var);
				else if (function_exists('exec'))
					$output = self::exec($command, $return_var);
//  			else if (function_exists('shell_exec'))
//	    			$output = self::shell_exec($command);
				else
					throw new Exception('Command execution not possible on this system.', self::E_COMMAND_EXECUTION_NOT_POSSIBLE);
			}

			$success = $return_var === 0; // 0 = no error

			return $output;
		}

		/**
		 * @param string $command Command(s) (can me multiple with && or ;)
		 * @param bool|null $success 'returns' true if no error, false if error
		 * @param string|null $forceCommand Terminal::SYSTEM|PASSTHRU|EXEC
		 * @return string Command(s) output
		 * @throws \Exception
		 */
		public static function execute(string $command, bool &$success = null, string $forceCommand = null): string {

			$output = '';
			$command = preg_split('#(&&|;)#', $command);
			$success = true; // True unless error

			foreach ($command as $c) {

				$output .= self::processCommand($c, $commandSuccess, $forceCommand);

				if ($commandSuccess === false)
					$success = false;
			}

			return $output;
		}
	}
