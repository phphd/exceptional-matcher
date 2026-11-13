<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\DependencyInjection\Definition;
use Webmozart\Assert\Assert;

use function array_filter;
use function array_map;
use function array_unique;
use function array_values;
use function sprintf;

/** @internal */
final class ExceptionMatchFormatterDefinition
{
    public function __construct(
        private readonly Definition $definition,
        private readonly string $serviceId,
        /** @var list<string> */
        private readonly array $aliasIds,
    ) {
    }

    public function addClassNameTags(): void
    {
        foreach ($this->genericDeclaredResultTypeArguments() as $resultType) {
            $this->addClassNameTag(ExceptionMatchFormatter::class.'<?,'.$resultType.'>');
        }
    }

    private function addClassNameTag(string $tag): void
    {
        /** @var array{array<string,mixed>} $formatterTag */
        $formatterTag = $this->definition->getTag(ExceptionMatchFormatter::class);
        Assert::count($formatterTag, 1);

        [$formatterTagAttributes] = $formatterTag;

        $this->definition->addTag($tag, $formatterTagAttributes);
    }

    /** @return list<string> */
    private function genericDeclaredResultTypeArguments(): array
    {
        $formatterClassNames = $this->implementedFormatterClassNames();

        if ([] === $formatterClassNames) {
            throw new LogicException(sprintf('Formatter "%s" must declare its generic type. Add #[AsAlias(ExceptionMatchFormatter::class.\'<TException,TResult>\')] to it.', $this->serviceId));
        }

        return array_values(array_unique(array_map(
            static fn(ExceptionMatchFormatterClassName $formatterClassName): string => $formatterClassName->getResultType(),
            $formatterClassNames,
        )));
    }

    /** @return list<ExceptionMatchFormatterClassName> */
    private function implementedFormatterClassNames(): array
    {
        $formatterClassNames = array_filter($this->serviceIds(), ExceptionMatchFormatterClassName::isFormatterClassName(...));

        return array_values(array_map(
            static fn(string $className): ExceptionMatchFormatterClassName => new ExceptionMatchFormatterClassName($className),
            $formatterClassNames,
        ));
    }

    /** @return non-empty-list<string> */
    private function serviceIds(): array
    {
        return [$this->serviceId, ...$this->aliasIds];
    }
}
