<?php

namespace src\Polymorph\Database;

use Polymorph\Database\DatabaseServiceProviderTrait;
use PhpSpec\ObjectBehavior;

class DatabaseServiceProviderTraitSpec extends ObjectBehavior
{

    public function let()
    {
        $this->beAnInstanceOf(DatabaseServiceProviderTraitStub::class);
    }

    public function it_encodes_table_values()
    {
        $tableDefinition = [
            'testInt' => 'int',
            'testString' => 'string',
            'testTrue' => 'bool',
            'testFalse' => 'bool',
            'testArray' => 'array',
            'testObject' => 'object',
            'testTypedObject' => DatabaseServiceProviderTraitTypedObjectStub::class,
        ];
        $this->beConstructedWith($tableDefinition);

        $instance = (object)[
            'testInt' => 10,
            'testString' => 'test',
            'testTrue' => true,
            'testFalse' => false,
            'testArray' => ['one', 'two'],
            'testObject' => (object)[
                'foo' => 'bar'
            ],
            'testTypedObject' => new DatabaseServiceProviderTraitTypedObjectStub([
                'foo' => 'bar'
            ])
        ];
        $this->encodeTableValues($instance)->shouldBe([
            'testInt' => 10,
            'testString' => 'test',
            'testTrue' => 1,
            'testFalse' => 0,
            'testArray' => json_encode($instance->testArray),
            'testObject' => json_encode($instance->testObject),
            'testTypedObject' => json_encode($instance->testTypedObject),
        ]);
    }

    public function it_decodes_table_values()
    {
        $tableDefinition = [
            'testInt' => 'int',
            'testString' => 'string',
            'testTrue' => 'bool',
            'testFalse' => 'bool',
            'testArray' => 'array',
            'testObject' => 'object',
            'testTypedObject' => DatabaseServiceProviderTraitTypedObjectStub::class,
        ];
        $this->beConstructedWith($tableDefinition);

        $row = [
            'testInt' => '10',
            'testString' => 'test',
            'testTrue' => '1',
            'testFalse' => '0',
            'testArray' => json_encode(['one', 'two']),
            'testObject' => json_encode([
                'foo' => 'bar'
            ]),
            'testTypedObject' => json_encode([
                'foo' => 'bar'
            ])
        ];

        $this->decodeTableValues($row)['testInt']->shouldBe(10);
        $this->decodeTableValues($row)['testString']->shouldBe('test');
        $this->decodeTableValues($row)['testTrue']->shouldBe(true);
        $this->decodeTableValues($row)['testFalse']->shouldBe(false);
        $this->decodeTableValues($row)['testArray']->shouldBe(['one', 'two']);
        $this->decodeTableValues($row)['testObject']->shouldBeLike((object)['foo' => 'bar']);
        $this->decodeTableValues($row)['testTypedObject']->shouldhaveType(
            DatabaseServiceProviderTraitTypedObjectStub::class
        );
    }
}

class DatabaseServiceProviderTraitStub {

    use DatabaseServiceProviderTrait;

    public function __construct($tableDefinition)
    {
        $this->tableDefinition = $tableDefinition;
    }
}

class DatabaseServiceProviderTraitTypedObjectStub {

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}