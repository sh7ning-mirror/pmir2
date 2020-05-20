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
            'ObjectID'                  => $p['ID'],
            'RealID'                    => $p['ID'],
            'Name'                      => $p['Name'],
            'GuildName'                 => $p['GuildName'],
            'GuildRank'                 => $p['GuildRankName'],
            'NameColor'                 => Int32(pack('c4', $p['NameColor']['R'], $p['NameColor']['G'], $p['NameColor']['B'], 255)),
            'Class'                     => $p['Class'],
            'Gender'                    => $p['Gender'],
            'Level'                     => $p['Level'],
            'Location'                  => $p['CurrentLocation'],
            'Direction'                 => $p['CurrentDirection'],
            'Hair'                      => $p['Hair'],
            'HP'                        => $p['HP'],
            'MP'                        => $p['MP'],
            'Experience'                => $p['Experience'],
            'MaxExperience'             => $p['MaxExperience'],
            'LevelEffect'               => $this->Enum::LevelEffectsNone,
            'InventoryBool'             => $p['Inventory']['Items'] ? true : false,
            'Inventory'                 => $p['Inventory']['Items'],
            'EquipmentBool'             => $p['Equipment']['Items'] ? true : false,
            'Equipment'                 => $p['Equipment']['Items'],
            'QuestInventoryBool'        => $p['QuestInventory']['Items'] ? true : false,
            'QuestInventory'            => $p['QuestInventory']['Items'],
            'Gold'                      => $p['Gold'] ?: 0,
            'Credit'                    => 100, // TODO
            'HasExpandedStorage'        => false, // TODO
            'ExpandedStorageExpiryTime' => 0, // TODO
            'test'                      => 0, // 未知
            'ClientMagics'              => [], // TODO,
        ];

        if ($data['InventoryBool']) {
            $data['InventoryCount'] = count($p['Inventory']['Items']);
        }

        if ($data['EquipmentBool']) {
            $data['EquipmentCount'] = count($p['Equipment']['Items']);
        }

        if ($data['QuestInventoryBool']) {
            $data['QuestInventoryCount'] = count($p['QuestInventory']['Items']);
        }

        return $data;
    }

    //玩家对象
    public function objectPlayer($p)
    {
        $data = [
            'ObjectID'         => $p['ID'],
            'Name'             => $p['Name'],
            'GuildName'        => $p['GuildName'],
            'GuildRankName'    => $p['GuildRankName'],
            'NameColor'        => Int32(pack('c4', $p['NameColor']['R'], $p['NameColor']['G'], $p['NameColor']['B'], 255)),
            'Class'            => $p['Class'],
            'Gender'           => $p['Gender'],
            'Level'            => $p['Level'],
            'Location'         => $p['CurrentLocation'],
            'Direction'        => $p['CurrentDirection'],
            'Hair'             => $p['Hair'],
            'Light'            => $p['Light'] ?: 0,
            'Weapon'           => $p['LooksWeapon'],
            'WeaponEffect'     => $p['LooksWeaponEffect'],
            'Armour'           => $p['LooksArmour'],
            'Poison'           => $this->Enum::PoisonTypeNone, //TODO
            'Dead'             => $p['Dead'],
            'Hidden'           => false,
            'Effect'           => $this->Enum::SpellEffectNone, //TODO
            'WingEffect'       => $p['LooksWings'],
            'Extra'            => false, //TODO
            'MountType'        => -1, //TODO
            'RidingMount'      => false, //TODO
            'Fishing'          => false, //TODO
            'TransformType'    => -1, //TODO
            'ElementOrbEffect' => 0, //TODO
            'ElementOrbLvl'    => 0, //TODO
            'ElementOrbMax'    => 200, //TODO
            'Buffs'            => [0, 0, 0, 0], //TODO
            'LevelEffects'     => $this->Enum::LevelEffectsNone, //TODO
        ];

        return $data;
    }

    public function objectTurn($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['CurrentDirection'],
        ];
    }

    public function objectWalk($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['CurrentDirection'],
        ];
    }

    public function objectRun($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['CurrentDirection'],
        ];
    }

    public function objectMonster($m)
    {
        return [
            'ObjectID'          => $m['ID'],
            'Name'              => $m['Name'],
            'NameColor'         => Int32(pack('c4', $m['NameColor']['R'], $m['NameColor']['G'], $m['NameColor']['B'], 255)),
            'Location'          => $m['CurrentLocation'],
            'Image'             => $m['Image'],
            'Direction'         => $m['CurrentDirection'],
            'Effect'            => $m['Effect'],
            'AI'                => $m['AI'],
            'Light'             => $m['Light'],
            'Dead'              => $m['Dead'],
            'Skeleton'          => false,
            'Poison'            => $m['Poison'],
            'Hidden'            => false,
            'ShockTime'         => 0,
            'BindingShotCenter' => false,
            'Extra'             => false,
            'ExtraByte'         => 0,
        ];
    }

    public function objectChat($p, $msg, $chatType)
    {
        return [
            'ObjectID' => $p['ID'],
            'Text'     => $p['Name'] . ':' . $msg,
            'Type'     => $chatType,
        ];
    }

    public function objectNPC($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Name'      => $object['Name'],
            'NameColor' => Int32(pack('c4', $object['NameColor']['R'], $object['NameColor']['G'], $object['NameColor']['B'], 255)),
            'Image'     => $object['Image'],
            'Color'     => 0,
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['Direction'],
            'QuestIDs'  => [0],
        ];
    }

    public function newUserItem($itemInfo, $ID)
    {
        return [
            'id'              => $ID,
            'item_id'         => $itemInfo['id'],
            'current_dura'    => 100,
            'max_dura'        => 100,
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
            'Info'            => $itemInfo,
        ];
    }

    public function gainedItem($itemInfo)
    {
        return [
            'GAINED_ITEM',
            [
                'Item' => $this->newUserItem($itemInfo, $this->Atomic->newObjectID()),
            ],
        ];
    }

    public function playerUpdate($p)
    {
        return [
            'ObjectID'     => $p['ID'],
            'Light'        => $p['Light'],
            'Weapon'       => $p['LooksWeapon'],
            'WeaponEffect' => $p['LooksWeaponEffect'],
            'Armour'       => $p['LooksArmour'],
            'WingEffect'   => $p['LooksWings'],
        ];
    }

    public function npcResponse($page)
    {
        return ['NPC_RESPONSE', ['Count' => count($page), 'Page' => $page]];
    }

    public function npcGoods($goods, $rate, $type)
    {
        return ['NPC_GOODS', [
            'Count' => !empty($goods) ? count($goods) : 0,
            'Goods' => $goods,
            'Rate'  => $rate,
            'Type'  => $type,
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
            $pack['Count']   = count($data);
            $pack['Storage'] = $data;
        } else {
            $pack['isset'] = false;
        }

        return ['USER_STORAGE', $pack];
    }

    public function npcRepair($p, $npc, $bool)
    {
        return [
            'NPC_REPAIR', [
                'Rate' => $npc['Info']['rate'] / 100,
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
            ['Gold' => $gold],
        ];
    }

    public function dropItem($id, $count, $status = false)
    {
        return [
            'DROP_ITEM', [
                'UniqueID' => $id,
                'Count'    => $count,
                'Success'  => $status,
            ],
        ];
    }

    public function sellItem($id, $count, $status = false)
    {
        return [
            'SELL_ITEM', [
                'UniqueID' => $id,
                'Count'    => $count,
                'Success'  => $status,
            ],
        ];
    }

    public function objectItem($item)
    {
        return [
            'OBJECT_ITEM', [
                'ObjectID'  => $item['ID'],
                'Name'      => $item['Name'],
                'NameColor' => Int32(pack('c4', $item['NameColor']['R'], $item['NameColor']['G'], $item['NameColor']['B'], 255)),
                'LocationX' => $item['CurrentLocation']['X'],
                'LocationY' => $item['CurrentLocation']['Y'],
                'Image'     => $this->Item->getImage($item),
                'Grade'     => $this->Enum::ItemGradeNone,
            ],
        ];
    }

    public function objectGold($item)
    {
        return [
            'OBJECT_GOLD', [
                'ObjectID'  => $item['ID'],
                'Gold'      => $item['Gold'],
                'LocationX' => $item['CurrentLocation']['X'],
                'LocationY' => $item['CurrentLocation']['Y'],
            ],
        ];
    }

    public function gainedGold($gold)
    {
        return [
            'GAINED_GOLD', [
                'Gold' => $gold,
            ],
        ];
    }

    public function objectRemove($item)
    {
        return [
            'OBJECT_REMOVE',
            [
                'ObjectID' => $item['ID'],
            ],
        ];
    }

    public function changeAMode($mode)
    {
        return [
            'CHANGE_A_MODE',
            [
                'Mode' => $mode,
            ],
        ];
    }

    public function changePMode($mode)
    {
        return [
            'CHANGE_P_MODE',
            [
                'Mode' => $mode,
            ],
        ];
    }

    public function useItem($id, $status = false)
    {
        return [
            'USE_ITEM',
            [
                'UniqueID' => $id,
                'Success'  => $status,
            ],
        ];
    }

    public function death($p)
    {
        return [
            'DEATH', [
                'LocationX' => $p['CurrentLocation']['X'],
                'LocationY' => $p['CurrentLocation']['Y'],
                'Direction' => $p['CurrentDirection'],
            ],
        ];
    }

    public function objectDied($p)
    {
        return [
            'OBJECT_DIED', [
                'ObjectID'  => $p['ID'],
                'LocationX' => $p['CurrentLocation']['X'],
                'LocationY' => $p['CurrentLocation']['Y'],
                'Direction' => $p['CurrentDirection'],
                'Type'      => 0,
            ],
        ];
    }

    public function healthChanged($p)
    {
        return [
            'HEALTH_CHANGED', [
                'HP' => $p['HP'],
                'MP' => $p['MP'],
            ],
        ];
    }

    public function health($health)
    {
        $newHealth = [
            // 生命药水回复
            'HPPotValue'    => null, // 回复总值
            'HPPotPerValue' => null, // 一次回复多少
            'HPPotNextTime' => null, // 下次生效时间
            'HPPotDuration' => null, // 两次生效时间间隔
            'HPPotTickNum'  => null, // 总共跳几次
            'HPPotTickTime' => null, // 当前第几跳
            // 魔法药水回复
            'MPPotValue'    => null,
            'MPPotPerValue' => null,
            'MPPotNextTime' => null,
            'MPPotDuration' => null,
            'MPPotTickNum'  => null,
            'MPPotTickTime' => null,
            // 角色生命/魔法回复
            'HealNextTime'  => null,
            'HealDuration'  => null,
        ];

        return array_merge($newHealth, $health);
    }
}
