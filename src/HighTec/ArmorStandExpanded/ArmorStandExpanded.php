<?php

declare(strict_types=1);

namespace HighTec\ArmorStandExpanded;

use HighTec\ArmorStandExpanded\entity\object\ArmorStand as EntityArmorStand;
use HighTec\ArmorStandExpanded\item\ArmorStand;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;


/**
 * Class ArmorStandExpanded
 * @package HighTec\ArmorStandExpanded
 */
class ArmorStandExpanded extends PluginBase
{
    /**
     * @var mixed
     */
    private $myPLot = null;

    public function onEnable()
    {
        Entity::registerEntity(EntityArmorStand::class, true, ['ArmorStand', 'minecraft:armor_stand']);
        ItemFactory::registerItem(new ArmorStand());
        Item::initCreativeItems();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        if (!is_null($this->getServer()->getPluginManager()->getPlugin("MyPlot"))) {
            $this->myPLot = $this->getServer()->getPluginManager()->getPlugin("MyPlot");
        }
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function canDoThis(Player $player): bool
    {
        /* MyPlot Stuff */

        if (!is_null($this->myPLot)) {
            $levelName = $player->getLevel()->getFolderName();
            if (!$this->myPLot->isLevelLoaded($levelName)) {
                return true;
            }
            $plot = $this->myPLot->getPlotByPosition($player);
            if ($plot !== null) {
                if ($plot->owner == $player->getName() || $plot->isHelper($player->getName()) || $player->hasPermission('myplot.admin.build.plot')) {
                    return true;
                }
            } elseif ($player->hasPermission('myplot.admin.build.road')) {
                return true;
            }
            return false;
        }
        return true;
    }

}