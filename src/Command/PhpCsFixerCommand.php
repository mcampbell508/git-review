<?php

namespace GitReview\Command;

use GitReview\File\File;
use GitReview\File\FilesFinder;
use GitReview\VersionControl\GitBranch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

class PhpCsFixerCommand extends Command
{
    const CONFIG_PATH = 'yml-config-path';
    private $useCache = false;
    private $verbose = false;
    private $allowRisky = false;
    private $dryRun = false;

    protected function configure()
    {
        $this->setName('tools:php-cs-fixer');

        $this->setDescription('Run Php-CS-Fixer on only the changed files on a Git branch.');

        $this->addArgument(
            self::CONFIG_PATH,
            InputArgument::OPTIONAL,
            'The path to the git-review configuration file, named `git-review.yml(.dist)`'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $currentWorkingDirectory = getcwd();

        $io->note("Current working directory: $currentWorkingDirectory");

        $configPath = $this->getYamlConfigurationFilePath(
            $currentWorkingDirectory,
            $input->getArgument(self::CONFIG_PATH)
        );

        if (!$configPath) {
            $io->error(
                "Configuration not found! Please ensure you setup the config file and run this command in the "
                . "appropriate root of your project"
            );
            exit(1);
        }

        $io->note("Using configuration file: $configPath");

        $branch = new GitBranch($currentWorkingDirectory);

        try {
            $config = Yaml::parse(file_get_contents($configPath));
        } catch (ParseException $exception) {
            $io->error('Unable to parse the YAML string: %s', $exception->getMessage());

            exit(1);
        }

        $phpCsFixerConfig = $config["tools"]["php_cs_fixer"];

        $io->title('Retrieving changed files on branch using the following filters:');
        $io->listing($phpCsFixerConfig["paths_to_scan"]);
        /** @var Collection $changedFiles */
        $changedFiles = $branch->getChangedFiles();

        $filesFinder = new FilesFinder($changedFiles, $phpCsFixerConfig["paths_to_scan"]);

        $output->writeln("<options=bold,underscore>Found files...</>\n");

        $filePaths = $filesFinder->getFoundFiles()
            ->sortBy(function (File $file) {
                return $file->getRelativePath();
            })
            ->reject(function (File $file) {
                return !file_exists($file->getRelativePath());
            })
            ->map(function (File $file) {
                return $file->getRelativePath();
            })
            ->unique()
            ->reduce(function (string $paths, string $file) use ($io) {
                $io->writeln("<info>{$file}</info>");

                if ($paths === '') {
                    return $paths = $file;
                }

                return $paths .= " {$file}";
            }, '');

        $command = $this->getCommand($phpCsFixerConfig, $filePaths, $branch->getName());

        $output->writeln("\n<options=bold,underscore>Running command:</>\n");
        $io->writeln("<info>$command</info>\n");
        $process = new \Symfony\Component\Process\Process($command);
        $process->start();

        $iterator = $process->getIterator($process::ITER_KEEP_OUTPUT);

        foreach ($iterator as $data) {
            echo $data;
        }

        if ($process->getExitCode() !== 0) {
            $io->writeln("\n<error>Php-CS-Fixer failed. Locally, you should run:</error>\n");

            $io->writeln(
                "<fg=red>php vendor/bin/php-cs-fixer fix {$filePaths} --config=.php_cs.dist -v</>"
            );

            exit($process->getExitCode());
        }

        $io->newLine();

        return $io->success("Php-CS-Fixer passed, good job!!!!");
    }

    protected function getYamlConfigurationFilePath(string $currentWorkingDirectory, string $configPath = null): string
    {
        if ($configPath) {
            return $configPath;
        }

        $finder = new Finder();
        $iterator = $finder
            ->files()
            ->name('git-review.yml.dist')
            ->name('git-review.yml')
            ->depth(0)
            ->in($currentWorkingDirectory);

        return (new Collection($iterator))
            ->mapWithKeys(function (\Symfony\Component\Finder\SplFileInfo $fileInfo) {
                return [$fileInfo->getFileName() => $fileInfo->getRealPath()];
            })
            ->sort()
            ->first(function ($item, $key) {
                return $key === 'git-review.yml' || $key === 'git-review.yml.dist';
            });
    }

    protected function getCommand(array $phpCsFixerConfig, string $filePaths, string $branchName): string
    {
        $phpCsFixerBin = $phpCsFixerConfig["bin_path"] ?? "vendor/bin/php-cs-fixer";
        $phpCsFixerConfigPath = $phpCsFixerConfig["config_path"] ?? ".php_cs.dist";

        $cmd = "php {$phpCsFixerBin} fix";

        if ($branchName !== 'master') {
            $cmd .= " {$filePaths}";
        }

        $cmd .= " --config={$phpCsFixerConfigPath}";

        if (isset($phpCsFixerConfig["verbose"]) && $phpCsFixerConfig["verbose"]) {
            $cmd .= " -v";
            $this->verbose = true;
        }

        if (isset($phpCsFixerConfig["dry_run"]) && $phpCsFixerConfig["dry_run"]) {
            $cmd .= " --dry-run";
            $this->dryRun = true;
        }

        if (isset($phpCsFixerConfig["use_cache"]) && $phpCsFixerConfig["use_cache"]) {
            $this->useCache = true;
        }

        $cmd .= ' --using-cache=' . (($this->useCache) ? 'yes' : 'no');

        return $cmd;
    }
}
