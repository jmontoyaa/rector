<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver\TraitTypeResolverTest
 */
final class TraitTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [Trait_::class];
    }

    /**
     * @param Trait_ $traitNode
     */
    public function resolve(Node $traitNode): Type
    {
        $traitName = (string) $traitNode->namespacedName;
        if (! $this->reflectionProvider->hasClass($traitName)) {
            return new MixedType();
        }

        $traitReflection = $this->reflectionProvider->getClass($traitName);

        $types = [];
        $types[] = new ObjectType($traitName);

        foreach ($traitReflection->getTraits() as $usedTraitReflection) {
            $types[] = new ObjectType($usedTraitReflection->getName());
        }

        if (count($types) === 1) {
            return $types[0];
        }

        if (count($types) > 1) {
            return new UnionType($types);
        }

        return new MixedType();
    }
}
