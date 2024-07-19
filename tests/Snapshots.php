<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Tests;

interface Snapshots
{
    public function getSnapshotDirectory(): string;

    public function getSnapshotId(): string;
}
