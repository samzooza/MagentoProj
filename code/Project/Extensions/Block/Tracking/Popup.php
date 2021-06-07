<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Project\Extensions\Block\Tracking;

use Project\Extensions\Model\Scg;

/**
 * @api
 * @since 100.0.2
 */
class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @var Scg
     */
    protected $_registry;
    private $scg;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Scg $scg
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Scg $scg,
        array $data = []
        
    ) {
        $this->_registry = $registry;
        $this->scg = $scg;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve array of tracking info
     *
     * @return array
     */
    public function getTrackingInfo()
    {
        $info = $this->_registry->registry('current_shipping_info');
        return $info->getTrackingInfo();
    }

    public function getSCGTrackingDetail($track)
    {   
        $number = is_object($track) ? $track->getTracking() : $track['number'];

        $trackingNumbers = array();
        array_push($trackingNumbers, $number);

        $response = $this->scg->GetTrackingInformation($trackingNumbers);
        return json_decode($response, true);
    }
}
