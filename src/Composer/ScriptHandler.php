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
namespace Novactive\EzLegacyToolsBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as DistributionBundleScriptHandler;
use Composer\Script\Event;

/**
 * eZ Publish Legacy Tools Composer Script class
 *
 * @category  Novactive
 * @package   Novactive.EzLegacyToolsBundle
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2016 Novactive
 */
class ScriptHandler extends DistributionBundleScriptHandler
{
    /**
     * Call eZ Publish Legacy Settings Installer Command
     *
     * @param Event $event composer command even
     */
    public static function installLegacyBundlesSettings(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir  = self::getConsoleDir($event, 'legacy-settings-install');
        $symlink = '';
        $force   = '';
        if ($options['legacy-settings-install']) {
            if (in_array('force', $options['legacy-settings-install'])) {
                $force = '--force ';
            }
            if (in_array('relative', $options['legacy-settings-install'])) {
                $symlink = '--relative ';
            }
        }

        if (!is_dir($consoleDir)) {
            echo 'The symfony-bin-dir (' . $consoleDir . ') specified in composer.json was not found in ' .
                 getcwd() . ', can not install settings.' . PHP_EOL;

            return;
        }

        static::executeCommand($event, $consoleDir, 'ezpublish:legacybundles:install_settings ' . $symlink . $force);
    }

    /**
     * Call eZ Publish Legacy Script Execution Command
     *
     * @param Event $event composer command even
     */
    public static function executeLegacyScripts(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir  = self::getConsoleDir($event, 'legacy-scripts-exec');

        if ($options['legacy-scripts-execution']) {
            foreach ($options['legacy-scripts-execution'] as $script) {
                static::executeCommand($event, $consoleDir, 'ezpublish:legacy:script ' . $script);
            }
        }
    }
}
