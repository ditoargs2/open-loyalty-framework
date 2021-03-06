<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Transaction\Domain;

use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;

/**
 * Class TransactionRepository.
 */
class TransactionRepository extends EventSourcingRepository
{
    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus,
        array $eventStreamDecorators = array()
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            '\OpenLoyalty\Component\Transaction\Domain\Transaction',
            new PublicConstructorAggregateFactory(),
            $eventStreamDecorators
        );
    }
}
