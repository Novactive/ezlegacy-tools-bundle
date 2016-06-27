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

use DirectoryIterator;
use RuntimeException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * eZ Publish Legacy Settings Locator Class
 *
 * @category  Novactive
 * @package   Novactive.EzLegacyToolsBundle
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2016 Novactive
 */
class LegacySettingsLocator implements LegacySettingsLocatorInterface
{
    /**
     * List of settings directories allowed to be installed
     *
     * @var array $allowedSettingsDirs
     */
    protected $allowedSettingsDirs = array(
        'override',
        'siteaccess'
    );

    /**
     * List of settings files allowed to be installed
     *
     * @var array $allowedSettingsFiles
     */
    protected $allowedSettingsFiles = array(
        'config.php' => 'config.php',
    );

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException if two bundles contain a legacy_settings folder
     */
    public function getSettingsDirectories($bundles)
    {
        $directories           = array();
        $legacySettingsBundles = $this->retrieveLegacySettingsDir($bundles);

        foreach ($legacySettingsBundles as $legacySettingsBundle) {
            foreach (new DirectoryIterator($legacySettingsBundle) as $item) {
                if (!$item->isDir() || $item->isDot()) {
                    continue;
                }

                if (in_array($item->getBasename(), $this->allowedSettingsDirs)) {
                    $directories[] = $item->getPathname();
                }
            }
        }

        return $directories;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException if two bundles contain a legacy_settings folder
     */
    public function getSettingsFiles($bundles)
    {
        $files = array();

        foreach ($this->retrieveLegacySettingsDir($bundles) as $legacySettingsBundle) {
            foreach ($this->allowedSettingsFiles as $sourcePath => $destPath) {
                if (file_exists("$legacySettingsBundle$sourcePath")) {
                    $files["$legacySettingsBundle$sourcePath"] = $destPath;
                }
            }
        }

        return $files;
    }

    /**
     * Check if a legacy_settings folder exists in project specific bundles
     *
     * @param array $bundles array of bundles
     *
     * @return array
     */
    protected function retrieveLegacySettingsDir($bundles)
    {
        $legacySettingsBundles = array();
        foreach ($bundles as $bundle) {
            $bundlePath = rtrim($bundle->getPath(), '/\\');
            $legacyPath = "$bundlePath/legacy_settings/";

            if (is_dir($legacyPath)) {
                $legacySettingsBundles[] = $legacyPath;
            }
        }

        if (count($legacySettingsBundles) > 1) {
            throw new RuntimeException("You can only have one bundle with legacy settings");
        }

        return $legacySettingsBundles;
    }
}
