<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://www.arcavias.com/en/license
 * @package MShop
 * @subpackage Price
 */


/**
 * Default price list manager for creating and handling price list items.
 * @package MShop
 * @subpackage Price
 */
class MShop_Price_Manager_List_Default
	extends MShop_Common_Manager_List_Abstract
	implements MShop_Price_Manager_List_Interface
{
	private $_searchConfig = array(
		'price.list.id' => array(
			'code' => 'price.list.id',
			'internalcode' => 'mprili."id"',
			'internaldeps' => array( 'LEFT JOIN "mshop_price_list" AS mprili ON ( mpri."id" = mprili."parentid" )' ),
			'label' => 'Price list ID',
			'type' => 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'price.list.siteid' => array(
			'code' => 'price.list.siteid',
			'internalcode' => 'mprili."siteid"',
			'label' => 'Price list site ID',
			'type' => 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'price.list.parentid' => array(
			'code' => 'price.list.parentid',
			'internalcode' => 'mprili."parentid"',
			'label' => 'Price list price ID',
			'type' => 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'price.list.domain' => array(
			'code' => 'price.list.domain',
			'internalcode' => 'mprili."domain"',
			'label' => 'Price list domain',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'price.list.typeid' => array(
			'code' => 'price.list.typeid',
			'internalcode' => 'mprili."typeid"',
			'label' => 'Price list type ID',
			'type' => 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
			'public' => false,
		),
		'price.list.refid' => array(
			'code' => 'price.list.refid',
			'internalcode' => 'mprili."refid"',
			'label' => 'Price list reference ID',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'price.list.datestart' => array(
			'code' => 'price.list.datestart',
			'internalcode' => 'mprili."start"',
			'label' => 'Price list start date',
			'type' => 'datetime',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'price.list.dateend' => array(
			'code' => 'price.list.dateend',
			'internalcode' => 'mprili."end"',
			'label' => 'Price list end date',
			'type' => 'datetime',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'price.list.config' => array(
			'code' => 'price.list.config',
			'internalcode' => 'mprili."config"',
			'label' => 'Price list config',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'price.list.position' => array(
			'code' => 'price.list.position',
			'internalcode' => 'mprili."pos"',
			'label' => 'Price list position',
			'type' => 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
		),
		'price.list.status' => array(
			'code' => 'price.list.status',
			'internalcode' => 'mprili."status"',
			'label' => 'Price list status',
			'type' => 'integer',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_INT,
		),
		'price.list.ctime' => array(
			'code' => 'price.list.ctime',
			'internalcode' => 'mprili."ctime"',
			'label' => 'Price list create date/time',
			'type' => 'datetime',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'price.list.mtime' => array(
			'code' => 'price.list.mtime',
			'internalcode' => 'mprili."mtime"',
			'label' => 'Price list modification date/time',
			'type' => 'datetime',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
		'price.list.editor' => array(
			'code' => 'price.list.editor',
			'internalcode' => 'mprili."editor"',
			'label' => 'Price list editor',
			'type' => 'string',
			'internaltype' => MW_DB_Statement_Abstract::PARAM_STR,
		),
	);


	/**
	 * Initializes the object.
	 *
	 * @param MShop_Context_Item_Interface $context Context object
	 */
	public function __construct( MShop_Context_Item_Interface $context )
	{
		parent::__construct( $context );
		$this->_setResourceName( 'db-price' );
	}


	/**
	 * Removes old entries from the storage.
	 *
	 * @param array $siteids List of IDs for sites whose entries should be deleted
	 */
	public function cleanup( array $siteids )
	{
		$path = 'classes/price/manager/list/submanagers';
		foreach( $this->_getContext()->getConfig()->get( $path, array( 'type') ) as $domain ) {
			$this->getSubManager( $domain )->cleanup( $siteids );
		}

		$this->_cleanup( $siteids, 'mshop/price/manager/list/default/item/delete' );
	}


	/**
	 * Returns the list attributes that can be used for searching.
	 *
	 * @param boolean $withsub Return also attributes of sub-managers if true
	 * @return array List of attribute items implementing MW_Common_Criteria_Attribute_Interface
	 */
	public function getSearchAttributes( $withsub = true )
	{
		$list = parent::getSearchAttributes();

		if( $withsub === true )
		{
			$context = $this->_getContext();

			$path = 'classes/price/manager/list/submanagers';
			foreach( $context->getConfig()->get( $path, array( 'type' ) ) as $domain ) {
				$list = array_merge( $list, $this->getSubManager( $domain )->getSearchAttributes() );
			}
		}

		return $list;
	}


	/**
	 * Returns a new manager for price list extensions.
	 *
	 * @param string $manager Name of the sub manager type in lower case
	 * @param string|null $name Name of the implementation, will be from configuration (or Default) if null
	 * @return mixed Manager for different extensions, e.g types, lists etc.
	 */
	public function getSubManager( $manager, $name = null )
	{
		/** classes/price/manager/list/name
		 * Class name of the used price list manager implementation
		 *
		 * Each default price list manager can be replaced by an alternative imlementation.
		 * To use this implementation, you have to set the last part of the class
		 * name as configuration value so the manager factory knows which class it
		 * has to instantiate.
		 *
		 * For example, if the name of the default class is
		 *
		 *  MShop_Price_Manager_List_Default
		 *
		 * and you want to replace it with your own version named
		 *
		 *  MShop_Price_Manager_List_Mylist
		 *
		 * then you have to set the this configuration option:
		 *
		 *  classes/price/manager/list/name = Mylist
		 *
		 * The value is the last part of your own class name and it's case sensitive,
		 * so take care that the configuration value is exactly named like the last
		 * part of the class name.
		 *
		 * The allowed characters of the class name are A-Z, a-z and 0-9. No other
		 * characters are possible! You should always start the last part of the class
		 * name with an upper case character and continue only with lower case characters
		 * or numbers. Avoid chamel case names like "MyList"!
		 *
		 * @param string Last part of the class name
		 * @since 2014.03
		 * @category Developer
		 */

		/** mshop/price/manager/list/decorators/excludes
		 * Excludes decorators added by the "common" option from the price list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "mshop/common/manager/decorators/default" before they are wrapped
		 * around the price list manager.
		 *
		 *  mshop/price/manager/list/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("MShop_Common_Manager_Decorator_*") added via
		 * "mshop/common/manager/decorators/default" for the price list manager.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/price/manager/list/decorators/global
		 * @see mshop/price/manager/list/decorators/local
		 */

		/** mshop/price/manager/list/decorators/global
		 * Adds a list of globally available decorators only to the price list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("MShop_Common_Manager_Decorator_*") around the price list manager.
		 *
		 *  mshop/price/manager/list/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "MShop_Common_Manager_Decorator_Decorator1" only to the price controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/price/manager/list/decorators/excludes
		 * @see mshop/price/manager/list/decorators/local
		 */

		/** mshop/price/manager/list/decorators/local
		 * Adds a list of local decorators only to the price list manager
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("MShop_Common_Manager_Decorator_*") around the price list manager.
		 *
		 *  mshop/price/manager/list/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "MShop_Common_Manager_Decorator_Decorator2" only to the price
		 * controller.
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 * @see mshop/common/manager/decorators/default
		 * @see mshop/price/manager/list/decorators/excludes
		 * @see mshop/price/manager/list/decorators/global
		 */

		return $this->_getSubManager( 'price', 'list/' . $manager, $name );
	}


	/**
	 * Returns the config path for retrieving the configuration values.
	 *
	 * @return string Configuration path
	 */
	protected function _getConfigPath()
	{
		return 'mshop/price/manager/list/default/item/';
	}


	/**
	 * Returns the search configuration for searching items.
	 *
	 * @return array Associative list of search keys and search definitions
	 */
	protected function _getSearchConfig()
	{
		return $this->_searchConfig;
	}
}