<?php

declare(strict_types=1);

namespace Codilar\OrderCancellation\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Throwable;

class Cancel implements HttpPostActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly RedirectFactory $resultRedirectFactory,
        private readonly ManagerInterface $messageManager,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Session $customerSession
    ) {
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/history');

        try {
            $orderId = (int) $this->request->getParam('order_id');

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

            // Check that this order belongs to the logged-in customer
            if (
                (int) $order->getCustomerId() !==
                (int) $this->customerSession->getCustomerId()
            ) {
                throw new LocalizedException(
                    __('You are not allowed to cancel this order.')
                );
            }

            // Check whether Magento allows cancellation
            if (!$order->canCancel()) {
                throw new LocalizedException(
                    __('This order cannot be cancelled.')
                );
            }

            // Cancel order
            $order->cancel();

            // Save cancelled order
            $this->orderRepository->save($order);

            $this->messageManager->addSuccessMessage(
                __(
                    'Order has been cancelled successfully.',
                    $order->getRealOrderId()
                )
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        } catch (Throwable $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to cancel the order. Please try again.')
            );
        }

        return $resultRedirect;
    }
}
