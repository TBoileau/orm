<?php

namespace TBoileau\ORM\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use TBoileau\ORM\DataMapping\Annotation\BelongsTo;
use TBoileau\ORM\DataMapping\Annotation\BelongsToMany;
use TBoileau\ORM\DataMapping\Annotation\Column;
use TBoileau\ORM\DataMapping\Annotation\Entity;
use TBoileau\ORM\DataMapping\Annotation\HasMany;
use TBoileau\ORM\DataMapping\Annotation\HasOne;
use TBoileau\ORM\DataMapping\Annotation\PrimaryKey;
use TBoileau\ORM\DataMapping\Metadata\EntityMetadata;
use TBoileau\ORM\DataMapping\Metadata\PrimaryKeyMetadata;
use TBoileau\ORM\DataMapping\Reader\ColumnReader;
use TBoileau\ORM\DataMapping\Reader\EntityReader;
use TBoileau\ORM\DataMapping\Reader\PrimaryKeyReader;
use TBoileau\ORM\DataMapping\Reader\RelationReader;
use TBoileau\ORM\DataMapping\Resolver\ColumnResolver;
use TBoileau\ORM\DataMapping\Resolver\MetadataResolver;
use TBoileau\ORM\DataMapping\Resolver\PrimaryKeyResolver;
use TBoileau\ORM\DataMapping\Resolver\RelationResolver;
use TBoileau\ORM\Tests\Fixtures\Entity\Bar;
use TBoileau\ORM\Tests\Fixtures\Entity\Baz;
use TBoileau\ORM\Tests\Fixtures\Entity\Foo;
use TBoileau\ORM\Tests\Fixtures\Entity\Quux;
use TBoileau\ORM\Tests\Fixtures\Entity\Qux;
use TBoileau\ORM\Tests\Fixtures\Repository\FooRepository;

/**
 * Class DataMappingTest
 * @package TBoileau\ORM\Tests
 */
class DataMappingTest extends TestCase
{
    /**
     * @dataProvider provideEntities
     * @param string $class
     * @param string $name
     * @param string|null $repository
     * @param array $columns
     * @param array $relations
     * @throws \ReflectionException
     */
    public function test get entity metadata(
        string $class,
        string $name,
        ?string $repository = null,
        array $columns = [],
        array $relations = []
    ) {
        $resolver = new MetadataResolver(
            new ColumnResolver(),
            new PrimaryKeyResolver()
        );

        $entityMetadata = $resolver->getMetadata($class);
        $this->assertInstanceOf(EntityMetadata::class, $entityMetadata);
        $this->assertEquals($name, $entityMetadata->getTableName());
        $this->assertEquals($repository, $entityMetadata->getRepositoryClass());

        $this->assertInstanceOf(PrimaryKeyMetadata::class, $entityMetadata->getPrimaryKey());

        $this->assertCount(count($columns), $entityMetadata->getColumns());

        $this->assertCount(count($relations), $entityMetadata->getRelations());
    }

    /**
     * @dataProvider provideEntities
     * @param string $class
     * @param string $name
     * @param string|null $repository
     * @throws \ReflectionException
     */
    public function test read entity annotation(string $class, string $name, ?string $repository = null)
    {
        $primaryKeyAnnotation = EntityReader::read(new \ReflectionClass($class));
        $this->assertInstanceOf(Entity::class, $primaryKeyAnnotation);
        $this->assertEquals($name, $primaryKeyAnnotation->name);
        $this->assertEquals($repository, $primaryKeyAnnotation->repositoryClass);
    }

    /**
     * @return Generator
     */
    public function provideEntities(): Generator
    {
        yield[
            Foo::class,
            "foo_entity",
            FooRepository::class,
            ["corge", "grault", "garply", "waldo", "fred", "plugh", "xyzzy", "thud"],
            ["quux", "bazes", "bars", "quxes"]
        ];
        yield[Baz::class, "baz", null, [], ["foos"]];
        yield[Bar::class, "bar", null, [], ["foo"]];
        yield[Quux::class, "quux", null, [], ["foos"]];
        yield[Qux::class, "qux", null, [], ["foos"]];
    }

    /**
     * @dataProvider providePrimaryKeys
     * @param string $class
     * @param string $property
     * @throws \ReflectionException
     */
    public function test read primary key annotation(string $class, string $property)
    {
        $primaryKeyAnnotation = PrimaryKeyReader::read(new \ReflectionProperty($class, $property));
        $this->assertInstanceOf(PrimaryKey::class, $primaryKeyAnnotation);
        $this->assertTrue($primaryKeyAnnotation->autoIncrement);
    }

    /**
     * @return Generator
     */
    public function providePrimaryKeys(): Generator
    {
        yield[Foo::class, "id"];
        yield[Baz::class, "id"];
        yield[Bar::class, "id"];
        yield[Quux::class, "id"];
        yield[Qux::class, "id"];
    }

    /**
     * @dataProvider provideColumns
     * @param string $class
     * @param string $property
     * @param string $name
     * @param string $type
     * @param null|int $length
     * @param bool $unique
     * @param null|int $precision
     * @param null|int $scale
     * @throws \ReflectionException
     */
    public function test read column annotation(
        string $class,
        string $property,
        string $name,
        string $type,
        ?int $length = null,
        bool $unique = false,
        ?int $precision = null,
        ?int $scale = null
    ) {
        $columnAnnotation = ColumnReader::read(new \ReflectionProperty($class, $property));
        $this->assertInstanceOf(Column::class, $columnAnnotation);
        $this->assertEquals($name, $columnAnnotation->name);
        $this->assertEquals($type, $columnAnnotation->type);
        $this->assertEquals($length, $columnAnnotation->length);
        $this->assertEquals($unique, $columnAnnotation->unique);
        $this->assertEquals($precision, $columnAnnotation->precision);
        $this->assertEquals($scale, $columnAnnotation->scale);
    }

    /**
     * @return Generator
     */
    public function provideColumns(): Generator
    {
        yield[Foo::class, "corge", "corge", "integer", null, true];
        yield[Foo::class, "grault", "grault_txt", "string", 100];
        yield[Foo::class, "garply", "garply", "float", null, false, 10, 5];
        yield[Foo::class, "waldo", "waldo", "boolean"];
        yield[Foo::class, "fred", "fred", "array"];
        yield[Foo::class, "plugh", "plugh", "text"];
        yield[Foo::class, "xyzzy", "xyzzy", "datetime"];
        yield[Foo::class, "thud", "thud", "date"];
    }

    /**
     * @dataProvider provideRelations
     * @param string $class
     * @param string $property
     * @param string $relation
     * @param string $targetEntity
     * @param string $targetProperty
     * @param string|null $name
     * @throws \ReflectionException
     */
    public function test read relation annotation(
        string $class,
        string $property,
        string $relation,
        string $targetEntity,
        string $targetProperty,
        ?string $name = null
    ) {
        $relationAnnotation = RelationReader::read(new \ReflectionProperty($class, $property));
        $this->assertInstanceOf($relation, $relationAnnotation);
        $this->assertEquals($targetEntity, $relationAnnotation->targetEntity);
        $this->assertEquals($targetProperty, $relationAnnotation->getTargetProperty());
        if ($name !== null) {
            $this->assertEquals($name, $relationAnnotation->name);
        }
    }

    /**
     * @return Generator
     */
    public function provideRelations(): Generator
    {
        yield[Foo::class, "bars", BelongsTo::class, Bar::class, "foo"];
        yield[Bar::class, "foo", HasOne::class, Foo::class, "bars", "bar_foo"];

        yield[Foo::class, "quxes", BelongsToMany::class, Qux::class, "foos"];
        yield[Qux::class, "foos", HasMany::class, Foo::class, "quxes", "qux_foos"];

        yield[Foo::class, "quux", HasOne::class, Quux::class, "foos", "foo_quux"];
        yield[Quux::class, "foos", BelongsTo::class, Foo::class, "quux"];

        yield[Foo::class, "bazes", HasMany::class, Baz::class, "foos", "foo_bazes"];
        yield[Baz::class, "foos", BelongsToMany::class, Foo::class, "bazes"];
    }
}
