<?php

declare(strict_types=1);

namespace M2E\OnBuy\Model\Magento\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;

class Track
{
    private \Magento\Sales\Model\Order $magentoOrder;
    private array $trackingDetails;
    private \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory;
    private \M2E\OnBuy\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager;

    public function __construct(
        \Magento\Sales\Model\Order $magentoOrder,
        array $trackingDetails,
        \M2E\OnBuy\Observer\Shipment\EventRuntimeManager $shipmentEventRuntimeManager,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory
    ) {
        $this->magentoOrder = $magentoOrder;
        $this->shipmentTrackFactory = $shipmentTrackFactory;
        $this->shipmentEventRuntimeManager = $shipmentEventRuntimeManager;
        $this->trackingDetails = $trackingDetails;
    }

    public function create(): array
    {
        $trackingDetails = $this->getFilteredTrackingDetails();
        if (empty($trackingDetails)) {
            return [];
        }

        // Skip shipment observer
        // ---------------------------------------
        $this->shipmentEventRuntimeManager->skipEvents();
        // ---------------------------------------

        $shipments = $this->magentoOrder->getShipmentsCollection()->getItems();

        if (empty($shipments)) {
            return [];
        }

        $tracks = [];
        foreach ($trackingDetails as $trackingDetail) {
            $trackNumber = (string)$trackingDetail['tracking_number'];
            $shipment = $this->findShipmentWithoutTrack($shipments, $trackNumber);

            if ($shipment === null) {
                return [];
            }

            // Sometimes Magento returns an array instead of Collection by a call of $shipment->getTracksCollection()
            if (
                $shipment->hasData(ShipmentInterface::TRACKS)
                && !($shipment->getData(ShipmentInterface::TRACKS) instanceof TrackCollection)
            ) {
                $shipment->unsetData(ShipmentInterface::TRACKS);
            }

            $track = $this->shipmentTrackFactory->create();
            $track->setNumber($trackingDetail['tracking_number']);
            $track->setTitle($trackingDetail['supplier_name']);
            $track->setCarrierCode($trackingDetail['supplier_name']);

            $shipment->addTrack($track)
                     ->save();

            $tracks[] = $track;
        }

        return $tracks;
    }

    private function getFilteredTrackingDetails(): array
    {
        if (empty($this->magentoOrder->getTracksCollection()->getSize())) {
            return $this->trackingDetails;
        }

        foreach ($this->magentoOrder->getTracksCollection() as $track) {
            foreach ($this->trackingDetails as $key => $trackingDetail) {
                if (strtolower((string)$track->getData('track_number')) === strtolower((string)$trackingDetail['tracking_number'])) {
                    unset($this->trackingDetails[$key]);
                }
            }
        }

        return $this->trackingDetails;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment[] $shipments
     * @param string $trackNumber
     *
     * @return \Magento\Sales\Model\Order\Shipment|null
     */
    private function findShipmentWithoutTrack(array $shipments, string $trackNumber): ?\Magento\Sales\Model\Order\Shipment
    {
        $shipmentWithoutTracks = [];
        foreach ($shipments as $shipment) {
            if ($this->isTrackNumberExistInShipment($trackNumber, $shipment)) {
                continue;
            }

            $shipmentWithoutTracks[] = $shipment;
        }

        if (empty($shipmentWithoutTracks)) {
            return null;
        }

        return $shipmentWithoutTracks[0];
    }

    private function isTrackNumberExistInShipment(
        string $trackNumber,
        \Magento\Sales\Model\Order\Shipment $shipment
    ): bool {
        $trackNumber = $this->clearTrackNumber($trackNumber);

        foreach ($shipment->getTracks() as $track) {
            $shippingTrackNumber = $this->clearTrackNumber($track->getTrackNumber());
            if ($shippingTrackNumber === $trackNumber) {
                return true;
            }
        }

        return false;
    }

    private function clearTrackNumber(string $trackNumber): string
    {
        return str_replace(['/', ' ', '-'], '', $trackNumber);
    }
}
