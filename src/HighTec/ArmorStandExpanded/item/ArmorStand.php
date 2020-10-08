<?php

declare(strict_types=1);

namespace HighTec\ArmorStandExpanded\item;

use HighTec\ArmorStandExpanded\entity\object\ArmorStand as EntityArmorStand;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

/**
 * Class ArmorStand
 * @package HighTec\ArmorStand\item
 */
class ArmorStand extends Item
{

    /**
     * ArmorStand constructor.
     * @param int $meta
     */
    public function __construct(int $meta = 0)
    {
        parent::__construct(self::ARMOR_STAND, $meta, "Armor Stand");
    }

    /**
     * @param Player $player
     * @param Block $blockReplace
     * @param Block $blockClicked
     * @param int $face
     * @param Vector3 $clickVector
     * @return bool
     */
    public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool
    {
        $entity = Entity::createEntity("ArmorStand", $player->level, Entity::createBaseNBT($blockReplace->asVector3()->add(0.5, 0, 0.5), null, $this->getDirection($player->getYaw())));


        if ($entity instanceof EntityArmorStand) {
            if ($player->isSurvival()) {
                $this->pop();
            }
            $entity->spawnToAll();
            $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_PLACE);
            return true;
        }

        return false;
    }

    /**
     * @param float $yaw
     * @return float|int
     */
    public function getDirection(float $yaw)
    {
        return (round($yaw / 22.5 / 2) * 45) - 180;
    }

}