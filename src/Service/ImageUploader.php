<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploader
{
    private string $targetDirectory;
    private Filesystem $filesystem;
    private SluggerInterface $slugger;

    public function __construct(string $targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->filesystem = new Filesystem();
    }

    public function upload(?UploadedFile $file, ?string $oldFilename = null, string $prefix = 'photo'): ?string
    {
        if (!$file) {
            if ($oldFilename) {
                $basename = basename($oldFilename);
                $oldPath = $this->targetDirectory . '/' . $basename;
                if ($this->filesystem->exists($oldPath)) {
                    $this->filesystem->remove($oldPath);
                }
            }
            return null;
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            return null;
        }

        $filename = $prefix . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move($this->targetDirectory, $filename);

        if ($oldFilename) {
            $basename = basename($oldFilename);
            $oldPath = $this->targetDirectory . '/' . $basename;
            if ($this->filesystem->exists($oldPath)) {
                $this->filesystem->remove($oldPath);
            }
        }

        return $filename;
    }


    /**
     * @param UploadedFile[] $files
     * @return string[] Liste des noms de fichiers uploadés
     */
    public function uploadMultiple(array $files, string $prefix = 'photo'): array
    {
        $filenames = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $filename = $this->upload($file, null, $prefix);
                if ($filename) {
                    $filenames[] = $filename;
                }
            }
        }

        return $filenames;
    }
}
