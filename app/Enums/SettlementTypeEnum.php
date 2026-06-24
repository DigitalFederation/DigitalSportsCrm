<?php

namespace App\Enums;

enum SettlementTypeEnum: string
{
    case ONLINE_PAYMENT = 'online_payment'; // Automatically set after successful online payment
    case OFFLINE_PAYMENT = 'offline_payment'; // Manually set by admin for offline payments
    case STOCK_EXCHANGE = 'stock_exchange'; // Manually set by admin for stock exchanges
    case MANUAL_APPROVAL = 'manual_approval'; // General manual approval by admin

    /**
     * Get the display label for the enum case.
     */
    public function label(): string
    {
        // Consider using translation keys for i18n
        return match ($this) {
            self::ONLINE_PAYMENT => 'Online Payment',
            self::OFFLINE_PAYMENT => 'Offline Payment',
            self::STOCK_EXCHANGE => 'Stock Exchange',
            self::MANUAL_APPROVAL => 'Manual Approval',
        };
    }

    /**
     * Get all enum values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
