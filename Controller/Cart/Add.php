<?php
/**
 * @package     CravenDunnill_TileSamplesLimit
 * @author      Craven Dunnill
 * @copyright   Copyright (c) Craven Dunnill (https://cravendunnill.co.uk)
 */

namespace CravenDunnill\TileSamplesLimit\Controller\Cart;

use CravenDunnill\TileSamplesLimit\Model\SamplesLimit;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Escaper;
use Magento\Framework\Controller\Result\RedirectFactory;

class Add extends \Magento\Checkout\Controller\Cart\Add implements HttpPostActionInterface
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
	 * @var CheckoutSession
	 */
	private $checkoutSession;

	/**
	 * @var RedirectFactory
	 */
	private $resultRedirectFactory;

	/**
	 * @param Context $context
	 * @param SamplesLimit $samplesLimit
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param Validator $formKeyValidator
	 * @param \Magento\Checkout\Model\Cart $cart
	 * @param ProductRepositoryInterface $productRepository
	 * @param RedirectFactory $resultRedirectFactory
	 * @param Escaper|null $escaper
	 */
	public function __construct(
		Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		Validator $formKeyValidator,
		\Magento\Checkout\Model\Cart $cart,
		ProductRepositoryInterface $productRepository,
		RedirectFactory $resultRedirectFactory,
		SamplesLimit $samplesLimit,
		?Escaper $escaper = null
	) {
		$this->samplesLimit = $samplesLimit;
		$this->productRepository = $productRepository;
		$this->checkoutSession = $checkoutSession;
		$this->resultRedirectFactory = $resultRedirectFactory;
		parent::__construct(
			$context,
			$scopeConfig,
			$checkoutSession,
			$storeManager,
			$formKeyValidator,
			$cart,
			$productRepository,
			$escaper
		);
	}

	/**
	 * Add product to shopping cart
	 *
	 * @return \Magento\Framework\Controller\Result\Redirect
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function execute()
	{
		if (!$this->_formKeyValidator->validate($this->getRequest())) {
			return $this->resultRedirectFactory->create()->setPath('*/*/');
		}

		$params = $this->getRequest()->getParams();

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
			
			return parent::execute();
		} catch (LocalizedException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			return $this->goBack($e->getMessage());
		} catch (NoSuchEntityException $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			return $this->goBack($e->getMessage());
		} catch (\Exception $e) {
			$this->messageManager->addExceptionMessage($e, __('We can\'t add this item to your shopping cart right now.'));
			return $this->goBack();
		}
	}

	/**
	 * Go back to the previous page
	 *
	 * @param string $message
	 * @return \Magento\Framework\Controller\Result\Redirect
	 */
	private function goBack($message = null)
	{
		$resultRedirect = $this->resultRedirectFactory->create();
		
		if ($this->getRequest()->getServer('HTTP_REFERER')) {
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		} else {
			$resultRedirect->setPath('*/*/');
		}
		
		return $resultRedirect;
	}
}