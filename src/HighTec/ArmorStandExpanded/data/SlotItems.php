<?php

declare(strict_types=1);

namespace HighTec\ArmorStandExpanded\data;


use pocketmine\item\Item;

/**
 * Class SlotItems
 * @package HighTec\ArmorStand\data
 */
class SlotItems
{
    public const SLOT_HELMET_ITEMS = [
        Item::CHAINMAIL_HELMET,
        Item::DIAMOND_HELMET,
        Item::GOLD_HELMET,
        Item::IRON_HELMET,
        Item::LEATHER_HELMET,
        Item::TURTLE_HELMET,
        Item::SKULL,
        Item::JACK_O_LANTERN
    ];

    public const SLOT_CHESTPLATE_ITEMS = [
        Item::CHAINMAIL_CHESTPLATE,
        Item::DIAMOND_CHESTPLATE,
        Item::GOLD_CHESTPLATE,
        Item::IRON_CHESTPLATE,
        Item::LEATHER_CHESTPLATE
    ];

    public const SLOT_LEGGINS_ITEMS = [
        Item::CHAINMAIL_LEGGINGS,
        Item::DIAMOND_LEGGINGS,
        Item::GOLD_LEGGINGS,
        Item::IRON_LEGGINGS,
        Item::LEATHER_LEGGINGS
    ];

    public const SLOT_BOOTS_ITEMS = [
        Item::CHAINMAIL_BOOTS,
        Item::DIAMOND_BOOTS,
        Item::GOLD_BOOTS,
        Item::IRON_BOOTS,
        Item::LEATHER_BOOTS
    ];

    public const OFFHAND_ITEMS = [
        Item::ARROW,
        Item::MAP,
        Item::FILLED_MAP,
        Item::FIREWORKS,
        Item::SHIELD,
        Item::TOTEM
    ];

}