<?php

namespace Project\Extensions\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Customer\Model\Customer;

use Project\Extensions\Model\Scg;

/**
 * Class MassPlaceorder
 */
class MassPlaceorder extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var Customer
     * @var Scg
     */
    protected $customer;
    protected $scg;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Customer $customer
     * @param Scg $scg
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Customer $customer,
        Scg $scg
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->customer = $customer;
        $this->scg = $scg;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        // check scg configuration is enabled 
        if(!$this->scg->IsEnabled())
        {
            $this->messageManager->addError(__(
                'This function is disabled, please check the configuration in "Stores > Configuration > SCG Express".'
            ));

            return $this->Refresh();
        }

        foreach ($collection->getItems() as $order)
        {
            // check the order can ship out
            if (!$order->canShip()) {
                $this->messageManager->addError(__('ID '.strval($order->getEntityId()).': You can\'t create an shipment.'));
                continue;
            }

            // place order to SCG Express
            $response = json_decode($this->PlaceOrder($order), true);

            if(isset($response['status']) && !$response['status'])
            {
                $this->messageManager->addError(__('ID '.strval($order->getEntityId()).': '.$response['message']));
                continue;
            }

            $this->Ship($order, $response['trackingNumber']);
            $this->messageManager->addSuccess(__('ID '.strval($order->getEntityId()).': Successful with tracking number(s): '.$response['trackingNumber'].'.'));
        }

        return $this->Refresh();
    }

    protected function PlaceOrder($order)
    {
        $shippingAddress = $order->getShippingAddress();

        return $this->scg->PlaceOrder(
            $shippingAddress->getData("street").' '.$shippingAddress->getData("city"),
            $shippingAddress->getData("postcode"),
            $shippingAddress->getData("firstname").' '.$shippingAddress->getData("lastname"),
            $shippingAddress->getData("telephone"),
            $order->getEntityId(),
            '1',
            date("Y-m-d"));
    }

    protected function Ship($order, $trackingNumerObj): void
    {
        // initialize the order shipment object
        $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipment = $convertOrder->toShipment($order);

        // loop through order items
        foreach ($order->getAllItems() AS $orderItem) {
            // check if order item is virtual or has quantity to ship
            if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();

            // create shipment item with qty
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

            // add shipment item to shipment
            $shipment->addItem($shipmentItem);
        }

        // register shipment
        $shipment->register();

        $trackingNumber = (gettype($trackingNumerObj)=='array')
                ? implode(" , ",$trackingNumerObj)
                : $trackingNumerObj;
        
        $data = array(
            'carrier_code' => 'Custom Value',
            'title' => 'SCG Express',
            'number' => $trackingNumber, // replace with SCG tracking number
        );

        $shipment->getOrder()->setIsInProcess(true);

        try {
            // save created shipment and order
            $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
            $shipment->addTrack($track)->save();
            $shipment->save();
            $shipment->getOrder()->save();

            // to send email notification
            // $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
            // ->notify($shipment);

            $shipment->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }

    protected function Refresh(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        
        return $resultRedirect;
    } 
}