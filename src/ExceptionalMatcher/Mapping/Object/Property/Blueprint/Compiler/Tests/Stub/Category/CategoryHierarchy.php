<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Category;

/** Declared in a trait, so that self and parent stay unresolved on every PHP version */
trait CategoryHierarchy
{
    private ?self $parentSubcategory;

    private ?parent $rootCategory;
}
