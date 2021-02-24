<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodReturnVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(ClassMethod $classMethod): bool
    {
        /** @var Scope $scope */
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        if (! $this->hasParentClassChildrenClassesOrImplementsInterface($classReflection)) {
            return false;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        if ($classReflection->getParentClass() instanceof ClassReflection) {
            return $this->isVendorLockedByParentClass($classReflection, $methodName);
        }

        if ($classReflection->isTrait()) {
            return false;
        }

        return $this->isMethodVendorLockedByInterface($classReflection, $methodName);
    }

    private function isVendorLockedByParentClass(ClassReflection $classReflection, string $methodName): bool
    {
        $parentClass = $this->nodeRepository->findClass($classReflection->getName());
        if ($parentClass !== null) {
            $parentClassMethod = $parentClass->getMethod($methodName);
            // validate type is conflicting
            // parent class method in local scope → it's ok
            if ($parentClassMethod !== null) {
                return $parentClassMethod->returnType !== null;
            }

            // if not, look for it's parent parent
        }

        // validate type is conflicting
        // parent class method in external scope → it's not ok
        return $classReflection->hasMethod($methodName);
    }
}
