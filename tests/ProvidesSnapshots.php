<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests;

use Spatie\Snapshots\MatchesSnapshots;

/**
 * @implements Snapshots
 */
trait ProvidesSnapshots
{
    use MatchesSnapshots;

    public function getSnapshotDirectory(): string
    {
        $reflectedClass = (new \ReflectionClass($this));

        $namespace = $reflectedClass->getNamespaceName();
        $namespacePieces = explode('\\', $namespace);

        $layer = match (true) {
            \in_array('Unit', $namespacePieces, true) => 'Unit',
            \in_array('Acceptance', $namespacePieces, true) => 'Acceptance',
            \in_array('Integration', $namespacePieces, true) => 'Integration',
            default => 'Unknown',
        };

        $context = end($namespacePieces);
        $context = match (\in_array($layer, $namespacePieces, true)) {
            true => $namespacePieces[array_search($layer, $namespacePieces, true) + 1] ?? $context,
            default => $context,
        };

        return __DIR__.\DIRECTORY_SEPARATOR
            .'Snapshots'.\DIRECTORY_SEPARATOR
            .$layer.\DIRECTORY_SEPARATOR
            .$context.\DIRECTORY_SEPARATOR
            .$reflectedClass->getShortName();
    }

    public function getSnapshotId(): string
    {
        return $this->name().'--'.$this->dataName().'__'.$this->snapshotIncrementor;
    }
}
