<?php

namespace App\Enums;

enum ShipmentStatusEnum: string
{
    case IN_DISTRIBUTON_CENTER= "in_distribution_center";
    case OUT_FOR_DELIVERY= "out_for_delivery";
    case DELIVERED= "delivered";
    case IN_PROGRESS= "in_progress";
    case FAILED= "failed";
    case RETURNED= "returned";
    /**
     * @return array
     */
    public static function getValues(): array
    {
        return array_map(fn(self $enum) => $enum->value, self::cases());
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return match($this) {
            self::IN_PROGRESS => 'در حال ارسال',
            self::IN_DISTRIBUTON_CENTER => 'رسیده به مرکز توزیع',
            self::OUT_FOR_DELIVERY => 'در حال تحویل',
            self::DELIVERED => 'تحویل داده شده',
            self::FAILED => 'ناموفق',
            self::RETURNED => 'مرجوع شده',
        };
    }
}


