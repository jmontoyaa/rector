<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodVendorLockResolver extends AbstractNodeVendorLockResolver
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
     * Checks for:
     * - interface required methods
     * - abstract classes reqired method
     *
     * Prevent:
     * - removing class methods, that breaks the code
     */
    public function isRemovalVendorLocked(ClassMethod $classMethod): bool
    {
        $classMethodName = $this->nodeNameResolver->getName($classMethod);

        /** @var Scope $scope */
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return false;
        }

        if ($this->isMethodVendorLockedByInterface($classReflection, $classMethodName)) {
            return true;
        }

        if ($classReflection->getParents() === []) {
            return false;
        }

        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        $classReflection = $this->reflectionProvider->getClass($className);

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (! $parentClassReflection->hasMethod($classMethodName)) {
                continue;
            }

            $methodReflection = $classReflection->getNativeMethod($classMethodName);
            if (! $methodReflection instanceof PhpMethodReflection) {
                continue;
            }

            if ($methodReflection->isAbstract()) {
                return true;
            }
        }

        return false;
    }
}
