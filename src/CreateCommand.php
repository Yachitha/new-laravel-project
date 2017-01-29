<?php
/**
 * New Laravel Project
 *   Generates a new Laravel project with specified version and composer packages.
 *
 * @author Patrick Organ <trick.developer@gmail.com>
 * @copyright Patrick Organ
 * @license MIT
 *
 * Portions of this code were borrowed from project laravel/installer (https://github.com/laravel/installer).
 * Some code @copyright Taylor Otwell
 *
 */

namespace Permafrost\NewLaravelProject\Console;

use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    protected $signature = 'create {name} {version} {--packages=}';

    protected $description = 'Create a new laravel project.';
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new Laravel project.')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addArgument('version', InputArgument::OPTIONAL)
            ->addOption  ('packages', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of additional composer packages to install')
            ;
            //->addOption('5.2', null, InputOption::VALUE_NONE, 'Installs the "5.2" release');
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectName = $input->getArgument('name');
        $laravelVersion = $input->getArgument('version') ? $input->getArgument('version') : '';
        $laravelVersionForComposer = (!empty($laravelVersion) ? $laravelVersion.'.*' : '');

        $this->verifyApplicationDoesntExist(
            $directory = getcwd() . DIRECTORY_SEPARATOR . $projectName,
            $output
        );

        $laravelVersionDisplay = (!empty($laravelVersion) ? ' (v'.$laravelVersion.')' : ' (latest version)');
        $output->writeln("<info>Creating new Laravel$laravelVersionDisplay project '$projectName'...</info>");

        $composer = $this->findComposerBinary();

        $packageList = $input->getOption('packages');
        $packages = [];
        if (!empty($packageList)) {
            $packagesArr = explode(',', $packageList);
            foreach($packagesArr as $package) {
                $package = trim($package);
                if (preg_match('/^[a-zA-Z0-9_\-]+\/[a-zA-Z0-9_\-]+$/', $package)==1) {
                    $packages[] = $package;
                } else {
                    $this->writeln('<comment>Skipping package with invalid name: '.$package.'.</comment>');
                }
            }
        }

        $createCommand = $composer.' create-project laravel/laravel ' . $projectName . ' '. $laravelVersionForComposer;

        $commands = [];
        if (count($packages) > 0)
            $commands[] = $composer.' require '.implode(' ', $packages);

        $process = new Process($createCommand, getcwd(), null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        if (!empty($commands)) {
            $output->writeln('<comment>Installing '.count($packages).' composer package'.(count($packages)>1?'s':'').'...</comment>');

            $process = new Process(implode(' && ', $commands), $directory, null, null, null);

            if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
                $process->setTty(true);
            }

            $process->run(function ($type, $line) use ($output) {
                $output->write($line);
            });
        }

        if (count($packages)>0) {
            $output->writeln('<comment>Installed Composer Packages:</comment>');
            foreach($packages as $package) {
                $output->writeln("<comment>    - $package</comment>");
            }
        }

        $output->writeln('<comment>Don\'t forget to install Service Providers for any installed packages.</info>');
        $output->writeln('');
        $output->writeln("<info>Laravel$laravelVersionDisplay project '$projectName' created.</info>");
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist($directory, OutputInterface $output)
    {
        if (is_dir($directory) || is_file($directory)) {
            throw new \Exception('Application already exists!');
        }
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposerBinary()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }

        return 'composer';
    }
}
