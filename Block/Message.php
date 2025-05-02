<?php
/**
 * @package     CravenDunnill_TileSamplesLimit
 * @author      Craven Dunnill
 * @copyright   Copyright (c) Craven Dunnill (https://cravendunnill.co.uk)
 */

namespace CravenDunnill\TileSamplesLimit\Block;

use CravenDunnill\TileSamplesLimit\Model\SamplesLimit;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Message extends Template
{
	/**
	 * @var SamplesLimit
	 */
	private $samplesLimit;

	/**
	 * @param Context $context
	 * @param SamplesLimit $samplesLimit
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		SamplesLimit $samplesLimit,
		array $data = []
	) {
		$this->samplesLimit = $samplesLimit;
		parent::__construct($context, $data);
	}

	/**
	 * Get current samples count
	 *
	 * @return int
	 */
	public function getCurrentSamplesCount(): int
	{
		return $this->samplesLimit->countSamplesInCart();
	}

	/**
	 * Get max samples limit
	 *
	 * @return int
	 */
	public function getMaxSamplesLimit(): int
	{
		return 4;
	}

	/**
	 * Check if we have samples in cart
	 *
	 * @return bool
	 */
	public function hasSamplesInCart(): bool
	{
		return $this->getCurrentSamplesCount() > 0;
	}

	/**
	 * Get remaining samples count
	 *
	 * @return int
	 */
	public function getRemainingSamplesCount(): int
	{
		$current = $this->getCurrentSamplesCount();
		$max = $this->getMaxSamplesLimit();
		
		return max(0, $max - $current);
	}
}