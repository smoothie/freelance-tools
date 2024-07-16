<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Contracts;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('controller.service_arguments')]
interface ControllerContract
{
}
