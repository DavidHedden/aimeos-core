<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2020
 */


namespace Aimeos\MShop\Index\Manager\Text;


class PgSQLTest extends \PHPUnit\Framework\TestCase
{
	private $object;


	protected function setUp() : void
	{
		$context = clone \TestHelperMShop::getContext();
		$config = $context->getConfig();

		$dbadapter = $config->get( 'resource/db-product/adapter', $config->get( 'resource/db/adapter' ) );

		if( $dbadapter !== 'pgsql' ) {
			$this->markTestSkipped( 'PostgreSQL specific test' );
		}

		$this->object = new \Aimeos\MShop\Index\Manager\Text\PgSQL( \TestHelperMShop::getContext() );
	}


	protected function tearDown() : void
	{
		unset( $this->object );
	}


	public function testGetSearchAttributes()
	{
		$list = $this->object->getSearchAttributes();

		foreach( $list as $attribute ) {
			$this->assertInstanceOf( \Aimeos\MW\Criteria\Attribute\Iface::class, $attribute );
		}
	}


	public function testSearchItemsRelevance()
	{
		$search = $this->object->createSearch();
		$search->setConditions( $search->compare( '>', $search->createFunction( 'index.text:relevance', ['de', 'T-DISC'] ), 0 ) );
		$search->setSortations( [$search->sort( '-', $search->createFunction( 'sort:index.text:relevance', ['de', 'T-DISC'] ) )] );

		$result = $this->object->searchItems( $search, [] );

		$this->assertGreaterThanOrEqual( 3, count( $result ) );
	}


	public function testSearchItemsRelevanceCase()
	{
		$search = $this->object->createSearch();
		$search->setConditions( $search->compare( '>', $search->createFunction( 'index.text:relevance', ['de', 't-disc'] ), 0 ) );
		$search->setSortations( [$search->sort( '-', $search->createFunction( 'sort:index.text:relevance', ['de', 't-disc'] ) )] );

		$result = $this->object->searchItems( $search, [] );

		$this->assertGreaterThanOrEqual( 3, count( $result ) );
	}
}
