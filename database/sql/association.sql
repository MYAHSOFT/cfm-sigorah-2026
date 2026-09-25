-- --------------------------------------------------------
-- Module Association — Crédit au groupement (groupes solidaires)
--
-- Complément des tables existantes `ass_groups` et `ass_members`.
-- Consommé par le micro-service Laravel + Flutter (collecte terrain),
-- toujours rattaché au noyau CBS :
--   * Customer : `crm_members` (`folio`) → `crm_customers`
--   * Lending  : `loan_applications` (`id_application`), `loan_contracts` (`id_contract`)
--   * Admin    : `bank_employes` (`id_employe`)
--
-- Convention : toute colonne `customer_id` / `id_group` du module référence
-- `crm_members`.`folio` (même ancrage que `loan_applications`.`folio`).
--
-- Serveur cible : MariaDB 12.x
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

--
-- Base de données : `otivzl_sigorah_db`
--

-- --------------------------------------------------------
-- Tables existantes — complétées, jamais recréées
-- --------------------------------------------------------

--
-- `ass_groups` (ex `cf_groupe_solidarites`) : colonnes héritées manquantes
--
ALTER TABLE `ass_groups`
  ADD COLUMN IF NOT EXISTS `old_id_group` varchar(10) DEFAULT NULL AFTER `num_group`,
  ADD COLUMN IF NOT EXISTS `event` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0: No event, 1: New group, 2: New transfer' AFTER `old_id_group`;

--
-- `ass_members` (ex `cf_membres`) : colonne héritée manquante
--
ALTER TABLE `ass_members`
  ADD COLUMN IF NOT EXISTS `profile` enum('1','2') NOT NULL DEFAULT '2' AFTER `member_role`;

-- --------------------------------------------------------
-- Nouvelles tables
-- --------------------------------------------------------

--
-- Structure de la table `ass_member_roles` (ex `cf_fonction_membre_groupement`)
--

DROP TABLE IF EXISTS `ass_member_roles`;
CREATE TABLE IF NOT EXISTS `ass_member_roles` (
  `id_role` varchar(5) NOT NULL,
  `description` varchar(60) NOT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_cycles` (ex `cf_dossiers`)
--

DROP TABLE IF EXISTS `ass_cycles`;
CREATE TABLE IF NOT EXISTS `ass_cycles` (
  `id_cycle` varchar(15) NOT NULL,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `cycle_start_date` date NOT NULL,
  `cycle_end_date` date NOT NULL,
  `planned_disbursement_date` date DEFAULT NULL,
  `actual_disbursement_date` date DEFAULT NULL,
  `planned_repayment_date` date DEFAULT NULL,
  `meeting_mode` char(1) NOT NULL,
  `meeting_day` enum('1','2','3','4','5','6','7') NOT NULL,
  `meeting_count` int(10) UNSIGNED NOT NULL DEFAULT 16,
  `nature` enum('1','2') NOT NULL DEFAULT '1',
  `status` enum('O','C','A','P') NOT NULL DEFAULT 'O' COMMENT 'O : Open cycle,\r\nA : Active cycle (loan disbursed),\r\nC : Closed cycle (end of cycle)\r\nP : Loan to be disbursed after analysis\r\nR : Cycle rejected after analysis ',
  `disbursement_status` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0 : Under analysis\r\n1 : Disbursed by the facilitator\r\n2 : Loan disbursed (final step),',
  `facilitator_id` varchar(20) NOT NULL,
  `group_count` int(11) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cycle`),
  KEY `group_id` (`group_id`),
  KEY `facilitator_id` (`facilitator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_cycle_calendars` (ex `cf_calendier_cycles`)
--

DROP TABLE IF EXISTS `ass_cycle_calendars`;
CREATE TABLE IF NOT EXISTS `ass_cycle_calendars` (
  `id_operation` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cycle_id` varchar(15) NOT NULL,
  `operation_date` date NOT NULL,
  `amount` double(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` double(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_operation`),
  KEY `cycle_id` (`cycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_applications` (ex `cf_demandes`)
--

DROP TABLE IF EXISTS `ass_applications`;
CREATE TABLE IF NOT EXISTS `ass_applications` (
  `id_application` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_uuid` char(36) DEFAULT NULL,
  `device_id` varchar(64) DEFAULT NULL,
  `created_at_local` datetime DEFAULT NULL,
  `cycle_id` varchar(15) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL COMMENT 'crm_members.folio',
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `principal_amount` double(15,2) DEFAULT 0.00,
  `loan_purpose` varchar(120) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_application`),
  UNIQUE KEY `client_uuid` (`client_uuid`),
  KEY `cycle_id` (`cycle_id`),
  KEY `customer_id` (`customer_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_loan_applications` (ex `cf_demande_pret_groupes`)
-- Lien groupe/cycle ↔ demande de prêt Lending (`loan_applications`)
--

DROP TABLE IF EXISTS `ass_loan_applications`;
CREATE TABLE IF NOT EXISTS `ass_loan_applications` (
  `id_loan_application` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `cycle_id` varchar(15) NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `received_amount` double(15,2) UNSIGNED DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_loan_application`),
  KEY `group_id` (`group_id`),
  KEY `cycle_id` (`cycle_id`),
  KEY `application_id` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_loan_contracts` (ex `cf_contrat_pret_groupes`)
-- Lien groupe/cycle ↔ contrat de prêt Lending (`loan_contracts`)
--

DROP TABLE IF EXISTS `ass_loan_contracts`;
CREATE TABLE IF NOT EXISTS `ass_loan_contracts` (
  `id_loan_contract` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `cycle_id` varchar(15) NOT NULL,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `received_amount` double(15,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT 'Amount received by the member',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_loan_contract`),
  UNIQUE KEY `contract_id` (`contract_id`) USING BTREE,
  KEY `group_id` (`group_id`),
  KEY `cycle_id` (`cycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_disbursements` (ex `cf_decaissements`)
--

DROP TABLE IF EXISTS `ass_disbursements`;
CREATE TABLE IF NOT EXISTS `ass_disbursements` (
  `id_operation` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `journal_id` varchar(30) NOT NULL,
  `cycle_id` varchar(15) NOT NULL,
  `piece_number` varchar(30) NOT NULL,
  `operation_date` date NOT NULL,
  `debit` double(15,2) DEFAULT 0.00,
  `credit` double(15,2) DEFAULT 0.00,
  `ref_operation` varchar(60) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_operation`),
  KEY `cycle_id` (`cycle_id`),
  KEY `journal_id` (`journal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_schedules` (ex `cf_echeanciers`)
--

DROP TABLE IF EXISTS `ass_schedules`;
CREATE TABLE IF NOT EXISTS `ass_schedules` (
  `id_operation` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `operation_date` date NOT NULL,
  `amount` double(15,2) DEFAULT 0.00,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_operation`),
  KEY `contract_id` (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ass_operations` (ex `cf_operations`)
-- Collecte en réunion (Flutter) : `client_uuid` / `idempotency_key` garantissent
-- une synchronisation sans doublon, comme `loan_transactions`.
--

DROP TABLE IF EXISTS `ass_operations`;
CREATE TABLE IF NOT EXISTS `ass_operations` (
  `id_operation` bigint(20) NOT NULL AUTO_INCREMENT,
  `client_uuid` char(36) DEFAULT NULL,
  `idempotency_key` char(36) DEFAULT NULL,
  `device_id` varchar(64) DEFAULT NULL,
  `created_at_local` datetime DEFAULT NULL,
  `cycle_id` varchar(15) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL COMMENT 'crm_members.folio',
  `operation_date` date NOT NULL,
  `meeting_date` date NOT NULL,
  `repayment_amount` double(15,2) DEFAULT 0.00,
  `deposit_amount` double(15,2) DEFAULT 0.00,
  `withdrawal_amount` double(15,2) DEFAULT 0.00,
  `penalty` double(15,2) DEFAULT 0.00,
  `ref_operation` varchar(60) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_operation`),
  UNIQUE KEY `client_uuid` (`client_uuid`),
  UNIQUE KEY `idempotency_key` (`idempotency_key`),
  KEY `customer_id` (`customer_id`),
  KEY `cycle_id` (`cycle_id`),
  KEY `ref_operation` (`ref_operation`),
  KEY `meeting_date` (`meeting_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Vues
-- --------------------------------------------------------

--
-- Structure de la vue `ass_group_contracts_view` (ex `cf_contrat_for_groupe`)
--
DROP VIEW IF EXISTS `ass_group_contracts_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ass_group_contracts_view` AS
SELECT `g`.`id_group` AS `id_group`, `g`.`agent_id` AS `agent_id`, `g`.`num_group` AS `num_group`,
       `c`.`cycle_id` AS `cycle_id`, `c`.`contract_id` AS `contract_id`, `c`.`received_amount` AS `received_amount`
FROM (`ass_groups` `g` join `ass_loan_contracts` `c` on(`g`.`id_group` = `c`.`group_id`));




-- --------------------------------------------------------
-- Contraintes (nouvelles tables uniquement ; celles de `ass_groups`
-- et `ass_members` existent déjà)
-- --------------------------------------------------------

ALTER TABLE `ass_cycles`
  ADD CONSTRAINT `fk_ass_cycles_group_id` FOREIGN KEY (`group_id`) REFERENCES `ass_groups` (`id_group`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_cycles_facilitator_id` FOREIGN KEY (`facilitator_id`) REFERENCES `bank_employes` (`id_employe`) ON UPDATE CASCADE;

ALTER TABLE `ass_cycle_calendars`
  ADD CONSTRAINT `fk_ass_cycle_calendars_cycle_id` FOREIGN KEY (`cycle_id`) REFERENCES `ass_cycles` (`id_cycle`) ON UPDATE CASCADE;

ALTER TABLE `ass_applications`
  ADD CONSTRAINT `fk_ass_applications_cycle_id` FOREIGN KEY (`cycle_id`) REFERENCES `ass_cycles` (`id_cycle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_applications_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `crm_members` (`folio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_applications_group_id` FOREIGN KEY (`group_id`) REFERENCES `ass_groups` (`id_group`) ON UPDATE CASCADE;

ALTER TABLE `ass_loan_applications`
  ADD CONSTRAINT `fk_ass_loan_applications_cycle_id` FOREIGN KEY (`cycle_id`) REFERENCES `ass_cycles` (`id_cycle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_loan_applications_group_id` FOREIGN KEY (`group_id`) REFERENCES `ass_groups` (`id_group`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_loan_applications_application_id` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id_application`) ON UPDATE CASCADE;

ALTER TABLE `ass_loan_contracts`
  ADD CONSTRAINT `fk_ass_loan_contracts_group_id` FOREIGN KEY (`group_id`) REFERENCES `ass_groups` (`id_group`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_loan_contracts_contract_id` FOREIGN KEY (`contract_id`) REFERENCES `loan_contracts` (`id_contract`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_loan_contracts_cycle_id` FOREIGN KEY (`cycle_id`) REFERENCES `ass_cycles` (`id_cycle`) ON UPDATE CASCADE;

ALTER TABLE `ass_disbursements`
  ADD CONSTRAINT `fk_ass_disbursements_cycle_id` FOREIGN KEY (`cycle_id`) REFERENCES `ass_cycles` (`id_cycle`) ON UPDATE CASCADE;

ALTER TABLE `ass_schedules`
  ADD CONSTRAINT `fk_ass_schedules_contract_id` FOREIGN KEY (`contract_id`) REFERENCES `loan_contracts` (`id_contract`) ON UPDATE CASCADE;

ALTER TABLE `ass_operations`
  ADD CONSTRAINT `fk_ass_operations_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `crm_members` (`folio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ass_operations_cycle_id` FOREIGN KEY (`cycle_id`) REFERENCES `ass_cycles` (`id_cycle`) ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
