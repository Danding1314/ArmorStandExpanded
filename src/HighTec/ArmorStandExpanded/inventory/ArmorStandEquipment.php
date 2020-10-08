<?php

declare(strict_types=1);

namespace HighTec\ArmorStandExpanded\inventory;

use pocketmine\entity\Living;
use pocketmine\inventory\BaseInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;

/**
 * Class ArmorStandEquipment
 * @package HighTec\ArmorStand\inventory
 */
class ArmorStandEquipment extends BaseInventory
{

    /**
     *
     */
    public const MAINHAND = 0;
    /**
     *
     */
    public const OFFHAND = 1;

    /**
     * @var Living
     */
    protected $holder;

    /**
     * ArmorStandEquipment constructor.
     * @param Living $entity
     */
    public function __construct(Living $entity)
    {
        $this->holder = $entity;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "Armor Stand Equipment";
    }

    /**
     * @return int
     */
    public function getDefaultSize(): int
    {
        return 2;
    }

    /**
     * @return Living
     */
    public function getHolder(): Living
    {
        return $this->holder;
    }

    /**
     * @return array
     */
    public function getViewers(): array
    {
        return $this->holder->getViewers();
    }

    /**
     * @return Item
     */
    public function getItemInHand(): Item
    {
        return $this->getItem(self::MAINHAND);
    }

    /**
     * @return Item
     */
    public function getOffhandItem(): Item
    {
        return $this->getItem(self::OFFHAND);
    }

    /**
     * @param Item $item
     * @param bool $send
     * @return bool
     */
    public function setItemInHand(Item $item, bool $send = true): bool
    {
        return $this->setItem(self::MAINHAND, $item, $send);
    }

    /**
     * @param Item $item
     * @param bool $send
     * @return bool
     */
    public function setOffhandItem(Item $item, bool $send = true): bool
    {
        return $this->setItem(self::OFFHAND, $item, $send);
    }

    /**
     * @param Player|Player[] $target
     */
    public function sendContents($target): void
    {
        $this->sendSlot(self::MAINHAND, $target);
        $this->sendSlot(self::OFFHAND, $target);
    }

    /**
     * @param int $index
     * @param Player|Player[] $target
     */
    public function sendSlot(int $index, $target): void
    {
        if ($target instanceof Player) {
            $target = [$target];
        }
        $pk = new MobEquipmentPacket();
        $pk->entityRuntimeId = $this->holder->getId();
        $pk->inventorySlot = $pk->hotbarSlot = $index;
        $pk->item = $this->getItem($index);
        if ($target instanceof Player) $target = [$target];
        foreach ($target as $player) {
            $player->sendDataPacket($pk);
        }
    }
}