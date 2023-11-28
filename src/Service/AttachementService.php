<?php

namespace Lle\HermesBundle\Service;

class AttachementService
{
    public function deleteAttachements(string $path): bool
    {
        if (file_exists($path)) {
            /** @var array $files */
            $files = scandir($path);
            $files = array_diff($files, ['.', '..']);
            foreach ($files as $file) {
                if (is_dir($path . '/' . $file)) {
                    return $this->deleteAttachements($path . '/' . $file);
                } else {
                    return unlink($path . '/' . $file);
                }
            }

            return rmdir($path);
        }

        return false;
    }
}