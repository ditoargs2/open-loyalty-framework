<?php
/**
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Component\Account\Tests\Domain\Command;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use OpenLoyalty\Component\Account\Domain\AccountId;
use OpenLoyalty\Component\Account\Domain\AccountRepository;
use OpenLoyalty\Component\Account\Domain\Command\AccountCommandHandler;
use OpenLoyalty\Component\Account\Domain\Command\TransferPoints;
use OpenLoyalty\Component\Account\Domain\Event\AccountWasCreated;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereAdded;
use OpenLoyalty\Component\Account\Domain\Event\PointsWereTransferred;
use OpenLoyalty\Component\Account\Domain\Model\AddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PAddPointsTransfer;
use OpenLoyalty\Component\Account\Domain\Model\P2PSpendPointsTransfer;
use OpenLoyalty\Component\Account\Domain\PointsTransferId;
use OpenLoyalty\Component\Account\Domain\CustomerId;

/**
 * Class TransferPointsTest.
 */
class TransferPointsTest extends AccountCommandHandlerTest
{
    /**
     * @param EventStore $eventStore
     * @param EventBus   $eventBus
     *
     * @return CommandHandler
     *
     * @throws \Assert\AssertionFailedException
     */
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler
    {
        $receiverId = new AccountId('00000000-0000-0000-0000-000000000001');
        $customerReceiverId = new CustomerId('00000000-1111-0000-0000-000000000001');

        $messages = [
            DomainMessage::recordNow($receiverId, -1, new Metadata([]), new AccountWasCreated($receiverId, $customerReceiverId)),
        ];

        $eventStore->append($receiverId, new DomainEventStream($messages));

        return new AccountCommandHandler(
            new AccountRepository($eventStore, $eventBus),
            $this->getUuidGenerator()
        );
    }

    public function setUp()
    {
        parent::setUp();
        self::$uuidCount = 0;
    }

    /**
     * @test
     */
    public function it_transfer_points(): void
    {
        $senderId = new AccountId('00000000-0000-0000-0000-000000000000');
        $customerSenderId = new CustomerId('00000000-1111-0000-0000-000000000000');
        $receiverId = new AccountId('00000000-0000-0000-0000-000000000001');
        $pointsTransferId = new PointsTransferId('00000000-1111-0000-0000-000000000111');
        $pointsTransfer2Id = new PointsTransferId('00000000-1111-0000-0000-000000000112');
        $pointsTransfer1 = new AddPointsTransfer($pointsTransferId, 200);

        $transfer = new P2PSpendPointsTransfer($receiverId, $pointsTransfer2Id, 100);

        $date = new \DateTime();
        $this->scenario
            ->withAggregateId($senderId)
            ->given([
                new AccountWasCreated($senderId, $customerSenderId),
                new PointsWereAdded($senderId, $pointsTransfer1),
            ])
            ->when(new TransferPoints($senderId, $transfer, $date))
            ->then(array(
                new PointsWereAdded($receiverId, P2PAddPointsTransfer::createFromAddPointsTransfer(
                    new PointsTransferId('00000000-0000-0000-0000-000000000000'),
                    $senderId,
                    100,
                    $pointsTransfer1,
                    $date
                )),
                new PointsWereTransferred($senderId, $transfer),
            ));
    }

    /**
     * @test
     */
    public function it_transfer_points_multiple_transfers(): void
    {
        $senderId = new AccountId('00000000-0000-0000-0000-000000000000');
        $customerSenderId = new CustomerId('00000000-1111-0000-0000-000000000000');
        $receiverId = new AccountId('00000000-0000-0000-0000-000000000001');
        $pointsTransferId = new PointsTransferId('00000000-1111-0000-0000-000000000113');
        $pointsTransfer2Id = new PointsTransferId('00000000-1111-0000-0000-000000000112');
        $pointsTransfer3Id = new PointsTransferId('00000000-1111-0000-0000-000000000114');
        $transfer = new P2PSpendPointsTransfer($receiverId, $pointsTransfer3Id, 100);
        $pointsTransfer1 = new AddPointsTransfer($pointsTransferId, 20);
        $pointsTransfer2 = new AddPointsTransfer($pointsTransfer2Id, 200);
        $date = new \DateTime();
        $this->scenario
            ->withAggregateId($senderId)
            ->given([
                new AccountWasCreated($senderId, $customerSenderId),
                new PointsWereAdded($senderId, $pointsTransfer1),
                new PointsWereAdded($senderId, $pointsTransfer2),
            ])
            ->when(new TransferPoints($senderId, $transfer, $date))
            ->then(array(
                new PointsWereAdded($receiverId, P2PAddPointsTransfer::createFromAddPointsTransfer(
                    new PointsTransferId('00000000-0000-0000-0000-000000000000'),
                    $senderId,
                    20,
                    $pointsTransfer1,
                    $date
                )),
                new PointsWereAdded($receiverId, P2PAddPointsTransfer::createFromAddPointsTransfer(
                    new PointsTransferId('00000000-0000-0000-0000-000000000001'),
                    $senderId,
                    80,
                    $pointsTransfer2,
                    $date
                )),
                new PointsWereTransferred($senderId, $transfer),
            ));
    }
}
