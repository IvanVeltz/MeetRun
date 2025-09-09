<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:convert-webp',
    description: 'Convertie les images jpeg/png en WebP',
)]
class ConvertWebpCommand extends Command
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $dir = __DIR__.'/../../public/img'; // Chemin vers le dossier image
        $io->note("Dossier source : $dir");

        // Récupère tous les fichiers JPG/JPEG/PNG
        $files = glob($dir . '/*.{jpg,png,jpeg}', GLOB_BRACE);

        if (!$files){
            $io->warning('Aucune image à convertir');
            return Command::SUCCESS;
        }

        foreach($files as $file) {
            $info = pathinfo($file);
            $newFile = $info['dirname'] . '/' . $info['filename'] . '.webp';
            $mime = mime_content_type($file);

            if ($mime === 'image/jpeg'){
                $img = imagecreatefromjpeg($file);
            } elseif ($mime === 'image/png') {
                $img = imagecreatefrompng($file);
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
            } else {
                $io->warning("Format non supporté : $file");
                continue;
            }

            if ($img) {
                imagewebp($img, $newFile, 80);
                imagedestroy($img);

                // Supprime l'image originale
                if (file_exists($file)) {
                    unlink($file);
                }
                $io->success("Converti : {$file} → {$newFile}");
            }
        }

        $io->success('Toutes les images ont été converties en WebP.');

        return Command::SUCCESS;
    }
}
