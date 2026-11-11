<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\Stub\Category;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests\PropertyInclusionRule;
use PhPhD\ExceptionalMatcher\Tests\Probe\ProbeOf;

/** Declared in a trait, so that self and parent stay unresolved on every PHP version */
trait CategoryHierarchy
{
    #[ProbeOf(PropertyInclusionRule::RelativeClassTypedPropertyIsIncluded)]
    private ?self $parentSubcategory;

    #[ProbeOf(PropertyInclusionRule::RelativeClassTypedPropertyIsIncluded)]
    private ?parent $rootCategory;
}
