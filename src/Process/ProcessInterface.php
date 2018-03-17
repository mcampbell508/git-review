<?php

namespace GitReview\Process;

use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;

interface ProcessInterface
{
    /**
     * Runs the process.
     *
     * The callback receives the type of output (out or err) and
     * some bytes from the output in real-time. It allows to have feedback
     * from the independent process during execution.
     *
     * The STDOUT and STDERR are also available after the process is finished
     * via the getOutput() and getErrorOutput() methods.
     *
     * @param callable|null $callback A PHP callback to run whenever there is some
     *                                output available on STDOUT or STDERR
     *
     * @return int The exit status code
     *
     * @throws RuntimeException When process can't be launched
     * @throws RuntimeException When process stopped after receiving signal
     * @throws LogicException   In case a callback is provided and output has been disabled
     */
    public function run($callback = null);

    /**
     * Returns the Pid (process identifier), if applicable.
     *
     * @return int|null The process id if running, null otherwise
     */
    public function getPid();

    /**
     * Sends a POSIX signal to the process.
     *
     * @param int $signal A valid POSIX signal (see http://www.php.net/manual/en/pcntl.constants.php)
     *
     * @return $this
     *
     * @throws LogicException   In case the process is not running
     * @throws RuntimeException In case --enable-sigchild is activated and the process can't be killed
     * @throws RuntimeException In case of failure
     */
    public function signal($signal);

    /**
     * Disables fetching output and error output from the underlying process.
     *
     * @return $this
     *
     * @throws RuntimeException In case the process is already running
     * @throws LogicException   if an idle timeout is set
     */
    public function disableOutput();

    /**
     * Enables fetching output and error output from the underlying process.
     *
     * @return $this
     *
     * @throws RuntimeException In case the process is already running
     */
    public function enableOutput();

    /**
     * Returns true in case the output is disabled, false otherwise.
     *
     * @return bool
     */
    public function isOutputDisabled();

    /**
     * Returns the current output of the process (STDOUT).
     *
     * @return string The process output
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getOutput();

    /**
     * Returns the output incrementally.
     *
     * In comparison with the getOutput method which always return the whole
     * output, this one returns the new output since the last call.
     *
     * @return string The process output since the last call
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getIncrementalOutput();

    /**
     * Clears the process output.
     *
     * @return $this
     */
    public function clearOutput();

    /**
     * Returns the current error output of the process (STDERR).
     *
     * @return string The process error output
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getErrorOutput();

    /**
     * Returns the errorOutput incrementally.
     *
     * In comparison with the getErrorOutput method which always return the
     * whole error output, this one returns the new error output since the last
     * call.
     *
     * @return string The process error output since the last call
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getIncrementalErrorOutput();

    /**
     * Clears the process output.
     *
     * @return $this
     */
    public function clearErrorOutput();

    /**
     * Returns the exit code returned by the process.
     *
     * @return null|int The exit status code, null if the Process is not terminated
     *
     * @throws RuntimeException In case --enable-sigchild is activated and the sigchild compatibility mode is disabled
     */
    public function getExitCode();

    /**
     * Returns a string representation for the exit code returned by the process.
     *
     * This method relies on the Unix exit code status standardization
     * and might not be relevant for other operating systems.
     *
     * @return null|string A string representation for the exit status code, null if the Process is not terminated
     *
     * @see http://tldp.org/LDP/abs/html/exitcodes.html
     * @see http://en.wikipedia.org/wiki/Unix_signal
     */
    public function getExitCodeText();

    /**
     * Checks if the process ended successfully.
     *
     * @return bool true if the process ended successfully, false otherwise
     */
    public function isSuccessful();

    /**
     * Returns true if the child process has been terminated by an uncaught signal.
     *
     * It always returns false on Windows.
     *
     * @return bool
     *
     * @throws RuntimeException In case --enable-sigchild is activated
     * @throws LogicException   In case the process is not terminated
     */
    public function hasBeenSignaled();

    /**
     * Returns the number of the signal that caused the child process to terminate its execution.
     *
     * It is only meaningful if hasBeenSignaled() returns true.
     *
     * @return int
     *
     * @throws RuntimeException In case --enable-sigchild is activated
     * @throws LogicException   In case the process is not terminated
     */
    public function getTermSignal();

    /**
     * Returns true if the child process has been stopped by a signal.
     *
     * It always returns false on Windows.
     *
     * @return bool
     *
     * @throws LogicException In case the process is not terminated
     */
    public function hasBeenStopped();

    /**
     * Returns the number of the signal that caused the child process to stop its execution.
     *
     * It is only meaningful if hasBeenStopped() returns true.
     *
     * @return int
     *
     * @throws LogicException In case the process is not terminated
     */
    public function getStopSignal();

    /**
     * Checks if the process is currently running.
     *
     * @return bool true if the process is currently running, false otherwise
     */
    public function isRunning();

    /**
     * Checks if the process has been started with no regard to the current state.
     *
     * @return bool true if status is ready, false otherwise
     */
    public function isStarted();

    /**
     * Checks if the process is terminated.
     *
     * @return bool true if process is terminated, false otherwise
     */
    public function isTerminated();

    /**
     * Gets the process status.
     *
     * The status is one of: ready, started, terminated.
     *
     * @return string The current process status
     */
    public function getStatus();

    /**
     * Stops the process.
     *
     * @param int|float $timeout The timeout in seconds
     * @param int $signal A POSIX signal to send in case the process has not stop at timeout, default is SIGKILL (9)
     *
     * @return int The exit-code of the process
     */
    public function stop($timeout = 10, $signal = null);

    /**
     * Adds a line to the STDOUT stream.
     *
     * @internal
     *
     * @param string $line The line to append
     */
    public function addOutput($line);

    /**
     * Adds a line to the STDERR stream.
     *
     * @internal
     *
     * @param string $line The line to append
     */
    public function addErrorOutput($line);

    /**
     * Gets the command line to be executed.
     *
     * @return string The command to execute
     */
    public function getCommandLine();

    /**
     * Sets the command line to be executed.
     *
     * @param string $commandline The command to execute
     *
     * @return ProcessInterface
     */
    public function setCommandLine($commandline);

    /**
     * Gets the process timeout (max. runtime).
     *
     * @return float|null The timeout in seconds or null if it's disabled
     */
    public function getTimeout();

    /**
     * Gets the process idle timeout (max. time since last output).
     *
     * @return float|null The timeout in seconds or null if it's disabled
     */
    public function getIdleTimeout();

    /**
     * Sets the process timeout (max. runtime).
     *
     * To disable the timeout, set this value to null.
     *
     * @param int|float|null $timeout The timeout in seconds
     *
     * @return ProcessInterface
     *
     * @throws InvalidArgumentException if the timeout is negative
     */
    public function setTimeout($timeout);

    /**
     * Sets the process idle timeout (max. time since last output).
     *
     * To disable the timeout, set this value to null.
     *
     * @param int|float|null $timeout The timeout in seconds
     *
     * @return ProcessInterface
     *
     * @throws LogicException           if the output is disabled
     * @throws InvalidArgumentException if the timeout is negative
     */
    public function setIdleTimeout($timeout);

    /**
     * Enables or disables the TTY mode.
     *
     * @param bool $tty True to enabled and false to disable
     *
     * @return ProcessInterface
     *
     * @throws RuntimeException In case the TTY mode is not supported
     */
    public function setTty($tty);

    /**
     * Checks if the TTY mode is enabled.
     *
     * @return bool true if the TTY mode is enabled, false otherwise
     */
    public function isTty();

    /**
     * Sets PTY mode.
     *
     * @param bool $bool
     *
     * @return ProcessInterface
     */
    public function setPty($bool);

    /**
     * Returns PTY state.
     *
     * @return bool
     */
    public function isPty();

    /**
     * Gets the working directory.
     *
     * @return string|null The current working directory or null on failure
     */
    public function getWorkingDirectory();

    /**
     * Sets the current working directory.
     *
     * @param string $cwd The new working directory
     *
     * @return ProcessInterface
     */
    public function setWorkingDirectory($cwd);

    /**
     * Gets the environment variables.
     *
     * @return array The current environment variables
     */
    public function getEnv();

    /**
     * Sets the environment variables.
     *
     * An environment variable value should be a string.
     * If it is an array, the variable is ignored.
     *
     * That happens in PHP when 'argv' is registered into
     * the $_ENV array for instance.
     *
     * @param array $env The new environment variables
     *
     * @return ProcessInterface
     */
    public function setEnv(array $env);

    /**
     * Gets the Process input.
     *
     * @return resource|string|\Iterator|null The Process input
     */
    public function getInput();

    /**
     * Sets the input.
     *
     * This content will be passed to the underlying process standard input.
     *
     * @param mixed $input The content
     *
     * @return ProcessInterface
     *
     * @throws LogicException In case the process is running
     */
    public function setInput($input);

    /**
     * Gets the options for proc_open.
     *
     * @return array The current options
     */
    public function getOptions();

    /**
     * Sets the options for proc_open.
     *
     * @param array $options The new options
     *
     * @return ProcessInterface
     */
    public function setOptions(array $options);

    /**
     * Performs a check between the timeout definition and the time the process started.
     *
     * In case you run a background process (with the start method), you should
     * trigger this method regularly to ensure the process timeout
     *
     * @throws ProcessTimedOutException In case the timeout was reached
     */
    public function checkTimeout();

    public function wait();
}
