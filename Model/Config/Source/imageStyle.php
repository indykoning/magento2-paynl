<?php
/**
 * Copyright © 2020 PAY. All rights reserved.
 */

namespace Paynl\Payment\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class imageStyle implements ArrayInterface
{

  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray()
  {
    $arrOptions = $this->toArray();

    $arrResult = [];
    foreach ($arrOptions as $value => $label) {
      $arrResult[] = ['value' => $value, 'label' => $label];
    }
    return $arrResult;
  }

  /**
   * Get options in "key-value" format
   *
   * @return array
   */
  public function toArray()
  {
    return [
      'classic' => __('Classic'),
      'newest' => __('Newest'),
    ];
  }

}
