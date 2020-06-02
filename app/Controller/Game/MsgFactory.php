<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class MsgFactory extends AbstractController
{
    //用户信息
    public function userInformation($p)
    {
        $data = [
            'object_id'                    => $p['id'],
            'real_id'                      => $p['id'],
            'name'                         => $p['name'],
            'guild_name'                   => $p['guild_name'],
            'guild_rank'                   => $p['guild_rank_name'],
            'name_color'                   => Int32(pack('c4', $p['name_color']['r'], $p['name_color']['g'], $p['name_color']['b'], 255)),
            'class'                        => $p['class'],
            'gender'                       => $p['gender'],
            'level'                        => $p['level'],
            'location'                     => $p['current_location'],
            'direction'                    => $p['current_direction'],
            'hair'                         => $p['hair'],
            'hp'                           => $p['hp'],
            'mp'                           => $p['mp'],
            'experience'                   => $p['experience'],
            'max_experience'               => $p['max_experience'],
            'level_effect'                 => $this->Enum::LevelEffectsNone,
            'inventory_bool'               => $p['inventory']['items'] ? true : false,
            'inventory'                    => $p['inventory']['items'],
            'equipment_bool'               => $p['equipment']['items'] ? true : false,
            'equipment'                    => $p['equipment']['items'],
            'quest_inventory_bool'         => $p['quest_inventory']['items'] ? true : false,
            'quest_inventory'              => $p['quest_inventory']['items'],
            'gold'                         => $p['gold'] ?: 0,
            'credit'                       => 100, // TODO
            'has_expanded_storage'         => false, // TODO
            'expanded_storage_expiry_time' => 0, // TODO
            'test'                         => 0, // 未知
            'client_magics'                => [], // TODO,
        ];

        if ($data['inventory_bool']) {
            $data['inventory_count'] = count($p['inventory']['items']);
        }

        if ($data['equipment_bool']) {
            $data['equipment_count'] = count($p['equipment']['items']);
        }

        if ($data['quest_inventory_bool']) {
            $data['quest_inventory_count'] = count($p['quest_inventory']['items']);
        }

        return $data;
    }

    //玩家对象
    public function objectPlayer($p)
    {
        $data = [
            'OBJECT_PLAYER', [
                'object_id'          => $p['id'],
                'name'               => $p['name'],
                'guild_name'         => $p['guild_name'],
                'guild_rank_name'    => $p['guild_rank_name'],
                'name_color'         => Int32(pack('c4', $p['name_color']['r'], $p['name_color']['g'], $p['name_color']['b'], 255)),
                'class'              => $p['class'],
                'gender'             => $p['gender'],
                'level'              => $p['level'],
                'location'           => $p['current_location'],
                'direction'          => $p['current_direction'],
                'hair'               => $p['hair'],
                'light'              => $p['light'] ?: 0,
                'weapon'             => $p['looks_weapon'],
                'weapon_effect'      => $p['looks_weapon_effect'],
                'armour'             => $p['looks_armour'],
                'poison'             => $this->Enum::PoisonTypeNone, //TODO
                'dead'               => $p['dead'],
                'hidden'             => false,
                'effect'             => $this->Enum::SpellEffectNone, //TODO
                'wing_effect'        => $p['looks_wings'],
                'extra'              => false, //TODO
                'mount_type'         => -1, //TODO
                'riding_mount'       => false, //TODO
                'fishing'            => false, //TODO
                'transform_type'     => -1, //TODO
                'element_orb_effect' => 0, //TODO
                'element_orb_lv_l'   => 0, //TODO
                'element_orb_max'    => 200, //TODO
                'buffs'              => [0, 0, 0, 0], //TODO
                'level_effects'      => $this->Enum::LevelEffectsNone, //TODO
            ],
        ];

        return $data;
    }

    public function objectTurn($object)
    {
        return [
            'object_id' => $object['id'],
            'location'  => $object['current_location'],
            'direction' => $object['current_direction'],
        ];
    }

    public function objectWalk($object)
    {
        return [
            'object_id' => $object['id'],
            'location'  => $object['current_location'],
            'direction' => $object['current_direction'],
        ];
    }

    public function objectRun($object)
    {
        return [
            'object_id' => $object['id'],
            'location'  => $object['current_location'],
            'direction' => $object['current_direction'],
        ];
    }

    public function objectMonster($m)
    {
        return [
            'OBJECT_MONSTER', [
                'object_id'           => $m['id'],
                'name'                => $m['name'],
                'name_color'          => Int32(pack('c4', $m['name_color']['r'], $m['name_color']['g'], $m['name_color']['b'], 255)),
                'location'            => $m['current_location'],
                'image'               => $m['image'],
                'direction'           => $m['direction'],
                'effect'              => $m['effect'],
                'ai'                  => $m['ai'],
                'light'               => $m['light'],
                'dead'                => $m['dead'],
                'skeleton'            => false,
                'poison'              => $m['poison'],
                'hidden'              => false,
                'shock_time'          => 0,
                'binding_shot_center' => false,
                'extra'               => false,
                'extra_byte'          => 0,
            ],
        ];
    }

    public function objectChat($p, $msg, $chatType)
    {
        return [
            'object_id' => $p['id'],
            'text'      => $p['name'] . ':' . $msg,
            'type'      => $chatType,
        ];
    }

    public function objectNPC($object)
    {
        return [
            'OBJECT_NPC', [
                'object_id'  => $object['id'],
                'name'       => $object['name'],
                'name_color' => Int32(pack('c4', $object['name_color']['r'], $object['name_color']['g'], $object['name_color']['b'], 255)),
                'image'      => $object['image'],
                'color'      => 0,
                'location'   => $object['current_location'],
                'direction'  => $object['direction'],
                'quest_ids'  => [0],
            ],
        ];
    }

    public function newUserItem($itemInfo, $id)
    {
        return [
            'id'              => $id,
            'item_id'         => $itemInfo['id'],
            'current_dura'    => !empty($itemInfo['current_dura']) ? $itemInfo['current_dura'] : $itemInfo['durability'],
            'max_dura'        => !empty($itemInfo['max_dura']) ? $itemInfo['max_dura'] : $itemInfo['durability'],
            'count'           => !empty($itemInfo['count']) ? $itemInfo['count'] : 1,
            'ac'              => $itemInfo['min_ac'],
            'mac'             => $itemInfo['max_ac'],
            'dc'              => $itemInfo['min_dc'],
            'mc'              => $itemInfo['min_mc'],
            'sc'              => $itemInfo['min_sc'],
            'accuracy'        => $itemInfo['accuracy'],
            'agility'         => $itemInfo['agility'],
            'hp'              => $itemInfo['hp'],
            'mp'              => $itemInfo['mp'],
            'attack_speed'    => $itemInfo['attack_speed'],
            'luck'            => $itemInfo['luck'],
            'soul_bound_id'   => !empty($itemInfo['soul_bound_id']) ? $itemInfo['soul_bound_id'] : 0,
            'bools'           => 0,
            'strong'          => 0,
            'magic_resist'    => 0,
            'poison_resist'   => 0,
            'health_recovery' => 0,
            'mana_recovery'   => 0,
            'poison_recovery' => 0,
            'critical_rate'   => 0,
            'critical_damage' => 0,
            'freezing'        => 0,
            'poison_attack'   => 0,
            'info'            => $itemInfo,
        ];
    }

    public function gainedItem($itemInfo)
    {
        return [
            'GAINED_ITEM',
            [
                'item' => $this->newUserItem($itemInfo, $this->Atomic->newObjectID()),
            ],
        ];
    }

    public function playerUpdate($p)
    {
        return [
            'object_id'     => $p['id'],
            'light'         => $p['light'],
            'weapon'        => $p['looks_weapon'],
            'weapon_effect' => $p['looks_weapon_effect'],
            'armour'        => $p['looks_armour'],
            'wing_effect'   => $p['looks_wings'],
        ];
    }

    public function npcResponse($page)
    {
        return ['NPC_RESPONSE', ['count' => count($page), 'page' => $page]];
    }

    public function npcGoods($goods, $rate, $type)
    {
        if($goods)
        {
            sort($goods);
        }
        
        return ['NPC_GOODS', [
            'count' => !empty($goods) ? count($goods) : 0,
            'goods' => $goods,
            'rate'  => $rate,
            'type'  => $type,
        ]];
    }

    public function userStorage($Items)
    {
        $data = [];
        if ($Items) {
            foreach ($Items as $k => $v) {
                $data[] = $this->newUserItem($v, $this->Atomic->newObjectID());
            }
        }

        $pack = [];

        if ($data) {
            $pack['isset']   = true;
            $pack['count']   = count($data);
            $pack['storage'] = $data;
        } else {
            $pack['isset'] = false;
        }

        return ['USER_STORAGE', $pack];
    }

    public function npcRepair($p, $npc, $bool)
    {
        return [
            'NPC_REPAIR', [
                'rate' => $npc['info']['rate'] / 100,
            ],
        ];
    }

    public function npcSell($value = '')
    {
        return [
            'NPC_SELL',
        ];
    }

    public function loseGold($gold)
    {
        return [
            'LOSE_GOLD',
            ['gold' => $gold],
        ];
    }

    public function dropItem($id, $count, $status = false)
    {
        return [
            'DROP_ITEM', [
                'unique_id' => $id,
                'count'     => $count,
                'success'   => $status,
            ],
        ];
    }

    public function sellItem($id, $count, $status = false)
    {
        return [
            'SELL_ITEM', [
                'unique_id' => $id,
                'count'     => $count,
                'success'   => $status,
            ],
        ];
    }

    public function objectItem($item)
    {
        return [
            'OBJECT_ITEM', [
                'object_id'  => $item['id'],
                'name'       => $item['name'],
                'name_color' => Int32(pack('c4', $item['name_color']['r'], $item['name_color']['g'], $item['name_color']['b'], 255)),
                'location_x' => $item['current_location']['x'],
                'location_y' => $item['current_location']['y'],
                'image'      => $this->Item->getImage($item),
                'grade'      => $this->Enum::ItemGradeNone,
            ],
        ];
    }

    public function objectGold($item)
    {
        return [
            'OBJECT_GOLD', [
                'object_id'  => $item['id'],
                'gold'       => $item['gold'],
                'location_x' => $item['current_location']['x'],
                'location_y' => $item['current_location']['y'],
            ],
        ];
    }

    public function gainedGold($gold)
    {
        return [
            'GAINED_GOLD', [
                'gold' => $gold,
            ],
        ];
    }

    public function objectRemove($item)
    {
        return [
            'OBJECT_REMOVE',
            [
                'object_id' => $item['id'],
            ],
        ];
    }

    public function changeAMode($mode)
    {
        return [
            'CHANGE_A_MODE',
            [
                'mode' => $mode,
            ],
        ];
    }

    public function changePMode($mode)
    {
        return [
            'CHANGE_P_MODE',
            [
                'mode' => $mode,
            ],
        ];
    }

    public function useItem($id, $status = false)
    {
        return [
            'USE_ITEM',
            [
                'unique_id' => $id,
                'success'   => $status,
            ],
        ];
    }

    public function death($p)
    {
        return [
            'DEATH', [
                'location_x' => $p['current_location']['x'],
                'location_y' => $p['current_location']['y'],
                'direction'  => $p['current_direction'],
            ],
        ];
    }

    public function objectDied($p)
    {
        return [
            'OBJECT_DIED', [
                'object_id'  => $p['id'],
                'location_x' => $p['current_location']['x'],
                'location_y' => $p['current_location']['y'],
                'direction'  => $p['current_direction'],
                'type'       => 0,
            ],
        ];
    }

    public function healthChanged($p)
    {
        return [
            'HEALTH_CHANGED', [
                'hp' => $p['hp'],
                'mp' => $p['mp'],
            ],
        ];
    }

    public function health($health)
    {
        $newHealth = [
            // 生命药水回复
            'hp_pot_value'     => null, // 回复总值
            'hp_pot_per_value' => null, // 一次回复多少
            'hp_pot_next_time' => null, // 下次生效时间
            'hp_pot_duration'  => null, // 两次生效时间间隔
            'hp_pot_tick_num'  => null, // 总共跳几次
            'hp_pot_tick_time' => null, // 当前第几跳
            // 魔法药水回复
            'mp_pot_value'     => null,
            'mp_pot_per_value' => null,
            'mp_pot_next_time' => null,
            'mp_pot_duration'  => null,
            'mp_pot_tick_num'  => null,
            'mp_pot_tick_time' => null,
            // 角色生命/魔法回复
            'heal_next_time'   => null,
            'heal_duration'    => null,
        ];

        return array_merge($newHealth, $health);
    }

    public function addBuff($buff)
    {
        return [
            'ADD_BUFF', [
                'type'      => $buff['type'],
                'caster'    => $buff['caster'],
                'object_id' => $buff['caster']['id'],
                'visible'   => $buff['visible'],
                'expire'    => 10000,
                'values'    => $buff['values'],
                'infinite'  => $buff['infinite'],
            ],
        ];
    }

    public function newMagic($clientMagic)
    {
        return [
            'NEW_MAGIC', [
                'magic' => $clientMagic,
            ],
        ];
    }

    public function getClientMagic($magic)
    {
        $info  = $magic['info'];
        $delay = $info['delay_base'] - ($magic['level'] * $info['delay_reduction']);

        $castTime = 0;

        return [
            'name'       => $info['name'],
            'spell'      => $magic['spell'],
            'base_cost'  => $info['base_cost'],
            'level_cost' => $info['level_cost'],
            'icon'       => $info['icon'],
            'level_1'    => $info['level_1'],
            'level_2'    => $info['level_2'],
            'level_3'    => $info['level_3'],
            'need_1'     => $info['need_1'],
            'need_2'     => $info['need_2'],
            'need_3'     => $info['need_3'],
            'level'      => $magic['level'],
            'key'        => !empty($magic['magic_key']) ? $magic['magic_key'] : 0,
            'experience' => !empty($magic['experience']) ? $magic['experience'] : 0,
            'delay'      => $delay,
            'range'      => $info['magic_range'],
            'cast_time'  => $info['cast_time'],
        ];
    }

    public function mapChange($m, $location, $direction)
    {
        return [
            'MAP_CHANGED', [
                'file_name'      => $m['info']['file_name'],
                'title'          => $m['info']['title'],
                'mini_map'       => $m['info']['mini_map'],
                'big_map'        => $m['info']['big_map'],
                'lights'         => $m['info']['light'],
                'location'       => $location,
                'direction'      => $direction,
                'map_dark_light' => $m['info']['map_dark_light'],
                'music'          => $m['info']['music'],
            ],
        ];
    }

    public function object($object, $type = null)
    {
        if (empty($object['id'])) {
            return false;
        }

        if ($type == null) {
            $type = !empty($object['object_type']) ? $object['object_type'] : null;
        }

        switch ($type) {
            case $this->Enum::ObjectTypePlayer:
                return $this->objectPlayer($object);
                break;

            case $this->Enum::ObjectTypeItem:
                return $this->objectItem($object);
                break;

            case $this->Enum::ObjectTypeMonster:
                return $this->objectMonster($object);
                break;

            case $this->Enum::ObjectTypeNPC:
                return $this->objectNPC($object);
                break;

            default:
                EchoLog(sprintf('未知的对象: object[%s] type:[%s]', json_encode($object), $type), 'w');
                break;
        }
    }

    public function repairItem($unique_id)
    {
        return [
            'REPAIR_ITEM', [
                'unique_id' => $unique_id,
            ],
        ];
    }

    public function itemRepaired($unique_id, $max_dura, $current_dura)
    {
        return [
            'ITEM_REPAIRED', [
                'unique_id'    => $unique_id,
                'max_dura'     => $max_dura,
                'current_dura' => $current_dura,
            ],
        ];
    }
}
