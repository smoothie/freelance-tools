<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Model\Common\DateTime;

interface Component
{
    public function fileName(string $extension, ?DateTime $now = null): string;

    public function title(): string;

    public function template(): string;

    /**
     * @return array<string, mixed>
     */
    public function context(): array;

    public function setPageNumber(int $pageNumber): void;
}
