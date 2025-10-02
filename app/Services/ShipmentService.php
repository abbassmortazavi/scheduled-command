<?php

namespace App\Services;

use App\Enums\ShipmentStatusEnum;
use App\Models\Shipment;
use Exception;
use Illuminate\Support\Facades\Log;

class ShipmentService implements ShipmentServiceInterface
{
    /**
     * @param Shipment $shipment
     * @param array $attributes
     * @return void
     */
    public function update(Shipment $shipment, array $attributes): void
    {
        $shipment->update($attributes);
    }

    /**
     * @return int[]
     */
    public function updateInProgressShipments(): array
    {
        $updatedCount = 0;
        $errorCount = 0;

        Shipment::query()
            ->where('status', '=', ShipmentStatusEnum::IN_PROGRESS->value)
            ->chunk(100, function ($shipments) use (&$updatedCount, &$errorCount) {
                foreach ($shipments as $shipment) {
                    try {
                        $newStatus = $this->fetchStatusFromExternalApi($shipment->tracking_number);

                        if ($newStatus && $newStatus !== $shipment->status) {
                            $this->update($shipment, [
                                'status' => $newStatus,
                                'last_status_check' => now(),
                            ]);

                            $updatedCount++;
                            Log::info("Shipment {$shipment->tracking_number} updated to {$newStatus}");
                        }
                    } catch (Exception $e) {
                        $errorCount++;
                        Log::error("Error updating shipment {$shipment->tracking_number}: " . $e->getMessage());
                    }
                }
            });

        return [$updatedCount, $errorCount];
    }

    /**
     * @param string $trackingNumber
     * @return string|null
     */
    private function fetchStatusFromExternalApi(string $trackingNumber): ?string
    {
        try {
            $statuses = ShipmentStatusEnum::getValues();
            $list = array_filter($statuses, fn($item) => $item !== ShipmentStatusEnum::IN_PROGRESS->value);
            return $statuses[array_rand($list)];
        } catch (Exception $e) {
            Log::warning("External API call failed for tracking {$trackingNumber}: " . $e->getMessage());
            return null;
        }
    }
}
