<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

final class EntityWithHook
{
    public string $title {
        set {
            $validate = Validation::createCallable(new NotBlank());

            /** @var string $title */
            $title = $validate($value);

            $this->title = $title;
        }
    }

    /** @psalm-suppress PossiblyUnusedReturnValue */
    public static function createWithTitle(string $title): self
    {
        $product = new self();
        $product->title = $title;

        return $product;
    }
}
