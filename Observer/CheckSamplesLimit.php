<?php
/**
 * @package     CravenDunnill_TileSamplesLimit
 * @author      Craven Dunnill
 * @copyright   Copyright (c) Craven Dunnill (https://cravendunnill.co.uk)
 */

namespace CravenDunnill\TileSamplesLimit\Observer;

use CravenDunnill\TileSamplesLimit\Model\SamplesLimit;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CheckSamplesLimit implements ObserverInterface
{
	/**
	 * @var SamplesLimit
	 */
	private $samplesLimit;

	/**
	 * @param SamplesLimit $samplesLimit
	 */
	public function __construct(
		SamplesLimit $samplesLimit
	) {
		$this->samplesLimit = $samplesLimit;
	}

	/**
	 * Execute observer
	 *
	 * @param Observer $observer
	 * @return void
	 * @throws LocalizedException
	 */
	public function execute(Observer $observer)
	{
		$product = $observer->getEvent()->getProduct();
		$requestItem = $observer->getEvent()->getRequest()->getParam('qty');
		$qty = empty($requestItem) ? 1 : $requestItem;
		
		$this->samplesLimit->validateLimit($product, (int)$qty);
	}
}