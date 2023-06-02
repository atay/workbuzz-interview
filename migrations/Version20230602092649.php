<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230602092649 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE answer (id UUID NOT NULL, survey_id UUID NOT NULL, quality INT NOT NULL, comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DADD4A25B3FE509D ON answer (survey_id)');
        $this->addSql('COMMENT ON COLUMN answer.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN answer.survey_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE report (id UUID NOT NULL, survey_id UUID NOT NULL, number_of_answers INT NOT NULL, quality INT NOT NULL, comments JSON NOT NULL, generated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C42F7784B3FE509D ON report (survey_id)');
        $this->addSql('COMMENT ON COLUMN report.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN report.survey_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN report.generated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE survey (id UUID NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(32) NOT NULL, report_email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN survey.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A25B3FE509D');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784B3FE509D');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE survey');
    }
}
