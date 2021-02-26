<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider;

use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DynamicSourceLocator
{
    /**
     * @var SmartFileInfo[]
     */
    private $fileInfos = [];

    /**
     * @var FileNodesFetcher
     */
    private $fileNodesFetcher;

    public function __construct(FileNodesFetcher $fileNodesFetcher)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
    }

    public function addFileInfo(SmartFileInfo $fileInfo): void
    {
        $this->fileInfos[] = $fileInfo;
    }

    public function setFileInfo(SmartFileInfo $fileInfo): void
    {
        $this->fileInfos = [$fileInfo];
    }

    public function provide(): SourceLocator
    {
        $sourceLocators = [];
        foreach ($this->fileInfos as $fileInfo) {
            $sourceLocators[] = new OptimizedSingleFileSourceLocator($this->fileNodesFetcher, $fileInfo->getRealPath());
        }

        return new AggregateSourceLocator($sourceLocators);
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    public function addFileInfos(array $fileInfos): void
    {
        $this->fileInfos = array_merge($this->fileInfos, $fileInfos);
    }
}
