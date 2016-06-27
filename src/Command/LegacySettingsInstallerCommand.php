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
namespace Novactive\EzLegacyToolsBundle\Command;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * eZ Publish Legacy Setting Installer Command Class
 *
 * @category  Novactive
 * @package   Novactive.EzLegacyToolsBundle
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2016 Novactive
 */
class LegacySettingsInstallerCommand extends ContainerAwareCommand
{
    /**
     * Filesystem manipulation object
     *
     * @var Filesystem $filesystem
     */
    private $filesystem;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('ezpublish:legacybundles:install_settings')
             ->addOption(
                 'copy',
                 null,
                 InputOption::VALUE_NONE,
                 'Creates copies of the settings instead of using a symlink'
             )
             ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
             ->addOption(
                 'force',
                 null,
                 InputOption::VALUE_NONE,
                 'Force overwriting of existing directory (will be removed)'
             )->setDescription(
                 'Installs legacy settings (default: symlink) defined in Symfony bundles' .
                 ' into ezpublish_legacy/settings'
             )->setHelp(
                 <<<EOT
The command <info>%command.name%</info> installs <info>legacy settings</info> stored in a Symfony 2 bundle
into the ezpublish_legacy folder.
EOT
             );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = $this->getContainer()->get('filesystem');
        $options          = array(
            'copy'     => (bool)$input->getOption('copy'),
            'relative' => (bool)$input->getOption('relative'),
            'force'    => (bool)$input->getOption('force')
        );

        $legacySettingsLocator = $this->getContainer()->get('ezpublish_legacy.legacy_bundles.settings_locator');
        $kernel                = $this->getContainer()->get('kernel');

        foreach ($legacySettingsLocator->getSettingsDirectories($kernel->getBundles()) as $settingsDir) {
            $output->writeln('- ' . $this->removeCwd($settingsDir));
            try {
                $target = $this->linkLegacySettingsDir($settingsDir, $options);
                $output->writeln('  <info>' . ($options['copy'] ? 'Copied' : 'linked') . "</info> to $target</info>");
            } catch (RuntimeException $e) {
                $output->writeln('  <error>' . $e->getMessage() . '</error>');
            }
        }

        foreach ($legacySettingsLocator->getSettingsFiles($kernel->getBundles()) as $sourceFile => $destFile) {
            $output->writeln('- ' . $this->removeCwd($sourceFile));
            try {
                $target = $this->linkLegacySettingsFile($sourceFile, $destFile, $options);
                $output->writeln('  <info>' . ($options['copy'] ? 'Copied' : 'linked') . "</info> to $target</info>");
            } catch (RuntimeException $e) {
                $output->writeln('  <error>' . $e->getMessage() . '</error>');
            }
        }
    }

    /**
     * Links the legacy settings at $path into ezpublish_legacy/settings
     *
     * @param string $settingsPath Absolute path to a legacy settings folder
     * @param array  $options      installation options
     *
     * @return string The resulting link/directory
     *
     * @throws \RuntimeException If a target link/directory exists and $options[force] isn't set to true
     */
    protected function linkLegacySettingsDir($settingsPath, array $options = array())
    {
        $options              += array('force' => false, 'copy' => false, 'relative' => false);
        $filesystem           = $this->filesystem;
        $legacyRootDir        = rtrim($this->getContainer()->getParameter('ezpublish_legacy.root_dir'), '/');
        $relativeSettingsPath = $filesystem->makePathRelative($settingsPath, realpath("$legacyRootDir/settings/"));
        $targetPath           = "$legacyRootDir/settings/" . basename($settingsPath);

        if (file_exists($targetPath) && $options['copy']) {
            if (!$options['force']) {
                throw new RuntimeException("Target directory $targetPath already exists");
            }
            $filesystem->remove($targetPath);
        }

        $this->prepareInstall($settingsPath, $relativeSettingsPath, $options, $targetPath);

        $this->install($settingsPath, $relativeSettingsPath, $options, $targetPath);

        return $targetPath;
    }

    /**
     * Links the legacy file at $path into ezpublish_legacy
     *
     * @param string $sourcePath Absolute path to a legacy file
     * @param string $destPath   Relative path where the file should be installed
     * @param array  $options    Installation options
     *
     * @return string The resulting link/directory
     *
     * @throws \RuntimeException If a target link/directory exists and $options[force] isn't set to true
     */
    protected function linkLegacySettingsFile($sourcePath, $destPath, array $options = array())
    {
        $options              += array('force' => false, 'copy' => false, 'relative' => false);
        $filesystem           = $this->filesystem;
        $legacyRootDir        = rtrim($this->getContainer()->getParameter('ezpublish_legacy.root_dir'), '/');
        $relativeSourcePath   = rtrim($filesystem->makePathRelative($sourcePath, realpath("$legacyRootDir")), '/');
        $targetPath           = "$legacyRootDir/" . $destPath;

        if (file_exists($targetPath) && $options['copy']) {
            if (!$options['force']) {
                throw new RuntimeException("Target file $targetPath already exists");
            }
            $filesystem->remove($targetPath);
        }

        $this->prepareInstall($sourcePath, $relativeSourcePath, $options, $targetPath);

        $this->install($sourcePath, $relativeSourcePath, $options, $targetPath, true);

        return $targetPath;
    }

    /**
     * Removes the cwd from $path
     *
     * @param string $path path to clean
     *
     * @return string
     */
    private function removeCwd($path)
    {
        return str_replace(getcwd() . '/', '', $path);
    }

    /**
     * Install settings
     *
     * @param string  $sourcePath         absolute path to settings to install
     * @param string  $relativeSourcePath relative path to settings to install
     * @param array   $options            installation options
     * @param string  $targetPath         path where the settings folder should be copied/symlinked
     * @param boolean $isFile             indicate if resource to install is a file
     */
    protected function install($sourcePath, $relativeSourcePath, array $options, $targetPath, $isFile = false)
    {
        $filesystem = $this->filesystem;
        if (!$options['copy']) {
            try {
                $filesystem->symlink(
                    $options['relative'] ? $relativeSourcePath : $sourcePath,
                    $targetPath
                );
            } catch (IOException $e) {
                $options['copy'] = true;
            }
        }

        if ($options['copy']) {
            if ($isFile) {
                $filesystem->copy($sourcePath, $targetPath);
            } else {
                $filesystem->mkdir($targetPath, 0777);
                $filesystem->mirror($sourcePath, $targetPath, Finder::create()->in($sourcePath));
            }
        }
    }

    /**
     * Prepare installation by cleaning target directory
     *
     * @param string $sourcePath         absolute path to settings to install
     * @param string $relativeSourcePath relative path to settings to install
     * @param array  $options            installation options
     * @param string $targetPath         path where the settings folder should be copied/symlinked
     */
    protected function prepareInstall($sourcePath, $relativeSourcePath, array $options, $targetPath)
    {
        $filesystem = $this->filesystem;
        if (file_exists($targetPath) && !$options['copy']) {
            if (is_link($targetPath)) {
                $existingLinkTarget = readlink($targetPath);
                if ($existingLinkTarget != $sourcePath && $existingLinkTarget != $relativeSourcePath &&
                    !$options['force']
                ) {
                    throw new RuntimeException("Target $targetPath already exists with a different target");
                }
            } else {
                if (!$options['force']) {
                    throw new RuntimeException("Target $targetPath already exists with a different target");
                }
            }
            $filesystem->remove($targetPath);
        }
    }
}
