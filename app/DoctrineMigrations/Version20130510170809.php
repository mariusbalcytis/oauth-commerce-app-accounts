<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20130510170809 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, transfer_id INT DEFAULT NULL, payer_id INT DEFAULT NULL, beneficiary_id INT DEFAULT NULL, client_id INT DEFAULT NULL, application_id INT DEFAULT NULL, `key` VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, amount INT NOT NULL, status INT NOT NULL, credentialsId INT NOT NULL, UNIQUE INDEX UNIQ_EAA81A4C537048AF (transfer_id), INDEX IDX_EAA81A4CC17AD9A9 (payer_id), INDEX IDX_EAA81A4CECCAAFA0 (beneficiary_id), INDEX IDX_EAA81A4C19EB6921 (client_id), INDEX IDX_EAA81A4C3E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE accounts (id INT AUTO_INCREMENT NOT NULL, disposable_account_type_id INT DEFAULT NULL, reservation_account_type_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_CAC89EACCC6B5092 (disposable_account_type_id), UNIQUE INDEX UNIQ_CAC89EAC53772492 (reservation_account_type_id), UNIQUE INDEX UNIQ_CAC89EAC7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE transfers (id INT AUTO_INCREMENT NOT NULL, manager_key VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE account_types (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE account_entries (id INT AUTO_INCREMENT NOT NULL, transfer_id INT DEFAULT NULL, account_type_id INT DEFAULT NULL, amount INT NOT NULL, date DATETIME NOT NULL, entry_key VARCHAR(255) DEFAULT NULL, INDEX IDX_E4D3A78D537048AF (transfer_id), INDEX IDX_E4D3A78DC6798DB (account_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE client_clients (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE client_applications (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE encrypted_credentials_local_sessions (id INT AUTO_INCREMENT NOT NULL, iv VARCHAR(4000) NOT NULL, key_exchange_parameters LONGTEXT NOT NULL COMMENT '(DC2Type:array)', public_parameters LONGTEXT NOT NULL COMMENT '(DC2Type:array)', credentials_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE encrypted_credentials_sessions (id INT AUTO_INCREMENT NOT NULL, provider_key VARCHAR(255) NOT NULL, session_id VARCHAR(255) NOT NULL, credentials_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C537048AF FOREIGN KEY (transfer_id) REFERENCES transfers (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CC17AD9A9 FOREIGN KEY (payer_id) REFERENCES accounts (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4CECCAAFA0 FOREIGN KEY (beneficiary_id) REFERENCES accounts (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C19EB6921 FOREIGN KEY (client_id) REFERENCES client_clients (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C3E030ACD FOREIGN KEY (application_id) REFERENCES client_applications (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE accounts ADD CONSTRAINT FK_CAC89EACCC6B5092 FOREIGN KEY (disposable_account_type_id) REFERENCES account_types (id)");
        $this->addSql("ALTER TABLE accounts ADD CONSTRAINT FK_CAC89EAC53772492 FOREIGN KEY (reservation_account_type_id) REFERENCES account_types (id)");
        $this->addSql("ALTER TABLE accounts ADD CONSTRAINT FK_CAC89EAC7E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE account_entries ADD CONSTRAINT FK_E4D3A78D537048AF FOREIGN KEY (transfer_id) REFERENCES transfers (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE account_entries ADD CONSTRAINT FK_E4D3A78DC6798DB FOREIGN KEY (account_type_id) REFERENCES account_types (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CC17AD9A9");
        $this->addSql("ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4CECCAAFA0");
        $this->addSql("ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C537048AF");
        $this->addSql("ALTER TABLE account_entries DROP FOREIGN KEY FK_E4D3A78D537048AF");
        $this->addSql("ALTER TABLE accounts DROP FOREIGN KEY FK_CAC89EACCC6B5092");
        $this->addSql("ALTER TABLE accounts DROP FOREIGN KEY FK_CAC89EAC53772492");
        $this->addSql("ALTER TABLE account_entries DROP FOREIGN KEY FK_E4D3A78DC6798DB");
        $this->addSql("ALTER TABLE accounts DROP FOREIGN KEY FK_CAC89EAC7E3C61F9");
        $this->addSql("ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C19EB6921");
        $this->addSql("ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C3E030ACD");
        $this->addSql("DROP TABLE transactions");
        $this->addSql("DROP TABLE accounts");
        $this->addSql("DROP TABLE transfers");
        $this->addSql("DROP TABLE account_types");
        $this->addSql("DROP TABLE account_entries");
        $this->addSql("DROP TABLE users");
        $this->addSql("DROP TABLE client_clients");
        $this->addSql("DROP TABLE client_applications");
        $this->addSql("DROP TABLE encrypted_credentials_local_sessions");
        $this->addSql("DROP TABLE encrypted_credentials_sessions");
    }
}
