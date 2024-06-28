<?php

namespace App\Enumerations;
/**
 * Class PaymentType
 * @package App\Enumerations;
 */
final class PaymentType
{
    public const TYPE_SHIFT4 = 'shift4';

    public const TYPE_ACI = 'aci';


    /**
     * @return array
     */
    public static function getCollection(): array
    {
        return [
            self::TYPE_ACI,
            self::TYPE_SHIFT4
        ];
    }
}
