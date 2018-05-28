<?php

namespace GitReview\VersionControl;

use GitReview\Commit\Author;
use GitReview\Commit\Commit;
use GitReview\Commit\CommitCollection;
use GitReview\Commit\Message;
use GitReview\File\File;
use GitReview\File\FileCollection;
use GitReview\Process\ProcessFactory;
use Stringy\Stringy;
use Tightenco\Collect\Support\Collection;

class GitBranch implements GitBranchInterface
{
    private $currentWorkingDirectory;
    private $gitBinary;
    private $processFactory;

    public function __construct(string $currentWorkingDirectory, string $gitBinary = '/usr/bin/git')
    {
        $this->currentWorkingDirectory = $currentWorkingDirectory;
        $this->gitBinary = $gitBinary;
        $this->processFactory = new ProcessFactory();
    }

    public function getName(): string
    {
        return $this->processFactory->create($this->gitBinary . " rev-parse --abbrev-ref HEAD")
            ->getOutput();
    }

    public function getChangedFiles(): Collection
    {
        $fileCollection = new FileCollection($this->currentWorkingDirectory);

        $branchName = $this->getName();

        $committedFilesProcess = $this->processFactory
            ->create(
                "git log --name-status --pretty=format: {$this->getParentHash()}..${branchName}" .
                " | grep -E '^[A-Z]\b' | sort | uniq"
            );

        if (!$committedFilesProcess->isSuccessful()) {
            return new Collection();
        }

        $fileCollection->addFiles(\array_filter(\explode("\n", $committedFilesProcess->getOutput())));

        return $fileCollection->getFileCollection();
    }

    public function getCommitsOnBranch(): CommitCollection
    {
        $commitCollection = new CommitCollection();
        $branchName = $this->getName();
        $format = "___GIT_REVIEW_COMMIT__%nHash: %H%nParents: %P%nSubject: %s%nAuthor Name: %an%nAuthor Date: %aD%n"
            . "Author Email: %ae%nCommitter Name: %cn%nCommitter Date: %cD%nCommitter Email: %ce%nName Status:";

        $commits = $this->processFactory
            ->create(
                $this->gitBinary .
                " log --name-status --pretty=format:\"${format}\" {$this->getParentHash()}..${branchName} --reverse"
            )->getOutput();

        $commits = collect(\explode("___GIT_REVIEW_COMMIT__", $commits));
        $commits->shift();

        $commits->each(function ($item) use ($commitCollection): void {
            $item = \trim($item);
            [$commitDetails, $changedFiles] = \explode("Name Status:", $item);
            $commitDetails = \explode(PHP_EOL, \trim($commitDetails));
            $changedFiles = \explode(PHP_EOL, \trim($changedFiles));

            $hash = Stringy::create($commitDetails[0])->removeLeft("Hash: ");
            $parents = \explode(" ", \trim(Stringy::create($commitDetails[1])->removeLeft("Parents: ")));
            $subject = Stringy::create($commitDetails[2])->removeLeft("Subject: ");

            $body = $this->processFactory->create($this->gitBinary . " log -1 ${hash} --pretty=format:\"%b\"")
                ->getOutput();

            $commitCollection->add(new Commit(
                $hash,
                $parents,
                new Message($subject, $body),
                new Author(
                    Stringy::create($commitDetails[3])->removeLeft("Author Name: "),
                    Stringy::create($commitDetails[4])->removeLeft("Author Date: "),
                    Stringy::create($commitDetails[5])->removeLeft("Author Email: "),
                    Stringy::create($commitDetails[6])->removeLeft("Committer Name: "),
                    Stringy::create($commitDetails[7])->removeLeft("Committer Date: "),
                    Stringy::create($commitDetails[8])->removeLeft("Committer Email: ")
                ),
                $this->getFilesCollection($changedFiles)
            ));
        });

        return $commitCollection;
    }

    /**
     * A master branch using the merge commit strategy will return 2 parent hashes:
     *  - the ancestor (commit before the merge commit)
     *  - the actual parent we need in this case
     *
     * A master branch using the rebase strategy will usually have one parent hash.
     */
    public function getParentHash()
    {
        $hashes = $this->processFactory
            ->create("git rev-list --boundary {$this->getName()}...master | grep '^-' | cut -c2-")
            ->getOutput();

        $hashes = \explode(PHP_EOL, $hashes);

        if (\count($hashes) === 1) {
            return $hashes[0];
        }

        return $hashes[1];
    }

    public function isDirty(): bool
    {
        return !empty($this->processFactory->create("git status --short")->getOutput());
    }

    private function getProjectBase(): string
    {
        return $this->processFactory->create('git rev-parse --show-toplevel')->getOutput();
    }

    private function getFilesCollection($changedFiles): FileCollection
    {
        $base = $this->getProjectBase();

        $files = new FileCollection($this->currentWorkingDirectory);

        if (empty($changedFiles)) {
            return $files;
        }

        foreach ($changedFiles as $file) {
            $fileData = \explode("\t", $file);
            $status = \reset($fileData);
            $relativePath = \end($fileData);

            $fullPath = \rtrim($base . DIRECTORY_SEPARATOR . $relativePath);

            $file = new File($status, $fullPath, $base);
            $files->append($file);
        }

        return $files;
    }
}
