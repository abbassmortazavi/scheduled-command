<?php

namespace App\Services;

use App\Models\Shipment;

interface ShipmentServiceInterface
{
    public function update(Shipment $shipment, array $attributes): void;

    public function updateInProgressShipments();
}
