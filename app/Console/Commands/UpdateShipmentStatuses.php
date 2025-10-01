<?php

namespace App\Console\Commands;

use App\Enums\ShipmentStatusEnum;
use App\Models\Shipment;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdateShipmentStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipments:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update statuses of in-progress shipments from external API';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->info('Starting shipment status update process...');
        Log::info('Shipment status update job started');

        $updatedCount = 0;
        $errorCount = 0;
        try {
            Shipment::query()->where('status', '=', ShipmentStatusEnum::IN_PROGRESS->value)
                ->chunk(100, function ($shipments) use (&$updatedCount, &$errorCount) {
                    foreach ($shipments as $shipment) {
                        try {
                            $newStatus = $this->fetchStatusFromExternalApi($shipment->tracking_number);

                            if ($newStatus && $newStatus !== $shipment->status) {
                                $shipment->update([
                                    'status' => $newStatus,
                                    'last_status_check' => now(),
                                ]);

                                $updatedCount++;
                                $this->info("Updated shipment {$shipment->tracking_number} to status: {$newStatus}");
                            }

                        } catch (Exception $e) {
                            $errorCount++;
                            Log::error("Failed to update shipment {$shipment->tracking_number}: " . $e->getMessage());
                            $this->error("Error updating shipment {$shipment->tracking_number}: " . $e->getMessage());
                        }
                    }
                });

            $logMessage = "Shipment status update completed. Updated: {$updatedCount}, Errors: {$errorCount}";
            $this->info($logMessage);
            Log::info($logMessage);

            return CommandAlias::SUCCESS;

        } catch (Exception $e) {
            $errorMessage = "Critical error in shipment status update: " . $e->getMessage();
            Log::error($errorMessage);
            $this->error($errorMessage);

            return CommandAlias::FAILURE;
        }
    }

    private function fetchStatusFromExternalApi(string $trackingNumber): ?string
    {
        try {
            sleep(1);

            $statusOptions = [
                'in_distribution_center',
                'out_for_delivery',
                'delivered',
                'in_progress'
            ];


            $hash = crc32($trackingNumber) % 100;

            if ($hash < 30) return 'in_distribution_center';
            if ($hash < 60) return 'out_for_delivery';
            if ($hash < 90) return 'delivered';

            return 'in_progress';

        } catch (Exception $e) {
            Log::warning("External API call failed for tracking {$trackingNumber}: " . $e->getMessage());
            return null;
        }
    }
}
