<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MusicLibrary
{
    private string $publicDir;
    private string $musicDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->publicDir = $params->get('kernel.project_dir') . '/public';
        $this->musicDir = $this->publicDir . '/assets/audio/musique';
    }

    /**
     * @return array<string, string> [slug => publicPath]
     */
    public function getTracks(): array
    {
        if (!is_dir($this->musicDir)) {
            return [];
        }

        $tracks = [];
        foreach (glob($this->musicDir . '/*.mp3') as $file) {
            $slug = pathinfo($file, PATHINFO_FILENAME);
            $publicPath = str_replace($this->publicDir, '', $file);
            $publicPath = str_replace('\\', '/', $publicPath);
            $tracks[$slug] = $publicPath;
        }

        return $tracks;
    }

    public function getTrackPath(string $slug): ?string
    {
        $tracks = $this->getTracks();

        return $tracks[$slug] ?? null;
    }
}
