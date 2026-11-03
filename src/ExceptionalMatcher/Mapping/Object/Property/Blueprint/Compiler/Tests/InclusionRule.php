<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\Compiler\Tests;

enum InclusionRule
{
    case CatchAttributedPropertyIsIncluded;
    case LooselyTypedPropertyIsIncluded;
    case IterableTypedPropertyIsIncluded;
    case InterfaceTypedPropertyIsIncluded;
    case NonFinalClassTypedPropertyIsIncluded;
    case FinalExceptionMappedClassTypedPropertyIsIncluded;
    case FinalNonExceptionMappedClassTypedPropertyIsSkipped;
    case ScalarTypedPropertyIsSkipped;
    case EnumTypedPropertyIsSkipped;
    case BuiltInClassTypedPropertyIsSkipped;
    case RelativeClassTypedPropertyIsIncluded;
}
