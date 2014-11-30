/**
 * Configuration access.
 * @module configuration
 * @class configuration
 * @static
 */
( function () {

	var
		/**
		 * Have values been initialised?
		 * @property {Boolean} initialised
		 * @private
		 */
		initialised = false,
		/**
		 * Stored values.
		 * @property {Object} values
		 * @private
		 */
		values = {} ;

	window.configuration = {} ;
	
	/**
	 * Initialise data (supposed to be with server defaults).
	 * @method initialise
	 * @param {Object} data The default configuration.
	 */
	window.configuration.initialise = function ( data )
	{
		if ( initialised )
		{
			throw new Exception( 'Configuration already filled' ) ;
		}
		
		initialised = true ;
		values = data ;
	} ;
	
	/**
	 * Get a configuration value.
	 * @method get
	 * @param {String} key The key of the value in the configuration (see configuration-values class).
	 */
	window.configuration.get = function ( key )
	{
		var res = JSON.parse(
			localStorage.getItem( 'configuration-' + key )
		) ;
		
		if ( res === null && typeof values[key] !== 'undefined' )
		{
			res = values[key] ;
		}
		
		return res ;
	} ;
	
	/**
	 * Set a local (persistent) value for a single configuration value.
	 * @method setLocal
	 * @param {String} key The key of the value in the configuration (see configuration-values class).
	 * @param value Value to store.
	 */
	window.configuration.setLocal = function ( key, value )
	{
		localStorage.setItem(
			'configuration-' + key,
			JSON.stringify( value )
		) ;
	} ;
	
	/**
	 * Delete a local configuration value.
	 * @method returnToDefault
	 * @param {String} key The key of the value in the configuration (see configuration-values class).
	 */
	window.configuration.returnToDefault = function ( key )
	{
		localStorage.removeItem( 'configuration-' + key ) ;
	} ;

} ) () ;
