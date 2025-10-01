<?php

namespace App;

enum ShipmentStatusEnum: string
{
    case IN_DISTRIBUTON_CENTER= "in_distribution_center";
    case OUT_FOR_DELIVERY= "out_for_delivery";
    case DELIVERED= "delivered";
    case IN_PROGRESS= "in_progress";
    /**
     * @return array
     */
    public static function getValues(): array
    {
        return array_map(fn(self $enum) => $enum->value, self::cases());
    }
}


