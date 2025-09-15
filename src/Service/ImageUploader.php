<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Service responsable de la gestion des uploads d'images :
 * - Dépots des fichiers dans le repertoire cible
 * - Génération de noms de fichiers uniques
 * - Suppression des anciens fichiers si besoin
 */
class ImageUploader
{
    private string $targetDirectory; // Répertoire où stocker lles fichiers
    private Filesystem $filesystem; // Utilitaire Symfony pour manipuler le système de fichier
    private SluggerInterface $slugger; // Slugger

    /**
     * Constructeur : initialise le répertoire cible et les dépendances
     */
    public function __construct(string $targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->filesystem = new Filesystem();
    }

    /**
     * Upload d'un ou plusieurs fichier
     * 
     * @param UploadedFile|UploadedFile[]|null  $files  Les fichiers à upload
     * @param string|null                       $oldFilename Ancien fichier à supprimer
     * @param string                            $prefix Prefixe pour le nom généré
     * 
     * @return string[] Liste des noms de fichiers uploadés
     */
    public function upload(UploadedFile|array|null $files, ?string $oldFilename = null, string $prefix): array
    {
        $filenames = [];

        // Si aucun fichier fourni
        if (!$files) {
            // Supprime l'ancien fichier
            if ($oldFilename) {
                $this->deleteFile($oldFilename);
            }
            return [];
        }

        // Force en tableau même si un seul fichier passé
        $files = is_array($files) ? $files : [$files];

        foreach ($files as $file) {
            // On ne traitre que les vraies uploadFile
            if (!$file instanceof UploadedFile) {
                continue;
            }

            // On accepte seulement les fichiers jpeg/png
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                continue;
            }

            // On génère le nom en WebP
            $filename = $prefix . '-' . uniqid() . '.webp';
            $destination = $this->targetDirectory . '/' . $filename;
            
            $tempPath = $file->getPathname();
            $image = null;

            if ($mimeType === 'image/jpeg') {
                $image = \imagecreatefromjpeg($tempPath);
            } elseif ($mimeType === 'image/png') {
                $image = \imagecreatefrompng($tempPath);
                // Garde la transparence pour PNG
                \imagepalettetotruecolor($image);
                \imagealphablending($image, true);
                \imagesavealpha($image, true);
            }

            if ($image) {
                \imagewebp($image, $destination, 80); // qualité 80%
                \imagedestroy($image);
                $filenames[] = $filename;
            }
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
