<?php

namespace Symfony\Component\HttpKernel\Bundle;

use Composer\InstalledVersions;
use Symfony\Component\DependencyInjection\Kernel\BundleInterface as BaseBundleInterface;

$httpKernelVersion = InstalledVersions::getVersion('symfony/http-kernel');

if (version_compare($httpKernelVersion, '8.1', '>=')) {
    /**
     * This is a snapshot of the {@see \Symfony\Component\HttpKernel\Bundle\BundleInterface} without its deprecation,
     * since extending Bundle is still needed to support older Symfony versions.
     */
    interface BundleInterface extends BaseBundleInterface
    {
        public function getNamespace(): string;
    }
} else {
    // Fix psalm on old releases by adding the new BaseBundleInterface symbol.
    class_alias(BundleInterface::class, BaseBundleInterface::class);
}
