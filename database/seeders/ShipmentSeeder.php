<?php

namespace Database\Seeders;

use App\Enums\ShipmentStatusEnum;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Random\RandomException;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory(5)->create();
        }

        $shipments = [];

        // Generate 5000 sample shipments
        for ($i = 0; $i < 5000; $i++) {
            $status = $this->getRandomStatus();

            $shipments[] = [
                'tracking_number' => $this->generateTrackingNumber(),
                'status' => $status,
                'last_status_check' => $this->getLastStatusCheckDate($status),
                'user_id' => $users->random()->id,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($shipments, 25) as $chunk) {
            Shipment::query()->insert($chunk);
        }

        $this->command->info('50 shipments seeded successfully!');
        $this->command->info('Status distribution:');

        $statusCounts = Shipment::groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($statusCounts as $status => $count) {
            $this->command->info("- {$status}: {$count} shipments");
        }

    }

    /**
     * Generate a unique tracking number
     * @throws RandomException
     */
    private function generateTrackingNumber(): string
    {
        $prefix = 'TRK';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(bin2hex(random_bytes(4)));

        return "{$prefix}{$timestamp}{$random}";
    }

    /**
     * @return string
     */
    private function getRandomStatus(): string
    {
        $weights = [
            ShipmentStatusEnum::IN_PROGRESS->value => 30,
            ShipmentStatusEnum::IN_DISTRIBUTON_CENTER->value => 25,
            ShipmentStatusEnum::OUT_FOR_DELIVERY->value => 20,
            ShipmentStatusEnum::DELIVERED->value => 15,
            ShipmentStatusEnum::FAILED->value => 5,
            ShipmentStatusEnum::RETURNED->value => 5,
        ];

        $total = array_sum($weights);
        $random = rand(1, $total);
        $current = 0;

        foreach ($weights as $status => $weight) {
            $current += $weight;
            if ($random <= $current) {
                return $status;
            }
        }

        return ShipmentStatusEnum::IN_PROGRESS->value;
    }

    /**
     * @param string $status
     * @return Carbon|null
     */
    private function getLastStatusCheckDate(string $status): ?Carbon
    {
        return match ($status) {
            ShipmentStatusEnum::IN_PROGRESS->value => now()->subHours(rand(1, 12)),
            ShipmentStatusEnum::OUT_FOR_DELIVERY->value => now()->subHours(rand(1, 6)),
            ShipmentStatusEnum::DELIVERED->value => now()->subDays(rand(1, 7)),
            ShipmentStatusEnum::FAILED->value => now()->subDays(rand(1, 3)),
            ShipmentStatusEnum::RETURNED->value => now()->subDays(rand(3, 10)),
            default => now()->subHours(rand(1, 24)),
        };
    }
}
