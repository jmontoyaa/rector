<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PHPStan\Reflection\ClassReflection;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

abstract class AbstractNodeVendorLockResolver
{
    /**
     * @var ClassManipulator
     */
    protected $classManipulator;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    /**
     * @required
     */
    public function autowireAbstractNodeVendorLockResolver(
        NodeRepository $nodeRepository,
        ClassManipulator $classManipulator,
        NodeNameResolver $nodeNameResolver,
        FamilyRelationsAnalyzer $familyRelationsAnalyzer
    ): void {
        $this->nodeRepository = $nodeRepository;
        $this->classManipulator = $classManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }

    protected function hasParentClassChildrenClassesOrImplementsInterface(ClassReflection $classReflection): bool
    {
        if ($classReflection->isClass()) {
            if ($classReflection->getParents()) {
                return true;
            }

            if ($classReflection->getInterfaces() !== []) {
                return true;
            }

            return $classReflection->getAncestors() !== [];
        }

        if ($classReflection->isInterface() && $classReflection->getInterfaces()) {
            return true;
        }

        return false;
    }

    protected function isMethodVendorLockedByInterface(ClassReflection $classReflection, string $methodName): bool
    {
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
                return true;
            }
        }

        return false;
    }
}
