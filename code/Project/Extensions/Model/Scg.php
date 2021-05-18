<?php
namespace Project\Extensions\Model;

use Project\Extensions\Model\DataAccess\DataAccess as dataAccess;
use Project\Extensions\Model\Config\ScgConfig;

class Scg extends DataAccess
{
    private $scgConfig;

    public function __construct(ScgConfig $scgConfig)
    {
        $this->scgConfig = $scgConfig;
    }

    public function IsEnabled()
    {
        return $this->scgConfig->IsEnabled();
    }

    public function PlaceOrder($deliveryAddress, $zipcode, $contactName, $tel, $orderCode, $totalBoxs, $orderDate)
    {
        return $this->PostWithKey(
            $this->scgConfig->getUri().'/api/orderwithouttrackingnumber',
            array(
                'ShipperCode' => $this->scgConfig->getShipperCode(),
                'ShipperName' => $this->scgConfig->getShipperName(),
                'ShipperTel' => $this->scgConfig->getShipperTel(),
                'ShipperAddress' => $this->scgConfig->getShipperAddress(),
                'ShipperZipcode' => $this->scgConfig->getShipperZipcode(),
                'DeliveryAddress' => $deliveryAddress,
                'Zipcode' => $zipcode,
                'ContactName' => $contactName,
                'Tel' => $tel,
                'OrderCode' => $orderCode,
                'TotalBoxs' => $totalBoxs,
                'OrderDate' => $orderDate
            ));
    }

    public function GetMobileLabel($tracking_numbers)
    {
        return $this->PostWithKey(
            $this->scgConfig->getUri().'/api/getMobileLabel',
            array(
                'tracking_number' => $tracking_numbers
            ));
    }

    function PostWithKey($uri, $param)
    {
        // get authentication
        $response = json_decode($this->GetKey(), true);

        if($response['status'])
        {
            // add postback token into next parameters
            $param['token'] = $response['token'];
            return $this->Post($uri, $param);
        }

        return $response;
    }

    public function GetKey()
    {
        return $this->Post(
            $this->scgConfig->getUri().'/api/authentication',
            array(
                'username' => $this->scgConfig->getUsername(),
                'password' => $this->scgConfig->getPassword()
            ));
    }
}