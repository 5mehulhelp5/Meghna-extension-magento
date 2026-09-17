<?php

declare(strict_types=1);

namespace Codilar\OrderCancellation\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;

class Cancel extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Session $customerSession
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/history');

        try {
            $orderId = (int) $this->getRequest()->getParam('order_id');

            if (!$orderId) {
                throw new LocalizedException(
                    __('Invalid order.')
                );
            }

            if (!$this->customerSession->isLoggedIn()) {
                throw new LocalizedException(
                    __('Please login to cancel the order.')
                );
            }

            $order = $this->orderRepository->get($orderId);

            if (
                (int) $order->getCustomerId()
            ) {
                throw new LocalizedException(
                    __('You are not allowed to cancel this order.')
                );
            }




            if (!$order->canCancel()) {
                throw new LocalizedException(
                    __('This order cannot be cancelled.')
                );
            }

            $order->cancel();

            $this->orderRepository->save($order);

            $this->messageManager->addSuccessMessage(
                __(
                    'Order #%1 has been cancelled successfully.',
                    $order->getRealOrderId()
                )
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to cancel the order. Please try again.')
            );
        }

        return $resultRedirect;
    }
}
