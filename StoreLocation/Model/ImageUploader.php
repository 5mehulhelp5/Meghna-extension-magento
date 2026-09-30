<?php

declare(strict_types=1);

namespace Codilar\StoreLocation\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ImageUploader
{
    private WriteInterface $mediaDirectory;
    private string $baseTmpPath = 'store_locations/tmp/image';
    private string $basePath = 'store_locations/image';
    private array $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly UploaderFactory $uploaderFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
    /**
     * Upload image to temporary directory
     */
    public function saveFileToTmpDir(string $fileId): array
    {
        $baseTmpPath = $this->getBaseTmpPath();

        $this->logger->info('ImageUploader: Starting saveFileToTmpDir for fileId: ' . $fileId);

        try {

            $this->mediaDirectory->create($baseTmpPath);

            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);

            $uploader->setAllowedExtensions($this->allowedExtensions);

            $uploader->setAllowRenameFiles(true);

            $destinationPath = $this->mediaDirectory->getAbsolutePath($baseTmpPath);

            $this->logger->info('ImageUploader: Destination path: ' . $destinationPath);

            $result = $uploader->save($destinationPath);

            $this->logger->info('ImageUploader: Uploader result: ' . var_export($result, true));

            if (!$result || !is_array($result)) {
                throw new LocalizedException(__('The image could not be uploaded.'));
            }

            $fileName = $result['file'] ?? '';

            if (!$fileName) {
                throw new LocalizedException(__('The uploaded image filename is missing.'));
            }

            $result['name'] = $fileName;

            $mediaUrl = $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            $result['url'] = $mediaUrl . $baseTmpPath . '/' . $fileName;

            $result['cookie'] = [
                'name' => session_name(),
                'value' => session_id(),
                'lifetime' => ini_get('session.cookie_lifetime'),
                'path' => ini_get('session.cookie_path'),
                'domain' => ini_get('session.cookie_domain')
            ];
            $this->logger->info('ImageUploader: Image uploaded successfully: ' . $fileName);

            return $result;

        } catch (Throwable $e) {

            $this->logger->error('ImageUploader Exception: ' . $e->getMessage());

            $this->logger->error($e->getTraceAsString());

            throw $e;
        }
    }

    public function getBaseTmpPath(): string
    {
        return $this->baseTmpPath;
    }

    public function moveFileFromTmp(string $imageName): string
    {

        $imageName = $this->getNewFileName($imageName);

        $tmpImageRelativePath = $this->getBaseTmpPath() . '/' . $imageName;

        $destinationRelativePath = $this->getBasePath() . '/' . $imageName;

        $this->logger->info('ImageUploader: Moving image from ' . $tmpImageRelativePath);

        $this->logger->info('ImageUploader: Moving image to ' . $destinationRelativePath);

        $this->mediaDirectory->create($this->getBasePath());

        if (!$this->mediaDirectory->isExist($tmpImageRelativePath)) {

            $this->logger->error('ImageUploader: Temporary image not found: ' . $tmpImageRelativePath);

            throw new LocalizedException(__('The temporary image %1 could not be found.', $imageName));
        }

        if ($this->mediaDirectory->isExist($destinationRelativePath)) {

            $this->logger->info('ImageUploader: Existing image found. Deleting: ' . $destinationRelativePath);

            $this->mediaDirectory->delete($destinationRelativePath);
        }

        $this->mediaDirectory->renameFile($tmpImageRelativePath, $destinationRelativePath);

        $this->logger->info('ImageUploader: Image successfully moved to ' . $destinationRelativePath);

        if ($this->mediaDirectory->isExist($tmpImageRelativePath)) {
            $this->mediaDirectory->delete($tmpImageRelativePath);
            $this->logger->info('ImageUploader: Temporary image deleted from tmp folder: ' . $tmpImageRelativePath);
        }

        return $imageName;
    }

    private function getNewFileName(string $file): string
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');

        return basename($file);
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }
}
