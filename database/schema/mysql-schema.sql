/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `affiliation_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliation_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration_months` int NOT NULL,
  `base_fee` decimal(10,2) NOT NULL,
  `type` enum('individual','entity') COLLATE utf8mb4_unicode_ci NOT NULL,
  `individual_fee` decimal(10,2) DEFAULT NULL,
  `entity_fee` decimal(10,2) DEFAULT NULL,
  `moloni_reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_rate` int NOT NULL DEFAULT '23' COMMENT 'VAT rate percentage (0, 6, 13, 23)',
  `is_validation_plan` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliation_plans_federation_id_foreign` (`federation_id`),
  CONSTRAINT `affiliation_plans_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `affiliations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `affiliations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `member_subscription_id` bigint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `individual_fee` decimal(10,2) DEFAULT NULL,
  `entity_fee` decimal(10,2) DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requester_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requester_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_type` enum('direct','entity_group','federation_facilitated') COLLATE utf8mb4_unicode_ci DEFAULT 'direct',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `member_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliations_federation_id_foreign` (`federation_id`),
  KEY `affiliations_member_subscription_id_foreign` (`member_subscription_id`),
  KEY `affiliations_member_type_member_id_index` (`member_type`,`member_id`),
  KEY `affiliations_requester_type_requester_id_index` (`requester_type`,`requester_id`),
  CONSTRAINT `affiliations_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliations_member_subscription_id_foreign` FOREIGN KEY (`member_subscription_id`) REFERENCES `member_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint unsigned NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `section` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_application_comments_application` (`application_id`),
  KEY `idx_application_comments_user` (`user_id`),
  CONSTRAINT `application_comments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `event_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint unsigned DEFAULT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by_id` bigint unsigned DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_application_documents_application` (`application_id`),
  KEY `idx_application_documents_template` (`template_id`),
  CONSTRAINT `application_documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `event_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_documents_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `application_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_state_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_state_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint unsigned NOT NULL,
  `from_state` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_state` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state_history_application` (`application_id`),
  KEY `idx_state_history_user` (`changed_by`),
  CONSTRAINT `application_state_history_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `event_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_state_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `application_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` enum('organization','competition') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_id` bigint unsigned DEFAULT NULL,
  `event_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submission_start_date` date NOT NULL,
  `submission_end_date` date NOT NULL,
  `event_start_date` date DEFAULT NULL,
  `event_end_date` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `target_audience` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'both',
  `state` enum('draft','open','closed','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `max_applications` int DEFAULT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_templates_sport_id_foreign` (`sport_id`),
  KEY `application_templates_created_by_foreign` (`created_by`),
  KEY `idx_templates_active` (`is_active`),
  KEY `idx_templates_submission_dates` (`submission_start_date`,`submission_end_date`),
  KEY `idx_templates_event_type` (`event_type`),
  KEY `application_templates_state_index` (`state`),
  CONSTRAINT `application_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_templates_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `evt_sports` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `athlete_enrollment_status_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `athlete_enrollment_status_backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `individual_id` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `discipline_id` text COLLATE utf8mb4_unicode_ci,
  `old_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `migrated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachment_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attachment_categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachment_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment_certifications` (
  `attachment_id` bigint unsigned NOT NULL,
  `certification_id` bigint unsigned NOT NULL,
  KEY `attachment_certifications_attachment_id_foreign` (`attachment_id`),
  KEY `attachment_certifications_certification_id_foreign` (`certification_id`),
  CONSTRAINT `attachment_certifications_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachment_certifications_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachment_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment_countries` (
  `attachment_id` bigint unsigned NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  KEY `attachment_countries_attachment_id_foreign` (`attachment_id`),
  KEY `attachment_countries_country_id_foreign` (`country_id`),
  CONSTRAINT `attachment_countries_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachment_countries_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `license` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachment_filterfederations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment_filterfederations` (
  `attachment_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned NOT NULL,
  KEY `attachment_filterfederations_attachment_id_foreign` (`attachment_id`),
  KEY `attachment_filterfederations_federation_id_foreign` (`federation_id`),
  CONSTRAINT `attachment_filterfederations_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachment_filterfederations_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachment_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment_licenses` (
  `attachment_id` bigint unsigned NOT NULL,
  `license_id` bigint unsigned NOT NULL,
  KEY `attachment_licenses_attachment_id_foreign` (`attachment_id`),
  KEY `attachment_licenses_license_id_foreign` (`license_id`),
  CONSTRAINT `attachment_licenses_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachment_licenses_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachment_professional_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachment_professional_roles` (
  `attachment_id` bigint unsigned NOT NULL,
  `professional_role_id` bigint unsigned NOT NULL,
  KEY `attachment_professional_roles_attachment_id_foreign` (`attachment_id`),
  KEY `attachment_professional_roles_professional_role_id_foreign` (`professional_role_id`),
  CONSTRAINT `attachment_professional_roles_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attachment_professional_roles_professional_role_id_foreign` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `language_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `committee_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_owner_type_owner_id_index` (`owner_type`,`owner_id`),
  KEY `attachments_recipient_type_recipient_id_index` (`recipient_type`,`recipient_id`),
  KEY `attachments_category_id_foreign` (`category_id`),
  KEY `attachments_language_id_foreign` (`language_id`),
  CONSTRAINT `attachments_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `attachment_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attachments_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `backup_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `backup_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certification` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` bigint unsigned NOT NULL,
  `certification_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_age` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confined_water_sessions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_water_sessions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theoretical_sessions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `unit_value` decimal(10,2) DEFAULT NULL,
  `unit_value_entity` decimal(10,2) DEFAULT NULL,
  `unit_value_federation` decimal(10,2) DEFAULT NULL,
  `tax_value` decimal(10,2) DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT NULL,
  `moloni_reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `digital_price` decimal(10,2) DEFAULT NULL,
  `digital_plus_card_price` decimal(10,2) DEFAULT NULL,
  `requester_model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `allow_entity_group_request` tinyint(1) NOT NULL DEFAULT '0',
  `requires_admin_validation` tinyint(1) NOT NULL DEFAULT '0',
  `certification_view` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `acronym` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_es` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_fr` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `license_id` bigint unsigned DEFAULT NULL,
  `professional_role_id` bigint unsigned DEFAULT NULL,
  `offset_initial` int NOT NULL DEFAULT '0',
  `offset_current` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `certification_committee_id_foreign` (`committee_id`),
  KEY `certification_license_id_foreign` (`license_id`),
  KEY `certification_professional_role_id_foreign` (`professional_role_id`),
  CONSTRAINT `certification_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`),
  CONSTRAINT `certification_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`),
  CONSTRAINT `certification_professional_role_id_foreign` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certification_attributed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certification_attributed` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certification_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned NOT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `status_class` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_code` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `holder_name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `federation_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructor_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `committee_id` bigint unsigned DEFAULT NULL,
  `code` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activator_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activator_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `current_term_starts_at` date DEFAULT NULL,
  `current_term_ends_at` date DEFAULT NULL,
  `last_billing_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `price_option` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'digital',
  `price_paid` decimal(10,2) DEFAULT NULL,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `international_code` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certification_attributed_international_code_unique` (`international_code`),
  KEY `certification_attributed_certification_id_foreign` (`certification_id`),
  KEY `certification_attributed_federation_id_foreign` (`federation_id`),
  KEY `certification_attributed_created_by_foreign` (`created_by`),
  KEY `certification_attributed_updated_by_foreign` (`updated_by`),
  KEY `certification_attributed_activator_id_index` (`activator_id`),
  KEY `certification_attributed_activator_type_index` (`activator_type`),
  KEY `certification_attributed_instructor_id_foreign` (`instructor_id`),
  KEY `certification_attributed_entity_id_foreign` (`entity_id`),
  KEY `certification_attributed_national_code_index` (`national_code`),
  KEY `certification_attributed_committee_id_foreign` (`committee_id`),
  KEY `certification_attributed_individual_id_foreign` (`individual_id`),
  KEY `certification_attributed_batch_id_index` (`batch_id`),
  CONSTRAINT `certification_attributed_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`),
  CONSTRAINT `certification_attributed_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`),
  CONSTRAINT `certification_attributed_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `certification_attributed_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`),
  CONSTRAINT `certification_attributed_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `certification_attributed_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE SET NULL,
  CONSTRAINT `certification_attributed_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `individual` (`id`),
  CONSTRAINT `certification_attributed_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certification_parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certification_parents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certification_parents_certification_id_foreign` (`certification_id`),
  KEY `certification_parents_parent_id_foreign` (`parent_id`),
  CONSTRAINT `certification_parents_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`),
  CONSTRAINT `certification_parents_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `certification` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certification_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certification_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `committee_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certification_role_committee_unique` (`certification_id`,`role_id`,`committee_id`),
  KEY `certification_roles_role_id_foreign` (`role_id`),
  KEY `certification_roles_committee_id_foreign` (`committee_id`),
  CONSTRAINT `certification_roles_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certification_roles_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certification_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certification_sport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certification_sport` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_id` bigint unsigned NOT NULL,
  `sport_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certification_sport_certification_id_sport_id_unique` (`certification_id`,`sport_id`),
  KEY `certification_sport_sport_id_foreign` (`sport_id`),
  CONSTRAINT `certification_sport_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certification_sport_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certifications_attributed_instructors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certifications_attributed_instructors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attributed_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructor_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certifications_attributed_instructors_attributed_id_foreign` (`attributed_id`),
  KEY `certifications_attributed_instructors_individual_id_foreign` (`individual_id`),
  CONSTRAINT `certifications_attributed_instructors_attributed_id_foreign` FOREIGN KEY (`attributed_id`) REFERENCES `certification_attributed` (`id`),
  CONSTRAINT `certifications_attributed_instructors_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `committee` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_international` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `country` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_region_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ioc` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `continent` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supported` tinyint NOT NULL DEFAULT '1',
  `geo_zone_id` bigint unsigned DEFAULT NULL,
  `sub_region_id` bigint unsigned DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `country_geo_zone_id_foreign` (`geo_zone_id`),
  KEY `country_sub_region_id_foreign` (`sub_region_id`),
  CONSTRAINT `country_geo_zone_id_foreign` FOREIGN KEY (`geo_zone_id`) REFERENCES `geo_zones` (`id`),
  CONSTRAINT `country_sub_region_id_foreign` FOREIGN KEY (`sub_region_id`) REFERENCES `sub_regions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `district_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `district_zone` (
  `district_id` bigint unsigned NOT NULL,
  `zone_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`district_id`,`zone_id`),
  KEY `district_zone_zone_id_foreign` (`zone_id`),
  CONSTRAINT `district_zone_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `district_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `districts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` bigint DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `districts_code_unique` (`code`),
  KEY `districts_country_id_index` (`country_id`),
  KEY `districts_is_active_index` (`is_active`),
  KEY `districts_code_index` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `diving_entity_technical_directors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diving_entity_technical_directors` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_attributed_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_id` bigint unsigned DEFAULT NULL,
  `certification_systems` json NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `diving_invite_entity_individual_idx` (`entity_id`,`individual_id`),
  KEY `diving_invite_status_idx` (`status_class`),
  KEY `diving_invite_license_idx` (`license_attributed_id`),
  KEY `diving_technical_director_invitations_license_id_foreign` (`license_id`),
  KEY `dtdi_individual_status_idx` (`individual_id`,`status_class`),
  KEY `dtdi_entity_status_idx` (`entity_id`,`status_class`),
  KEY `dtdi_license_attr_idx` (`license_attributed_id`),
  KEY `dtdi_status_idx` (`status_class`),
  KEY `tech_dir_approved_at_idx` (`approved_at`),
  KEY `tech_dir_rejected_at_idx` (`rejected_at`),
  KEY `tech_dir_license_approval_idx` (`license_attributed_id`,`approved_at`),
  CONSTRAINT `diving_invite_license_fk` FOREIGN KEY (`license_attributed_id`) REFERENCES `license_attributed` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diving_technical_director_invitations_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diving_technical_director_invitations_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diving_technical_director_invitations_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `diving_professional_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `diving_professional_certifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certification_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certification_system` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_type` enum('medical_statement','professional_insurance','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certification_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `national_equivalency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validation_notes` text COLLATE utf8mb4_unicode_ci,
  `validated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `diving_professional_certifications_validated_by_foreign` (`validated_by`),
  KEY `diving_cert_individual_system_idx` (`individual_id`,`certification_system`),
  KEY `diving_cert_status_idx` (`status_class`),
  KEY `diving_cert_expiration_idx` (`expiration_date`),
  KEY `dpc_individual_status_idx` (`individual_id`,`status_class`),
  KEY `dpc_cert_system_idx` (`certification_system`),
  KEY `dpc_status_idx` (`status_class`),
  CONSTRAINT `diving_professional_certifications_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `diving_professional_certifications_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `status_class` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` text COLLATE utf8mb4_unicode_ci,
  `owner_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `net_value` decimal(12,2) DEFAULT NULL,
  `tax_value` decimal(12,2) DEFAULT '0.00',
  `tax_percentage` decimal(5,2) DEFAULT '0.00',
  `total_value` decimal(12,2) DEFAULT NULL,
  `method_id` bigint unsigned DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `number` int unsigned DEFAULT NULL,
  `number_pad` int DEFAULT NULL,
  `number_year` year DEFAULT NULL,
  `number_extended` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_number` int unsigned DEFAULT NULL,
  `invoice_year` year DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT '0.00',
  `customer_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_invoice_number_invoice_year_unique` (`invoice_number`,`invoice_year`),
  KEY `document_type_id_foreign` (`type_id`),
  KEY `document_owner_type_owner_id_index` (`owner_type`,`owner_id`),
  KEY `document_method_id_foreign` (`method_id`),
  KEY `document_created_by_foreign` (`created_by`),
  KEY `document_updated_by_foreign` (`updated_by`),
  CONSTRAINT `document_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `document_method_id_foreign` FOREIGN KEY (`method_id`) REFERENCES `payment_method` (`id`),
  CONSTRAINT `document_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `document_type` (`id`),
  CONSTRAINT `document_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `document_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `owner_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` text COLLATE utf8mb4_unicode_ci,
  `quantity` smallint DEFAULT '1',
  `unit_value` decimal(12,2) DEFAULT NULL,
  `net_value` decimal(12,2) DEFAULT NULL,
  `tax_value` decimal(12,2) DEFAULT '0.00',
  `tax_percentage` decimal(5,2) DEFAULT '0.00',
  `total_value` decimal(12,2) DEFAULT NULL,
  `is_debit` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_detail_document_id_foreign` (`document_id`),
  KEY `document_detail_owner_type_owner_id_index` (`owner_type`,`owner_id`),
  CONSTRAINT `document_detail_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_status` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_type` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefix` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint unsigned NOT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `vat_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `legal_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_responsible_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `member_code` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_number` bigint unsigned DEFAULT NULL,
  `qrcode_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `x_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_description` text COLLATE utf8mb4_unicode_ci,
  `has_international_portal` tinyint(1) NOT NULL DEFAULT '0',
  `visible_in_club_registry` tinyint(1) NOT NULL DEFAULT '1',
  `visible_in_diving_service_provider_registry` tinyint(1) NOT NULL DEFAULT '1',
  `visible_in_map` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity_member_number_unique` (`member_number`),
  KEY `entity_country_id_foreign` (`country_id`),
  KEY `entity_member_code_index` (`member_code`),
  KEY `entity_district_id_index` (`district_id`),
  KEY `entity_member_number_index` (`member_number`),
  CONSTRAINT `entity_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`),
  CONSTRAINT `entity_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_athletes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_athletes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_id` bigint unsigned NOT NULL,
  `entity_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `individual_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sport_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_class` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_athletes_entity_id_foreign` (`entity_id`),
  KEY `entity_athletes_sport_id_foreign` (`sport_id`),
  KEY `entity_athletes_individual_id_foreign` (`individual_id`),
  CONSTRAINT `entity_athletes_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`),
  CONSTRAINT `entity_athletes_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entity_athletes_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_committee` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint unsigned NOT NULL,
  `committee_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_committee_entity_id_foreign` (`entity_id`),
  KEY `entity_committee_committee_id_foreign` (`committee_id`),
  CONSTRAINT `entity_committee_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`),
  CONSTRAINT `entity_committee_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_federation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_federation` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `national_federation_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_federation_entity_id_foreign` (`entity_id`),
  KEY `entity_federation_federation_id_foreign` (`federation_id`),
  CONSTRAINT `entity_federation_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`),
  CONSTRAINT `entity_federation_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_professional_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_professional_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `professional_role_id` bigint unsigned NOT NULL,
  `sport_id` bigint unsigned DEFAULT NULL,
  `entity_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `individual_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_class` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `deactivation_reason` text COLLATE utf8mb4_unicode_ci,
  `deactivated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_professional_role_professional_role_id_foreign` (`professional_role_id`),
  KEY `entity_professional_role_individual_id_foreign` (`individual_id`),
  KEY `entity_professional_role_sport_id_foreign` (`sport_id`),
  KEY `entity_professional_role_entity_id_individual_id_sport_id_index` (`entity_id`,`individual_id`,`sport_id`),
  CONSTRAINT `entity_professional_role_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`),
  CONSTRAINT `entity_professional_role_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entity_professional_role_professional_role_id_foreign` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`),
  CONSTRAINT `entity_professional_role_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_professional_role_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_professional_role_invitations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint unsigned DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `professional_role_id` bigint unsigned DEFAULT NULL,
  `sport_id` bigint unsigned DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `inviting_entity_id` bigint unsigned NOT NULL,
  `invited_user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `committee_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_pending_prof_role_invite` (`inviting_entity_id`,`invited_user_id`,`committee_code`,`status`),
  KEY `prof_role_invites_user_status_idx` (`invited_user_id`,`status`),
  KEY `entity_professional_role_invitations_entity_id_foreign` (`entity_id`),
  KEY `epri_individual_fk` (`individual_id`),
  KEY `epri_prof_role_fk` (`professional_role_id`),
  KEY `entity_professional_role_invitations_sport_id_foreign` (`sport_id`),
  CONSTRAINT `entity_professional_role_invitations_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entity_professional_role_invitations_invited_user_id_foreign` FOREIGN KEY (`invited_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entity_professional_role_invitations_inviting_entity_id_foreign` FOREIGN KEY (`inviting_entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entity_professional_role_invitations_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `epri_individual_fk` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epri_prof_role_fk` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint unsigned NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_user_entity_id_foreign` (`entity_id`),
  KEY `entity_user_user_id_foreign` (`user_id`),
  CONSTRAINT `entity_user_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`),
  CONSTRAINT `entity_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entity_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_zone` (
  `entity_id` bigint unsigned NOT NULL,
  `zone_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`entity_id`,`zone_id`),
  KEY `entity_zone_zone_id_foreign` (`zone_id`),
  CONSTRAINT `entity_zone_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entity_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_type` enum('federation_initiated','direct_submission') COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_id` bigint unsigned DEFAULT NULL,
  `entity_id` bigint unsigned NOT NULL,
  `entity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entity',
  `status_class` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` enum('organization','competition') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_id` bigint unsigned DEFAULT NULL,
  `event_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `municipality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_audience` text COLLATE utf8mb4_unicode_ci,
  `expected_participants` int DEFAULT NULL,
  `form_data` json DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `published_event_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_applications_sport_id_foreign` (`sport_id`),
  KEY `event_applications_district_id_foreign` (`district_id`),
  KEY `idx_applications_status` (`status_class`),
  KEY `idx_applications_entity` (`entity_id`,`entity_type`),
  KEY `idx_applications_template` (`template_id`),
  KEY `idx_applications_dates` (`start_date`,`end_date`),
  KEY `idx_applications_submitted` (`submitted_at`),
  KEY `idx_template_entity_deleted` (`template_id`,`entity_id`,`deleted_at`),
  CONSTRAINT `event_applications_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `event_applications_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `evt_sports` (`id`) ON DELETE SET NULL,
  CONSTRAINT `event_applications_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `application_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_district`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_district` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `district_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_district_event_id_district_id_unique` (`event_id`,`district_id`),
  KEY `event_district_district_id_foreign` (`district_id`),
  CONSTRAINT `event_district_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_district_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_zone` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `zone_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_zone_event_id_zone_id_unique` (`event_id`,`zone_id`),
  KEY `event_zone_zone_id_foreign` (`zone_id`),
  CONSTRAINT `event_zone_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_age_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_age_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `min_age` int NOT NULL,
  `max_age` int NOT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `discipline_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_age_categories_discipline_id_foreign` (`discipline_id`),
  CONSTRAINT `evt_age_categories_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_antidoping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_antidoping` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `responsible_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsible_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_athletes` int DEFAULT NULL,
  `competition_id` bigint unsigned NOT NULL,
  `num_controls_planned` int DEFAULT NULL,
  `date_updated` date NOT NULL,
  `number_of_controls` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_antidoping_competition_id_foreign` (`competition_id`),
  CONSTRAINT `evt_antidoping_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_athletes_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_athletes_enrollment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint unsigned NOT NULL,
  `event_id` bigint unsigned NOT NULL,
  `discipline_id` bigint unsigned DEFAULT NULL,
  `federation_id` bigint unsigned DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `per_person_price` decimal(8,2) DEFAULT NULL,
  `pricing_id` bigint DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discipline_price` decimal(8,2) DEFAULT NULL,
  `discipline_pricing_id` bigint unsigned DEFAULT NULL,
  `event_fee` double DEFAULT NULL,
  `total_price` double NOT NULL,
  `per_person_pricing_id` bigint unsigned DEFAULT NULL,
  `event_fee_pricing_id` bigint unsigned DEFAULT NULL,
  `team_identifier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_athletes_enrollment_individual_id_foreign` (`individual_id`),
  KEY `evt_athletes_enrollment_entity_id_foreign` (`entity_id`),
  KEY `evt_athletes_enrollment_enrollment_id_foreign` (`enrollment_id`),
  KEY `evt_athletes_enrollment_event_id_foreign` (`event_id`),
  KEY `evt_athletes_enrollment_discipline_id_foreign` (`discipline_id`),
  KEY `evt_athletes_enrollment_federation_id_foreign` (`federation_id`),
  KEY `evt_athletes_enrollment_discipline_pricing_id_foreign` (`discipline_pricing_id`),
  KEY `evt_athletes_enrollment_team_identifier_index` (`team_identifier`),
  CONSTRAINT `evt_athletes_enrollment_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_athletes_enrollment_discipline_pricing_id_foreign` FOREIGN KEY (`discipline_pricing_id`) REFERENCES `evt_pricing` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evt_athletes_enrollment_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `evt_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_athletes_enrollment_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_athletes_enrollment_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_athletes_enrollment_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_athletes_enrollment_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_athletes_enrollment_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_athletes_enrollment_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `athlete_enrollment_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_athletes_enrollment_attributes_athlete_enrollment_id_foreign` (`athlete_enrollment_id`),
  KEY `evt_athletes_enrollment_attributes_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_athletes_enrollment_attributes_athlete_enrollment_id_foreign` FOREIGN KEY (`athlete_enrollment_id`) REFERENCES `evt_athletes_enrollment` (`id`),
  CONSTRAINT `evt_athletes_enrollment_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_attribute_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_attribute_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_attribute_groups_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_attribute_groups_attribute` (
  `attribute_group_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  UNIQUE KEY `evt_attr_grp_attr_unique` (`attribute_group_id`,`attribute_id`),
  KEY `evt_attribute_groups_attribute_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_attribute_groups_attribute_attribute_group_id_foreign` FOREIGN KEY (`attribute_group_id`) REFERENCES `evt_attribute_groups` (`id`),
  CONSTRAINT `evt_attribute_groups_attribute_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_attribute_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_attribute_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint unsigned NOT NULL,
  `operator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comparison_field` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_validation` tinyint(1) NOT NULL DEFAULT '0',
  `min_value` int DEFAULT NULL COMMENT 'Minimum allowed value for the attribute',
  `max_value` int DEFAULT NULL COMMENT 'Maximum allowed value for the attribute',
  `comparison_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_attribute_rules_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_attribute_rules_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validation_rules` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fillable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fillable_global` tinyint(1) NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether this attribute is required to be filled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attribute_data` json DEFAULT NULL,
  `enrollment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_chief_judge_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_chief_judge_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `submitted_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `technical_considerations` text COLLATE utf8mb4_unicode_ci,
  `is_submitted` tinyint(1) NOT NULL DEFAULT '0',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_chief_judge_reports_event_id_unique` (`event_id`),
  KEY `evt_chief_judge_reports_submitted_by_foreign` (`submitted_by`),
  CONSTRAINT `evt_chief_judge_reports_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_chief_judge_reports_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_coaches_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_coaches_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `coach_enrollment_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_coaches_attributes_coach_enrollment_id_foreign` (`coach_enrollment_id`),
  KEY `evt_coaches_attributes_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_coaches_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_coaches_attributes_coach_enrollment_id_foreign` FOREIGN KEY (`coach_enrollment_id`) REFERENCES `evt_coaches_enrollment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_coaches_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_coaches_enrollment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned DEFAULT NULL,
  `event_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `pricing_id` bigint DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_coaches_enrollment_individual_id_foreign` (`individual_id`),
  KEY `evt_coaches_enrollment_enrollment_id_foreign` (`enrollment_id`),
  KEY `evt_coaches_enrollment_event_id_foreign` (`event_id`),
  KEY `evt_coaches_enrollment_federation_id_foreign` (`federation_id`),
  KEY `evt_coaches_enrollment_entity_id_foreign` (`entity_id`),
  CONSTRAINT `evt_coaches_enrollment_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `evt_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_coaches_enrollment_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_coaches_enrollment_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_coaches_enrollment_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_coaches_enrollment_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_competition_coach_certification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_competition_coach_certification` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_id` bigint unsigned NOT NULL,
  `competition_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_competition_coach_certification_competition_id_foreign` (`competition_id`),
  CONSTRAINT `evt_competition_coach_certification_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_competition_discipline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_competition_discipline` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` bigint unsigned NOT NULL,
  `discipline_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_competition_discipline_competition_id_discipline_id_unique` (`competition_id`,`discipline_id`),
  KEY `evt_competition_discipline_discipline_id_foreign` (`discipline_id`),
  CONSTRAINT `evt_competition_discipline_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_competition_discipline_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_competition_referee_certification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_competition_referee_certification` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `certification_id` bigint unsigned NOT NULL,
  `competition_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_competition_referee_certification_competition_id_foreign` (`competition_id`),
  CONSTRAINT `evt_competition_referee_certification_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_competition_referees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_competition_referees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_competition_referees_competition_id_foreign` (`competition_id`),
  KEY `evt_competition_referees_individual_id_foreign` (`individual_id`),
  CONSTRAINT `evt_competition_referees_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_competition_referees_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_competition_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_competition_staff` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_competition_staff_competition_id_foreign` (`competition_id`),
  KEY `evt_competition_staff_individual_id_foreign` (`individual_id`),
  CONSTRAINT `evt_competition_staff_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_competition_staff_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_competition_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_competition_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `competition_id` bigint unsigned NOT NULL,
  `competition_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_competition_types_competition_id_index` (`competition_id`),
  CONSTRAINT `evt_competition_types_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_competitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_competitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sport_id` bigint unsigned DEFAULT NULL,
  `event_id` bigint unsigned NOT NULL,
  `year` int DEFAULT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rounds_total` int DEFAULT NULL,
  `cat_age` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_competition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `environment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_country_id` int DEFAULT NULL,
  `venue_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `medals_gold` int DEFAULT NULL,
  `medals_silver` int DEFAULT NULL,
  `medals_bronze` int DEFAULT NULL,
  `trophies_first` int unsigned DEFAULT NULL,
  `trophies_second` int unsigned DEFAULT NULL,
  `trophies_third` int unsigned DEFAULT NULL,
  `discipline_template_id` bigint unsigned DEFAULT NULL,
  `medals_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_athlete_licenses` json DEFAULT NULL,
  `required_coach_certifications` json DEFAULT NULL,
  `required_referee_certifications` json DEFAULT NULL,
  `requires_athlete_adel` tinyint(1) NOT NULL DEFAULT '0',
  `requires_coach_adel` tinyint(1) NOT NULL DEFAULT '0',
  `requires_referee_adel` tinyint(1) NOT NULL DEFAULT '0',
  `requires_official_adel` tinyint(1) NOT NULL DEFAULT '0',
  `requires_local_federation_affiliation` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Requires athlete to have active membership in the same local federation as the registering entity',
  `requires_athlete_entity_sport_registration` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Requires athlete to be registered for the competition sport in the enrolling entity',
  `requires_coach_entity_sport_registration` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Requires coach to be registered for the competition sport in the enrolling entity',
  `required_athlete_documents` json DEFAULT NULL,
  `required_coach_documents` json DEFAULT NULL,
  `required_referee_documents` json DEFAULT NULL,
  `required_official_documents` json DEFAULT NULL,
  `max_disciplines_per_athlete` int DEFAULT NULL,
  `max_relays_per_athlete` int DEFAULT NULL,
  `max_teams_per_athlete` int DEFAULT NULL,
  `moloni_reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_competitions_event_id_foreign` (`event_id`),
  KEY `evt_competitions_discipline_template_id_foreign` (`discipline_template_id`),
  KEY `evt_competitions_sport_id_foreign` (`sport_id`),
  CONSTRAINT `evt_competitions_discipline_template_id_foreign` FOREIGN KEY (`discipline_template_id`) REFERENCES `evt_discipline_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evt_competitions_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_competitions_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_discipline_attribute_association`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_discipline_attribute_association` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discipline_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `custom_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_discipline_attribute_association_discipline_id_foreign` (`discipline_id`),
  KEY `evt_discipline_attribute_association_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_discipline_attribute_association_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`),
  CONSTRAINT `evt_discipline_attribute_association_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_discipline_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_discipline_fees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discipline_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_discipline_fees_discipline_id_foreign` (`discipline_id`),
  CONSTRAINT `evt_discipline_fees_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_discipline_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_discipline_licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discipline_id` bigint unsigned NOT NULL,
  `license_id` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_discipline_licenses_discipline_id_index` (`discipline_id`),
  KEY `evt_discipline_licenses_license_id_index` (`license_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_discipline_sport_age_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_discipline_sport_age_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discipline_id` bigint unsigned NOT NULL,
  `sport_age_group_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discipline_age_group_unique` (`discipline_id`,`sport_age_group_id`),
  KEY `evt_discipline_sport_age_groups_sport_age_group_id_foreign` (`sport_age_group_id`),
  CONSTRAINT `evt_discipline_sport_age_groups_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_discipline_sport_age_groups_sport_age_group_id_foreign` FOREIGN KEY (`sport_age_group_id`) REFERENCES `evt_sport_age_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_discipline_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_discipline_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_disciplines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_disciplines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_id` bigint unsigned NOT NULL,
  `gender` enum('male','female','mixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `enrollment_type` enum('individual','team','relay') COLLATE utf8mb4_unicode_ci NOT NULL,
  `enrollment_type_value` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `team_composition_requirements` json DEFAULT NULL,
  `athlete_limit` int unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `sport_age_group_id` bigint unsigned DEFAULT NULL,
  `style` varchar(48) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_disciplines_sport_age_group_id_foreign` (`sport_age_group_id`),
  KEY `evt_disciplines_sport_id_foreign` (`sport_id`),
  CONSTRAINT `evt_disciplines_sport_age_group_id_foreign` FOREIGN KEY (`sport_age_group_id`) REFERENCES `evt_sport_age_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_disciplines_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_enrollment_credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_enrollment_credits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `enrollable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enrollable_id` bigint unsigned NOT NULL,
  `role_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `available_slots` int NOT NULL DEFAULT '0',
  `monetary_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_enrollment_credits_enrollable_type_enrollable_id_index` (`enrollable_type`,`enrollable_id`),
  KEY `evt_credits_composite_index` (`event_id`,`enrollable_id`,`enrollable_type`,`role_type`),
  CONSTRAINT `evt_enrollment_credits_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_enrollments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` bigint unsigned NOT NULL,
  `pricing_id` bigint unsigned DEFAULT NULL,
  `document_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollable_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_price` double DEFAULT NULL,
  `credits_applied` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_enrollments_user_id_foreign` (`user_id`),
  KEY `evt_enrollments_event_id_foreign` (`event_id`),
  KEY `evt_enrollments_enrollable_id_enrollable_type_index` (`enrollable_id`,`enrollable_type`),
  KEY `evt_enrollments_pricing_id_foreign` (`pricing_id`),
  KEY `evt_enrollments_document_id_foreign` (`document_id`),
  CONSTRAINT `evt_enrollments_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evt_enrollments_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`),
  CONSTRAINT `evt_enrollments_pricing_id_foreign` FOREIGN KEY (`pricing_id`) REFERENCES `evt_pricing` (`id`),
  CONSTRAINT `evt_enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_attribute` (
  `event_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`event_id`,`attribute_id`),
  KEY `evt_event_attribute_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_event_attribute_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_attribute_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_attribute_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_attribute_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `attribute_group_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_event_attribute_groups_event_id_foreign` (`event_id`),
  KEY `evt_event_attribute_groups_attribute_group_id_foreign` (`attribute_group_id`),
  CONSTRAINT `evt_event_attribute_groups_attribute_group_id_foreign` FOREIGN KEY (`attribute_group_id`) REFERENCES `evt_attribute_groups` (`id`),
  CONSTRAINT `evt_event_attribute_groups_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_event_attributes_event_id_foreign` (`event_id`),
  KEY `evt_event_attributes_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_event_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`),
  CONSTRAINT `evt_event_attributes_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_coach_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_coach_attribute` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_event_coach_attribute_event_id_attribute_id_unique` (`event_id`,`attribute_id`),
  KEY `evt_event_coach_attribute_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_event_coach_attribute_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_coach_attribute_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_disciplines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_disciplines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `discipline_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_event_disciplines_event_id_foreign` (`event_id`),
  KEY `evt_event_disciplines_discipline_id_foreign` (`discipline_id`),
  CONSTRAINT `evt_event_disciplines_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`),
  CONSTRAINT `evt_event_disciplines_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_geographic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_geographic` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `zone_id` bigint unsigned DEFAULT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `geo_entity_id` bigint unsigned NOT NULL,
  `geo_entity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_event_geographic_event_id_foreign` (`event_id`),
  KEY `evt_event_geographic_zone_id_foreign` (`zone_id`),
  KEY `evt_event_geographic_district_id_foreign` (`district_id`),
  CONSTRAINT `evt_event_geographic_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_geographic_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_geographic_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_official_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_official_attribute` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_event_official_attribute_event_id_attribute_id_unique` (`event_id`,`attribute_id`),
  KEY `evt_event_official_attribute_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_event_official_attribute_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_official_attribute_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_organizer_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_organizer_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `bod_meeting_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_sending_contract` date DEFAULT NULL,
  `date_sending_invoice_loc` date DEFAULT NULL,
  `date_reception_payment_loc` date DEFAULT NULL,
  `date_reception_contract_signed` date DEFAULT NULL,
  `date_reception_specific_rules` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `responsible_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_event_organizer_details_event_id_foreign` (`event_id`),
  CONSTRAINT `evt_event_organizer_details_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_pins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_pins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usage_count` int unsigned NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_referee_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_referee_attribute` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_event_referee_attribute_event_id_attribute_id_unique` (`event_id`,`attribute_id`),
  KEY `evt_event_referee_attribute_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_event_referee_attribute_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_referee_attribute_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_report_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_report_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `documentable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documentable_id` bigint unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_event_report_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `evt_report_docs_morph_idx` (`documentable_type`,`documentable_id`),
  CONSTRAINT `evt_event_report_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('technical_delegate','chief_judge','competition_director') COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_event_roles_event_id_individual_id_unique` (`event_id`,`individual_id`),
  UNIQUE KEY `evt_event_roles_event_id_role_unique` (`event_id`,`role`),
  KEY `evt_event_roles_individual_id_foreign` (`individual_id`),
  CONSTRAINT `evt_event_roles_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_roles_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_event_staff_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_event_staff_attribute` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_event_staff_attribute_event_id_attribute_id_unique` (`event_id`,`attribute_id`),
  KEY `evt_event_staff_attribute_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_event_staff_attribute_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_event_staff_attribute_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_fee` decimal(8,2) DEFAULT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_geographical_coverage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `start_registration` datetime DEFAULT NULL,
  `end_registration` datetime DEFAULT NULL,
  `other_deadlines` json DEFAULT NULL,
  `event_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geo_zone_id` bigint unsigned DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geographical_coverage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regulations_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_district_id` bigint unsigned DEFAULT NULL,
  `location_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue_country_id` bigint unsigned DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `allow_coach_enrollment` tinyint(1) NOT NULL DEFAULT '1',
  `allow_referee_enrollment` tinyint(1) NOT NULL DEFAULT '1',
  `allow_official_enrollment` tinyint(1) NOT NULL DEFAULT '1',
  `allow_individual_enrollment` tinyint(1) NOT NULL DEFAULT '1',
  `broadcast` tinyint(1) DEFAULT NULL,
  `broadcast_information` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moloni_reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_athlete_list` tinyint(1) NOT NULL DEFAULT '0',
  `public_coach_list` tinyint(1) NOT NULL DEFAULT '0',
  `public_referee_list` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `evt_events_venue_district_id_foreign` (`venue_district_id`),
  CONSTRAINT `evt_events_venue_district_id_foreign` FOREIGN KEY (`venue_district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_events_professional_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_events_professional_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `professional_role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_events_professional_roles_event_id_index` (`event_id`),
  KEY `evt_events_professional_roles_professional_role_id_index` (`professional_role_id`),
  CONSTRAINT `evt_events_professional_roles_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`),
  CONSTRAINT `evt_events_professional_roles_professional_role_id_foreign` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_individual_enrollment_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_individual_enrollment_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `individual_enrollment_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ind_enroll_attr_ind_enroll_id_foreign` (`individual_enrollment_id`),
  KEY `ind_enroll_attr_attr_id_foreign` (`attribute_id`),
  CONSTRAINT `ind_enroll_attr_attr_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ind_enroll_attr_ind_enroll_id_foreign` FOREIGN KEY (`individual_enrollment_id`) REFERENCES `evt_individuals_enrollment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_individuals_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_individuals_enrollment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint unsigned NOT NULL,
  `event_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `federation_id` bigint unsigned DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `pricing_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_individuals_enrollment_individual_id_foreign` (`individual_id`),
  KEY `evt_individuals_enrollment_enrollment_id_foreign` (`enrollment_id`),
  KEY `evt_individuals_enrollment_event_id_foreign` (`event_id`),
  KEY `evt_individuals_enrollment_federation_id_foreign` (`federation_id`),
  KEY `evt_individuals_enrollment_entity_id_foreign` (`entity_id`),
  CONSTRAINT `evt_individuals_enrollment_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `evt_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_individuals_enrollment_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evt_individuals_enrollment_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_individuals_enrollment_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_individuals_enrollment_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_officials_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_officials_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `officials_enrollment_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_officials_attributes_officials_enrollment_id_foreign` (`officials_enrollment_id`),
  KEY `evt_officials_attributes_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_officials_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`),
  CONSTRAINT `evt_officials_attributes_officials_enrollment_id_foreign` FOREIGN KEY (`officials_enrollment_id`) REFERENCES `evt_officials_enrollment` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_officials_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_officials_enrollment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `event_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `pricing_id` bigint DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_officials_enrollment_enrollment_id_foreign` (`enrollment_id`),
  KEY `evt_officials_enrollment_federation_id_foreign` (`federation_id`),
  KEY `evt_officials_enrollment_event_id_foreign` (`event_id`),
  KEY `evt_officials_enrollment_individual_id_foreign` (`individual_id`),
  CONSTRAINT `evt_officials_enrollment_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `evt_enrollments` (`id`),
  CONSTRAINT `evt_officials_enrollment_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`),
  CONSTRAINT `evt_officials_enrollment_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `evt_officials_enrollment_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_organizers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_organizers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organizable_id` bigint unsigned NOT NULL,
  `organizable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_organizers_event_id_foreign` (`event_id`),
  CONSTRAINT `evt_organizers_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_pricing` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `discipline_id` bigint unsigned DEFAULT NULL,
  `price_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `pricing_option` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enrollment_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_pricing_event_id_foreign` (`event_id`),
  KEY `evt_pricing_discipline_id_foreign` (`discipline_id`),
  CONSTRAINT `evt_pricing_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evt_pricing_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_referee_function_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_referee_function_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `referee_enrollment_id` bigint unsigned NOT NULL,
  `is_present` tinyint(1) NOT NULL DEFAULT '1',
  `referee_function_id` bigint unsigned DEFAULT NULL,
  `function_text` text COLLATE utf8mb4_unicode_ci,
  `assigned_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `competition_days` int unsigned DEFAULT NULL,
  `number_of_games` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_referee_function_assignments_referee_enrollment_id_foreign` (`referee_enrollment_id`),
  KEY `evt_referee_function_assignments_referee_function_id_foreign` (`referee_function_id`),
  KEY `evt_referee_function_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `idx_event_referee` (`event_id`,`referee_enrollment_id`),
  CONSTRAINT `evt_referee_function_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `individual` (`id`),
  CONSTRAINT `evt_referee_function_assignments_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_referee_function_assignments_referee_enrollment_id_foreign` FOREIGN KEY (`referee_enrollment_id`) REFERENCES `evt_referees_enrollment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_referee_function_assignments_referee_function_id_foreign` FOREIGN KEY (`referee_function_id`) REFERENCES `evt_referee_functions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_referee_functions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_referee_functions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sport_id` bigint unsigned NOT NULL,
  `function_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `function_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `display_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_referee_functions_sport_id_is_active_index` (`sport_id`,`is_active`),
  CONSTRAINT `evt_referee_functions_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `evt_sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_referees_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_referees_enrollment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `event_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `pricing_id` bigint DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evaluation` tinyint unsigned DEFAULT NULL,
  `evaluation_notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `evt_referees_enrollment_enrollment_id_foreign` (`enrollment_id`),
  KEY `evt_referees_enrollment_federation_id_foreign` (`federation_id`),
  KEY `evt_referees_enrollment_event_id_foreign` (`event_id`),
  KEY `evt_referees_enrollment_individual_id_foreign` (`individual_id`),
  KEY `evt_referees_enrollment_entity_id_foreign` (`entity_id`),
  CONSTRAINT `evt_referees_enrollment_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `evt_enrollments` (`id`),
  CONSTRAINT `evt_referees_enrollment_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evt_referees_enrollment_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`),
  CONSTRAINT `evt_referees_enrollment_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `evt_referees_enrollment_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_referees_enrollment_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_referees_enrollment_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `referee_enrollment_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_referee_attribute` (`referee_enrollment_id`,`attribute_id`),
  KEY `evt_referees_enrollment_attributes_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_referees_enrollment_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_referees_enrollment_attributes_referee_enrollment_id_foreign` FOREIGN KEY (`referee_enrollment_id`) REFERENCES `evt_referees_enrollment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_sport_age_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_sport_age_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sport_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthday_start` date NOT NULL,
  `birthday_end` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_sport_age_groups_sport_id_foreign` (`sport_id`),
  CONSTRAINT `evt_sport_age_groups_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_sports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_staff_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_staff_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_enrollment_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_staff_attributes_staff_enrollment_id_attribute_id_unique` (`staff_enrollment_id`,`attribute_id`),
  KEY `evt_staff_attributes_attribute_id_foreign` (`attribute_id`),
  CONSTRAINT `evt_staff_attributes_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `evt_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_staff_attributes_staff_enrollment_id_foreign` FOREIGN KEY (`staff_enrollment_id`) REFERENCES `evt_staff_enrollment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_staff_enrollment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_staff_enrollment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_staff_enrollment_event_id_foreign` (`event_id`),
  KEY `evt_staff_enrollment_federation_id_foreign` (`federation_id`),
  KEY `evt_staff_enrollment_individual_id_foreign` (`individual_id`),
  CONSTRAINT `evt_staff_enrollment_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`),
  CONSTRAINT `evt_staff_enrollment_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `evt_staff_enrollment_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_technical_delegate_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_technical_delegate_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `submitted_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `participants_withdrawals` text COLLATE utf8mb4_unicode_ci,
  `incidents_occurrences` text COLLATE utf8mb4_unicode_ci,
  `officials_performance` text COLLATE utf8mb4_unicode_ci,
  `facilities_evaluation` text COLLATE utf8mb4_unicode_ci,
  `safety_first_aid` text COLLATE utf8mb4_unicode_ci,
  `anti_doping_control` text COLLATE utf8mb4_unicode_ci,
  `sports_protests` text COLLATE utf8mb4_unicode_ci,
  `observations_recommendations` text COLLATE utf8mb4_unicode_ci,
  `is_submitted` tinyint(1) NOT NULL DEFAULT '0',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evt_technical_delegate_reports_event_id_unique` (`event_id`),
  KEY `evt_technical_delegate_reports_submitted_by_foreign` (`submitted_by`),
  CONSTRAINT `evt_technical_delegate_reports_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_technical_delegate_reports_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_technical_delegates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_technical_delegates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned DEFAULT NULL,
  `competition_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_code_delegate_federation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `appointment_by_bod_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_bod_appointment` date DEFAULT NULL,
  `date_of_report_reception` date DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `date_bod_validation_report` date DEFAULT NULL,
  `num_bod_validation_report` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_technical_delegates_federation_id_foreign` (`federation_id`),
  KEY `evt_technical_delegates_competition_id_foreign` (`competition_id`),
  KEY `evt_technical_delegates_individual_id_index` (`individual_id`),
  CONSTRAINT `evt_technical_delegates_competition_id_foreign` FOREIGN KEY (`competition_id`) REFERENCES `evt_competitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_technical_delegates_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `evt_template_discipline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evt_template_discipline` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL,
  `discipline_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evt_template_discipline_template_id_foreign` (`template_id`),
  KEY `evt_template_discipline_discipline_id_foreign` (`discipline_id`),
  CONSTRAINT `evt_template_discipline_discipline_id_foreign` FOREIGN KEY (`discipline_id`) REFERENCES `evt_disciplines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evt_template_discipline_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `evt_discipline_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint unsigned NOT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_local` tinyint DEFAULT '0',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_manual` tinyint(1) NOT NULL DEFAULT '0',
  `legal_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `website` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `board_members` json DEFAULT NULL,
  `member_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_default_federation` tinyint(1) NOT NULL DEFAULT '0',
  `can_issue_certifications` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `federation_country_id_foreign` (`country_id`),
  KEY `federation_parent_id_foreign` (`parent_id`),
  KEY `federation_district_id_index` (`district_id`),
  CONSTRAINT `federation_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`),
  CONSTRAINT `federation_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `federation_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `federation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation_committee` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `committee_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `federation_committee_federation_id_committee_id_unique` (`federation_id`,`committee_id`),
  KEY `federation_committee_federation_id_index` (`federation_id`),
  KEY `federation_committee_committee_id_index` (`committee_id`),
  CONSTRAINT `federation_committee_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`) ON DELETE CASCADE,
  CONSTRAINT `federation_committee_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation_licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `license_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_federation_license` (`federation_id`,`license_id`),
  KEY `idx_federation_id` (`federation_id`),
  KEY `idx_license_id` (`license_id`),
  CONSTRAINT `federation_licenses_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `federation_licenses_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation_professional_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation_professional_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `professional_role_id` bigint unsigned NOT NULL,
  `federation_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `individual_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_class` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `federation_professional_role_federation_id_foreign` (`federation_id`),
  KEY `federation_professional_role_professional_role_id_foreign` (`professional_role_id`),
  KEY `federation_professional_role_individual_id_foreign` (`individual_id`),
  CONSTRAINT `federation_professional_role_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `federation_professional_role_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `federation_professional_role_professional_role_id_foreign` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned DEFAULT NULL,
  `role_id` bigint unsigned NOT NULL,
  `requires_active_membership` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `federation_role_unique` (`federation_id`,`role_id`),
  KEY `federation_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `federation_roles_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `federation_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `federation_user_federation_id_foreign` (`federation_id`),
  KEY `federation_user_user_id_foreign` (`user_id`),
  CONSTRAINT `federation_user_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `federation_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation_voting_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation_voting_rights` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `year` smallint unsigned NOT NULL,
  `general_assembly_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `technical_committee_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `scientific_committee_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `sport_committee_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `finswimming_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `freediving_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `aquathlon_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `underwater_hockey_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `underwater_rugby_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `target_shooting_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `sport_diving_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `spearfishing_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `orienteering_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `visual_commission_status` enum('Voting right','Suspended','Probation','No Voting Right') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Voting Right',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `federation_voting_rights_federation_id_year_unique` (`federation_id`,`year`),
  CONSTRAINT `federation_voting_rights_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `federation_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `federation_zone` (
  `federation_id` bigint unsigned NOT NULL,
  `zone_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`federation_id`,`zone_id`),
  KEY `federation_zone_zone_id_foreign` (`zone_id`),
  CONSTRAINT `federation_zone_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `federation_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `generated_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `generated_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_on` timestamp NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `insurer_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filters` json DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `generated_reports_generated_by_foreign` (`generated_by`),
  CONSTRAINT `generated_reports_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `geo_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_zones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `import_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_errors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `import_id` bigint unsigned NOT NULL,
  `row_number` int NOT NULL,
  `row_data` json NOT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('error','warning') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'error',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_errors_import_id_severity_index` (`import_id`,`severity`),
  KEY `import_errors_import_id_row_number_index` (`import_id`,`row_number`),
  CONSTRAINT `import_errors_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_rows` int NOT NULL DEFAULT '0',
  `processed_rows` int NOT NULL DEFAULT '0',
  `success_count` int NOT NULL DEFAULT '0',
  `error_count` int NOT NULL DEFAULT '0',
  `warning_count` int NOT NULL DEFAULT '0',
  `field_mapping` json NOT NULL,
  `options` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_status_index` (`user_id`,`status`),
  KEY `imports_type_created_at_index` (`type`,`created_at`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `individual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `individual` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  `district_id` bigint unsigned DEFAULT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name_latin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Given Name transliterated to Latin characters',
  `last_name_latin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Surname transliterated to Latin characters',
  `native_name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `doc_ref_type` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'passport, cc, etc.',
  `doc_ref` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ref_validation_date` date DEFAULT NULL,
  `vat_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `member_code` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_number` bigint unsigned DEFAULT NULL,
  `national_federation_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `x_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_international_portal` tinyint(1) NOT NULL DEFAULT '0',
  `visible_in_coach_registry` tinyint(1) NOT NULL DEFAULT '1',
  `visible_in_technical_official_registry` tinyint(1) NOT NULL DEFAULT '1',
  `visible_in_diving_professional_registry` tinyint(1) NOT NULL DEFAULT '1',
  `member_number_active` bigint GENERATED ALWAYS AS (if((`deleted_at` is null),`member_number`,NULL)) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `individual_member_number_unique` (`member_number_active`),
  KEY `individual_country_id_foreign` (`country_id`),
  KEY `individual_created_by_foreign` (`created_by`),
  KEY `individual_updated_by_foreign` (`updated_by`),
  KEY `individual_member_code_index` (`member_code`),
  KEY `individual_user_id_foreign` (`user_id`),
  KEY `individual_first_name_latin_index` (`first_name_latin`),
  KEY `individual_last_name_latin_index` (`last_name_latin`),
  KEY `individual_district_id_index` (`district_id`),
  KEY `individual_member_number_index` (`member_number`),
  KEY `duplicate_check_index` (`name`,`surname`,`birthdate`,`country_id`),
  KEY `individual_email_index` (`email`),
  KEY `individual_national_federation_number_index` (`national_federation_number`),
  CONSTRAINT `individual_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`),
  CONSTRAINT `individual_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `individual_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `individual_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `individual_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `individual_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `individual_entity` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_class` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `individual_entity_entity_id_foreign` (`entity_id`),
  KEY `individual_entity_individual_id_foreign` (`individual_id`),
  CONSTRAINT `individual_entity_entity_id_foreign` FOREIGN KEY (`entity_id`) REFERENCES `entity` (`id`),
  CONSTRAINT `individual_entity_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `individual_federation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `individual_federation` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned NOT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint DEFAULT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `individual_federation_federation_id_foreign` (`federation_id`),
  KEY `individual_federation_individual_id_foreign` (`individual_id`),
  CONSTRAINT `individual_federation_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `individual_federation_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `individual_professional_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `individual_professional_role` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `professional_role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `individual_professional_role_professional_role_id_foreign` (`professional_role_id`),
  KEY `individual_professional_role_individual_id_foreign` (`individual_id`),
  CONSTRAINT `individual_professional_role_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `individual_professional_role_professional_role_id_foreign` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `individual_zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `individual_zone` (
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`individual_id`,`zone_id`),
  KEY `individual_zone_zone_id_foreign` (`zone_id`),
  CONSTRAINT `individual_zone_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE CASCADE,
  CONSTRAINT `individual_zone_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `insurance_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `insurance_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `insurance_plan_id` bigint unsigned NOT NULL,
  `documentable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documentable_id` bigint unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `insurance_documents_insurance_plan_id_foreign` (`insurance_plan_id`),
  KEY `insurance_documents_documentable_type_documentable_id_index` (`documentable_type`,`documentable_id`),
  CONSTRAINT `insurance_documents_insurance_plan_id_foreign` FOREIGN KEY (`insurance_plan_id`) REFERENCES `insurance_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `insurance_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `insurance_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_audience` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `individual_fee` decimal(10,2) DEFAULT NULL,
  `entity_fee` decimal(10,2) DEFAULT NULL,
  `moloni_reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `period` int unsigned DEFAULT NULL,
  `period_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `policy_number_prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `policy_number_sequence` int unsigned NOT NULL DEFAULT '0',
  `policy_number_format` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insured_activity` text COLLATE utf8mb4_unicode_ci,
  `territorial_scope` text COLLATE utf8mb4_unicode_ci,
  `insurer_address` text COLLATE utf8mb4_unicode_ci,
  `insurer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurer_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applicable_deductibles` text COLLATE utf8mb4_unicode_ci,
  `coverage_details` text COLLATE utf8mb4_unicode_ci,
  `insurance_company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cmas_license_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `vat_rate` int NOT NULL DEFAULT '23',
  `requires_official_document` tinyint(1) NOT NULL DEFAULT '0',
  `required_document_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_active_affiliation` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether this insurance requires an active affiliation to be purchased',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `insurances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `insurances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `insurance_plan_id` bigint unsigned NOT NULL,
  `member_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_subscription_id` bigint unsigned DEFAULT NULL,
  `requester_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requester_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_type` enum('direct','entity_group','federation_facilitated') COLLATE utf8mb4_unicode_ci DEFAULT 'direct',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_external` tinyint(1) NOT NULL DEFAULT '0',
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `individual_fee` decimal(10,2) DEFAULT NULL,
  `entity_fee` decimal(10,2) DEFAULT NULL,
  `policy_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `insurances_insurance_plan_id_foreign` (`insurance_plan_id`),
  KEY `insurances_member_type_member_id_index` (`member_type`,`member_id`),
  KEY `insurances_member_subscription_id_foreign` (`member_subscription_id`),
  KEY `insurances_requester_type_requester_id_index` (`requester_type`,`requester_id`),
  CONSTRAINT `insurances_insurance_plan_id_foreign` FOREIGN KEY (`insurance_plan_id`) REFERENCES `insurance_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `insurances_member_subscription_id_foreign` FOREIGN KEY (`member_subscription_id`) REFERENCES `member_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `iso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_iso_unique` (`iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `license`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` bigint unsigned NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `interval` int unsigned DEFAULT NULL,
  `interval_unit` enum('weeks','months','years') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validity_type` enum('fixed_duration','calendar_year') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed_duration' COMMENT 'Determines how license validity is calculated',
  `sport_id` bigint unsigned DEFAULT NULL,
  `professional_role_id` bigint unsigned DEFAULT NULL,
  `requester_model` json DEFAULT NULL,
  `allow_entity_group_request` tinyint(1) NOT NULL DEFAULT '1',
  `requires_official_documents` tinyint(1) NOT NULL DEFAULT '0',
  `required_document_types` json DEFAULT NULL,
  `required_athlete_documents` json DEFAULT NULL,
  `required_coach_documents` json DEFAULT NULL,
  `required_official_documents` json DEFAULT NULL,
  `required_diving_professional_documents` json DEFAULT NULL,
  `unit_value` decimal(12,2) DEFAULT NULL,
  `unit_value_individual` decimal(10,2) DEFAULT NULL,
  `unit_value_entity` decimal(10,2) DEFAULT NULL,
  `unit_value_federation` decimal(10,2) DEFAULT NULL,
  `tax_value` decimal(12,2) DEFAULT '0.00',
  `moloni_reference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT '0.00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `license_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_admin_validation` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `license_committee_id_foreign` (`committee_id`),
  KEY `license_type_id_foreign` (`type_id`),
  KEY `license_sport_id_foreign` (`sport_id`),
  KEY `license_professional_role_id_foreign` (`professional_role_id`),
  CONSTRAINT `license_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`),
  CONSTRAINT `license_professional_role_id_foreign` FOREIGN KEY (`professional_role_id`) REFERENCES `professional_roles` (`id`),
  CONSTRAINT `license_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`),
  CONSTRAINT `license_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `license_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `license_attributed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license_attributed` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_class` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requester_model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_by_id` bigint unsigned DEFAULT NULL,
  `request_type` enum('direct','entity_group','federation_facilitated') COLLATE utf8mb4_unicode_ci DEFAULT 'direct',
  `payment_id` bigint unsigned DEFAULT NULL,
  `purchased_at` timestamp NULL DEFAULT NULL,
  `license_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `holder_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `federation_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `national_license_code` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_value` decimal(12,2) DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `current_term_starts_at` date DEFAULT NULL,
  `current_term_ends_at` date DEFAULT NULL,
  `last_billing_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `license_number` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_member_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `validation_notes` text COLLATE utf8mb4_unicode_ci,
  `validated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_attributed_international_code_unique` (`license_number`),
  KEY `license_attributed_license_id_foreign` (`license_id`),
  KEY `license_attributed_federation_id_foreign` (`federation_id`),
  KEY `license_attributed_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `license_attributed_created_by_foreign` (`created_by`),
  KEY `license_attributed_updated_by_foreign` (`updated_by`),
  KEY `license_attributed_requested_by_id_foreign` (`requested_by_id`),
  KEY `license_attributed_validated_by_foreign` (`validated_by`),
  CONSTRAINT `license_attributed_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `license_attributed_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `license_attributed_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`),
  CONSTRAINT `license_attributed_requested_by_id_foreign` FOREIGN KEY (`requested_by_id`) REFERENCES `entity` (`id`) ON DELETE SET NULL,
  CONSTRAINT `license_attributed_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `license_attributed_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `license_required_certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license_required_certifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_id` bigint unsigned NOT NULL,
  `certification_id` bigint unsigned DEFAULT NULL,
  `requester_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certification_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lic_req_cert_level_unique` (`license_id`,`certification_id`,`requester_type`,`certification_level`),
  KEY `license_required_certifications_license_id_requester_type_index` (`license_id`,`requester_type`),
  KEY `license_required_certifications_certification_id_index` (`certification_id`),
  CONSTRAINT `license_required_certifications_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certification` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_required_certifications_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `license_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `committee_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_role_committee_unique` (`license_id`,`role_id`,`committee_id`),
  KEY `license_roles_role_id_foreign` (`role_id`),
  KEY `license_roles_committee_id_foreign` (`committee_id`),
  CONSTRAINT `license_roles_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_roles_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `license_sport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license_sport` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_id` bigint unsigned NOT NULL,
  `sport_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_sport_license_id_sport_id_unique` (`license_id`,`sport_id`),
  KEY `license_sport_sport_id_foreign` (`sport_id`),
  CONSTRAINT `license_sport_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`) ON DELETE CASCADE,
  CONSTRAINT `license_sport_sport_id_foreign` FOREIGN KEY (`sport_id`) REFERENCES `sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `license_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `license_type` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_individual` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `local_membership_plan_associations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `local_membership_plan_associations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `local_federation_id` bigint unsigned NOT NULL,
  `membership_plan_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `local_membership_plan_associations_local_federation_id_foreign` (`local_federation_id`),
  KEY `local_membership_plan_associations_membership_plan_id_foreign` (`membership_plan_id`),
  CONSTRAINT `local_membership_plan_associations_local_federation_id_foreign` FOREIGN KEY (`local_federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `local_membership_plan_associations_membership_plan_id_foreign` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_number_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_number_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_number_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `member_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `membership_package_id` bigint unsigned NOT NULL,
  `status_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requester_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requester_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_type` enum('direct','entity_group','federation_facilitated') COLLATE utf8mb4_unicode_ci DEFAULT 'direct',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_subscriptions_member_type_member_id_index` (`member_type`,`member_id`),
  KEY `member_subscriptions_membership_package_id_foreign` (`membership_package_id`),
  KEY `member_subscriptions_requester_type_requester_id_index` (`requester_type`,`requester_id`),
  CONSTRAINT `member_subscriptions_membership_package_id_foreign` FOREIGN KEY (`membership_package_id`) REFERENCES `membership_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `federation_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_class` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated_at` datetime DEFAULT NULL,
  `current_term_starts_at` date DEFAULT NULL,
  `current_term_ends_at` date DEFAULT NULL,
  `last_billing_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `membership_federation_id_foreign` (`federation_id`),
  KEY `membership_parent_id_foreign` (`parent_id`),
  CONSTRAINT `membership_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`),
  CONSTRAINT `membership_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `membership` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_membership_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_membership_plan` (
  `membership_id` bigint unsigned NOT NULL,
  `membership_plan_id` bigint unsigned NOT NULL,
  KEY `membership_membership_plan_membership_id_foreign` (`membership_id`),
  KEY `membership_membership_plan_membership_plan_id_foreign` (`membership_plan_id`),
  CONSTRAINT `membership_membership_plan_membership_id_foreign` FOREIGN KEY (`membership_id`) REFERENCES `membership` (`id`) ON DELETE CASCADE,
  CONSTRAINT `membership_membership_plan_membership_plan_id_foreign` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `federation_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `target_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distribution_methods` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_plan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `committee_id` bigint unsigned DEFAULT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `interval` int unsigned DEFAULT NULL,
  `interval_unit` enum('weeks','months','years') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tax_value` decimal(12,2) DEFAULT '0.00',
  `tax_percentage` decimal(5,2) DEFAULT '0.00',
  `friendly_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `membership_plan_committee_id_foreign` (`committee_id`),
  CONSTRAINT `membership_plan_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_plan_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_plan_licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `license_id` bigint unsigned NOT NULL,
  `membership_plan_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `membership_plan_licenses_license_id_foreign` (`license_id`),
  KEY `membership_plan_licenses_membership_plan_id_foreign` (`membership_plan_id`),
  CONSTRAINT `membership_plan_licenses_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`),
  CONSTRAINT `membership_plan_licenses_membership_plan_id_foreign` FOREIGN KEY (`membership_plan_id`) REFERENCES `membership_plan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `menu_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name for the group',
  `machine_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Machine-readable name',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Optional description',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Optional icon for the group',
  `order` int NOT NULL DEFAULT '0' COMMENT 'Display order',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether this is the default group',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether this group is active',
  `visibility_type` enum('all','roles') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all' COMMENT 'Visibility type: all users or specific roles',
  `required_roles` json DEFAULT NULL COMMENT 'Array of role names that can see this group',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_groups_menu_id_machine_name_unique` (`menu_id`,`machine_name`),
  KEY `menu_groups_menu_id_machine_name_index` (`menu_id`,`machine_name`),
  KEY `menu_groups_menu_id_active_index` (`menu_id`,`active`),
  KEY `menu_groups_menu_id_order_index` (`menu_id`,`order`),
  CONSTRAINT `menu_groups_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` bigint unsigned NOT NULL,
  `menu_group_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `committee_id` bigint unsigned DEFAULT NULL COMMENT 'Committee ID (no foreign key constraint)',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Translation key, e.g., "menu.cmas.dashboard"',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Heroicon name, e.g., "chart-bar"',
  `order` int NOT NULL DEFAULT '0' COMMENT 'Display order within parent',
  `route_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Laravel named route',
  `route_parameters` json DEFAULT NULL COMMENT 'Route parameters as JSON',
  `active_patterns` json DEFAULT NULL COMMENT 'URL patterns that mark this item active',
  `permissions` json DEFAULT NULL COMMENT 'Array of required permissions',
  `selected_roles` json DEFAULT NULL,
  `visibility_conditions` json DEFAULT NULL COMMENT 'Complex visibility rules',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Base visibility flag',
  `badge_config` json DEFAULT NULL COMMENT 'Badge/count display configuration',
  `translation_namespace` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Translation namespace override',
  `metadata` json DEFAULT NULL COMMENT 'Additional metadata',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_parent_id_foreign` (`parent_id`),
  KEY `menu_items_menu_id_parent_id_order_index` (`menu_id`,`parent_id`,`order`),
  KEY `menu_items_menu_id_visible_index` (`menu_id`,`visible`),
  KEY `menu_items_route_name_index` (`route_name`),
  KEY `menu_items_committee_id_index` (`committee_id`),
  KEY `menu_items_menu_group_id_foreign` (`menu_group_id`),
  KEY `menu_items_menu_id_menu_group_id_index` (`menu_id`,`menu_group_id`),
  CONSTRAINT `menu_items_menu_group_id_foreign` FOREIGN KEY (`menu_group_id`) REFERENCES `menu_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name, e.g., "CMAS Admin Menu"',
  `machine_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Machine name, e.g., "cmas"',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Optional description of the menu',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Whether this menu is currently active',
  `metadata` json DEFAULT NULL COMMENT 'Additional metadata for the menu',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menus_machine_name_unique` (`machine_name`),
  KEY `menus_machine_name_active_index` (`machine_name`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `moloni_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moloni_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customerable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customerable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moloni_customer_id` int unsigned NOT NULL,
  `moloni_vat` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moloni_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `moloni_customers_customerable_type_customerable_id_unique` (`customerable_type`,`customerable_id`),
  KEY `moloni_customers_moloni_customer_id_index` (`moloni_customer_id`),
  KEY `moloni_customers_customerable_type_customerable_id_index` (`customerable_type`,`customerable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `moloni_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moloni_invoices` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moloni_document_id` int unsigned NOT NULL,
  `moloni_document_set_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moloni_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moloni_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moloni_total` decimal(10,2) NOT NULL,
  `moloni_response` json DEFAULT NULL,
  `synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `moloni_invoices_document_id_unique` (`document_id`),
  KEY `moloni_invoices_moloni_document_id_index` (`moloni_document_id`),
  KEY `moloni_invoices_moloni_number_index` (`moloni_number`),
  CONSTRAINT `moloni_invoices_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `moloni_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moloni_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `is_encrypted` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `moloni_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `moloni_sync_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moloni_sync_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sync_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `duration_ms` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `moloni_sync_logs_sync_type_index` (`sync_type`),
  KEY `moloni_sync_logs_status_index` (`status`),
  KEY `moloni_sync_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `moloni_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moloni_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `access_token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_expires_at` timestamp NOT NULL,
  `refresh_token_expires_at` timestamp NOT NULL,
  `company_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `official_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `official_documents` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `individual_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_attributed_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `federation_id` bigint unsigned DEFAULT NULL,
  `status_class` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `official_documents_created_by_foreign` (`created_by`),
  KEY `official_documents_updated_by_foreign` (`updated_by`),
  KEY `official_documents_country_id_foreign` (`country_id`),
  KEY `official_documents_federation_id_index` (`federation_id`),
  KEY `official_documents_individual_id_foreign` (`individual_id`),
  KEY `official_documents_owner_type_owner_id_index` (`owner_type`,`owner_id`),
  KEY `official_documents_license_attributed_id_index` (`license_attributed_id`),
  CONSTRAINT `official_documents_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `country` (`id`),
  CONSTRAINT `official_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `official_documents_individual_id_foreign` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`id`) ON DELETE SET NULL,
  CONSTRAINT `official_documents_license_attributed_id_foreign` FOREIGN KEY (`license_attributed_id`) REFERENCES `license_attributed` (`id`) ON DELETE CASCADE,
  CONSTRAINT `official_documents_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `package_affiliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_affiliation` (
  `package_id` bigint unsigned NOT NULL,
  `affiliation_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`package_id`,`affiliation_id`),
  KEY `package_affiliation_affiliation_id_foreign` (`affiliation_id`),
  CONSTRAINT `package_affiliation_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliation_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_affiliation_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `membership_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `package_federation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_federation` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint unsigned NOT NULL,
  `federation_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_federation_package_id_federation_id_unique` (`package_id`,`federation_id`),
  KEY `package_federation_federation_id_foreign` (`federation_id`),
  CONSTRAINT `package_federation_federation_id_foreign` FOREIGN KEY (`federation_id`) REFERENCES `federation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_federation_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `membership_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `package_insurance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_insurance` (
  `package_id` bigint unsigned NOT NULL,
  `insurance_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`package_id`,`insurance_id`),
  KEY `package_insurance_insurance_id_foreign` (`insurance_id`),
  CONSTRAINT `package_insurance_insurance_id_foreign` FOREIGN KEY (`insurance_id`) REFERENCES `insurance_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_insurance_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `membership_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `package_license`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_license` (
  `package_id` bigint unsigned NOT NULL,
  `license_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`package_id`,`license_id`),
  KEY `package_license_license_id_foreign` (`license_id`),
  CONSTRAINT `package_license_license_id_foreign` FOREIGN KEY (`license_id`) REFERENCES `license` (`id`) ON DELETE CASCADE,
  CONSTRAINT `package_license_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `membership_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `package_pricings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_pricings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `package_id` bigint unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_pricings_package_id_foreign` (`package_id`),
  CONSTRAINT `package_pricings_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `membership_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_method` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(145) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `handler` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_transactions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method_id` bigint unsigned DEFAULT NULL,
  `amount` double NOT NULL,
  `status` enum('success','failed','pending') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_data` json DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_transactions_document_id_foreign` (`document_id`),
  KEY `payment_transactions_payment_method_id_foreign` (`payment_method_id`),
  CONSTRAINT `payment_transactions_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_transactions_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`),
  KEY `permissions_created_by_foreign` (`created_by`),
  KEY `permissions_updated_by_foreign` (`updated_by`),
  KEY `permissions_category_index` (`category`),
  CONSTRAINT `permissions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permissions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `professional_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `professional_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `committee_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `professional_roles_role_index` (`role`),
  KEY `professional_roles_committee_id_foreign` (`committee_id`),
  CONSTRAINT `professional_roles_committee_id_foreign` FOREIGN KEY (`committee_id`) REFERENCES `committee` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint unsigned NOT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  KEY `push_subscriptions_subscribable_type_subscribable_id_index` (`subscribable_type`,`subscribable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `context` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `role_audit_logs_user_id_index` (`user_id`),
  KEY `role_audit_logs_action_index` (`action`),
  KEY `role_audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `role_audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `role_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `permissions` json DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_templates_created_by_foreign` (`created_by`),
  KEY `role_templates_category_index` (`category`),
  KEY `role_templates_is_active_index` (`is_active`),
  CONSTRAINT `role_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `protection_level` enum('system','admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope` enum('system','federation','entity','individual') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Organizational scope for the role',
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`),
  KEY `roles_created_by_foreign` (`created_by`),
  KEY `roles_updated_by_foreign` (`updated_by`),
  KEY `roles_category_index` (`category`),
  KEY `roles_is_protected_index` (`is_protected`),
  KEY `roles_protection_level_index` (`protection_level`),
  CONSTRAINT `roles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `roles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `route_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `route_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `route_pattern` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middleware` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_route_permission` (`route_pattern`,`permission_name`),
  KEY `route_permissions_created_by_foreign` (`created_by`),
  KEY `route_permissions_route_pattern_index` (`route_pattern`),
  KEY `route_permissions_permission_name_index` (`permission_name`),
  KEY `route_permissions_is_active_index` (`is_active`),
  KEY `route_permissions_updated_by_foreign` (`updated_by`),
  CONSTRAINT `route_permissions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `route_permissions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sub_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_regions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `geo_zone_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sub_regions_geo_zone_id_foreign` (`geo_zone_id`),
  CONSTRAINT `sub_regions_geo_zone_id_foreign` FOREIGN KEY (`geo_zone_id`) REFERENCES `geo_zones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_group` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint DEFAULT '1',
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `welcome_email_sent_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `versions` (
  `version_id` int unsigned NOT NULL AUTO_INCREMENT,
  `versionable_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `versionable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_data` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`version_id`),
  KEY `versions_versionable_id_index` (`versionable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `webhook_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_logs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headers` json DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `response` json DEFAULT NULL,
  `transaction_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `response_code` int DEFAULT NULL,
  `processing_time_ms` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `webhook_logs_gateway_index` (`gateway`),
  KEY `webhook_logs_request_id_index` (`request_id`),
  KEY `webhook_logs_status_index` (`status`),
  KEY `webhook_logs_transaction_id_index` (`transaction_id`),
  KEY `webhook_logs_document_id_index` (`document_id`),
  CONSTRAINT `webhook_logs_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`) ON DELETE SET NULL,
  CONSTRAINT `webhook_logs_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zones_code_unique` (`code`),
  KEY `zones_created_by_foreign` (`created_by`),
  KEY `zones_is_active_index` (`is_active`),
  KEY `zones_code_index` (`code`),
  CONSTRAINT `zones_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- WARNING: can't read the INFORMATION_SCHEMA.libraries table. It's most probably an old server 8.4.5.
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_09_27_212641_create_versions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2014_10_12_200000_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2022_11_08_125435_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2022_11_09_093636_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2022_11_09_093637_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2022_11_22_000005_create_geo_zones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2022_11_22_000006_create_sub_regions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2022_11_22_000007_create_country_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2022_11_22_000010_create_document_type_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2022_11_22_000016_create_document_status_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2022_11_22_000017_create_commitee_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2022_11_22_000021_create_payment_method_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2022_11_22_000025_create_license_type_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2022_11_22_000027_create_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2022_11_22_000028_create_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2022_11_22_000031_create_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2022_11_22_000033_create_document_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2022_11_22_000035_create_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2022_11_22_000036_create_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2022_11_22_000037_create_membership_plan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2022_11_22_000038_create_certifications_slot_prices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2022_11_22_000039_create_entity_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2022_11_22_000040_create_federation_user_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2022_11_22_000041_create_individual_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2022_11_22_000042_create_entity_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2022_11_22_000043_create_individual_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2022_11_22_000044_create_document_detail_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2022_11_22_000046_create_membership_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2022_11_22_000049_create_certification_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2022_11_22_000050_create_certifications_slot_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2022_11_22_000051_create_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2022_11_30_180956_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2022_11_30_180957_add_event_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2022_11_30_180958_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2022_12_15_092645_add_email_to_entity',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2022_12_19_170620_create_sports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2022_12_19_231852_add_interval_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2022_12_20_002825_add_member_code_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2022_12_20_125022_add_member_code_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2022_12_21_145857_create_user_group_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2022_12_21_150119_add_group_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2022_12_22_111721_add_license_id_to_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2022_12_23_091806_create_certifications_slot_type_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2022_12_23_092334_add_slot_type_to_certifications_slot_prices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2022_12_23_123633_add_instructor_to_certification_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2022_12_23_130946_add_entity_id_to_certification_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2022_12_24_020759_create_certifications_attributed_instructors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2022_12_26_141424_create_membership_plan_licenses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2022_12_30_092911_add_slot_type_to_certification_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2023_01_03_115638_create_professional_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2023_01_03_115654_create_individual_professional_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2023_01_03_171757_add_sport_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2023_01_03_174742_add_professional_role_id_to_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2023_01_03_174751_add_professional_role_id_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2023_01_16_104419_add_number_to_document_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2023_01_16_172628_add_member_code_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2023_01_16_185937_add_soft_delete_to_certifications_slot_prices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2023_01_18_113341_add_unit_value_to_document_detail_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2023_01_18_113827_add_unit_value_tax_value_tax_percentage_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2023_01_18_145610_add_shipped_date_to_certifications_slot',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2023_01_19_171629_add_tax_value_tax_percentage_to_membership_plan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2023_01_19_171733_add_tax_value_tax_percentage_to_certifications_slot_prices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2023_01_19_225802_add_zip_code_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2023_01_20_115641_add_phone_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2023_01_20_123558_add_soft_delete_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2023_01_20_155415_create_entity_committee_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2023_02_01_151706_create_entity_instructor_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2023_02_07_163452_add_soft_delete_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2023_02_09_151253_add_number_pad_and_number_year_to_document_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2023_02_09_163050_add_lat_and_lng_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2023_02_09_163549_add_lat_and_lng_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2023_02_16_113945_add_national_code_to_certification_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2023_02_23_104059_add_committee_id_to_certifications_attributed',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2023_02_24_183749_add_iso_to_country_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2023_03_01_123606_alter_tax_default_0_to_many_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2023_03_01_154451_create_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2023_03_02_152345_update_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2023_03_02_160850_add_international_code_to_certifications_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2023_03_08_160919_add_committee_id_to_professional_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2023_03_10_152421_create_certification_parents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2023_03_15_122407_create_entity_coach_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2023_03_16_160901_create_entity_professional_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2023_03_20_163614_change_active_to_status_class_individual_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2023_03_20_172342_create_entity_athletes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2023_03_23_170240_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2023_03_24_182420_official_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2023_04_03_142735_add_parent_id_to_membership_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2023_04_04_113634_add_languages_to_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2023_04_05_165009_add_category_to_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2023_04_17_082254_move_national_federation_number_to_individual_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2023_04_17_132238_add_friendly_name_to_membership_plan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2023_04_24_161316_add_native_name_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2023_04_26_143844_add_activated_at_to_official_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2023_04_27_082008_add_active_and_timestamp_to_entity_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2023_04_28_093446_add_country_id_to_official_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2023_04_28_140128_create_job_batches_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2023_04_28_140150_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2023_05_03_180828_add_latitude_longitude_to_country_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2023_05_17_083527_add_qrcode_path_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2023_05_18_081845_create_push_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2023_06_02_165955_add_qrcode_path_to_certification_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2023_06_10_104055_add_owner_member_code_to_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2023_06_12_100759_add_customer_name_to_document_detail_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2023_06_28_115514_add_gender_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2023_07_10_142822_add_professional_role_id_to_individual_professional_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2023_08_16_101850_add_address_location_postal_code_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2023_08_22_145121_create_evt_sports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2023_08_22_145122_create_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2023_08_22_145123_create_evt_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2023_08_22_145124_create_evt_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2023_08_22_145125_create_evt_discipline_attribute_association_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2023_08_22_145125_create_evt_event_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2023_08_22_145126_create_evt_attribute_rules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2023_08_22_145126_create_evt_enrollments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2023_08_22_145128_create_evt_event_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2023_08_22_145129_create_evt_age_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2023_08_22_154703_create_membership_membership_plan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2023_08_24_112251_add_invoice_number_to_document_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2023_08_24_144339_add_name_to_attribute_rules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2023_08_24_173137_update_evt_attribute_rules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2023_08_25_105716_add_json_to_evt_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2023_08_29_095533_add_activated_at_and_payment_status_to_evt_enrollments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2023_08_30_104457_create_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2023_08_30_104504_create_evt_coaches_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2023_08_30_104843_create_evt_staff_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2023_08_30_111228_create_evt_athletes_enrollment_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2023_08_30_121905_update_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2023_08_30_151305_create_evt_discipline_fees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2023_09_04_093059_add_doc_ref_validation_date_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2023_09_04_134731_create_attachment_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2023_09_04_140730_create_attachments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2023_09_04_142116_create_attachment_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2023_09_04_142154_create_attachment_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2023_09_04_142213_create_attachment_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2023_09_05_141312_create_attachment_professional_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2023_09_05_141405_create_attachment_filterfederations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2023_09_05_171402_create_languages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2023_09_05_171643_update_attachments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2023_09_06_144312_add_is_default_federation_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2023_09_11_133520_update_committee_id_to_nullable_in_attachments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2023_09_16_004422_add_nullable_committee_id_to_professional_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2023_09_17_234947_add_license_code_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2023_09_18_001228_add_last_login_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2023_09_20_171635_update_payment_methods_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2023_09_20_171858_create_payment_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2023_09_20_172755_add_soft_delete_to_document_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2023_09_23_095835_create_local_membership_plan_association_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2023_09_25_000438_add_rejected_to_individual_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2023_09_28_094843_add_payment_method_id_to_payment_transactions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2023_10_09_112901_update_with_candidacy_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2023_10_09_114809_create_evt_federation_candidacies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2023_10_09_114809_create_evt_federation_candidacy_attachments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2023_10_09_114809_create_evt_federation_candidacy_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2023_10_09_114809_create_evt_federation_candidacy_winners_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2023_10_09_160645_add_organization_type_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2023_10_09_164704_update_nullable_event_type_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2023_10_10_225103_add_enrollment_type_and_geographical_coverage_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2023_10_10_225107_rename_event_scope_to_event_geographical_coverage_in_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2023_10_12_175109_add_notes_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2023_10_17_153129_create_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2023_10_17_162818_modify_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2023_10_17_165532_create_evt_competition_discipline_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2023_10_18_151423_add_national_federation_number_and_rejected_at_to_entity_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2023_10_19_224448_create_event_geographics_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2023_10_20_113331_create_evt_antidoping_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2023_10_20_144741_create_evt_technical_delegates',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2023_10_23_115218_add_sport_id_to_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2023_10_23_165746_add_timestamps_to_evt_technical_delegates',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2023_10_24_151234_add_individual_id_to_evt_athletes_coaches_enrollment_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2023_10_24_155014_add_individual_id_to_evt_technical_delegates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2023_10_25_144340_create_evt_competition_referees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2023_10_26_104337_add_medals_to_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2023_10_26_151253_alter_date_of_report_reception_from_evt_technical_delegates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2023_10_27_110902_add_country_and_city_to_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2023_10_27_162914_alter_nullable_from_evt_antidoping_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2023_10_31_180922_create_evt_competition_referee_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2023_11_02_185303_create_competition_coach_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2023_11_02_230206_add_fee_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2023_11_03_164454_create_evt_event_pins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2023_11_03_172319_create_evt_competition_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2023_11_06_160047_create_evt_events_professional_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2023_11_09_144615_add_order_to_certifications_slot_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2023_11_09_235552_add_slot_type_to_certifications_slot_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2023_11_17_134009_add_federation_id_to_official_documents',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2023_11_17_163842_add_recipient_name_to_attachments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2023_11_17_175809_add_key_to_payment_method_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2023_11_27_141113_create_generated_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2023_11_30_193552_add_deleted_at_to_entity_athlete_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2023_12_03_012445_add_status_class_to_individual_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2023_12_04_185447_create_federation_professional_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2023_12_04_231210_add_role_to_official_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2023_12_05_154406_create_evt_individuals_enrollment',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2023_12_11_154630_create_evt_organizers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2023_12_12_141546_add_external_link_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2023_12_15_001030_create_evt_pricing_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2023_12_17_235036_add_status_class_to_evt_federation_candidacies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2023_12_19_163714_add_pricing_option_to_evt_pricing_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2023_12_19_182809_add_pricing_id_to_evt_enrollments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2023_12_20_101608_add_entity_id_to_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2023_12_20_182944_add_individual_id_nullable_to_official_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2023_12_22_111847_create_competition_staff_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2023_12_26_164227_add_soft_deletes_to_document_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2023_12_27_095623_add_is_enabled_to_payment_methods_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2023_12_28_160057_update_decimal_precision_on_certifications_slot_prices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2023_12_30_195118_add_status_class_to_entity_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2024_01_11_011231_add_status_class_to_evt_individuals_enrollment',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2024_01_13_184716_add_deleted_at_to_certifications_slot_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2024_01_13_185359_change_location_on_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2024_01_16_004142_add_usage_tracking_to_event_pins_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2024_01_26_161620_add_requester_model_type_to_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2024_01_26_162748_add_pvp_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2024_01_28_011025_add_requester_model_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2024_01_29_173159_create_individual_enrollment_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2024_01_29_175200_create_evt_event_attribute_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2024_01_31_014154_add_deleted_at_to_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2024_02_05_130654_add_requires_cmas_approval_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2024_02_15_160406_add_paid_amount_to_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2024_02_16_234827_create_evt_attribute_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2024_02_17_000855_create_evt_event_attribute_group_attribute_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2024_02_18_222219_create_evt_event_attribute_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2024_02_20_011927_add_venue_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2024_03_04_163238_create_evt_event_organizer_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2024_03_07_155815_create_discipline_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2024_03_07_160634_create_evt_template_discipline_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2024_03_11_134722_add_dates_team_composition_to_evt_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2024_03_11_143742_create_evt_discipline_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2024_03_11_164721_add_discipline_template_to_evt_competition_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2024_03_13_142111_add_responsible_to_evt_event_organizer_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2024_03_14_002715_remove_event_fee_type_from_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2024_03_15_012839_add_description_to_pricing_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2024_03_17_144657_add_contacts_to_evt_antidoping_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2024_03_19_105251_make_medals_fields_nullable_in_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2024_04_19_173713_add_visibility_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2024_05_15_141727_add_address_fields_to_document_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2024_05_16_105353_update_foreign_keys_for_enrollments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2024_05_16_115613_add_enrollment_role_to_evt_pricing_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2024_05_17_004401_create_evt_referees_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2024_05_17_004617_create_evt_officials_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2024_05_17_162113_add_individual_id_to_evt_officials_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2024_05_17_162336_add_individual_id_to_evt_referee_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2024_05_24_002205_add_max_min_to_evt_attribute_rules_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2024_05_24_002206_add_required_to_evt_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2024_05_26_112627_add_athlete_limit_to_discipline_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2024_05_26_230056_add_soft_deletes_to_evt_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2024_05_27_235052_create_sport_age_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2024_05_28_000124_add_age_group_to_evt_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2024_05_29_113911_remove_contrain_evt_sport_age_group_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2024_05_30_231837_remove_sport_constrain_evt_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2024_05_30_233808_remove_sport_constrain_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2024_06_02_220914_add_soft_deletes_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (269,'2024_06_06_222143_add_pricing_to_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (270,'2024_06_07_014731_add_pricing_to_evt_coaches_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (271,'2024_06_07_014744_add_pricing_to_evt_individuals_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (272,'2024_06_07_014758_add_pricing_to_evt_officials_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (273,'2024_06_07_014831_add_pricing_to_evt_referees_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (274,'2024_06_08_004607_add_discipline_price_to_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (275,'2024_06_09_153702_add_discipline_price_id_to_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (276,'2024_06_10_121937_add_enrollment_flags_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (277,'2024_06_12_115337_change_nullable_fields_on_technical_delegates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (278,'2024_06_12_123256_add_offset_columns_to_certification_slots_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (279,'2024_06_17_174719_create_officials_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (280,'2024_06_17_215158_add_enrollment_type_to_evt_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (281,'2024_06_30_013248_update_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (282,'2024_06_30_013813_add_total_price_to_evt_enrollments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (283,'2024_07_05_114459_add_status_to_generated_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (284,'2024_07_13_095228_create_evt_discipline_sport_age_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (285,'2024_08_01_004758_add_entity_id_to_evt_officials_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (286,'2024_08_15_230312_add_qrcodepath_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (287,'2024_09_19_142530_modify_certification_attributed_individual_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (288,'2024_09_19_142917_modify_entity_athletes_individual_id_foreign',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (289,'2024_09_19_143330_modify_individual_federation_individual_id_foreign',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (290,'2024_09_19_152134_add_instructor_name_to_certifications_instructors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (291,'2024_09_19_152359_modify_user_id_on_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (292,'2024_09_19_155135_fix_individual_foreign_keys_cascade',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (293,'2024_09_19_160315_fix_individual_foreign_keys_cascade',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (294,'2024_09_25_215825_add_broadcast_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (295,'2024_09_25_221728_add_validation_fields_to_evt_technical_delegates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (296,'2024_09_25_223740_add_loc_contract_to_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (297,'2024_11_05_155515_add_postal_code_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (298,'2024_11_28_130713_add_distance_to_evt_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (299,'2024_11_29_084809_add_public_lists_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (300,'2024_12_05_115325_update_discipline_on_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (301,'2024_12_12_150633_add_document_id_to_evt_enrollments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (302,'2024_12_30_160805_create_evt_coaches_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (303,'2024_12_31_145938_add_entity_id_to_evt_individuals_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (304,'2025_01_01_000000_remove_systempay_payment_methods',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (305,'2025_01_02_122530_add_role_filtering_to_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (306,'2025_01_02_230541_add_team_identifier_to_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (307,'2025_01_03_180626_add_max_limit_to_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (308,'2025_01_06_125223_add_entity_id_to_evt_coaches_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (309,'2025_01_07_121538_create_evt_staff_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (310,'2025_01_07_140000_create_menus_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (311,'2025_01_07_140001_create_menu_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (312,'2025_02_02_222837_add_soft_deletes_to_evt_athletes_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (313,'2025_02_02_223126_add_soft_deletes_evt_athletes_enrollment_attributes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (314,'2025_02_20_084658_add_filters_to_generated_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (315,'2025_03_04_154225_add_issue_date_to_official_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (316,'2025_03_13_234501_create_evt_event_staff_attribute_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (317,'2025_03_22_091511_create_evt_event_referee_attribute_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (318,'2025_03_22_172703_create_evt_referees_enrollment_attributes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (319,'2025_03_27_013243_update_athlete_enrollments_with_pending_payment_status',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (320,'2025_04_03_000000_create_evt_enrollment_credits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (321,'2025_04_10_162518_add_social_fields_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (322,'2025_04_10_162524_add_social_fields_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (323,'2025_04_10_171802_create_federation_voting_rights_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (324,'2025_04_11_180052_add_public_description_to_entities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (325,'2025_04_22_150627_create_entity_professional_role_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (326,'2025_04_29_151034_add_delivery_method_to_certifications_slot_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (327,'2025_04_29_180619_add_settlement_details_to_certifications_slot_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (328,'2025_04_30_153622_add_latin_names_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (329,'2025_05_03_115406_add_diving_course_fields_to_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (330,'2025_05_07_145221_change_certification_fields_to_text',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (331,'2025_05_21_141201_add_acronym_to_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (332,'2025_05_21_172922_add_is_international_to_certification_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (333,'2025_06_02_101832_create_membership_packages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (334,'2025_06_02_111447_create_member_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (335,'2025_06_02_124233_create_affiliation_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (336,'2025_06_02_125952_create_insurances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (337,'2025_06_04_180227_create_package_insurance_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (338,'2025_06_05_122410_create_affiliations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (339,'2025_06_05_124236_create_missing_packages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (340,'2025_06_12_151736_add_status_class_to_affiliations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (341,'2025_06_18_131129_add_vat_rate_to_affiliation_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (342,'2025_06_18_132512_add_vat_rate_to_insurance_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (343,'2025_06_18_143806_add_status_to_insurances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (344,'2025_06_18_155153_add_policy_number_generation_fields_to_insurance_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (345,'2025_06_18_201012_add_distribution_methods_to_membership_packages',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (346,'2025_06_19_002948_create_districts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (347,'2025_06_19_003022_create_zones_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (348,'2025_06_19_003048_create_district_zone_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (349,'2025_06_19_003048_create_entity_zone_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (350,'2025_06_19_003048_create_federation_zone_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (351,'2025_06_19_003049_create_individual_zone_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (352,'2025_06_19_010248_add_district_id_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (353,'2025_06_19_010254_add_district_id_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (354,'2025_06_19_010259_add_district_id_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (355,'2025_06_19_103615_add_requires_official_document_to_insurance_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (356,'2025_06_19_224446_add_entity_group_request_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (357,'2025_06_19_224523_add_purchase_fields_to_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (358,'2025_06_19_224552_remove_cmas_approval_from_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (359,'2025_06_19_224729_simplify_license_attributed_states',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (360,'2025_06_20_124731_add_federation_id_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (361,'2025_06_20_update_licenses_allow_entity_group_request',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (362,'2025_06_24_161415_add_is_validation_plan_to_affiliation_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (363,'2025_06_25_134428_update_affiliations_member_id_to_support_uuid',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (364,'2025_06_25_134742_update_member_subscriptions_member_id_to_uuid',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (365,'2025_06_26_145636_add_validity_type_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (366,'2025_06_26_155430_add_required_documents_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (367,'2025_06_26_161000_add_polymorphic_columns_to_official_documents',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (368,'2025_06_26_162000_populate_official_documents_polymorphic_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (369,'2025_06_26_172957_update_entity_official_document_types',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (370,'2025_06_26_231107_update_entity_official_documents_to_main_federation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (371,'2025_06_26_235250_add_member_number_to_individuals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (372,'2025_06_26_235254_add_member_number_to_entities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (373,'2025_06_26_235259_create_member_number_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (374,'2025_06_28_154758_create_diving_professional_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (375,'2025_06_28_154802_create_diving_technical_director_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (376,'2025_06_29_214424_add_requires_admin_validation_to_license_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (377,'2025_06_29_214756_add_validation_fields_to_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (378,'2025_06_29_220626_add_message_and_license_id_to_diving_technical_director_invitations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (379,'2025_06_29_221212_add_license_attributed_id_to_official_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (380,'2025_06_30_100000_add_indexes_to_diving_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (381,'2025_06_30_135000_remove_problematic_migration_record',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (382,'2025_06_30_140000_add_missing_columns_to_entity_professional_role_invitations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (383,'2025_06_30_212105_add_is_international_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (384,'2025_07_03_134626_fix_license_requester_model_values',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (385,'2025_07_03_195707_create_license_required_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (386,'2025_07_04_124733_add_pricing_fields_to_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (387,'2025_07_04_130032_drop_certification_slot_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (388,'2025_07_04_195331_remove_unit_value_individual_from_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (389,'2025_07_05_001430_create_federation_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (390,'2025_07_05_003116_remove_federation_id_from_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (391,'2025_07_06_224310_add_requires_active_affiliation_to_insurance_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (392,'2025_07_07_113133_create_license_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (393,'2025_07_07_113137_create_certification_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (394,'2025_07_07_113142_create_federation_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (395,'2025_07_07_163205_add_menu_management_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (396,'2025_07_07_201547_enhance_roles_table_for_dynamic_management',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (397,'2025_07_07_201552_enhance_permissions_table_for_dynamic_management',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (398,'2025_07_07_201556_create_role_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (399,'2025_07_07_201601_create_route_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (400,'2025_07_07_201605_create_role_audit_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (401,'2025_07_09_225920_add_category_and_description_to_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (402,'2025_07_14_162255_change_insurance_status_to_status_class',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (403,'2025_07_14_163054_add_requested_by_user_id_to_insurances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (404,'2025_07_14_223652_add_requester_tracking_to_member_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (405,'2025_07_14_223717_add_requester_tracking_to_affiliations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (406,'2025_07_14_224408_fix_requester_tracking_in_insurances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (407,'2025_07_15_002208_fix_incorrect_requester_types_in_memberships_data',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (408,'2025_07_15_111851_change_license_requester_model_to_json',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (409,'2025_07_21_000000_make_member_subscription_id_nullable_in_insurances_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (410,'2025_07_21_153835_add_federation_facilitated_to_request_type_enums',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (411,'2025_07_21_153908_add_federation_managed_distribution_method',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (412,'2025_07_22_234910_create_imports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (413,'2025_07_22_234916_create_import_errors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (414,'2025_07_22_234954_add_duplicate_check_index_to_individuals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (415,'2025_07_23_012322_add_national_federation_number_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (416,'2025_07_23_012358_remove_national_federation_number_from_individual_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (417,'2025_08_04_211108_create_menu_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (418,'2025_08_04_211129_add_menu_group_id_to_menu_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (419,'2025_08_04_221259_add_role_visibility_to_menu_groups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (420,'2025_08_12_085041_add_vat_number_and_phone_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (421,'2025_08_21_164555_add_deactivation_fields_to_entity_professional_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (422,'2025_08_22_171812_add_cmas_to_diving_professional_certifications_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (423,'2025_08_23_231138_add_document_type_to_diving_professional_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (424,'2025_08_25_154403_add_certification_level_to_license_required_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (425,'2025_08_25_154528_make_certification_id_nullable_in_license_required_certifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (426,'2025_08_27_213729_rename_diving_technical_director_invitations_to_assignments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (427,'2025_08_27_213747_convert_diving_invitations_to_assignments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (428,'2025_09_01_094820_update_evt_events_for_single_country_federation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (429,'2025_09_01_114423_create_evt_event_roles_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (430,'2025_09_02_165711_create_user_role_sources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (431,'2025_09_02_172814_populate_existing_user_role_sources',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (432,'2025_09_03_010423_add_has_international_portal_to_individual_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (433,'2025_09_03_232430_fix_student_role_guard_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (434,'2025_09_04_124419_make_country_id_nullable_in_districts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (435,'2025_09_04_174016_update_professional_role_names_to_portuguese',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (436,'2025_09_05_012436_fix_license_attributed_morph_types',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (437,'2025_09_06_150003_fix_morph_types_to_use_aliases',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (438,'2025_09_08_125151_migrate_cmas_to_admin_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (439,'2025_09_08_143403_update_cmas_group_to_admin',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (440,'2025_09_08_151120_update_menu_cmas_to_admin',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (441,'2025_09_08_203003_update_route_permissions_cmas_to_admin',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (442,'2025_09_09_202147_add_missing_federation_142_sport_licenses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (443,'2025_09_09_202410_normalize_license_requester_model_values',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (444,'2025_09_10_100439_update_allow_entity_group_request_default_value',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (445,'2025_09_10_105747_update_diving_licenses_require_admin_validation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (446,'2025_09_10_131912_add_approval_fields_to_diving_entity_technical_directors_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (447,'2025_09_11_151037_sync_orphaned_roles_to_user_role_sources',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (448,'2025_09_12_153946_rename_international_code_to_license_number_in_license_attributed_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (449,'2025_09_15_203953_fix_entity_official_documents_owner_type',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (450,'2025_09_16_144227_fix_remaining_entity_morph_types',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (451,'2025_09_22_093315_add_insurer_details_to_insurance_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (452,'2025_09_29_141910_add_category_fields_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (453,'2025_09_29_214014_add_can_issue_certifications_to_federation_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (454,'2025_09_30_152520_add_sport_id_to_entity_professional_role_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (455,'2025_10_01_120453_cleanup_orphaned_permissions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (456,'2025_10_01_172644_simplify_role_system',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (457,'2025_10_06_155636_add_selected_roles_to_menu_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (458,'2025_10_06_163130_add_scope_to_roles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (459,'2025_10_06_163200_populate_roles_scope',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (460,'2025_10_06_163228_populate_permissions_category',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (461,'2025_10_07_161204_drop_user_role_sources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (462,'2025_10_07_193646_remove_candidacy_system_from_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (463,'2025_10_07_204744_create_application_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (464,'2025_10_07_204744_create_event_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (465,'2025_10_07_204745_create_application_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (466,'2025_10_07_204746_create_application_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (467,'2025_10_07_204747_create_application_state_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (468,'2025_10_08_121956_change_event_category_id_to_string_in_event_applications',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (469,'2025_10_08_194045_fix_event_applications_entity_type_morph_values',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (470,'2025_10_10_113651_add_state_to_application_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (471,'2025_12_27_195111_create_event_zone_and_event_district_pivot_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (472,'2025_12_30_004049_add_allow_official_enrollment_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (473,'2025_12_30_004049_add_venue_postal_code_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (474,'2025_12_30_004050_add_trophies_and_requires_official_adel_to_evt_competitions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (475,'2025_12_30_004051_create_evt_event_coach_attribute_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (476,'2025_12_30_004052_create_evt_event_official_attribute_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (477,'2025_12_30_010631_add_required_documents_json_to_evt_competitions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (478,'2025_12_30_011638_add_poster_and_location_url_to_evt_events',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (479,'2026_01_03_184938_fix_is_local_for_modalidade_federations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (480,'2026_01_03_185343_add_requires_local_federation_affiliation_to_evt_competitions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (481,'2026_01_04_161516_add_role_based_document_requirements_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (482,'2026_01_05_122623_add_diving_professional_documents_to_licenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (483,'2026_01_06_161955_add_sport_id_to_entity_professional_role_invitations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (484,'2026_01_06_200023_normalize_license_requester_model_values',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (485,'2026_01_07_033051_update_certification_pricing_model',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (486,'2026_01_07_033229_add_price_option_to_certification_attributed',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (487,'2026_01_07_170345_add_is_international_to_committee_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (488,'2026_01_07_170415_create_federation_committee_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (489,'2026_01_07_170502_add_divingservices_committee_and_set_international_flags',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (490,'2026_01_07_170623_migrate_certifications_to_divingservices',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (491,'2026_01_07_170707_migrate_licenses_to_divingservices',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (492,'2026_01_07_170731_seed_federation_committee_relationships',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (493,'2026_01_07_181518_remove_is_international_from_certifications_and_licenses',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (494,'2026_01_07_220308_alter_model_has_permissions_model_id_to_string',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (495,'2026_01_09_104536_standardize_morph_type_values_in_licenses_attributed',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (496,'2026_01_09_134543_add_published_event_id_to_event_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (497,'2026_01_10_003005_add_entity_sport_registration_requirements_to_evt_competitions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (498,'2026_01_10_015640_create_evt_technical_delegate_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (499,'2026_01_10_015641_create_evt_chief_judge_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (500,'2026_01_10_015642_add_is_present_to_evt_referee_function_assignments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (501,'2026_01_10_015643_create_evt_event_report_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (502,'2026_01_11_200945_add_competition_fields_to_referee_assignments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (503,'2026_01_11_224200_update_professional_roles_referee_judge_to_technical_official',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (504,'2026_01_12_160614_add_entity_id_to_evt_referees_enrollment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (505,'2026_01_12_191033_create_webhook_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (506,'2026_01_12_201545_create_moloni_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (507,'2026_01_12_201558_create_moloni_customers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (508,'2026_01_12_201558_create_moloni_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (509,'2026_01_12_201559_create_moloni_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (510,'2026_01_12_201600_create_moloni_sync_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (511,'2026_01_13_180931_add_moloni_reference_to_plans',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (512,'2026_01_13_181913_add_moloni_reference_to_license_and_certification',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (513,'2026_01_16_152414_add_insurance_company_name_to_insurance_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (514,'2026_01_21_175329_add_welcome_email_sent_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (515,'2026_01_23_003942_consolidate_judge_referee_roles_to_technical_official',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (516,'2026_01_23_190000_fix_diving_licenses_wrong_state',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (517,'2026_01_23_190631_fix_international_diving_licenses_skip_admin_validation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (518,'2026_01_23_193000_create_missing_license_payment_documents',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (519,'2026_01_23_194000_fix_committee_international_flags',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (520,'2026_01_28_030704_add_access_backups_permission',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (521,'2026_01_28_035933_create_backup_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (522,'2026_01_28_100000_populate_missing_certification_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (523,'2026_01_29_120000_insert_individual_approved_federation_role',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (524,'2026_02_01_171443_add_underwater_sports_coach_and_update_visual_jury',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (525,'2026_02_01_171758_rename_juiz_arbitro_to_oficial_tecnico',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (526,'2026_02_01_171917_remove_cmas_professional_roles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (527,'2026_02_01_172049_remove_duplicate_diving_instructor_role',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (528,'2026_02_01_175508_add_has_international_portal_to_entity_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (529,'2026_02_01_204736_cleanup_legacy_official_document_types',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (530,'2026_02_04_165542_fix_moloni_customers_customerable_id_to_uuid',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (531,'2026_02_05_133255_add_menu_permissions_to_federation_admin',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (532,'2026_02_05_143430_fix_menu_items_committee_ids',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (533,'2026_02_07_205323_null_offline_payment_method_instructions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (534,'2026_02_07_225506_add_evaluation_to_evt_referees_enrollment',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (535,'2026_02_07_231126_add_evaluation_notes_to_evt_referees_enrollment',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (536,'2026_02_08_201454_add_public_registry_visibility_to_individual',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (537,'2026_02_08_210034_add_public_registry_visibility_to_entity',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (538,'2026_02_10_183353_add_sport_type_to_sports_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (539,'2026_02_11_155529_add_moloni_reference_to_evt_events_and_evt_competitions',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (540,'2026_02_11_175528_add_target_audience_to_application_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (541,'2026_02_12_210948_add_form_data_to_event_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (542,'2026_02_13_002909_add_registration_type_category_age_group_to_application_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (543,'2026_02_13_082319_add_section_to_application_comments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (544,'2026_02_13_092637_change_document_type_to_varchar_on_application_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (545,'2026_02_13_092956_backfill_created_at_on_application_state_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (546,'2026_02_13_141838_add_category_to_event_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (547,'2026_02_14_112146_add_batch_id_to_certification_attributed',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (548,'2026_02_23_003517_fix_generic_coach_certification_professional_role',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (549,'2026_02_23_023823_update_menu_items_technical_delegate_route',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (550,'2026_02_28_000043_fix_escola_mergulho_requires_admin_validation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (551,'2026_03_04_195653_fix_individual_member_number_unique_index_for_soft_deletes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (552,'2026_03_05_182716_create_certification_sport_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (553,'2026_03_05_182749_seed_certification_sport_for_generic_coach_certs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (554,'2026_03_05_200000_create_license_sport_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (555,'2026_03_05_200001_seed_license_sport_from_sport_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (556,'2026_03_05_210000_add_atividades_subaquaticas_sport',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (557,'2026_03_05_210001_update_license_61_sport_to_atividades_subaquaticas',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (558,'2026_03_18_100000_assign_manage_events_to_federation_admin',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (559,'2026_04_05_150238_add_insurer_status_to_generated_reports_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (560,'2026_04_13_122242_normalize_document_owner_type_morph_aliases',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (561,'2026_04_26_131507_add_regulations_url_to_evt_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (562,'2026_05_12_220000_renormalize_document_owner_type_morph_aliases',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (563,'2026_06_05_120000_rename_cmas_diving_routes_to_international_diving',1);
