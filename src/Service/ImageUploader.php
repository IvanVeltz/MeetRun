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

    public function upload(?UploadedFile $file, ?string $oldFilename = null): ?string
    {
        if (!$file) {
            if ($oldFilename && $this->filesystem->exists($this->targetDirectory.'/'.$oldFilename)) {
                $this->filesystem->remove($this->targetDirectory.'/'.$oldFilename);
            }
            return null;
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            return null; // Ou lève une exception
        }

        $filename = 'user-' . uniqid() . '.' . $file->guessExtension();
        $file->move($this->targetDirectory, $filename);

        // Supprime l'ancienne image
        if ($oldFilename && $this->filesystem->exists($this->targetDirectory.'/'.$oldFilename)) {
            $this->filesystem->remove($this->targetDirectory.'/'.$oldFilename);
        }

        return $filename;
    }
}
