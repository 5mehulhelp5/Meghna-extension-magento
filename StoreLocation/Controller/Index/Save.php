<?php
//declare(strict_types=1);
//
//namespace Codilar\StoreLocation\Controller\Index;
//
//use Codilar\StoreLocation\Api\Data\StoreInterfaceFactory;
//use Codilar\StoreLocation\Api\StoreRepositoryInterface;
//use Magento\Framework\App\Action\HttpPostActionInterface;
//use Magento\Framework\App\RequestInterface;
//use Magento\Framework\Controller\ResultFactory;
//use Magento\Framework\File\UploaderFactory;
//use Magento\Framework\Filesystem;
//
//class Save implements HttpPostActionInterface
//{
//    /**
//     * @param RequestInterface $request
//     * @param ResultFactory $resultFactory
//     * @param StoreInterfaceFactory $storeFactory
//     * @param StoreRepositoryInterface $storeRepository
//     * @param UploaderFactory $uploaderFactory
//     * @param Filesystem $filesystem
//     */
//    public function __construct(
//        private readonly RequestInterface $request,
//        private readonly ResultFactory $resultFactory,
//        private readonly StoreInterfaceFactory $storeFactory,
//        private readonly StoreRepositoryInterface $storeRepository,
//        private readonly UploaderFactory $uploaderFactory,
//        private readonly Filesystem $filesystem
//    ) {
//    }
//
//    /**
//     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
//     */
//    public function execute()
//    {
//        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
//        $data = $this->request->getPostValue();
//
//        if (!$data) {
//            return $redirect->setPath('stores/index/form');
//        }
//
//        try {
//            $imageName = null;
//            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
//                $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
//                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
//                $uploader->setAllowRenameFiles(true);
//                $uploader->setFilesDispersion(false);
//
//                $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
//                $targetPath = $mediaDirectory->getAbsolutePath('store_locations/');
//
//                $result = $uploader->save($targetPath);
//                $imageName = 'store_locations/' . $result['file'];
//            }
//
//            $store = $this->storeFactory->create();
//            $store->setName($data['name']);
//            $store->setAddress($data['address']);
//            $store->setCity($data['city']);
//            $store->setPincode($data['pincode']);
//            $store->setPhone($data['phone']);
//            $store->setOpeningHours($data['opening_hours']);
//            if ($imageName) {
//                $store->setImage($imageName);
//            }
//
//            $this->storeRepository->save($store);
//            return $redirect->setPath('stores/index/success');
//        } catch (\Exception $e) {
//            return $redirect->setPath('stores/index/form');
//        }
//    }
//}
