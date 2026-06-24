<?php

namespace Domain\Memberships\Enums;

enum ValidationPlanPrivilegeType: string
{
    case INSURANCE = 'insurance';
    case LICENSE = 'license';
    case ENTITY_MEMBER_LICENSES = 'entity_member_licenses';
    case ENTITY_MEMBER_SUBSCRIPTIONS = 'entity_member_subscriptions';

    public function getDescription(): string
    {
        return match ($this) {
            self::INSURANCE => 'Request insurance policies',
            self::LICENSE => 'Request licenses and certifications',
            self::ENTITY_MEMBER_LICENSES => 'Request licenses for entity members',
            self::ENTITY_MEMBER_SUBSCRIPTIONS => 'Subscribe members to membership packages',
        };
    }

    public function getFailureMessage(): string
    {
        return match ($this) {
            self::INSURANCE => __('memberships.validation_plan_no_insurance_privileges'),
            self::LICENSE => __('memberships.validation_plan_no_license_privileges'),
            self::ENTITY_MEMBER_LICENSES => __('memberships.validation_plan_no_entity_member_licenses'),
            self::ENTITY_MEMBER_SUBSCRIPTIONS => __('memberships.validation_plan_no_entity_member_subscriptions'),
        };
    }
}
