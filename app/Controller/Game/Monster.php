<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Monster extends AbstractController
{
    public function newMonster($map, $point, $monster)
    {
        $monsterObejct = [
            'id'               => $this->Atomic->newObjectID(),
            'map'              => $map,
            'name'             => $monster['name'],
            'name_color'       => $this->Enum::ColorWhite,
            'image'            => $monster['image'],
            'ai'               => $monster['ai'],
            'effect'           => $monster['effect'],
            'light'            => $monster['light'],
            'target'           => null,
            'poison'           => $this->Enum::PoisonTypeNone,
            'current_location' => $point,
            'direction'        => rand($this->Enum::MirDirectionUp, $this->Enum::MirDirectionCount),
            'dead'             => false,
            'level'            => $monster['level'],
            'pet_level'        => 0,
            'experience'       => $monster['experience'],
            'hp'               => $monster['hp'],
            'max_hp'           => $monster['hp'],
            'min_ac'           => $monster['min_ac'],
            'max_ac'           => $monster['max_ac'],
            'min_mac'          => $monster['min_mac'],
            'max_mac'          => $monster['max_mac'],
            'min_dc'           => $monster['min_dc'],
            'max_dc'           => $monster['max_dc'],
            'min_mc'           => $monster['min_mc'],
            'max_mc'           => $monster['max_mc'],
            'min_sc'           => $monster['min_sc'],
            'max_sc'           => $monster['max_sc'],
            'accuracy'         => $monster['accuracy'],
            'agility'          => $monster['agility'],
            'move_speed'       => $monster['move_speed'],
            'attack_speed'     => $monster['attack_speed'],
            'armour_rate'      => 1.0,
            'damage_rate'      => 1.0,
            'action_list'      => [],
            'w'                => time(),
            'action_time'      => time(),
            'move_time'        => time(),
            'view_range'       => $monster['view_range'],
            'behavior'         => [$monster['ai'], $monster],
            'poison_list'      => [],
            'current_poison'   => $this->Enum::PoisonTypeNone,
        ];

        return $monsterObejct;
    }

    public function broadcastInfo($monster)
    {
        $this->broadcast($monster, $this->MsgFactory->objectMonster($monster));
    }

    public function broadcast($monster, $msg)
    {
        $this->Map->broadcastP($monster['current_location'], $msg, $monster);
    }

    public function broadcastHealthChange($monster)
    {
        $this->MapObject->iMapObject_BroadcastHealthChange($monster);
    }
}
