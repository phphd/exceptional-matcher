<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Category;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\InclusionRule;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\ProbeOf;

/** Declared in a trait, so that self and parent stay unresolved on every PHP version */
trait CategoryHierarchy
{
    #[ProbeOf(InclusionRule::RelativeClassTypedPropertyIsIncluded)]
    private ?self $parentSubcategory;

    #[ProbeOf(InclusionRule::RelativeClassTypedPropertyIsIncluded)]
    private ?parent $rootCategory;
}
