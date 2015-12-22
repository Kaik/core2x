<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package HookDispatcher
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\SortableColumns\Tests;

use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;

class SortableColumnsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SortableColumns
     */
    private $sortableColumns;

    public function setUp()
    {
        $router = $this
            ->getMockBuilder('Symfony\Component\Routing\RouterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $router
            ->method('generate')
            ->will($this->returnCallback(function ($id, $params) {
                return '/foo?' . http_build_query($params);
            }));

        $this->sortableColumns = new SortableColumns($router, 'foo');
        $this->sortableColumns->addColumn(new Column('a'));
        $this->sortableColumns->addColumn(new Column('b'));
        $this->sortableColumns->addColumn(new Column('c'));
    }

    /**
     * @covers SortableColumns::getColumn
     */
    public function testGetColumn()
    {
        $a = $this->sortableColumns->getColumn('a');
        $this->assertInstanceOf('Zikula\Component\SortableColumns\Column', $a);
        $this->assertEquals('a', $a->getName());
    }

    /**
     * @covers SortableColumns::addColumn
     */
    public function testAddColumn()
    {
        $d = new Column('d');
        $this->sortableColumns->addColumn($d);
        $this->assertEquals($d, $this->sortableColumns->getColumn('d'));
    }

    /**
     * @covers SortableColumns::getDefaultColumn
     */
    public function testGetDefaultColumn()
    {
        $a = $this->sortableColumns->getColumn('a');
        $b = $this->sortableColumns->getColumn('b');
        $this->assertEquals($a, $this->sortableColumns->getDefaultColumn());
        $this->assertNotEquals($b, $this->sortableColumns->getDefaultColumn());
    }

    /**
     * @covers SortableColumns::removeColumn
     */
    public function testRemoveColumn()
    {
        $this->sortableColumns->removeColumn('b');
        $this->assertNull($this->sortableColumns->getColumn('b'));
    }

    /**
     * @covers SortableColumns::getSortDirection
     */
    public function testGetSortDirection()
    {
        $this->assertEquals(Column::DIRECTION_ASCENDING, $this->sortableColumns->getSortDirection());
    }

    /**
     * @covers SortableColumns::getSortColumn
     */
    public function testGetSortColumn()
    {
        $a = $this->sortableColumns->getColumn('a');
        $this->assertEquals($a, $this->sortableColumns->getSortColumn());
    }

    /**
     * @covers SortableColumns::setOrderBy
     */
    public function testSetOrderBy()
    {
        $c = $this->sortableColumns->getColumn('c');
        $this->sortableColumns->setOrderBy($c, Column::DIRECTION_DESCENDING);
        $this->assertEquals(Column::DIRECTION_DESCENDING, $this->sortableColumns->getSortDirection());
        $this->assertEquals($c, $this->sortableColumns->getSortColumn());
    }

    /**
     * @covers SortableColumns::setAdditionalUrlParameters
     * @covers SortableColumns::getAdditionalUrlParameters
     */
    public function testAdditionalUrlParameters()
    {
        $this->sortableColumns->setAdditionalUrlParameters(['x' => 1, 'z' => 0]);
        $this->assertEquals(['x' => 1, 'z' => 0], $this->sortableColumns->getAdditionalUrlParameters());
    }

    /**
     * @dataProvider columnDefProvider
     * @covers SortableColumns::generateSortableColumns
     * @param $columnDef
     */
    public function testGenerateSortableColumns($col, $dir, $columnDef)
    {
        $this->sortableColumns->setOrderBy($this->sortableColumns->getColumn($col), $dir);
        $this->assertEquals($columnDef, $this->sortableColumns->generateSortableColumns());
    }

    /**
     * @covers SortableColumns::generateSortableColumns
     * @covers SortableColumns::setAdditionalUrlParameters
     */
    public function testGenerateSortableColumnsWithAdditionalUrlParameters()
    {
        $expected = [
                'a' => ['url' => '/foo?x=1&z=0&sort-direction=' . Column::DIRECTION_DESCENDING . '&sort-field=a', 'class' => Column::CSS_CLASS_ASCENDING],
                'b' => ['url' => '/foo?x=1&z=0&sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=b', 'class' => Column::CSS_CLASS_UNSORTED],
                'c' => ['url' => '/foo?x=1&z=0&sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=c', 'class' => Column::CSS_CLASS_UNSORTED],
            ];

        $this->sortableColumns->setOrderBy($this->sortableColumns->getColumn('a'), Column::DIRECTION_ASCENDING);
        $this->sortableColumns->setAdditionalUrlParameters(['x' => 1, 'z' => 0]);
        $this->assertEquals($expected, $this->sortableColumns->generateSortableColumns());
    }

    public function columnDefProvider()
    {
        return [
            [null, '',
                [
                    'a' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_DESCENDING . '&sort-field=a', 'class' => Column::CSS_CLASS_ASCENDING],
                    'b' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=b', 'class' => Column::CSS_CLASS_UNSORTED],
                    'c' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=c', 'class' => Column::CSS_CLASS_UNSORTED],
                ]
            ],
            ['a', Column::DIRECTION_ASCENDING,
                [
                    'a' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_DESCENDING . '&sort-field=a', 'class' => Column::CSS_CLASS_ASCENDING],
                    'b' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=b', 'class' => Column::CSS_CLASS_UNSORTED],
                    'c' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=c', 'class' => Column::CSS_CLASS_UNSORTED],
                ]
            ],
            ['a', Column::DIRECTION_DESCENDING,
                [
                    'a' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=a', 'class' => Column::CSS_CLASS_DESCENDING],
                    'b' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=b', 'class' => Column::CSS_CLASS_UNSORTED],
                    'c' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=c', 'class' => Column::CSS_CLASS_UNSORTED],
                ]
            ],
            ['c', Column::DIRECTION_ASCENDING,
                [
                    'a' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=a', 'class' => Column::CSS_CLASS_UNSORTED],
                    'b' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=b', 'class' => Column::CSS_CLASS_UNSORTED],
                    'c' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_DESCENDING . '&sort-field=c', 'class' => Column::CSS_CLASS_ASCENDING],
                ]
            ],
            ['b', Column::DIRECTION_DESCENDING,
                [
                    'a' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=a', 'class' => Column::CSS_CLASS_UNSORTED],
                    'b' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=b', 'class' => Column::CSS_CLASS_DESCENDING],
                    'c' => ['url' => '/foo?sort-direction=' . Column::DIRECTION_ASCENDING . '&sort-field=c', 'class' => Column::CSS_CLASS_UNSORTED],
                ]
            ],
        ];
    }
}
