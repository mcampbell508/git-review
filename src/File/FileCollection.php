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

    public function addFiles(array $files)
    {
        foreach ($files as $file) {
            list($status, $relativePath) = explode("\t", $file);

            $fullPath = \rtrim($this->path . DIRECTORY_SEPARATOR . $relativePath);

            $file = new File($status, $fullPath, $this->path);
            $this->append($file);
        }
    }

    public function append(File $file)
    {
        $this->fileCollection->push($file);
    }

    public function getFileCollection(): Collection
    {
        return $this->fileCollection;
    }
}
