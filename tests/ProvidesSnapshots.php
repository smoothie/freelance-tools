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

    /**
     * Sets the snapshot directory to a specific pattern.
     *
     * Trying to keep the structure simple and still kind a guessable what belongs to where.
     * The current structure implies the layer and that the next nested namespace item is the context.
     *
     * {$SNAPSHOT_DIRECTORY}/{$LAYER}/{$CONTEXT}/{$TESTFILE}/
     *
     * @example tests/Snapshots/Integration/Doctrine/TasksRepositoryTest/
     */
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

    /**
     * Sets the snapshot filename to a specific pattern.
     *
     * {$TEST_NAME_WITHOUT_CLASS}--{$DATA_OR_DATASET}__{$SNAPSHOT_COUNTER}
     *
     * @example testThatWeAreGood--forSure__1.yml
     */
    public function getSnapshotId(): string
    {
        return $this->name().'--'.$this->dataName().'__'.$this->snapshotIncrementor;
    }
}
