<?php
/**
 * @package     CravenDunnill_TileSamplesLimit
 * @author      Craven Dunnill
 * @copyright   Copyright (c) Craven Dunnill (https://cravendunnill.co.uk)
 */

namespace CravenDunnill\TileSamplesLimit\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item;

class SamplesLimit
{
	const SAMPLE_ATTRIBUTE_SET = 'Tile Samples';
	
	/**
	 * @var CheckoutSession
	 */
	private $checkoutSession;
	
	/**
	 * @var ManagerInterface
	 */
	private $messageManager;
	
	/**
	 * @var int
	 */
	private $samplesLimit;

	/**
	 * @var AttributeSetRepositoryInterface
	 */
	private $attributeSetRepository;

	/**
	 * @var ProductRepositoryInterface
	 */
	private $productRepository;

	/**
	 * @param CheckoutSession $checkoutSession
	 * @param ManagerInterface $messageManager
	 * @param AttributeSetRepositoryInterface $attributeSetRepository
	 * @param ProductRepositoryInterface $productRepository
	 * @param int $samplesLimit
	 */
	public function __construct(
		CheckoutSession $checkoutSession,
		ManagerInterface $messageManager,
		AttributeSetRepositoryInterface $attributeSetRepository,
		ProductRepositoryInterface $productRepository,
		int $samplesLimit = 4
	) {
		$this->checkoutSession = $checkoutSession;
		$this->messageManager = $messageManager;
		$this->attributeSetRepository = $attributeSetRepository;
		$this->productRepository = $productRepository;
		$this->samplesLimit = $samplesLimit;
	}

	/**
	 * Check if product is a tile sample
	 *
	 * @param Product $product
	 * @return bool
	 */
	public function isTileSample(Product $product): bool
	{
		try {
			// Get attribute set ID from product
			$attributeSetId = $product->getAttributeSetId();
			if (!$attributeSetId) {
				return false;
			}
			
			// Load attribute set by ID
			$attributeSet = $this->attributeSetRepository->get($attributeSetId);
			
			// Compare attribute set name with exact match
			return $attributeSet->getAttributeSetName() === self::SAMPLE_ATTRIBUTE_SET;
		} catch (NoSuchEntityException $e) {
			return false;
		} catch (\Exception $e) {
			// Added more robust error handling
			return false;
		}
	}

	/**
	 * Count samples in cart
	 *
	 * @return int
	 */
	public function countSamplesInCart(): int
	{
		$quote = $this->checkoutSession->getQuote();
		$sampleCount = 0;
		
		foreach ($quote->getAllItems() as $item) {
			try {
				$productId = $item->getProductId();
				if (!$productId) {
					continue;
				}
				
				$product = $this->productRepository->getById($productId);
				if ($this->isTileSample($product)) {
					$sampleCount += (int)$item->getQty();
				}
			} catch (NoSuchEntityException $e) {
				continue;
			} catch (\Exception $e) {
				// Added more robust error handling
				continue;
			}
		}
		
		return (int)$sampleCount;
	}

	/**
	 * Validate samples limit
	 *
	 * @param Product $product
	 * @param int $qty
	 * @return bool
	 * @throws LocalizedException
	 */
	public function validateLimit(Product $product, int $qty = 1): bool
	{
		// First explicitly check if this is a tile sample
		if (!$this->isTileSample($product)) {
			return true;
		}

		$currentSamplesCount = $this->countSamplesInCart();
		
		if ($currentSamplesCount + $qty > $this->samplesLimit) {
			$this->messageManager->addErrorMessage(
				__('You can only order 4 samples. If you want to change one, remove it from the Cart.')
			);
			return false;
		}
		
		return true;
	}
}