<?php

namespace Luur;

use Luur\Exceptions\BranchNotFoundException;
use PHPUnit\Framework\TestCase;

class TraversTest extends TestCase
{
    /**
     * @var array
     */
    protected $tree;

    public function setUp(): void
    {
        parent::setUp();

        $this->tree = [
            'food'  => [
                'vegetables' => [
                    'potatoes' => [],
                    'onions'   => null,
                ],
                'fruits'     => [
                    'apple'   => 1,
                    'avocado' => 1.1,
                    'banana'  => [
                        'baby'      => 'baby',
                        'ice_cream' => true,
                        'casual'    => false,
                    ],
                ]
            ],
            'tools' => 'many',
        ];
    }

    public function testInitializesNewInstance()
    {
        $trav = $this->createTraversInstance();
        $this->assertTrue($trav instanceof Travers);
    }

    public function createTraversInstance(array $tree = [], bool $fail = false, $delimiter = Travers::DEFAULT_DELIMITER)
    {
        return new Travers($tree, $fail, $delimiter);
    }

    public function testInitializesNewInstanceWithCorrectDelimiter()
    {
        $delimiter = '=';
        $trav      = $this->createTraversInstance([], false, $delimiter);
        $this->assertEquals($delimiter, $trav->getDelimiter());
    }

    public function testSetsDelimiter()
    {
        $delimiter = '=';
        $trav      = $this->createTraversInstance();
        $trav->setDelimiter($delimiter);
        $this->assertEquals($delimiter, $trav->getDelimiter());
    }

    /**
     * @dataProvider nonFailingDataProvider
     * @param string $path
     * @param $expected
     */
    public function testFindsBranchWithoutFailing(string $path, $expected)
    {
        $trav = $this->createTraversInstance($this->tree);
        $this->assertEquals($expected, $trav->find($path));
    }

    /**
     * @dataProvider failingDataProvider
     * @param string $path
     */
    public function testThrowsExceptionWithFailingEnabled(string $path)
    {
        $this->expectException(BranchNotFoundException::class);
        $trav = $this->createTraversInstance($this->tree, true);
        $trav->find($path);
    }

    /**
     * @dataProvider failingDataProvider
     */
    public function testThrowsExceptionOnRemove($path)
    {
        $this->expectException(BranchNotFoundException::class);
        $trav = $this->createTraversInstance($this->tree, true);
        $trav->remove($path);
    }

    /**
     * @dataProvider failingDataProvider
     *
     * @param $path
     *
     * @throws BranchNotFoundException
     */
    public function testRemoveWithoutFailing($path)
    {
        $trav = $this->createTraversInstance($this->tree);
        $tree = $trav->remove($path);
        $this->assertEquals($this->tree, $tree);
    }

    /**
     * @dataProvider nonFailingDataProvider
     * @param string $path
     * @param $expected
     */
    public function testStaticallyFindsBranchWithoutFailing(string $path, $expected)
    {
        $this->assertEquals($expected, Travers::get($path, $this->tree));
    }

    /**
     * @dataProvider setterDataProvider
     * @param string $path
     * @param $value
     */
    public function testChangesLeafValue(string $path, $value)
    {
        $trav = $this->createTraversInstance($this->tree);
        $trav->change($path, $value);
        $this->assertEquals($value, $trav->find($path));
    }

    /**
     * @dataProvider setterDataProvider
     * @param string $path
     * @param $value
     */
    public function testStaticallyChangesLeafValue(string $path, $value)
    {
        $this->assertEquals($value, Travers::get($path, Travers::set($path, $value, $this->tree)));
    }

    /**
     * @dataProvider removeDataProvider
     *
     * @param string $path
     *
     * @throws BranchNotFoundException
     */
    public function testRemoveValue(string $path)
    {
        $tree = Travers::delete($path, $this->tree);
        $trav = $this->createTraversInstance($tree);
        $this->assertNull($trav->find($path));
    }

    public function nonFailingDataProvider(): array
    {
        return [
            [
                'food.vegetables.potatoes',
                [],
            ],
            [
                'food.vegetables.onions',
                null,
            ],
            [
                'food.fruits.apple',
                1,
            ],
            [
                'food.fruits.avocado',
                1.1,
            ],
            [
                'food.fruits.banana.baby',
                'baby',
            ],
            [
                'food.fruits.banana.ice_cream',
                true,
            ],
            [
                'food.fruits.banana.casual',
                false,
            ],
            [
                'tools',
                'many',
            ],
            [
                'invalid_path',
                null,
            ],
        ];
    }

    public function failingDataProvider(): array
    {
        return [
            [
                'food.vegetables.potatoes.invalid_path',
            ],
            [
                'food.invalid_path.potatoes',
            ],
            [
                'tools.invalid_path',
            ],
            [
                'invalid_path',
            ],
        ];
    }

    public function setterDataProvider(): array
    {
        return [
            [
                'food.fruits.apple.red',
                true,
            ],
            [
                'food.vegetables.potatoes',
                'test value',
            ],
            [
                'food.vegetables.potatoes',
                true,
            ],
            [
                'food.vegetables.potatoes',
                false,
            ],
            [
                'food.new_path.potatoes',
                1,
            ],
            [
                'tools.new_path',
                1.1,
            ],
            [
                'new_path',
                null,
            ],
        ];
    }

    public function removeDataProvider()
    {
        return [
            [
                'food.vegetables.potatoes',
            ],
            [
                'food.fruits',
            ],
            [
                'food',
            ]
        ];
    }
}
