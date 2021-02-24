<?php

declare(strict_types=1);

namespace Rector\FamilyTree\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

final class FamilyRelationsAnalyzer
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
     * @return ClassReflection[]
     */
    public function getChildrenOfClass(string $className): array
    {
        $classReflection = $this->reflectionProvider->getClass($className);
        return $classReflection->getAncestors();
    }

    public function isParentClass(string $class): bool
    {
        foreach (get_declared_classes() as $declaredClass) {
            if ($declaredClass === $class) {
                continue;
            }

            if (! is_a($declaredClass, $class, true)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
