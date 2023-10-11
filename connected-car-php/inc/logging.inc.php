<?php

function log_error($message, $exit = 0){
	if(CONSOLE_OUTPUT)
	{
		echo 'Error: ' . $message . "\n";
		debug_print_backtrace();
	}

	if($exit > 0){
		if(CONSOLE_OUTPUT)
		{
			echo 'Exiting...';
		}
		exit($exit);
	}
}

function log_message($message){
	if(CONSOLE_OUTPUT)
	{
		echo 'Info: ' . $message . "\n";
	}
}

?>
