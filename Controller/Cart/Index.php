<?php
/**
 * @package     CravenDunnill_TileSamplesLimit
 * @author      Craven Dunnill
 * @copyright   Copyright (c) Craven Dunnill (https://cravendunnill.co.uk)
 */

namespace CravenDunnill\TileSamplesLimit\Controller\Cart;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index implements HttpGetActionInterface
{
	/**
	 * @var RedirectFactory
	 */
	private $resultRedirectFactory;

	/**
	 * @param Context $context
	 * @param RedirectFactory $resultRedirectFactory
	 */
	public function __construct(
		Context $context,
		RedirectFactory $resultRedirectFactory
	) {
		$this->resultRedirectFactory = $resultRedirectFactory;
	}

	/**
	 * Redirect to cart
	 *
	 * @return \Magento\Framework\Controller\Result\Redirect
	 */
	public function execute()
	{
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('checkout/cart');
		
		return $resultRedirect;
	}
}