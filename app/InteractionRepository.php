<?php
class InteractionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(array $filters = []): array
    {
        $query = 'SELECT * FROM interactions';
        $conditions = [];
        $params = [];

        if (!empty($filters['query'])) {
            $conditions[] = '(drug_a_name LIKE :query OR drug_b_name LIKE :query OR drug_a_atc LIKE :query OR drug_b_atc LIKE :query)';
            $params[':query'] = '%' . $filters['query'] . '%';
        }

        if (!empty($filters['severity'])) {
            $conditions[] = 'severity = :severity';
            $params[':severity'] = $filters['severity'];
        }

        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY drug_a_name, drug_b_name';

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM interactions WHERE id = :id');
        $statement->execute([':id' => $id]);
        $interaction = $statement->fetch();

        return $interaction ?: null;
    }

    public function upsert(array $data): int
    {
        if (!empty($data['id'])) {
            $statement = $this->pdo->prepare('UPDATE interactions SET drug_a_name = :drug_a_name, drug_a_atc = :drug_a_atc, drug_b_name = :drug_b_name, drug_b_atc = :drug_b_atc, severity = :severity, description = :description, clinical_management = :clinical_management, evidence_level = :evidence_level, source_url = :source_url WHERE id = :id');
            $statement->execute([
                ':drug_a_name' => $data['drug_a_name'],
                ':drug_a_atc' => $data['drug_a_atc'],
                ':drug_b_name' => $data['drug_b_name'],
                ':drug_b_atc' => $data['drug_b_atc'],
                ':severity' => $data['severity'],
                ':description' => $data['description'],
                ':clinical_management' => $data['clinical_management'],
                ':evidence_level' => $data['evidence_level'],
                ':source_url' => $data['source_url'],
                ':id' => $data['id'],
            ]);

            return (int) $data['id'];
        }

        $statement = $this->pdo->prepare('INSERT INTO interactions (drug_a_name, drug_a_atc, drug_b_name, drug_b_atc, severity, description, clinical_management, evidence_level, source_url) VALUES (:drug_a_name, :drug_a_atc, :drug_b_name, :drug_b_atc, :severity, :description, :clinical_management, :evidence_level, :source_url)');
        $statement->execute([
            ':drug_a_name' => $data['drug_a_name'],
            ':drug_a_atc' => $data['drug_a_atc'],
            ':drug_b_name' => $data['drug_b_name'],
            ':drug_b_atc' => $data['drug_b_atc'],
            ':severity' => $data['severity'],
            ':description' => $data['description'],
            ':clinical_management' => $data['clinical_management'],
            ':evidence_level' => $data['evidence_level'],
            ':source_url' => $data['source_url'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM interactions WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    public function import(array $records): int
    {
        $count = 0;
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare('INSERT INTO interactions (drug_a_name, drug_a_atc, drug_b_name, drug_b_atc, severity, description, clinical_management, evidence_level, source_url) VALUES (:drug_a_name, :drug_a_atc, :drug_b_name, :drug_b_atc, :severity, :description, :clinical_management, :evidence_level, :source_url) ON DUPLICATE KEY UPDATE drug_a_atc = VALUES(drug_a_atc), drug_b_atc = VALUES(drug_b_atc), severity = VALUES(severity), description = VALUES(description), clinical_management = VALUES(clinical_management), evidence_level = VALUES(evidence_level), source_url = VALUES(source_url)');

            foreach ($records as $record) {
                $statement->execute([
                    ':drug_a_name' => $record['drug_a_name'],
                    ':drug_a_atc' => $record['drug_a_atc'],
                    ':drug_b_name' => $record['drug_b_name'],
                    ':drug_b_atc' => $record['drug_b_atc'],
                    ':severity' => $record['severity'],
                    ':description' => $record['description'],
                    ':clinical_management' => $record['clinical_management'],
                    ':evidence_level' => $record['evidence_level'],
                    ':source_url' => $record['source_url'],
                ]);
                $count++;
            }
            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $count;
    }
}
