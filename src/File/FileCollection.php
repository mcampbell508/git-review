<?php

namespace GitReview\File;

use Tightenco\Collect\Support\Collection;

class FileCollection
{
    private $fileCollection;
    private $path;

    public function __construct($path)
    {
        $this->fileCollection = new Collection([]);
        $this->path = $path;
    }

    public function addFiles(array $files): void
    {
        foreach ($files as $file) {
            [$status, $relativePath] = \explode("\t", $file);

            $fullPath = \rtrim($this->path . DIRECTORY_SEPARATOR . $relativePath);

            $file = new File($status, $fullPath, $this->path);
            $this->append($file);
        }
    }

    public function append(File $file): void
    {
        $this->fileCollection->push($file);
    }

    public function getFileCollection(): Collection
    {
        return $this->fileCollection;
    }
}
