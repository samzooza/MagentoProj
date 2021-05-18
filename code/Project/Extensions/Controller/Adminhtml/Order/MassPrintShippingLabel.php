<?php

namespace Project\Extensions\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

use Project\Extensions\Model\Scg;

/**
 * Class MassPrintShippingLabel
 */
class MassPrintShippingLabel extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    protected $fileFactory;
    protected $filesystem;
    protected $scg;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param Scg $scg
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        Scg $scg
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
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

        $trackingNumbers = array();

        foreach ($collection->getItems() as $order)
        {   // push all selected tracking numbers into array
            $tracksCollection = $order->getTracksCollection();

            foreach ($tracksCollection->getItems() as $track)
                if(!empty($track->getTrackNumber()))
                    array_push($trackingNumbers, $track->getTrackNumber());
        }

        if(!empty($trackingNumbers))
        {
            // Print shipping labels
            $file = $this->scg->GetMobileLabel(join(",", $trackingNumbers));
            
            /* to debug reponse value */
            //$this->messageManager->addError(__(strlen($file)));
            //return $this->Refresh();
            /* end to debug reponse value */

            // Save file into temp (\var\tmp)
            $filename = $this->SaveFile($file);

            return $this->fileFactory->create('shipping_labels.pdf', [
                'type' => 'filename',
                'value' => $filename,
                'rm' => true
            ]);
        }
        else
        {
            $this->messageManager->addError(__('You can\'t print SCG shipping labels, not found SCG tracking number.'));
            return $this->Refresh();
        }
    }

    public function SaveFile($file)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $destination = $directory->getAbsolutePath(sprintf('export-%s.pdf', date('Ymd-His')));

        file_put_contents($destination, $file);

        return $destination;
    }

    protected function Refresh(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    } 
}