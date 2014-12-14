<?php

abstract class Entity
{
	/** Get the name of the directory of the object.
	 * 
	 * @warning Child classes MUST override this method.
	 * @return The name of the directory.
	 */
	protected static function getEntityType()
	{
		return null ;
	}
	
/***** Class *****/

	/** Get an active entity by its name.
	 * 
	 * @param string $name The name to look for.
	 * 
	 * @return The Entity instance or null.
	 */
	public static function getByName( $name )
	{
		$entity = null ;
		
		$activeEntities = Configuration::loadJson(
			Configuration::getDataDir( static::getEntityType() ) . '/active.json',
			array()
		) ;
		
		if ( array_key_exists( $name, $activeEntities ) )
		{
			try
			{
				$entity = self::getById( $activeEntities[$name] ) ;
	
				if ( ! $entity->isActive() )
				{
					$entity = null ;
				}
			}
			catch ( NoSuchEntityException $e ) {}
		}
		
		return $entity ;
	}
	
	/** Get active entities.
	 * 
	 * @return The list of the entities which are active.
	 */
	public static function getAllActive()
	{
		$list = Configuration::loadJson(
			Configuration::getDataDir( static::getEntityType() ) . '/active.json',
			array()
		) ;
		
		$class = get_called_class() ;
		
		$entities = array_map(
			function ( $id ) use ( $class )
			{
				return $class::getById( $id ) ;
			},
			$list
		) ;
		
		return array_filter(
			$entities,
			function ( Entity $entity )
			{
				return $entity->isActive() ;
			}
		) ;
	}
	
	/** Get an entity by id.
	 * 
	 * @param int $entityId The id to look for.
	 * 
	 * @return The Entity instance.
	 */
	
	public static function getById( $entityId )
	{
		static $entities = array() ;
		
		if ( ! array_key_exists( $entityId, $entities ) )
		{
			$class = get_called_class() ;
			$entities[$entityId] = new $class( $entityId ) ;
		}
		
		return $entities[$entityId] ;
	}
	
	/** Create an entity (for use by children clases).
	 * 
	 * @param string $entityName The name of the entity which is created.
	 * 
	 * @throw EntityNameTooShortException If the name is too short.
	 * @throw EntityNameAlreadyTakenException If the name is already in use.
	 * 
	 * @return The Entity instance.
	 */
	protected static function createEntity( $name, $initialArray )
	{
		if ( self::containsIllegalCharacter( $name ) )
		{
			throw new ContainsIllegalCharacterException( $name ) ;
		}
		
		if ( strlen( $name ) < Configuration::getValue( static::getEntityType() . '.minnamelength' ) )
		{
			throw new EntityNameTooShortException( $name ) ;
		}
		
		if ( self::getByName( $name ) !== null )
		{
			throw new EntityNameAlreadyTakenException( $name ) ;
		}

		$lastIdFile = Configuration::getDataDir( static::getEntityType() ) . '/lastid.int' ;
		$id = Configuration::incrementCounter( $lastIdFile ) ;
		
		$data = array( 'name' => $name ) ;
		$data += $initialArray ;
		
		Configuration::saveJson(
			self::getEntityFile( $id ),
			$data
		) ;
		
		$activeEntitiesFile = Configuration::getDataDir( static::getEntityType() ) . '/active.json' ;
		$activeEntities = Configuration::loadJson( $activeEntitiesFile, array() ) ;
		$activeEntities[$name] = $id ;
		Configuration::saveJson( $activeEntitiesFile, $activeEntities ) ;
		
		return self::getById( $id ) ;
	}
	
	/** Get the file of the entity by id.
	 * @param int $entityId The id of the entity whose file is searched.
	 * 
	 * @return The File of the entity.
	 */
	private static function getEntityFile( $entityId )
	{
		return Configuration::getDataDir( static::getEntityType() ) . '/' . $entityId . '.json' ;
	}
	
	
/***** Instances *****/
	
	/** The id of the entity. */
	private $id = -1 ;
	
	/** The array which contains the data concerning the entity. */
	private $data = null ;
	
	/** The data have been edited */
	private $modified = false ;
	
	/** Constructor.
	 * 
	 * @param int $entityId The id of the Entity which is built.
	 * 
	 * @throw NoSuchEntityException If there is no entity with this id.
	 */
	public function __construct( $entityId )
	{
		$this->id = $entityId ;
		$this->data = Configuration::loadJson(
			$this->getEntityFile( $entityId )
		) ;
		
		if ( $this->data === null )
		{
			throw new NoSuchEntityException( $entityId ) ;
		}
	}
	
	/** Destructor.
	 * Save the data if modified.
	 */
	public function __destruct()
	{
		if ( $this->modified )
		{
			Configuration::saveJson(
				$this->getEntityFile( $this->id ),
				$this->data
			) ;
		}
	}
	
	/** Change a key in the data array.
	 * @param string $key The key to change.
	 * @param mixed $value The new value.
	 */
	protected function setValue( $key, $value )
	{
		$this->data[$key] = $value ;
		$this->modified = true ;
	}
	
	/** Get a key in the data array.
	 * @param string $key The key to change.
	 * @param mixed $value The new value.
	 */
	protected function getValue( $key )
	{
		return $this->data[$key] ;
	}
	
	/** Check whether the entity is active or not.
	 * 
	 * @return True if the entity is active, false otherwise.
	 */
	public function isActive()
	{
		return time() - $this->data['last-action'] < Configuration::getValue( static::getEntityType() . '.inactivity' ) ;
	}
	
	/** Put an entity inactive
	 * 
	 * @throw EntityAlreadyInactiveException If the entity is already inactive.
	 */
	public function isNowInactive()
	{
		if ( ! $this->isActive() )
		{
			throw new EntityAlreadyInactiveException( $this->id ) ;
		}
		else
		{
			$activeFile = Configuration::getDataDir( static::getEntityType() ) . '/active.json' ;
			
			$activeEntities = Configuration::loadJson( $activeFile, array() ) ;
			
			unset ( $activeEntities[$this->data['name']] ) ;
			
			Configuration::saveJson( $activeFile, $activeEntities) ;
		}
	}
	
	/** Remember the entity is active just now. */
	public function isActiveNow()
	{
		$this->setValue( 'last-action', time() ) ;
	}
	
	/** Get the id of the entity.
	 * 
	 * @return The id of the Entity instance.
	 * @codeCoverageIgnore
	 */
	public function getId()
	{
		return $this->id ;
	}
	
	
	/** Get the name of the entity.
	 * 
	 * @return The name of the Entity instance.
	 * @codeCoverageIgnore
	 */
	public function getName()
	{
		return $this->data['name'] ;
	}
	
	/** Send error if the string contains illegal characters.
	 * 
	 * @param string $name
	 * 
	 * @return bool true: there is illegal characters, false otherwise.
	 */
	public function containsIllegalCharacter( $name )
	{
		return ! preg_match( '#^[A-Z��������������a-z��������������\' -]*$#', $name ) ;
	}
	
}
