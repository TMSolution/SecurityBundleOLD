<?php

/**
 * Copyright (c) 2014, TMSolution
 * All rights reserved.
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

namespace Core\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Core\SecurityBundle\Annotations\PermissionCreator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;

/**
 * Adds security annotations to controllers.
 * 
 * @author KrzysiekPiasecki
 */
class SecurityCommand extends ContainerAwareCommand
{

    const USE_CASE_BUNDLE = 1;
    const USE_CASE_CONTROLLER = 2;

    protected $backupDirectoryName = "Backup";
    protected $backupControllerDirectoryName = "Controller";

    protected function configure()
    {
        $this->setName('generate:security')
                ->addArgument('name', InputArgument::REQUIRED, 'A bundle name, a namespace, or a class name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $name = $input->getArgument('name');

        try {
            $useCase = self::USE_CASE_BUNDLE;
            $bundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('name'));
        } catch (\InvalidArgumentException $e) {
            $useCase = self::USE_CASE_CONTROLLER;
        }

        if ($useCase === self::USE_CASE_CONTROLLER) {
            list($bundle, $name) = $this->parseShortcutNotation($input->getArgument('name'));
            $output->writeln(sprintf('Generating security for controller "<info>%s</info>"', $bundle . ":" . $name));

            if (is_string($bundle)) {
                $bundle = Validators::validateBundleName($bundle);
                try {
                    $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
                } catch (\Exception $e) {
                    $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
                }
            }            
            $bundleDirectory = $bundle->getPath();            
            $controllerPath = $this->getControllerPath($bundleDirectory, $name);    
            $copyControllerPath = $this->getCopyControllerPath($bundleDirectory, $name);

            if (file_exists($controllerPath) === false) {
                throw new \RuntimeException(sprintf('Controller "%s" does not exists', $name));
            }

            if (file_exists($copyControllerPath) === true) {
                throw new \RuntimeException(sprintf('Copy of controller "%s" exist. Remove this from the backup.', $name));
            }
            $creator = new PermissionCreator($controllerPath);
            $buckupDirectory = $this->createBackupDirectory($bundleDirectory);
            $this->createCopy($controllerPath, $copyControllerPath);
            @$annotatedSource = $creator->annotate();        
            $result = file_put_contents($controllerPath, $annotatedSource);
            if ($result === false) {
                $this->throwIOException();
            }
            $output->writeln("Files were modified");

            return 0;
        } else {
            $output->writeln(sprintf('Generating security for bundle "<info>%s</info>"', $bundle->getName()));
            $bundleDirectory = $bundle->getPath();
            if ($this->backupExists($bundleDirectory) == true) {
                throw new \RuntimeException(sprintf('Backup exists!'));
            }
            $buckupDirectory = $this->createBackupDirectory($bundleDirectory);
            $controllerDirectory = $this->getControllerDirectory($bundleDirectory);
            foreach (new \DirectoryIterator($controllerDirectory) as $item) {
                $fileInfo = $item->getFileInfo();
                if ($fileInfo->isFile() == true) {
                    $copyControllerPath = $buckupDirectory . DIRECTORY_SEPARATOR . $fileInfo->getFilename();
                    $this->createCopy($fileInfo->getRealPath(), $copyControllerPath);
                    $creator = new PermissionCreator($fileInfo->getRealPath());
                    @$annotatedSource = $creator->annotate();
                    $result = file_put_contents($fileInfo->getRealPath(), $annotatedSource);
                    if ($result === false) {
                        $this->throwIOException();
                    }
                }
            }
            $output->writeln("Files were modified");

            return 0;
        };
    }

    protected function backupExists($bundleDirectory)
    {
        $backupDirectory = $this->getBackupDirectory($bundleDirectory);
        return is_dir($backupDirectory);
    }

    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The controller name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Post)', $entity));
        }

        return [substr($entity, 0, $pos), substr($entity, $pos + 1)];
    }

    protected function createBackupDirectory($bundleDirectory)
    {
        $backupDirectory = $this->getBackupDirectory($bundleDirectory);
        if (is_dir($backupDirectory) === false) {
            $created = mkdir($backupDirectory, 0777, true);
            if ($created === false) {
                $this->throwIOException("Creating backup directory failed");
            }
        }
        return $backupDirectory;
    }

    protected function createCopy($controllerFile, $copyControllerPath)
    {
        $copied = copy($controllerFile, $copyControllerPath);
        if ($copied === false) {
            $this->throwIOException("Copying controller failed");
        }
    }

    protected function getControllerDirectory($bundleDirectory)
    {
        return $bundleDirectory
                . DIRECTORY_SEPARATOR
                . "Controller";
    }

    protected function getBackupDirectory($bundleDirectory)
    {
        return $bundleDirectory
                . DIRECTORY_SEPARATOR
                . $this->backupDirectoryName
                . DIRECTORY_SEPARATOR
                . $this->backupControllerDirectoryName;
    }

    protected function throwIOException($message)
    {
        if ($message === "") {
            $message = "Failed or interrupted I/O operation";
        }
        throw new \RuntimeException($message);
    }

    protected function getControllerPath($bundleDirectory, $controllerShortName)
    {
        return $bundleDirectory
                . DIRECTORY_SEPARATOR
                . 'Controller'
                . DIRECTORY_SEPARATOR
                . $controllerShortName
                . 'Controller.php';
    }

    protected function getCopyControllerPath($bundleDirectory, $controllerShortName)
    {
        return $bundleDirectory
                . DIRECTORY_SEPARATOR
                . $this->backupDirectoryName
                . DIRECTORY_SEPARATOR
                . $this->backupControllerDirectoryName
                . DIRECTORY_SEPARATOR
                . $controllerShortName
                . 'Controller.php';
    }

}
