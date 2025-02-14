<?php

declare(strict_types=1);

namespace App\Application;

interface ApplicationInterface
{
        public function generateTimesheet(GenerateTimesheet $command): void;
    //    public function importTasks(WorkTimeProvider $workTimeProvider): void;
    //    public function render(Component $component): void;
}
