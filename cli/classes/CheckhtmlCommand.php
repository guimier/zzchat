<?php

/** Check input HTML. */
class CheckhtmlCommand extends Command
{

	/** See Command::getDocumentation. */
	public function getDocumentation()
	{
		return array() ;
	}

	/** See Command::execute. */
	protected function execute()
	{
		HTML::checkInput( file_get_contents( 'php://stdin' ) ) ;
	}

}

