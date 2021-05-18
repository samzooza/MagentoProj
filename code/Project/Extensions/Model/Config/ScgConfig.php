<?php
namespace Project\Extensions\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ScgConfig
{
    const XML_PATH_ENABLED = 'scgexpress/general/enabled';
    const XML_PATH_URI = 'scgexpress/apisetting/uri';
    const XML_PATH_USERNAME = 'scgexpress/apisetting/username';
    const XML_PATH_PASSWORD = 'scgexpress/apisetting/password';
    const XML_PATH_CODE = 'scgexpress/shipper_details/code';
    const XML_PATH_NAME = 'scgexpress/shipper_details/name';
    const XML_PATH_TEL = 'scgexpress/shipper_details/tel';
    const XML_PATH_ADDRESS = 'scgexpress/shipper_details/address';
    const XML_PATH_ZIPCODE = 'scgexpress/shipper_details/zipcode';

    private $config;

    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    public function isEnabled()
    {
        return $this->config->getValue(self::XML_PATH_ENABLED);
    }

    public function getUri()
    {
        return $this->config->getValue(self::XML_PATH_URI);
    }

    public function getUsername()
    {
        return $this->config->getValue(self::XML_PATH_USERNAME);
    }

    public function getPassword()
    {
        return $this->config->getValue(self::XML_PATH_PASSWORD);
    }

    public function getShipperCode()
    {
        return $this->config->getValue(self::XML_PATH_CODE);
    }

    public function getShipperName()
    {
        return $this->config->getValue(self::XML_PATH_NAME);
    }

    public function getShipperTel()
    {
        return $this->config->getValue(self::XML_PATH_TEL);
    }

    public function getShipperAddress()
    {
        return $this->config->getValue(self::XML_PATH_ADDRESS);
    }

    public function getShipperZipcode()
    {
        return $this->config->getValue(self::XML_PATH_ZIPCODE);
    }
}