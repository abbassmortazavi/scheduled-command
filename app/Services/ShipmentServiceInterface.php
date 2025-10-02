<?php

namespace App\Services;

use App\Models\Shipment;

interface ShipmentServiceInterface
{
    /**
     * @param Shipment $shipment
     * @param array $attributes
     * @return void
     */
    public function update(Shipment $shipment, array $attributes): void;

    /**
     * @return mixed
     */
    public function updateInProgressShipments(): mixed;
}
