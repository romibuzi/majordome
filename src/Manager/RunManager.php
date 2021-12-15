<?php

namespace Majordome\Manager;

use Doctrine\DBAL\Connection;
use Majordome\Resource\Resource;
use Majordome\Resource\ResourceUrlGeneratorInterface;

class RunManager
{
    private $connection;
    private $resourceUrlGenerator;

    public function __construct(
        Connection $connection,
        ResourceUrlGeneratorInterface $resourceUrlGenerator = null
    ) {
        $this->connection = $connection;
        $this->resourceUrlGenerator = $resourceUrlGenerator;
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getLastRuns($limit)
    {
        $query = sprintf(
            "SELECT r.*, COUNT(v.id) as violationsCount
             FROM runs r
             LEFT JOIN violations v ON v.run_id = r.id
             GROUP BY run_id
             ORDER BY createdAt DESC LIMIT %s
            ",
            $limit
        );

        if ($limit === 1) {
            return $this->connection->fetchAssociative($query);
        }

        return $this->connection->fetchAllAssociative($query);
    }

    /**
     * @param int $runId
     *
     * @return array
     */
    public function getRunDetails($runId)
    {
        $run = $this->connection->fetchAssociative("SELECT * FROM runs WHERE id = ?", [$runId], [\PDO::PARAM_INT]);

        if (!$run) {
            return [false, false];
        }

        $violations = $this->connection->fetchAllAssociative(
            "SELECT
               v.resource_id,
               v.resource_type,
               r.id AS rule_id,
               r.name AS rule_name,
               r.description AS rule_description
             FROM violations v
             JOIN rules r ON v.rule_id = r.id
             WHERE v.run_id = ?",
            [$runId],
            [\PDO::PARAM_INT]
        );

        $violationsByRule = [];

        foreach ($violations as $violation) {
            $id   = $violation['rule_id'];
            $name = $violation['rule_name'];
            $desc = $violation['rule_description'];

            if (!array_key_exists($id, $violationsByRule)) {
                $violationsByRule[$id] = [
                    'name'        => $name,
                    'description' => $desc,
                    'violations'  => []
                ];
            }

            $resource = new Resource($violation['resource_id'], $violation['resource_type']);

            if (null !== $this->resourceUrlGenerator) {
                $resourceUrl = $this->resourceUrlGenerator->generateUrl($resource);
            } else {
                $resourceUrl = '';
            }

            $violationsByRule[$id]['violations'][] = [
                'id'   => $resource->getId(),
                'url'  => $resourceUrl,
            ];
        }

        return [$run, $violationsByRule];
    }
}
