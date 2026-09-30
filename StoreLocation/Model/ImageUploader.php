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

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly UploaderFactory $uploaderFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger,
        public readonly string $baseTmpPath = 'store_locations/tmp/image',
        public readonly string $basePath = 'store_locations/image',
        public readonly array $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png']
    ) {
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Upload image to temporary directory
     *
     * @param string $fileId
     * @return array
     * @throws LocalizedException
     * @throws Throwable
     */
    public function saveFileToTmpDir(string $fileId): array
    {
        $this->logger->info('ImageUploader: Starting saveFileToTmpDir for fileId: ' . $fileId);

        try {
            $this->mediaDirectory->create($this->baseTmpPath);

            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($this->allowedExtensions);
            $uploader->setAllowRenameFiles(true);

            $destinationPath = $this->mediaDirectory->getAbsolutePath($this->baseTmpPath);
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

            $result['url'] = $mediaUrl . $this->baseTmpPath . '/' . $fileName;

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

    /**
     * Move file from temporary directory to permanent destination path and cleanup temp file
     *
     * @param string $imageName
     * @return string
     * @throws LocalizedException
     */
    public function moveFileFromTmp(string $imageName): string
    {
        $imageName = $this->getNewFileName($imageName);

        $tmpImageRelativePath = $this->baseTmpPath . '/' . $imageName;
        $destinationRelativePath = $this->basePath . '/' . $imageName;

        $this->logger->info('ImageUploader: Moving image from ' . $tmpImageRelativePath);
        $this->logger->info('ImageUploader: Moving image to ' . $destinationRelativePath);

        $this->mediaDirectory->create($this->basePath);

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

        // Cleanup temporary image file after a successful move
        if ($this->mediaDirectory->isExist($tmpImageRelativePath)) {
            $this->mediaDirectory->delete($tmpImageRelativePath);
            $this->logger->info('ImageUploader: Temporary image deleted from tmp folder: ' . $tmpImageRelativePath);
        }

        return $imageName;
    }

    /**
     * Normalize file name
     *
     * @param string $file
     * @return string
     */
    private function getNewFileName(string $file): string
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');
        return basename($file);
    }
}
