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
     * Menghasilkan URL publik untuk file di Firebase Storage
     *
     * @param string $path Path file di bucket (misal: 'spt/filename.jpg')
     * @return string
     */
    public function getPublicUrl(string $path)
    {
        if (!$path || $path === '-') return '#';
        
        try {
            $bucket = $this->storage->getBucket();
            $object = $bucket->object('spt/' . $path);

            if ($object->exists()) {
                // Menghasilkan URL yang berlaku selama 1 jam
                return $object->signedUrl(new \DateTime('+1 hour'));
            }
        } catch (\Exception $e) {
            return '#';
        }

        return '#';
    }
}
