<?php

namespace GitReview\File;

use Spatie\Regex\Regex;
use Tightenco\Collect\Support\Collection;

class FilesFinder
{
    private $files;
    private $pathCriteria;
    /** @var Collection $foundFiles */
    private $foundFiles;

    public function __construct(Collection $files, array $pathCriteria = [])
    {
        $this->files = $files;
        $this->pathCriteria = $pathCriteria;
        $this->foundFiles = new Collection([]);

        $this->formatCriteria();

        $this->findFilesByGivenCriteria();
    }

    public function getFoundFiles(): Collection
    {
        return $this->foundFiles;
    }

    public function count(): int
    {
        return \count($this->getFoundFiles());
    }

    private function findFilesByGivenCriteria()
    {
        $this->files->filter(function (File $file) {
            $found = false;

            foreach ($this->pathCriteria as $criteria) {
                if (Regex::match($criteria, $file->getRelativePath())->hasMatch()) {
                    $found = true;

                    break;
                }
            }

            return $found;
        })->each(function (File $file) {
            $this->addFile($file);
        });
    }

    private function addFile($file)
    {
        $this->foundFiles->push($file);
    }

    private function formatCriteria()
    {
        $this->pathCriteria = \array_map(function ($value) {
            $escapeChars = \str_replace("/*", "/.*", $value);
            $escapeChars = \str_replace("/", "\/", $escapeChars);

            return "/${escapeChars}/";
        }, $this->pathCriteria);
    }
}
