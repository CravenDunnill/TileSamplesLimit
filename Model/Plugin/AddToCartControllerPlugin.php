<?php
/**
 * @package     CravenDunnill_TileSamplesLimit
 * @author      Craven Dunnill
 * @copyright   Copyright (c) Craven Dunnill (https://cravendunnill.co.uk)
 */

namespace CravenDunnill\TileSamplesLimit\Model\Plugin;

use CravenDunnill\TileSamplesLimit\Model\SamplesLimit;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Controller\Cart\Add as CartAddController;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;

class AddToCartControllerPlugin
{
	/**
	 * @var SamplesLimit
	 */
	private $samplesLimit;

	/**
	 * @var ProductRepositoryInterface
	 */
	private $productRepository;

	/**
	 * @var RedirectFactory
	 */
	private $resultRedirectFactory;

	/**
	 * @var ManagerInterface
	 */
	private $messageManager;

	/**
	 * @var RequestInterface
	 */
	private $request;

	/**
	 * @param SamplesLimit $samplesLimit
	 * @param ProductRepositoryInterface $productRepository
	 * @param RedirectFactory $resultRedirectFactory
	 * @param ManagerInterface $messageManager
	 * @param RequestInterface $request
	 */
	public function __construct(
		SamplesLimit $samplesLimit,
		ProductRepositoryInterface $productRepository,
		RedirectFactory $resultRedirectFactory,
		ManagerInterface $messageManager,
		RequestInterface $request
	) {
		$this->samplesLimit = $samplesLimit;
		$this->productRepository = $productRepository;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->messageManager = $messageManager;
		$this->request = $request;
	}

	/**
	 * Check sample limits before proceeding with add to cart
	 *
	 * @param CartAddController $subject
	 * @param callable $proceed
	 * @return mixed
	 */
	public function aroundExecute(CartAddController $subject, callable $proceed)
	{
		$params = $this->request->getParams();

		try {
			if (isset($params['product'])) {
				$product = $this->productRepository->getById($params['product']);
				
				if ($this->samplesLimit->isTileSample($product)) {
					$qty = isset($params['qty']) ? (int)$params['qty'] : 1;
					
					if (!$this->samplesLimit->validateLimit($product, $qty)) {
						return $this->resultRedirectFactory->create()->setPath('checkout/cart');
					}
				}
			}
			
			// Proceed with the original controller execution
			return $proceed();
		} catch (LocalizedException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setPath('*/*/');
		} catch (\Exception $e) {
			$this->messageManager->addExceptionMessage($e, __('We can\'t add this item to your shopping cart right now.'));
			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setPath('*/*/');
		}
	}
}