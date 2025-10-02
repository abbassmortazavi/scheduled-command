<?php

namespace App\Console\Commands;

use App\Services\ShipmentServiceInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdateShipmentStatuses extends Command
{
    /**
     * @param ShipmentServiceInterface $shipmentService
     */
    public function __construct(private readonly ShipmentServiceInterface $shipmentService)
    {
        parent::__construct();
    }

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
    public function handle(): int
    {
        $this->info('Starting shipment status update process...');
        Log::info('Shipment status update job started');
        try {
            [$updatedCount, $errorCount] = $this->shipmentService->updateInProgressShipments();
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
}
