<?php

namespace App\Services;

use Kreait\Firebase\Contract\Storage;

class FirebaseStorageService
{
    protected $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Upload file ke Firebase Storage
     */
    public function upload(string $path, $file)
    {
        try {
            $bucket = $this->storage->getBucket();
            $stream = fopen($file->getRealPath(), 'r');
            $bucket->upload($stream, [
                'name' => $path
            ]);
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Menghasilkan URL publik untuk file di Firebase Storage
     */
    public function getPublicUrl(string $path)
    {
        if (!$path || $path === '-') return null;
        
        try {
            $bucket = $this->storage->getBucket();
            $object = $bucket->object($path);

            if ($object->exists()) {
                // Menghasilkan URL yang berlaku selama 1 tahun (untuk icon dashboard)
                return $object->signedUrl(new \DateTime('+1 year'));
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
