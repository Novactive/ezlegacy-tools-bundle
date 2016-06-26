<?php
/**
 * File part of the Novactive eZ Publish Legacy Tools Bundle
 *
 * @category  Novactive
 * @package   Novactive.EzLegacyToolsBundle
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2016 Novactive
 * @license   https://opensource.org/licenses/MIT MIT
 */
namespace Novactive\EzLegacyToolsBundle\Locator;

use RuntimeException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * eZ Publish Legacy Settings Locator Interface
 *
 * @category  Novactive
 * @package   Novactive.EzLegacyToolsBundle
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2016 Novactive
 */
interface LegacySettingsLocatorInterface
{
    /**
     * Returns the path to legacy settings directories within $bundles
     *
     * @param array $bundles array of bundles
     *
     * @return array An array of path to legacy settings
     */
    public function getSettingsDirectories($bundles);

    /**
     * Returns the path to legacy configuration files within $bundles
     *
     * @param array $bundles array of bundles
     *
     * @return array An array of path to legacy configuration files
     */
    public function getSettingsFiles($bundles);
}
