<?php

declare(strict_types=1);

namespace Smoothie\ContractorTools\Contracts;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('smoothie.contractor-tools.controllers')]
#[AutoconfigureTag('controller.service_arguments')]
interface Controller
{
}
