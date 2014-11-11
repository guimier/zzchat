<?php

/** Generic child class of Context.
 * @codeCoverageIgnore
 */
class _Context extends Context {

	public function getParameter( $key, $more = null )
	{
		switch ( $key )
		{
			case 'foo' : return 'bar' ;
			case 'empty' : return '' ;
			case 'list' : return 'foo,bar,baz' ;
			case 'null' : return null ;
		}
	}
	
}

