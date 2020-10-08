<?php

declare(strict_types=1);

namespace HighTec\ArmorStandExpanded\entity\object;

use HighTec\ArmorStandExpanded\data\SlotItems;
use HighTec\ArmorStandExpanded\inventory\ArmorStandEquipment;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

/**
 * Class ArmorStand
 * @package HighTec\ArmorStand\entity\object
 */
class ArmorStand extends Living
{

    public const NETWORK_ID = EntityIds::ARMOR_STAND;

    public const TAG_MAINHAND = "ArmorStandExpanded-Mainhand";
    public const TAG_OFFHAND = "ArmorStandExpanded-Offhand";
    public const TAG_POSE_INDEX = "ArmorStandExpanded-PoseIndex";
    public const TAG_ARMOR = "ArmorStandExpanded-Armor";

    public const MAINHAND = 0;
    public const OFFHAND = 1;

    public const SLOT_HELMET = 0;
    public const SLOT_CHESTPLATE = 1;
    public const SLOT_LEGGINGS = 2;
    public const SLOT_BOOTS = 3;

    public const EVENT_PARTICLE_ARMOR_STAND_DESTROY = 2017;

    /** @var ArmorStandEquipment */
    protected $equipment;

    /**
     * @var float
     */
    public $width = 0.5;
    /**
     * @var float
     */
    public $height = 1.975;

    /**
     * @var float
     */
    protected $gravity = 0.04;

    /**
     * @var int
     */
    protected $vibrateTimer = 0;

    protected function initEntity(): void
    {
        $this->setMaxHealth(6);
        $this->setImmobile(true);

        parent::initEntity();

        $this->equipment = new ArmorStandEquipment($this);

        if ($this->namedtag->hasTag(self::TAG_ARMOR, ListTag::class)) {
            $armors = $this->namedtag->getListTag(self::TAG_ARMOR);

            /** @var CompoundTag $armor */
            foreach ($armors as $armor) {
                $slot = $armor->getByte("Slot", 0);

                $this->armorInventory->setItem($slot, Item::nbtDeserialize($armor));
            }
        }

        if ($this->namedtag->hasTag(self::TAG_MAINHAND, CompoundTag::class)) {
            $this->equipment->setItemInHand(Item::nbtDeserialize($this->namedtag->getCompoundTag(self::TAG_MAINHAND)));
        }
        if ($this->namedtag->hasTag(self::TAG_OFFHAND, CompoundTag::class)) {
            $this->equipment->setOffhandItem(Item::nbtDeserialize($this->namedtag->getCompoundTag(self::TAG_OFFHAND)));
        }

        $this->setPose(min($this->namedtag->getInt(self::TAG_POSE_INDEX, 0), 12));
        $this->propertyManager->setString(self::DATA_INTERACTIVE_TAG, "armorstand.change.pose");
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "ArmorStand";
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if ($this->getGenericFlag(self::DATA_FLAG_VIBRATING) and $this->vibrateTimer-- <= 0) {
            $this->setGenericFlag(self::DATA_FLAG_VIBRATING, false);
        }

        return $hasUpdate;
    }


    /**
     * @param int $pose
     */
    public function setPose(int $pose): void
    {
        $this->propertyManager->setInt(self::DATA_ARMOR_STAND_POSE_INDEX, $pose);
    }

    /**
     * @return int
     */
    public function getPose(): int
    {
        return $this->propertyManager->getInt(self::DATA_ARMOR_STAND_POSE_INDEX);
    }

    /**
     * @param Item $item
     * @return int
     */
    public function getSlot(Item $item)
    {
        if (in_array($item->getId(), SlotItems::SLOT_HELMET_ITEMS)) return self::SLOT_HELMET;
        if (in_array($item->getId(), SlotItems::SLOT_CHESTPLATE_ITEMS)) return self::SLOT_CHESTPLATE;
        if (in_array($item->getId(), SlotItems::SLOT_LEGGINS_ITEMS)) return self::SLOT_LEGGINGS;
        if (in_array($item->getId(), SlotItems::SLOT_BOOTS_ITEMS)) return self::SLOT_BOOTS;
        if (in_array($item->getId(), SlotItems::OFFHAND_ITEMS)) return self::OFFHAND;
        return self::MAINHAND;
    }

    /**
     * @return ArmorStandEquipment
     */
    public function getEquipment(): ArmorStandEquipment
    {
        return $this->equipment;
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Vector3 $clickPos
     * @return bool
     */
    public function onFirstInteract(Player $player, Item $item, Vector3 $clickPos): bool
    {
        if ($player->isSneaking()) {
            $this->setPose(($this->getPose() + 1) % 13);
            return true;
        }

        if ($this->isValid() and !$player->isSpectator()) {
            $targetSlot = self::MAINHAND;
            if ($this->equipment->getItem(self::MAINHAND)->isNull()) $targetSlot = self::OFFHAND;
            $isArmorSlot = false;
            if ($item->isNull()) {
                $clickOffset = $clickPos->y - $this->y;
                if ($clickOffset >= 0.1 and $clickOffset < 0.55 and !$this->armorInventory->getItem(ArmorInventory::SLOT_FEET)->isNull()) {
                    $targetSlot = self::SLOT_BOOTS;
                    $isArmorSlot = true;
                } elseif ($clickOffset >= 0.9 and $clickOffset < 1.6 and !$this->armorInventory->getItem(ArmorInventory::SLOT_CHEST)->isNull()) {
                    $targetSlot = self::SLOT_CHESTPLATE;
                    $isArmorSlot = true;
                } elseif ($clickOffset >= 0.4 and $clickOffset < 1.2 and !$this->armorInventory->getItem(ArmorInventory::SLOT_LEGS)->isNull()) {
                    $targetSlot = self::SLOT_LEGGINGS;
                    $isArmorSlot = true;
                } elseif ($clickOffset >= 1.6 and !$this->armorInventory->getItem(ArmorInventory::SLOT_HEAD)->isNull()) {
                    $targetSlot = self::SLOT_HELMET;
                    $isArmorSlot = true;
                }
            } else {
                if ($item instanceof Armor) $isArmorSlot = true;
                $targetSlot = $this->getSlot($item);
            }

            $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_MOB_ARMOR_STAND_PLACE);

            $this->tryChangeEquipment($player, $item, $targetSlot, $isArmorSlot);

            return true;
        }

        return false;
    }

    /**
     * @param Player $player
     * @param Item $targetItem
     * @param int $slot
     * @param bool $isArmorSlot
     */
    protected function tryChangeEquipment(Player $player, Item $targetItem, int $slot, bool $isArmorSlot = false): void
    {
        $sourceItem = $isArmorSlot ? $this->armorInventory->getItem($slot) : $this->equipment->getItem($slot);

        if ($isArmorSlot) {
            $this->armorInventory->setItem($slot, (clone $targetItem)->setCount(1));
        } else {
            $this->equipment->setItem($slot, (clone $targetItem)->setCount(1));
        }
        if (!$targetItem->isNull() and $player->isSurvival()) {
            if (!$targetItem->equals($sourceItem)) {
                $contents = $player->getInventory()->getContents();
                $contents[$player->getInventory()->getHeldItemIndex()]->setCount($contents[$player->getInventory()->getHeldItemIndex()]->getCount()-1);
                $player->getInventory()->setContents($contents);
            }
        }
        if (!$targetItem->isNull() and $targetItem->equals($sourceItem)) {
            $targetItem->setCount($targetItem->getCount() + $sourceItem->getCount());
        } else {
            $player->getInventory()->addItem($sourceItem);
        }

        $this->equipment->sendContents($player);
        $this->armorInventory->sendContents($player);
    }

    /**
     * @param float $fallDistance
     */
    public function fall(float $fallDistance): void
    {
        parent::fall($fallDistance);

        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_FALL, $this->getId());
    }

    public function saveNBT(): void
    {
        parent::saveNBT();

        if ($this->equipment instanceof ArmorStandEquipment) {
            $this->namedtag->setTag($this->equipment->getItemInHand()->nbtSerialize(-1, self::TAG_MAINHAND), true);
            $this->namedtag->setTag($this->equipment->getOffhandItem()->nbtSerialize(-1, self::TAG_OFFHAND), true);
        }

        if ($this->armorInventory !== null) {
            $armorTag = new ListTag(self::TAG_ARMOR, [], NBT::TAG_Compound);

            for ($i = 0; $i < 4; $i++) {
                $armorTag->push($this->armorInventory->getItem($i)->nbtSerialize($i));
            }

            $this->namedtag->setTag($armorTag, true);
        }

        $this->namedtag->setInt(self::TAG_POSE_INDEX, $this->getPose(), true);
    }

    /**
     * @return array
     */
    public function getDrops(): array
    {
        return array_merge($this->equipment->getContents(), $this->armorInventory->getContents(), [ItemFactory::get(Item::ARMOR_STAND)]);
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void
    {
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                if ($damager->isCreative()) {
                    $this->kill();
                }
            }
        }
        if ($source->getCause() === EntityDamageEvent::CAUSE_CONTACT) { // cactus
            $source->setCancelled(true);
        }

        Entity::attack($source);

        if (!$source->isCancelled()) {
            $this->setGenericFlag(self::DATA_FLAG_VIBRATING, true);
            $this->vibrateTimer += 30;
        }
    }

    protected function doHitAnimation(): void
    {
        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_HIT);
    }

    public function startDeathAnimation(): void
    {
        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_BREAK);
        $this->level->broadcastLevelEvent($this, self::EVENT_PARTICLE_ARMOR_STAND_DESTROY);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    protected function onDeathUpdate(int $tickDiff): bool
    {
        return true;
    }

    /**
     * @param Player $player
     */
    protected function sendSpawnPacket(Player $player): void
    {
        parent::sendSpawnPacket($player);

        $this->equipment->sendContents($player);
    }


    /**
     * @return bool
     */
    public function canBePushed(): bool
    {
        return false;
    }

}
