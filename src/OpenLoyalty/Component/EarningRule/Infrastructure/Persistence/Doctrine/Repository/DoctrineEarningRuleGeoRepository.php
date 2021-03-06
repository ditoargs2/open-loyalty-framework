<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\EarningRule\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use OpenLoyalty\Component\EarningRule\Domain\EarningRuleGeoRepository;

/**
 * Class DoctrineEarningRuleGeoRepository.
 */
class DoctrineEarningRuleGeoRepository extends EntityRepository implements EarningRuleGeoRepository
{
    use DoctrineEarningRuleRepositoryTrait;

    /**
     * {@inheritdoc}
     */
    public function findGeoRules(
        array $segmentIds = [],
        $levelId = null,
        \DateTime $date = null,
        $posId = null
    ): array {
        $qb = $this->getEarningRulesForLevelAndSegmentQueryBuilder($segmentIds, $levelId, $date, $posId);

        return $qb->getQuery()->getResult();
    }
}
