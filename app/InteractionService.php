<?php
class InteractionService
{
    public function __construct(private InteractionRepository $repository)
    {
    }

    public function list(array $filters = []): array
    {
        return $this->repository->all($filters);
    }

    public function exportCsv(array $filters = []): string
    {
        $interactions = $this->list($filters);
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['drug_a_name', 'drug_a_atc', 'drug_b_name', 'drug_b_atc', 'severity', 'description', 'clinical_management', 'evidence_level', 'source_url']);
        foreach ($interactions as $interaction) {
            fputcsv($handle, [
                $interaction['drug_a_name'],
                $interaction['drug_a_atc'],
                $interaction['drug_b_name'],
                $interaction['drug_b_atc'],
                $interaction['severity'],
                $interaction['description'],
                $interaction['clinical_management'],
                $interaction['evidence_level'],
                $interaction['source_url'],
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function importCsv(string $path): int
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new RuntimeException('Unable to open import file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new RuntimeException('Import file is empty.');
        }

        $records = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $record = array_combine($header, $row);
            if ($record === false) {
                continue;
            }
            $records[] = [
                'drug_a_name' => $record['drug_a_name'] ?? '',
                'drug_a_atc' => $record['drug_a_atc'] ?? '',
                'drug_b_name' => $record['drug_b_name'] ?? '',
                'drug_b_atc' => $record['drug_b_atc'] ?? '',
                'severity' => $record['severity'] ?? 'moderate',
                'description' => $record['description'] ?? '',
                'clinical_management' => $record['clinical_management'] ?? '',
                'evidence_level' => $record['evidence_level'] ?? '',
                'source_url' => $record['source_url'] ?? '',
            ];
        }
        fclose($handle);

        return $this->repository->import($records);
    }

    public function toFhirBundle(array $filters = []): array
    {
        $interactions = $this->list($filters);
        $entries = [];

        foreach ($interactions as $interaction) {
            $entries[] = [
                'resource' => $this->toMedicationKnowledge($interaction),
                'fullUrl' => sprintf('urn:uuid:%s', $interaction['id'] ?? uniqid()),
            ];
        }

        return [
            'resourceType' => 'Bundle',
            'type' => 'collection',
            'timestamp' => gmdate('c'),
            'entry' => $entries,
        ];
    }

    public function toMedicationKnowledge(array $interaction): array
    {
        return [
            'resourceType' => 'MedicationKnowledge',
            'id' => (string) ($interaction['id'] ?? uniqid()),
            'status' => 'active',
            'code' => [
                'coding' => [
                    [
                        'system' => 'http://www.whocc.no/atc',
                        'code' => $interaction['drug_a_atc'],
                        'display' => $interaction['drug_a_name'],
                    ],
                    [
                        'system' => 'http://www.whocc.no/atc',
                        'code' => $interaction['drug_b_atc'],
                        'display' => $interaction['drug_b_name'],
                    ],
                ],
                'text' => $interaction['drug_a_name'] . ' + ' . $interaction['drug_b_name'],
            ],
            'contraindication' => [[
                'reference' => 'DetectedIssue/' . ($interaction['id'] ?? uniqid()),
                'display' => $interaction['severity'] . ' interaction',
            ]],
            'monitoringProgram' => [[
                'name' => 'Clinical management',
                'type' => [
                    'text' => $interaction['clinical_management'],
                ],
            ]],
            'clinicalUseIssue' => [[
                'classification' => [[
                    'text' => $interaction['severity'],
                ]],
                'applicability' => [
                    'text' => $interaction['description'],
                ],
            ]],
            'relatedMedicationKnowledge' => [[
                'type' => [
                    'coding' => [[
                        'system' => 'http://terminology.hl7.org/CodeSystem/medicationknowledge-characteristic',
                        'code' => 'interaction',
                        'display' => 'Interaction',
                    ]],
                ],
                'reference' => [[
                    'reference' => $interaction['source_url'],
                    'display' => $interaction['evidence_level'],
                ]],
            ]],
        ];
    }
}
