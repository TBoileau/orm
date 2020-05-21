<?php

namespace TBoileau\ORM\Tests\Fixtures;

use TBoileau\ORM\DataMapping\Annotation as ORM;

/**
 * Class Foo
 * @package TBoileau\ORM\Tests\Fixtures
 */
class Foo
{
    /**
     * @var int
     * @ORM\Column(type="integer", unique=true)
     */
    public int $corge;

    /**
     * @var string
     * @ORM\Column(name="grault_txt", type="string", length=100)
     */
    public string $grault;

    /**
     * @var float
     * @ORM\Column(type="float", precision=10, scale=5)
     */
    public float $garply;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    public bool $waldo;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    public array $fred;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    public string $plugh;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    public \DateTime $xyzzy;

    /**
     * @var \DateTime
     * @ORM\Column(type="date")
     */
    public \DateTime $thud;

    /**
     * @var Quux
     * @ORM\HasOne(targetEntity="TBoileau\ORM\Tests\Fixtures\Quux", inversedBy="foos")
     */
    public Quux $quux;

    /**
     * @var Baz[]
     * @ORM\HasMany(targetEntity="TBoileau\ORM\Tests\Fixtures\Baz", inversedBy="foos")
     */
    public array $bazes;

    /**
     * @var Bar[]
     * @ORM\BelongsTo(targetEntity="TBoileau\ORM\Tests\Fixtures\Bar", mappedBy="foo")
     */
    public array $bars;

    /**
     * @var Qux[]
     * @ORM\BelongsToMany(targetEntity="TBoileau\ORM\Tests\Fixtures\Qux", mappedBy="foos")
     */
    public array $quxes;
}
