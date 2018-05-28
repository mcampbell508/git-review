<?php

namespace GitReview\Command\Review;

use GitReview\Commit\Commit;
use GitReview\Commit\CommitMessage;
use GitReview\GitReview;
use GitReview\Issue\Issue;
use GitReview\Reporter\Reporter;
use GitReview\Review\Message\BodyLineLengthReview;
use GitReview\Review\Message\SubjectImperativeReview;
use GitReview\Review\Message\SubjectLineCapitalReview;
use GitReview\Review\Message\SubjectLineLengthReview;
use GitReview\Review\Message\SubjectLinePeriodReview;
use GitReview\Review\Message\WorkInProgressReview;
use GitReview\VersionControl\GitBranch;
use League\CLImate\CLImate;
use OndraM\CiDetector\CiDetector;
use Stringy\Stringy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommitStructureReviewCommand extends Command
{
    public const CONFIG_PATH = 'yml-config-path';

    private $gitBranch;
    private $ciDetector;
    private $climate;

    public function __construct(GitBranch $gitBranch, CiDetector $ciDetector)
    {
        parent::__construct();
        $this->gitBranch = $gitBranch;
        $this->ciDetector = $ciDetector;
    }

    protected function configure(): void
    {
        $this->setName('review:commits');

        $this->setDescription('Review commit structure on a Git branch.');

        $this->addArgument(
            self::CONFIG_PATH,
            InputArgument::OPTIONAL,
            'The path to the git-review configuration file, named `git-review.yml(.dist)`'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $branchName = $this->ciDetector->isCiDetected()
            ? $this->ciDetector->detect()->getGitBranch() : $this->gitBranch->getName();

        if ($branchName === 'master') {
            $io->success("Currently checked out branch is `master`, nothing to check!");

            return 0;
        }

        $commits = $this->gitBranch->getCommitsOnBranch();

        if ($commits->count() === 0) {
            $io->success(
                "There are currently no commits to review on the currently checkout out branch `${branchName}`"
            );

            return 0;
        }

        $io->title("Commit structure review for `${branchName}`");
        $time = \date("Y-m-d H:i:s");

        $io->writeln("<fg=green>Time of review: ${time}</>");
        $io->newLine();

        $count = 1;

        $reporter = new Reporter();
        $review = new GitReview($reporter);

        $subjectReview = new SubjectLineLengthReview();
        $subjectReview->setMaximumLength(60);

        $review
            ->addReview(new SubjectImperativeReview())
            ->addReview(new SubjectLineCapitalReview())
            ->addReview($subjectReview)
            ->addReview(new SubjectLinePeriodReview())
            ->addReview(new BodyLineLengthReview())
            ->addReview(new WorkInProgressReview());

        $this->climate = new CLImate();

        $commits->getCommitCollection()->map(function (Commit $commit) use ($io, &$count, $reporter, $review) {

            $subject = $commit->getCommitMessage()->getSubject();
            $io->section("Commit #$count: '". $subject . "'");

            $body = $commit->getCommitMessage()->getBodyLength()
                ? "Body: \n". $commit->getCommitMessage()->getBody() : '<red>Body: N/A</red>';

            $hash = $commit->getHash();

            $text = <<<EOL
<yellow>commit $hash</yellow>
Author:     <{$commit->getAuthor()->presentNameAndEmail()}>
AuthorDate: <{$commit->getAuthor()->getAuthorDate()}>

EOL;

            $this->climate->out($text)->br();

            $io->section("Subject");

            $this->climate->out($subject)->br();

            $row['subjectReview'] = [
                strlen($subject) . ' / 60',
                ($commit->isWorkInProgress() || $commit->isFixup() ? "<fg=red>✔</>" : "<fg=green>✘</>"),
            ];

            $table = new Table($io);
            $table
                ->setHeaders([
                    [
                        'Length',
                        'WIP / Fixup?',
                        'Capitalised?',
                        'No full stop?',
                        'Imperative mood?'
                    ]
                ])
                ->setRows([
                    $row['subjectReview']
                ]);
            $table->render();

            $this->climate->br();
            $io->section("Body");

            $this->displayCommitBody($commit->getCommitMessage()->getBody());

            $this->climate->br();

            $row['bodyReview'] = [
                $commit->getCommitMessage()->getBodyLength(),
                '',
                '',
            ];

            $table = new Table($io);
            $table
                ->setHeaders([
                    [
                        'Length',
                        'Lines wrapped to 72 chars?',
                        'Issue referenced?',
                    ]
                ])
                ->setRows([$row['bodyReview']]);
            $table->render();

            $this->climate->br();
            $count++;

            $message = new CommitMessage($subject . "\n\n" . $commit->getCommitMessage()->getBody(), $hash);
            $review->message($message);

            if ($reporter->hasIssues()) {
                $issues = collect($reporter->getIssues())
                    ->filter(function (Issue $issue) use ($hash) {
                        return $issue->getSubject()->getHash() === $hash;
                    });

                if ($issues->isEmpty()) {
                    $this->climate->green('✔ Commit looks good!');
                } else {
                    $issues->each(function (Issue $issue) {
                        $this->climate->red($issue);
                    });
                }
            }

            return $row;
        });

        $io->title("Commit summary");

        $this->overallReview($output, $io, $commits);
        $this->climate->br();

        if ($reporter->hasIssues()) {
            foreach ($reporter->getIssues() as $issue) {
                $this->climate->red($issue);
            }

            $io->newLine();

            return $io->error("✘ Please fix the errors above using: git rebase -i master");
        }

        return $io->success("No issues found, good job!");
    }

    protected function getReview(): void
    {
        $reporter = new Reporter();
        $review = new GitReview($reporter);
    }

    protected function overallReview(OutputInterface $output, $io, $commits): void
    {
        $count = 1;
        $rows = $commits
            ->getCommitCollection()
            ->map(function (Commit $commit) use (&$count) {
                $commitId = $count;
                $count++;

                $subjectLength = $commit->getCommitMessage()->getSubjectLength();
                $bodyLength = $commit->getCommitMessage()->getBodyLength();

                $subjectLength = "<fg=" . ($subjectLength > 60
                        ? "red> ✘ " : "green> ✔ ") . "$subjectLength / 60 </>";

                $isWipOrFixup = "<fg=" . (($commit->isDraft()) ? "red> ✔ ": "green> ✘ ") . " </>";

                return [
                    $commitId,
                    Stringy::create($commit->getHash())->first(6),
                    $subjectLength,
                    $isWipOrFixup,
                    $bodyLength,
                    '',
                    '',
                    $commit->getAuthor()->getAuthorName(),
                    0
                ];
            })->toArray();

        $table = new Table($output);
        $table
            ->setHeaders([
                array(new TableCell('Branch commits overview', array('colspan' => 9))),
                [
                    '#',
                    'Hash',
                    'Subject',
                    'WIP / Fixup?',
                    'Body chars',
                    'Body wrapped',
                    'Author',
                    'Has issue ref',
                    'Changed files',
                ]
            ])
            ->setRows($rows);

        $table->render();
    }

    private function displayCommitBody(string $body): void
    {
        $bodyStrings = \explode(PHP_EOL, $body);

        if (count($bodyStrings) === 1 && $bodyStrings[0] === '') {
            $this->climate->red("Body N/A");
        }

        foreach ($bodyStrings as $string) {
            if (strlen($string) <= 72) {
                $this->climate->out($string);
                continue;
            }

            $permittedChars = substr($string, 0, 72);
            $overLimitChars = mb_substr($string, 72, strlen($string) - 72);

            $this->climate->out("<red>$permittedChars</red><underline><red>$overLimitChars</red></underline>");
        }
    }
}
