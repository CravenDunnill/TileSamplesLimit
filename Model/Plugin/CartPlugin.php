<?php
/**
 * @package     CravenDunnill_TileSamplesLimit
 * @author      Craven Dunnill
 * @copyright   Copyright (c) Craven Dunnill (https://cravendunnill.co.uk)
 */

namespace CravenDunnill\TileSamplesLimit\Model\Plugin;

use CravenDunnill\TileSamplesLimit\Model\SamplesLimit;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CartPlugin
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
	 * @param SamplesLimit $samplesLimit
	 * @param ProductRepositoryInterface $productRepository
	 */
	public function __construct(
		SamplesLimit $samplesLimit,
		ProductRepositoryInterface $productRepository
	) {
		$this->samplesLimit = $samplesLimit;
		$this->productRepository = $productRepository;
	}

	/**
	 * Validate samples limit before adding to cart
	 *
	 * @param Cart $subject
	 * @param mixed $productInfo
	 * @param mixed $requestInfo
	 * @return array
	 * @throws LocalizedException
	 * @throws NoSuchEntityException
	 */
	public function beforeAddProduct(
		Cart $subject,
		$productInfo,
		$requestInfo = null
	) {
		$productId = is_object($productInfo) ? $productInfo->getId() : $productInfo;
		$product = $this->productRepository->getById($productId);
		
		// Only apply limit validation for tile samples
		if ($this->samplesLimit->isTileSample($product)) {
			$qty = isset($requestInfo['qty']) ? (int)$requestInfo['qty'] : 1;
			
			if (!$this->samplesLimit->validateLimit($product, $qty)) {
				throw new LocalizedException(
					__('You can only order 4 samples. If you want to change one, remove it from the Cart.')
				);
			}
		}
		
		return [$productInfo, $requestInfo];
	}
}