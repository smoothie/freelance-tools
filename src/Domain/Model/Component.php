<?php

declare(strict_types=1);

namespace App\Domain\Model;

interface Component
{
    public function fileName(string $extension): string;

    public function title(): string;

    public function template(): string;

    public function context(): array;

    public function setPageNumber(int $pageNumber): void;
}
