<?php

namespace GitReview\Container;

use GitReview\Command\HookInstallCommand;
use GitReview\Command\HookListCommand;
use GitReview\Command\HookRunCommand;
use GitReview\Command\PhpCsFixerCommand;
use GitReview\Command\Review\CommitStructureReviewCommand;
use GitReview\Console\Application;
use GitReview\VersionControl\GitBranch;
use OndraM\CiDetector\CiDetector;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->registerServices();
    }

    private function registerServices(): void
    {
        $app = $this;

        $this['command.commits.review'] = function (): CommitStructureReviewCommand {
            return new CommitStructureReviewCommand(
                new GitBranch(\getcwd()),
                new CiDetector()
            );
        };

        $this['commands'] = function (): array {
            return [
                new HookListCommand(),
                new HookInstallCommand(),
                new HookRunCommand(),
                new PhpCsFixerCommand(),
                $this['command.commits.review'],
            ];
        };

        $this['application'] = function (PimpleContainer $app): Application {
            $application = new Application(
                'GitReview Command Line Tool',
                '3.0.0'
            );
            $application->addCommands($app['commands']);

            return $application;
        };
    }
}
