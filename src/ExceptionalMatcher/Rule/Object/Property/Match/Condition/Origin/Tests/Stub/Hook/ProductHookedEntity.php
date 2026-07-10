<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub\Hook;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

final class ProductHookedEntity
{
    public string $title {
        set {
            $validate = Validation::createCallable(new NotBlank());

            /** @var string $title */
            $title = $validate($value);

            $this->title = $title;
        }
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
