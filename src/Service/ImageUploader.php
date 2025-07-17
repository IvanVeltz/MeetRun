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

    /**
     * @param UploadedFile|UploadedFile[]|null $files
     * @param string|null $oldFilename
     * @param string $prefix
     * @return string[] Liste des noms de fichiers uploadés
     */
    public function upload(UploadedFile|array|null $files, ?string $oldFilename = null, string $prefix): array
    {
        $filenames = [];

        if (!$files) {
            if ($oldFilename) {
                $this->deleteFile($oldFilename);
            }
            return [];
        }

        // Force en tableau
        $files = is_array($files) ? $files : [$files];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                continue;
            }

            $filename = $prefix . '-' . uniqid() . '.' . $file->guessExtension();
            $file->move($this->targetDirectory, $filename);
            $filenames[] = $filename;
        }

        // Supprimer l’ancien fichier (utile dans les cas simples à 1 image)
        if ($oldFilename) {
            $this->deleteFile($oldFilename);
        }

        return $filenames;
    }

    private function deleteFile(string $path): void
    {
        $basename = basename($path);
        $fullPath = $this->targetDirectory . '/' . $basename;

        if ($this->filesystem->exists($fullPath)) {
            $this->filesystem->remove($fullPath);
        }
    }
}
