<?php

namespace Drupal\commerce_custom_fees;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariationInterface;

/**
 * Applies custom fees to orders during the order refresh process.
 */
class CustomFeesOrderProcessor implements OrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    if ($this->hasHazardousItems($order)) {
      $this->addFee($order, t('Hazardous'), new Price('30.00', 'USD'));
    }
  }

  protected function addFee(OrderInterface $order, $label, Price $price) {
    $order->addAdjustment(new Adjustment([
      'type' => 'custom_fee',
      'label' => $label,
      'amount' => $price,
      'source_id' => 'commerce_custom_fees',
    ]));
  }

  protected function hasHazardousItems(OrderInterface $order) {
    $hazardous = FALSE;
    $field = 'field_hazardous';

    foreach ($order->getItems() as $orderItem) {
      $variation = $orderItem->getPurchasedEntity();

      if ($variation instanceof ProductVariationInterface) {
        $product = $variation->getProduct();

        if ($product->hasField($field) && $product->get($field)->value) {
          $hazardous = TRUE;
          break;
        }
      }
    }

    return $hazardous;
  }
}
