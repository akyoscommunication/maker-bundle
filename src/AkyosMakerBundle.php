<?php

namespace Akyos\MakerBundle;

use Akyos\MakerBundle\DependencyInjection\AkyosMakerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkyosMakerBundle extends Bundle
{
    protected function createContainerExtension(): ?ExtensionInterface
    {
        return new AkyosMakerExtension();
    }
}