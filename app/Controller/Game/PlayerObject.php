<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class PlayerObject extends AbstractController
{
    public $playerInfo = [
        'fd'                    => null,
        'account_id'            => null, //账户id
        'game_stage'            => null, //游戏状态
        'map_object'            => null, //地图信息
        'hp'                    => null, //血量
        'mp'                    => null, //魔法值
        'level'                 => null, //等级
        'experience'            => null, //经验值
        'max_experience'        => null, //最大经验值
        'gold'                  => null, //金币
        'guild_name'            => null, //工会名称
        'guild_rank_name'       => null, //公会等级名称
        'class'                 => null, //职业
        'gender'                => null, //性别
        'hair'                  => null, //发型
        'light'                 => null,
        'inventory'             => null,
        'equipment'             => null,
        'quest_inventory'       => null, //任务清单
        'storage'               => null,
        'trade'                 => null,
        'refine'                => null,
        'looks_armour'          => null, //衣服外观
        'looks_wings'           => null, //翅膀外观
        'looks_weapon'          => null, //武器外观
        'looks_weapon_effect'   => null, //武器特效
        'send_item_info'        => null,
        'current_bag_weight'    => null,
        'max_hp'                => null, //最大血值
        'max_mp'                => null, //最大魔法值
        'min_ac'                => null, // 物理防御力
        'max_ac'                => null,
        'min_mac'               => null, // 魔法防御力
        'max_mac'               => null,
        'min_dc'                => null, // 攻击力
        'max_dc'                => null,
        'min_mc'                => null, // 魔法力
        'max_mc'                => null,
        'min_sc'                => null, // 道术力
        'max_sc'                => null,
        'accuracy'              => null, //精准度
        'agility'               => null, //敏捷
        'critical_rate'         => null,
        'critical_damage'       => null,
        'max_bag_weight'        => null, //Other Stats'=>null,
        'max_wear_weight'       => null,
        'max_hand_weight'       => null,
        'a_speed'               => null,
        'luck'                  => null,
        'life_on_hit'           => null,
        'hp_drain_rate'         => null,
        'reflect'               => null, // TODO
        'magic_resist'          => null,
        'poison_resist'         => null,
        'health_recovery'       => null,
        'spell_recovery'        => null,
        'poison_recovery'       => null,
        'holy'                  => null,
        'freezing'              => null,
        'poison_attack'         => null,
        'exp_rate_offset'       => null,
        'item_drop_rate_offset' => null,
        'mine_rate'             => null,
        'gem_rate'              => null,
        'fish_rate'             => null,
        'craft_rate'            => null,
        'gold_drop_rate_offset' => null,
        'attack_bonus'          => null,
        'magics'                => null,
        'action_list'           => null,
        'poison_list'           => null,
        'buff_list'             => null,
        'health'                => null, // 状态恢复
        'pets'                  => null,
        'pk_points'             => null,
        'a_mode'                => null,
        'p_mode'                => null,
        'calling_npc'           => null,
        'calling_npc_page'      => null,
        'slaying'               => null,
        'flaming_sword'         => null,
        'twin_drake_blade'      => null, // TODO
        'bind_map_index'        => null, // 绑定的地图 死亡时复活用
        'bind_location'         => null, // 绑定的坐标 死亡时复活用
        'magic_shield'          => null, // TODO 是否有魔法盾
        'magic_shieldLv'        => null, // TODO 魔法盾等级
        'armour_rate'           => null, // 防御
        'damage_rate'           => null, // 伤害
        'struck_time'           => null, // 被攻击硬直时间
        'allow_group'           => null, // 是否允许组队
        'group_members'         => null, // 小队成员
        'group_invitation'      => null, // 组队邀请人
        'current_direction'     => null,
        'current_location'      => null,
        'characters'            => null,
    ];

    public function getPlayer($fd)
    {
        return $this->GameData->getPlayer($fd);
    }

    public function getIdPlayer($id)
    {
        return $this->GameData->getIdPlayer($id);
    }

    public function setPlayer($fd, $data = null, $field = null)
    {
        $this->GameData->setPlayer($fd, $data, $field);
    }

    public function updatePlayerInfo(&$p, $accountCharacter, $user_magic)
    {
        $p['game_stage']        = $this->Enum::GAME;
        $p['id']                = $accountCharacter['id'];
        $p['name']              = $accountCharacter['name'];
        $p['name_color']        = ['r' => 255, 'g' => 255, 'b' => 255];
        $p['current_direction'] = $accountCharacter['direction'];
        $p['current_location']  = ['x' => $accountCharacter['current_location_x'], 'y' => $accountCharacter['current_location_y']];
        $p['bind_location']     = ['x' => $accountCharacter['bind_location_x'], 'y' => $accountCharacter['bind_location_y']];
        $p['bind_map_index']    = $accountCharacter['bind_map_id'];

        $p['inventory']       = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeInventory, 46);
        $p['equipment']       = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeEquipment, 14);
        $p['quest_inventory'] = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeQuestInventory, 40);
        $p['storage']         = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeStorage, 80);

        $p['dead']            = 0;
        $p['hp']              = $accountCharacter['hp'];
        $p['mp']              = $accountCharacter['mp'];
        $p['level']           = $accountCharacter['level'];
        $p['experience']      = $accountCharacter['experience'];
        $p['gold']            = $accountCharacter['gold'];
        $p['guild_name']      = ''; //TODO
        $p['guild_rank_name'] = ''; //TODO
        $p['class']           = $accountCharacter['class'];
        $p['gender']          = $accountCharacter['gender'];
        $p['hair']            = $accountCharacter['hair'];
        $p['send_item_info']  = [];
        $p['max_experience']  = $this->GameData->getExpList($accountCharacter['level']);
        $p['object_type']     = $this->Enum::ObjectTypePlayer;

        if ($user_magic) {
            foreach ($user_magic as $k => $v) {
                $user_magic[$k]['info'] = $this->GameData->getMagicInfoByID($v['magic_id']);
            }
        }

        $p['magics'] = $user_magic;

        $p['action_list'] = []; //TODO
        $p['poison_list'] = []; //TODO
        $p['buff_list']   = [];

        $health = [
            'hp_pot_next_time' => time(), //下次生效时间
            'hp_pot_duration'  => 1, //两次生效时间间隔
            'mp_pot_next_time' => time(),
            'mp_pot_duration'  => 1,
            'heal_next_time'   => time(),
            'heal_duration'    => config('heal_duration'), //自然恢复
        ];

        $p['health'] = $this->MsgFactory->health($health);

        $p['pk_points']   = 0;
        $p['a_mode']      = $accountCharacter['attack_mode'];
        $p['p_mode']      = $accountCharacter['pet_mode'];
        $p['calling_npc'] = null;
        $p['struck_time'] = time();
        $p['damage_rate'] = 1.0;
        $p['armour_rate'] = 1.0;
        $p['allow_group'] = $accountCharacter['allow_group'];
        $p['pets']        = [];

        $StartItems = $this->GameData->getStartItems();

        if ($p['level'] < 1) {
            foreach ($StartItems as $k => $v) {
                $this->gainItem($p, $v);
            }
        }
    }

    // GainItem 为玩家增加物品，增加成功返回 true
    public function gainItem(&$p, $item)
    {
        $itemInfo                  = $this->MsgFactory->newUserItem($item, $this->Atomic->newObjectID());
        $itemInfo['soul_bound_id'] = $p['id'];

        if ($itemInfo['info']['stack_size'] > 1) {
            foreach ($p['inventory']['items'] as $k => $v) {

                if (empty($v['isset']) || $itemInfo['info']['id'] != $v['info']['id'] || $v['count'] > $v['info']['stack_size']) {
                    continue;
                }

                if ($itemInfo['count'] + $v['count'] <= $v['info']['stack_size']) {

                    $this->Bag->setCount($p['inventory'], $k, $v['count'] + $itemInfo['count']);

                    $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedItem($itemInfo['info']));
                    return true;
                }

                $this->Bag->setCount($p['inventory'], $k, $v['count'] + $itemInfo['count']);
                $itemInfo['count'] -= $v['info']['stack_size'] - $v['count'];
            }
        }

        $i = 0;
        $j = 46;

        $type = $itemInfo['info']['type'];

        if ($type == $this->Enum::ItemTypePotion || $type == $this->Enum::ItemTypeScroll
            || ($type == $this->Enum::ItemTypeScript && $itemInfo['info']['effect'] == 1)) {
            $i = 0;
            $j = 4;
        } elseif ($type == $this->Enum::ItemTypeAmulet) {
            $i = 4;
            $j = 6;
        } else {
            $i = 6;
            $j = 46;
        }

        for ($i = $i; $i < $j; $i++) {

            if (!empty($p['inventory']['items'][$i]['isset'])) {
                continue;
            }

            $this->Bag->set($p['id'], $p['inventory'], $i, $itemInfo);
            $this->enqueueItemInfo($p, $itemInfo['item_id']);
            $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedItem($itemInfo['info']));
            $this->refreshBagWeight($p);

            return true;
        }

        for ($i = 0; $i < 46; $i++) {
            if (!empty($p['inventory']['items'][$i]['isset'])) {
                continue;
            }

            $this->Bag->set($p['id'], $p['inventory'], $i, $itemInfo);
            $this->enqueueItemInfo($p, $itemInfo['item_id']);
            $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedItem($itemInfo['info']));
            $this->refreshBagWeight($p);

            return true;
        }

        $this->receiveChat($p['fd'], '没有合适的格子放置物品', $this->Enum::ChatTypeSystem);

        return false;
    }

    //为玩家增加金币
    public function gainGold(&$p, $gold)
    {
        if ($gold <= 0) {
            return;
        }

        $p['gold'] += $gold;

        $this->PlayersList->saveGold($p['id'], $p['gold']);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedGold($gold));
    }

    public function receiveChat($fd, $msg, $type)
    {
        $this->SendMsg->send($fd, ['CHAT', ['message' => $msg, 'type' => $type]]);
    }

    public function enqueueItemInfo(&$p, $ItemID)
    {
        if ($p['send_item_info']) {
            foreach ($p['send_item_info'] as $k => $v) {
                if (!empty($v['id']) && $v['id'] == $ItemID) {
                    return $p;
                }
            }
        }

        $item = $this->GameData->getItemInfoById($ItemID);

        if (!$item) {
            return false;
        }

        $this->SendMsg->send($p['fd'], ['NEW_ITEM_INFO', ['info' => $item]]);

        $p['send_item_info'][] = $item;

        return true;
    }

    public function StartGame(&$p)
    {
        $this->receiveChat($p['fd'], '[欢迎进入游戏,游戏目前处于测试模式]', $this->Enum::ChatTypeHint);
        $this->receiveChat($p['fd'], '[本模拟器为学习研究使用,禁止一切商业行为]', $this->Enum::ChatTypeHint);
        $this->receiveChat($p['fd'], '[模拟器已经开源,其他人员非法使用与本模拟器无关]', $this->Enum::ChatTypeHint);
        $this->receiveChat($p['fd'], '[有任何意见及建议欢迎加QQ群并联系管理员,群号186510932]', $this->Enum::ChatTypeHint);

        $this->enqueueItemInfos($p);

        $this->refreshStats($p);

        $this->enqueueQuestInfo($p); //任务

        $mapInfo = $this->GameData->getMap($p['map']['info']['id']);

        $this->SendMsg->send($p['fd'], ['MAP_INFORMATION', $mapInfo['info']]);

        $this->SendMsg->send($p['fd'], ['USER_INFORMATION', $this->MsgFactory->userInformation($p)]);

        $this->SendMsg->send($p['fd'], ['TIME_OF_DAY', ['lights' => $this->Settings->lightSet()]]);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changeAMode($p['a_mode']));

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changePMode($p['p_mode']));

        $this->SendMsg->send($p['fd'], ['SWITCH_GROUP', ['allow_group' => $p['allow_group'] ?: 0]]);

        $this->enqueueAreaObjects($p, null, $p['current_location'], $mapInfo);

        $this->broadcast($p, $this->MsgFactory->objectPlayer($p));
    }

    public function stopGame($p)
    {
        $this->broadcast($p, $this->MsgFactory->objectRemove($p));
    }

    //物品
    public function enqueueItemInfos(&$p)
    {
        $itemInfos = [];

        if ($p['inventory']['items']) {
            foreach ($p['inventory']['items'] as $k => $v) {
                if ($v) {
                    $p['inventory']['items'][$k]['isset'] = true;
                    $itemInfos[]                          = $this->GameData->getItemInfoById($v['item_id']);
                } else {
                    $p['inventory']['items'][$k]['isset'] = false;
                }

            }
        }

        if ($p['equipment']['items']) {
            foreach ($p['equipment']['items'] as $k => $v) {
                if ($v) {
                    $p['equipment']['items'][$k]['isset'] = true;
                    $itemInfos[]                          = $this->GameData->getItemInfoById($v['item_id']);
                } else {
                    $p['equipment']['items'][$k]['isset'] = false;
                }
            }
        }

        if ($p['quest_inventory']['items']) {
            foreach ($p['quest_inventory']['items'] as $k => $v) {
                if ($v) {
                    $p['quest_inventory']['items'][$k]['isset'] = true;
                    $itemInfos[]                                = $this->GameData->getItemInfoById($v['item_id']);
                } else {
                    $p['quest_inventory']['items'][$k]['isset'] = false;
                }
            }
        }

        if ($itemInfos) {
            foreach ($itemInfos as $k => $v) {
                $this->enqueueItemInfo($p, $v['id']);
            }
        }
    }

    //状态
    public function refreshStats(&$p)
    {
        $this->refreshLevelStats($p);
        $this->refreshBagWeight($p);
        $this->refreshEquipmentStats($p);
        $this->refreshItemSetStats($p);
        $this->refreshMirSetStats($p);
        $this->refreshBuffs($p);
        $this->refreshStatCaps($p);
        $this->refreshMountStats($p);
        $this->refreshGuildBuffs($p);
    }

    //刷新级别状态
    public function refreshLevelStats(&$p)
    {
        $baseStats = $this->Settings->getBaseStats($p['class']); //职业属性

        $p['accuracy']        = $baseStats['start_accuracy'];
        $p['agility']         = $baseStats['start_agility'];
        $p['critical_rate']   = $baseStats['start_critical_rate'];
        $p['critical_damage'] = $baseStats['start_critical_damage'];

        $ExpList = $this->GameData->getExpList();

        if ($p['level'] < count($ExpList)) {
            $p['max_experience'] = $ExpList[$p['level'] - 1];
        } else {
            $p['max_experience'] = 0;
        }

        $p['max_hp'] = intval(14 + ($p['level'] / $baseStats['hp_gain'] + $baseStats['hp_gain_rate']) * $p['level']);

        $p['min_ac'] = 0;
        if ($baseStats['min_ac'] > 0) {
            $p['min_ac'] = intval($p['level'] / $baseStats['min_ac']);
        }

        $p['max_ac'] = 0;
        if ($baseStats['max_ac'] > 0) {
            $p['max_ac'] = intval($p['level'] / $baseStats['max_ac']);
        }

        $p['min_mac'] = 0;
        if ($baseStats['min_mac'] > 0) {
            $p['min_mac'] = intval($p['level'] / $baseStats['min_mac']);
        }

        $p['max_mac'] = 0;
        if ($baseStats['max_mac'] > 0) {
            $p['max_mac'] = intval($p['level'] / $baseStats['max_mac']);
        }

        $p['min_dc'] = 0;
        if ($baseStats['min_dc'] > 0) {
            $p['min_dc'] = intval($p['level'] / $baseStats['min_dc']);
        }

        $p['max_dc'] = 0;
        if ($baseStats['max_dc'] > 0) {
            $p['max_dc'] = intval($p['level'] / $baseStats['max_dc']);
        }

        $p['min_mc'] = 0;
        if ($baseStats['min_mc'] > 0) {
            $p['min_mc'] = intval($p['level'] / $baseStats['min_mc']);
        }

        $p['max_mc'] = 0;
        if ($baseStats['max_mc'] > 0) {
            $p['max_mc'] = intval($p['level'] / $baseStats['max_mc']);
        }

        $p['min_sc'] = 0;
        if ($baseStats['min_sc'] > 0) {
            $p['min_sc'] = intval($p['level'] / $baseStats['min_sc']);
        }

        $p['max_sc'] = 0;
        if ($baseStats['max_sc'] > 0) {
            $p['max_sc'] = intval($p['level'] / $baseStats['max_sc']);
        }

        $p['critical_rate'] = 0;
        if ($baseStats['critial_rate_gain'] > 0) {
            $p['critical_rate'] = intval($p['critical_rate'] + ($p['level'] / $baseStats['critial_rate_gain']));
        }

        $p['critical_damage'] = 0;
        if ($baseStats['critical_damage_gain'] > 0) {
            $p['critical_damage'] = intval($p['critical_damage'] + ($p['level'] / $baseStats['critical_damage_gain']));
        }

        $p['max_bag_weight']  = intval(50.0 + $p['level'] / $baseStats['bag_weight_gain'] * $p['level']);
        $p['max_wear_weight'] = intval(15.0 + $p['level'] / $baseStats['wear_weight_gain'] * $p['level']);
        $p['max_hand_weight'] = intval(12.0 + $p['level'] / $baseStats['hand_weight_gain'] * $p['level']);

        switch ($p['class']) {
            case $this->Enum::MirClassWarrior:
                $p['max_hp'] = intval(14.0 + ($p['level'] / $baseStats['hp_gain'] + $baseStats['hp_gain_rate'] + $p['level'] / 20.0) * $p['level']);
                $p['max_mp'] = intval(11.0 + ($p['level'] * 3.5) + ($p['level'] * $baseStats['mp_gain_rate']));
                break;

            case $this->Enum::MirClassWizard:
                $p['max_mp'] = intval(13.0 + (($p['level'] / 5.0 + 2.0) * 2.2 * $p['level']) + ($p['level'] * $baseStats['mp_gain_rate']));
                break;

            case $this->Enum::MirClassTaoist:
                $p['max_mp'] = intval((13 + $p['level'] / 8.0 * 2.2 * $p['level']) + ($p['level'] * $baseStats['mp_gain_rate']));
                break;
        }
    }

    //刷新负重
    public function refreshBagWeight(&$p)
    {
        $p['current_bag_weight'] = 0;

        foreach ($p['inventory']['items'] as $k => $v) {
            if ($v && $v['isset']) {
                $item = $this->GameData->getItemInfoById($v['info']['id']);
                $p['current_bag_weight'] += $item['weight'];
            }
        }
    }

    public function refreshEquipmentStats(&$p)
    {
        $oldLooksWeapon       = $p['looks_weapon'];
        $oldLooksWeaponEffect = $p['looks_weapon_effect'];
        $oldLooksArmour       = $p['looks_armour'];
        $oldLooksWings        = $p['looks_wings'];
        $oldLight             = $p['light'];

        $p['looks_armour']        = 0;
        $p['looks_weapon']        = -1;
        $p['looks_weapon_effect'] = 0;
        $p['looks_wings']         = 0;

        $ItemInfos = $this->GameData->getItemInfosIds();
        foreach ($p['equipment']['items'] as $temp) {
            if (!$temp || !$temp['isset']) {
                continue;
            }

            $RealItem = $this->Item->getRealItem($temp['info'], $p['level'], $p['class'], $ItemInfos);

            $p['min_ac']  = toUint16(intval($p['min_ac']) + intval($RealItem['min_ac']));
            $p['max_ac']  = toUint16(intval($p['max_ac']) + intval($RealItem['max_ac']) + intval($temp['ac']));
            $p['min_mac'] = toUint16(intval($p['min_mac']) + intval($RealItem['min_mac']));
            $p['max_mac'] = toUint16(intval($p['max_mac']) + intval($RealItem['max_mac']) + intval($temp['mac']));
            $p['min_dc']  = toUint16(intval($p['min_dc']) + intval($RealItem['min_dc']));
            $p['max_dc']  = toUint16(intval($p['max_dc']) + intval($RealItem['max_dc']) + intval($temp['dc']));
            $p['min_mc']  = toUint16(intval($p['min_mc']) + intval($RealItem['min_mc']));
            $p['max_mc']  = toUint16(intval($p['max_mc']) + intval($RealItem['max_mc']) + intval($temp['mc']));
            $p['min_sc']  = toUint16(intval($p['min_sc']) + intval($RealItem['min_sc']));
            $p['max_sc']  = toUint16(intval($p['max_sc']) + intval($RealItem['max_sc']) + intval($temp['sc']));
            $p['max_hp']  = toUint16(intval($p['max_hp']) + intval($RealItem['hp']) + intval($temp['hp']));
            $p['max_mp']  = toUint16(intval($p['max_mp']) + intval($RealItem['mp']) + intval($temp['mp']));

            $p['max_bag_weight']  = toUint16(intval($p['max_bag_weight']) + intval($RealItem['bag_weight']));
            $p['max_wear_weight'] = toUint16(intval($p['max_wear_weight']) + intval($RealItem['wear_weight']));
            $p['max_hand_weight'] = toUint16(intval($p['max_hand_weight']) + intval($RealItem['hand_weight']));

            $p['a_speed']  = toInt8(intval($p['a_speed']) + intval($temp['attack_speed']) + intval($RealItem['attack_speed']));
            $p['luck']     = toInt8(intval($p['luck']) + intval($temp['luck']) + intval($RealItem['luck']));
            $p['accuracy'] = toUint8(intval($p['accuracy']) + intval($RealItem['accuracy']) + intval($temp['accuracy']));
            $p['agility']  = toUint8(intval($p['agility']) + intval($RealItem['agility']) + intval($temp['agility']));

            $p['magic_resist']    = toUint8(intval($p['magic_resist']) + intval($temp['magic_resist']) + intval($RealItem['magic_resist']));
            $p['poison_resist']   = toUint8(intval($p['poison_resist']) + intval($temp['poison_resist']) + intval($RealItem['poison_resist']));
            $p['health_recovery'] = toUint8(intval($p['health_recovery']) + intval($temp['health_recovery']) + intval($RealItem['health_recovery']));
            $p['spell_recovery']  = toUint8(intval($p['spell_recovery']) + intval($temp['mana_recovery']) + intval($RealItem['spell_recovery']));
            $p['poison_recovery'] = toUint8(intval($p['poison_recovery']) + intval($temp['poison_recovery']) + intval($RealItem['poison_recovery']));
            $p['critical_rate']   = toUint8(intval($p['critical_rate']) + intval($temp['critical_rate']) + intval($RealItem['critical_rate']));
            $p['critical_damage'] = toUint8(intval($p['critical_damage']) + intval($temp['critical_damage']) + intval($RealItem['critical_damage']));
            $p['holy']            = toUint8(intval($p['holy']) + intval($RealItem['holy']));
            $p['freezing']        = toUint8(intval($p['freezing']) + intval($temp['freezing']) + intval($RealItem['freezing']));
            $p['poison_attack']   = toUint8(intval($p['poison_attack']) + intval($temp['poison_attack']) + intval($RealItem['poison_attack']));
            $p['reflect']         = toUint8(intval($p['reflect']) + intval($RealItem['reflect']));
            $p['hp_drain_rate']   = toUint8(intval($p['hp_drain_rate']) + intval($RealItem['hp_drain_rate']));

            switch ($RealItem['type']) {
                case $this->Enum::ItemTypeArmour:
                    $p['looks_armour'] = intval($RealItem['shape']);
                    $p['looks_wings']  = intval($RealItem['effect']);
                    break;

                case $this->Enum::ItemTypeWeapon:
                    $p['looks_weapon']        = intval($RealItem['shape']);
                    $p['looks_weapon_effect'] = intval($RealItem['effect']);
                    break;
            }
        }

        if ($oldLooksArmour != $p['looks_armour'] || $oldLooksWeapon != $p['looks_weapon'] || $oldLooksWeaponEffect != $p['looks_weapon_effect'] || $oldLooksWings != $p['looks_wings'] || $oldLight != $p['light']) {
            $this->broadcast($p, $this->getUpdateInfo($p));
        }
    }

    public function getUpdateInfo(&$p)
    {
        $this->updateConcentration($p);

        return [
            'PLAYER_UPDATE',
            [
                'object_id'     => $p['id'],
                'light'         => !empty($p['light']) ? $p['light'] : 0,
                'weapon'        => $p['looks_weapon'],
                'weapon_effect' => $p['looks_weapon_effect'],
                'armour'        => $p['looks_armour'],
                'wing_effect'   => $p['looks_wings'],
            ],
        ];
    }

    public function updateConcentration($p)
    {
        $this->SendMsg->send($p['fd'], ['SET_CONCENTRATION', ['object_id' => $p['account_id'], 'enabled' => 0, 'interrupted' => 0]]);
        $this->broadcast($p, ['SET_OBJECT_CONCENTRATION', ['object_id' => $p['account_id'], 'enabled' => 0, 'interrupted' => 0]]);
    }

    public function broadcast($p, $msg)
    {
        $this->Map->broadcastP($p['current_location'], $msg, $p);
    }

    public function refreshItemSetStats(&$p)
    {
        # code...
    }

    public function refreshMirSetStats(&$p)
    {
        # code...
    }

    public function refreshSkills(&$p)
    {
        if (!empty($p['magics'])) {
            foreach ($p['magics'] as $k => $magic) {
                switch ($magic['spell']) {
                    case $this->Enum::SpellFencing: // 基本剑术
                        $p['accuracy'] = toUint8(intval($p['accuracy']) + $magic['level'] * 3);
                        $p['max_ac']   = toUint16(intval($p['max_ac']) + ($magic['level'] + 1) * 3);
                        break;

                    case $this->Enum::SpellFatalSword: // 刺客的技能 忽略
                        break;

                    case $this->Enum::SpellSpiritSword: // 精神力战法
                        $p['accuracy'] = toUint8(intval($p['accuracy']) + $magic['level']);
                        $p['max_ac']   = toUint16(intval($p['max_dc']) + intval($p['max_sc'] * $magic['level'] + 1 * 0.1));
                        break;

                }
            }
        }
    }

    //刷新玩家身上的 buff
    public function refreshBuffs(&$p)
    {
        # code...
    }

    //刷新各种状态
    public function refreshStatCaps(&$p)
    {
        # code...
    }

    //刷新装备嵌套的宝石属性
    public function refreshMountStats(&$p)
    {
        # code...
    }

    //刷新工会 buff
    public function refreshGuildBuffs(&$p)
    {
        # code...
    }

    public function enqueueQuestInfo(&$p)
    {
        # code...
    }

    //更新对象周边对象
    public function enqueueAreaObjects($p, $oldCell = null, $newCell = null, $map = null)
    {
        if ($oldCell == null) {
            $this->Map->rangeObject($p, $p['current_location'], 20, function ($p, $object) {
                if ($object['id'] != $p['id']) {
                    $this->SendMsg->send($p['fd'], $this->MsgFactory->object($object));
                }
                return true;
            });

            return false;
        }

        //移动后 删除原有格子对象,新格子对象加入
        $cells = $this->Map->calcDiff($map, $oldCell, $newCell);

        foreach ($cells as $cellId => $idAdd) {
            if ($idAdd) {
                co(function () use ($p, $cellId) {
                    $newObjects = $this->GameData->getCellObject($p['map']['info']['id'], $cellId);
                    if ($newObjects) {
                        foreach ($newObjects as $k => $v) {

                            //地图小于40的npc不更新
                            if ($p['map']['width'] <= 40 && $p['map']['height'] <= 40 && $v['object_type'] == $this->Enum::ObjectTypeNPC) {
                                continue;
                            }

                            co(function () use ($p, $v) {
                                if ($v['id'] != $p['id']) {
                                    $this->SendMsg->send($p['fd'], $this->MsgFactory->object($v));
                                }
                            });
                        }
                    }
                });
            } else {
                co(function () use ($p, $cellId) {
                    $oldObjects = $this->GameData->getCellObject($p['map']['info']['id'], $cellId);
                    if ($oldObjects) {
                        foreach ($oldObjects as $k => $v) {
                            //地图小于40的npc不更新
                            if ($p['map']['width'] <= 40 && $p['map']['height'] <= 40 && $v['object_type'] == $this->Enum::ObjectTypeNPC) {
                                continue;
                            }

                            co(function () use ($p, $v) {
                                if ($v['id'] != $p['id']) {
                                    $this->SendMsg->send($p['fd'], $this->MsgFactory->objectRemove($v));
                                }
                            });
                        }
                    }
                });
            }
        }
    }

    public function getCell($map, $CurrentLocation)
    {
        return $this->Map->getCell($map, $CurrentLocation);
    }

    //检查可以地图跳转
    public function checkMovement($point, &$p)
    {
        $movementInfos = $this->GameData->getMovements();

        $maps = $this->GameData->getMap();

        foreach ($movementInfos as $k => $v) {
            if ($v['source_map'] == $p['map']['info']['id']) {
                if ($point['x'] == $v['source_x'] && $point['y'] == $v['source_y']) {
                    $m = $maps[$v['destination_map']] ?? null;
                    if (!$m) {
                        EchoLog(sprintf('未知的地图ID: %s', $maps[$v['destination_map']]), 'e');
                    }

                    $this->teleport($p, $m, ['x' => $v['destination_x'], 'y' => $v['destination_y']]);
                    return true;
                }
            }
        }

        return false;
    }

    public function teleport(&$p, $m, $point)
    {
        $oldMap = $p['map'];

        if (!$this->Map->inMap($m, $point['x'], $point['y'])) {
            return false;
        }

        $this->Map->deleteObject($p, $this->Enum::ObjectTypePlayer);

        $this->broadcast($p, ['OBJECT_TELEPORT_OUT', ['object_id' => $p['id'], 'type' => 0]]);
        $this->broadcast($p, $this->MsgFactory->objectRemove($p));

        $p['map'] = [
            'id'     => $m['info']['id'],
            'width'  => $m['width'],
            'height' => $m['height'],
            'info'   => [
                'id' => $m['info']['id'],
            ],
        ];

        $p['current_location'] = $point;

        $this->Map->addObject($p, $this->Enum::ObjectTypePlayer);

        $this->broadcastInfo($p); //广播人物

        $this->broadcast($p, ['OBJECT_TELEPORT_IN', ['object_id' => $p['id'], 'type' => 0]]);

        $this->broadcastHealthChange($p);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->mapChange($m, $p['current_location'], $p['current_direction']));

        $this->enqueueAreaObjects($p, null, $p['current_location'], $m);

        $this->SendMsg->send($p['fd'], ['OBJECT_TELEPORT_IN', ['object_id' => $p['id'], 'type' => 0]]);

        return true;
    }

    public function teleportRandom(&$p, $attempts, $distance, $m)
    {
        if (!$m) {
            $m = $this->GameData->getMap($p['map']['info']['id']);
        }

        for ($i = 0; $i < $attempts; $i++) {
            $loc = ['x' => rand(0, $m['width'] - 1), 'y' => rand(0, $m['height'] - 1)];
            if ($this->teleport($p, $m, $loc)) {
                return true;
            }
        }

        return false;
    }

    public function broadcastInfo($p)
    {
        $this->broadcast($p, $this->MsgFactory->objectPlayer($p));
    }

    //广播血值状态
    public function broadcastHealthChange($p)
    {
        $msg = $this->MapObject->iMapObject_BroadcastHealthChange($p, $this->Enum::ObjectTypePlayer);

        $this->broadcast($p, ['OBJECT_HEALTH', $msg]);
    }

    //开门
    public function openDoor($p, $doorIndex)
    {
        if ($this->Map->openDoor($p['map']['info']['id'], $doorIndex)) {
            $this->SendMsg->send($p['fd'], ['OPENDOOR', ['door_index' => $doorIndex, 'close' => false]]);
            $this->broadcast($p, ['OPENDOOR', ['door_index' => $doorIndex, 'close' => false]]);
        }
    }

    //获取用户物品
    public function getUserItemByID($p, $mirGridType, $id)
    {
        $Items = [];

        switch ($mirGridType) {
            case $this->Enum::MirGridTypeInventory:
                $Items = $p['inventory']['items'];
                break;

            case $this->Enum::MirGridTypeEquipment:
                $Items = $p['equipment']['items'];
                break;

            case $this->Enum::MirGridTypeStorage:
                $Items = $p['storage']['items'];
                break;

            default:
                EchoLog(sprintf('错误的装备类型: %s', $mirGridType), 'e');
                return [false, []];
                break;
        }

        foreach ($Items as $k => $v) {
            if (!empty($v['isset']) && $v['id'] == $id) {
                return [$k, $v];
            }
        }

        return [-1, []];
    }

    public function callNPC1(&$p, $npc, $key)
    {
        $say = $this->Npc->callScript($p, $npc, $key);

        $p['calling_npc']      = $npc['id'];
        $p['calling_npc_page'] = $key;

        if ($say === false) {
            EchoLog(sprintf('NPC脚本执行失败 ID: %s  key: %s', $npc['id'], $key), 'w');
        }

        $this->SendMsg->send($p['fd'], $this->MsgFactory->npcResponse($this->NpcScript->replaceTemplates($npc, $p, $say)));

        $key = strtoupper($key);

        switch ($key) {
            case $this->Enum::BuyKey:
                $this->sendNpcGoods($p, $npc);
                break;

            case $this->Enum::SellKey:
                $this->SendMsg->send($p['fd'], $this->MsgFactory->npcSell());
                break;

            case $this->Enum::BuySellKey:
                $this->sendNpcGoods($p, $npc);
                $this->SendMsg->send($p['fd'], $this->MsgFactory->npcSell());
                break;

            case $this->Enum::StorageKey:
                $this->sendStorage($p, $npc);
                $this->SendMsg->send($p['fd'], $this->MsgFactory->npcStorage());
                break;

            case $this->Enum::BuyBackKey:
                $this->sendBuyBackGoods($p, $npc, true);
                break;

            case $this->Enum::RepairKey:
                $this->SendMsg->send($p['fd'], $this->MsgFactory->npcRepair($p, $npc, false));
                break;

            default:
                # code...
                break;
        }
    }

    public function sendNpcGoods(&$p, $npc)
    {
        if (!empty($npc['goods'])) {
            foreach ($npc['goods'] as $key => $item) {
                $this->enqueueItemInfo($p, $item['item_id']);
            }

            $this->SendMsg->send($p['fd'], $this->MsgFactory->npcGoods($npc['goods'], 1.0, $this->Enum::PanelTypeBuy));
        }
    }

    public function sendStorage(&$p, $npc)
    {
        if (!empty($p['storage']['items'])) {
            foreach ($p['storage']['items'] as $key => $item) {
                $this->enqueueItemInfo($p, $item['item_id']);
            }

            $this->SendMsg->send($p['fd'], $this->MsgFactory->userStorage($p['storage']['items']));
        }
    }

    public function sendBuyBackGoods(&$p, $npc, $syncItem)
    {
        $goods = $this->GameData->getPlayerBuyBack($p['id'], $npc['id']);

        if ($syncItem && $goods) {
            foreach ($goods as $key => $item) {
                $this->enqueueItemInfo($p, $item['item_id']);
            }
        }

        $this->SendMsg->send($p['fd'], $this->MsgFactory->npcGoods($goods, 1, $this->Enum::PanelTypeBuy));
    }

    public function takeGold(&$p, $gold)
    {
        if ($gold > $p['gold']) {
            EchoLog(sprintf('没有足够的金币 余额: %s  需要: %s', $p['gold'], $gold), 'w');
            $p['gold'] = 0;
        } else {
            $p['gold'] -= $gold;
        }

        co(function () use ($p) {
            $this->PlayersList->saveGold($p['id'], $p['gold']);
        });

        $this->SendMsg->send($p['fd'], $this->MsgFactory->loseGold($gold));
    }

    public function canUseItem($p, $item)
    {
        return true;
    }

    # 药水是消耗品，可以治愈或增强玩家的生命。
    # 常用名称形状使用的统计信息描述
    #   普通药水 0 HP / MP逐渐治愈玩家。
    #   太阳药剂 1 HP / MP立即治愈玩家。
    #   神秘水 2 没有允许玩家取消装备被诅咒的物品（仅适用于官方神秘物品）。
    #   Buff 药剂 3 DC / MC / SC / ASpeed / HP / MP / MaxAC / MaxMAC /Durability 为玩家提供相对的增益 抛光时间的长短取决于药水的耐久性。 1 dura = 1分钟。
    #   经验值 4 运气/耐力通过运气统计数据增加玩家获得的经验值百分比。 抛光时间的长短取决于药水的耐久性。 1 dura = 1分钟。
    public function userItemPotion(&$p, $item)
    {
        $info = $item['info'];

        switch ($info['shape']) {
            //一般药水
            case 0:
                $ph = &$p['health'];
                if ($info['hp'] > 0) {
                    $ph['hp_pot_value']     = $info['hp']; // 回复总值
                    $ph['hp_pot_per_value'] = $info['hp'] / 3; // 一次回复多少
                    $ph['hp_pot_next_time'] = time(); // 下次生效时间
                    $ph['hp_pot_tick_num']  = 3; // 总共跳几次
                    $ph['hp_pot_tick_time'] = 0; // 当前第几跳
                }

                if ($info['mp'] > 0) {
                    $ph['mp_pot_value']     = $info['mp'];
                    $ph['mp_pot_per_value'] = $info['mp'] / 3;
                    $ph['mp_pot_next_time'] = time();
                    $ph['mp_pot_tick_num']  = 3;
                    $ph['mp_pot_tick_time'] = 0;
                }

                break;

            //太阳水
            case 1:
                $this->changeHp($p, $info['hp']);
                $this->changeMp($p, $info['mp']);
                break;

            //神秘药剂 TODO
            case 2:
                break;

            // Buff
            case 3:
                $expireTime = $info['durability'];

                if ($info['max_dc'] + $item['dc'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeImpact, $p, $expireTime, $info['max_dc'] + $item['dc']));
                }

                if ($info['max_mc'] + $item['mc'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeMagic, $p, $expireTime, $info['max_mc'] + $item['mc']));
                }

                if ($info['max_sc'] + $item['sc'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeTaoist, $p, $expireTime, $info['max_sc'] + $item['sc']));
                }

                if ($info['attack_speed'] + $item['attack_speed'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeStorm, $p, $expireTime, $info['attack_speed'] + $item['attack_speed']));
                }

                if ($info['hp'] + $item['hp'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeHealthAid, $p, $expireTime, $info['hp'] + $item['hp']));
                }

                if ($info['mp'] + $item['mp'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeManaAid, $p, $expireTime, $info['mp'] + $item['mp']));
                }

                if ($info['max_ac'] + $item['ac'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeDefence, $p, $expireTime, $info['max_ac'] + $item['ac']));
                }

                if ($info['max_mac'] + $item['mac'] > 0) {
                    $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeMagicDefence, $p, $expireTime, $info['max_mac'] + $item['mac']));
                }

                break;

            // Exp 经验
            case 4:
                $expireTime = $info['durability'];
                $this->addBuff($p, $this->Buff->newBuff($this->Enum::BuffTypeExp, $p, $expireTime, $info['luck'] + $item['luck']));
        }

        return true;
    }

    public function addBuff(&$p, $b)
    {
        if ($this->Buff->has($p['buff_list'], $b, function ($b, $temp) {return $temp['infinite'] && $b['type'] == $temp['type'];})) {
            return false;
        }

        $this->Buff->addBuff($p['buff_list'], $b);

        $caster = '';
        if ($b['caster']) {
            $caster = $b['caster']['name'];
        }

        $msg = $this->MsgFactory->addBuff($b);

        $this->SendMsg->send($p['fd'], $msg);

        if ($b['visible']) {
            $this->broadcast($p, $msg);
        }

        $this->refreshStats($p);
    }

    //卷轴是各种用途的消耗品。
    //常用名称 Shape    使用了stats描述
    //副本逃脱  0   将玩家传送到他们最后保存的地图上的任意位置。
    //城镇传送  1   传送玩家到他们最后保存的安全区域。
    //随机传送  2   随机传送玩家到同一地图上的一个新坐标。
    //祝福油    3   装备武器的玩家幸运的机会。
    //修理石油  4   修理装备武器耐久性5，同时减少其最大耐久性。
    //战神石油  5   修理装备的武器耐久性到最大。
    //复活卷轴  6   使玩家恢复100%的HP和MP。
    //积分卷轴      7   价格给玩家x数量的游戏商店积分。
    //地图喊话滚动    8   允许一个单一的特殊喊话横跨玩家当前的地图。
    //服务器喊  9   允许一个特殊的喊过服务器。
    //公会技能卷轴    10  增加一个技能到玩家公会。只能由公会领袖使用。由效果号选择的技能。
    public function useItemScroll(&$p, $item)
    {
        switch ($item['info']['shape']) {
            case 0: //DE
                $temp = $this->GameData->getMap($p['bind_map_index']);

                for ($i = 0; $i < 20; $i++) {
                    $x   = $p['bind_location']['x'] + rand(-100, 100);
                    $y   = $p['bind_location']['y'] + rand(-100, 100);
                    $loc = ['x' => $x, 'y' => $y];
                    if ($this->teleport($p, $temp, $loc)) {
                        return true;
                    }
                }

                break;
            case 1: //TT
                $temp = $this->GameData->getMap($p['bind_map_index']);

                if (!$this->teleport($p, $temp, $p['bind_location'])) {
                    return false;
                }

                break;
            case 2: //RT
                $mapInfo = $this->GameData->getMap($p['map']['info']['id']);
                if (!$this->teleportRandom($p, 200, $item['info']['durability'], $mapInfo)) {
                    return true;
                }

                break;
        }

        return true;
    }

    public function process($p)
    {
        // $p = $this->getPlayer($p['fd']);
        $p = &$this->PlayerData::$players[$p['fd']];

        if (!$p || $p == 'null') {
            return;
        }

        $this->processRegen($p);
        $this->processBuffs($p);
        $this->processPoison($p);
    }

    public function processRegen(&$p)
    {
        if (!empty($p['dead'])) {
            return false;
        }

        $now = time();

        $ch = &$p['health'];

        if ($ch['hp_pot_value'] != 0 && $ch['hp_pot_next_time'] < $now) {

            $this->changeHp($p, $ch['hp_pot_per_value']);

            $ch['hp_pot_tick_time'] += 1;

            if ($ch['hp_pot_tick_time'] >= $ch['hp_pot_tick_num']) {
                $ch['hp_pot_value'] = 0; //回复总值
            } else {
                $ch['hp_pot_next_time'] = $now + $ch['hp_pot_duration']; //下次生效时间
            }
        }

        if ($ch['mp_pot_value'] != 0 && $ch['mp_pot_next_time'] < $now) {

            $this->changeMp($p, $ch['mp_pot_per_value']);

            $ch['mp_pot_tick_time'] += 1;

            if ($ch['mp_pot_tick_time'] >= $ch['mp_pot_tick_num']) {
                $ch['mp_pot_value'] = 0; //回复总值
            } else {
                $ch['mp_pot_next_time'] = $now + $ch['mp_pot_duration']; //下次生效时间
            }
        }

        if ($ch['heal_next_time'] < $now) {
            $ch['heal_next_time'] = $now + $ch['heal_duration']; //自然恢复时间

            $this->changeHp($p, intval($p['max_hp'] * 0.03) + 1);
            $this->changeMp($p, intval($p['max_mp'] * 0.03) + 1);
        }
    }

    public function processBuffs(&$p)
    {

    }

    public function processPoison(&$p)
    {

    }

    public function giveSkill(&$p, $spell, $level)
    {
        $info = $this->GameData->getMagicInfoBySpell($spell);

        if ($info) {
            foreach ($p['magics'] as $key => $v) {
                if ($v['spell'] == $spell) {
                    $this->receiveChat($p, '你已经学习该技能', $this->Enum::ChatTypeSystem);
                    return true;
                }
            }

            $magic = [
                'level'        => $Level,
                'character_id' => $p['id'],
                'magic_id'     => $info['id'],
                'spell'        => $spell,
            ];

            $this->PlayersList->addSkill($magic);

            $magic['info'] = $info;

            $p['magics'] = $magic;

            $this->SendMsg->send($p['fd'], $this->MsgFactory->newMagic($this->MsgFactory->getClientMagic($magic)));

            $this->refreshStats($p);

            return true;
        }

        return false;
    }

    public function changeHp(&$p, $amount)
    {
        if ($amount == 0 || $p['dead'] || $p['hp'] >= $p['max_hp']) {
            return false;
        }

        $hp = intval($p['hp'] + $amount);

        if ($hp <= 0) {
            $hp = 0;
        }

        $this->setHp($p, $hp);
    }

    public function changeMp(&$p, $amount)
    {
        if ($amount == 0 || $p['dead'] || $p['mp'] >= $p['max_mp']) {
            return;
        }

        $mp = intval($p['mp'] + $amount);

        if ($mp <= 0) {
            $mp = 0;
        }

        $this->setMp($p, $mp);
    }

    public function setHp(&$p, $amount)
    {
        if ($p['hp'] == $amount) {
            return;
        }

        if ($amount >= $p['max_hp']) {
            $amount = $p['max_hp'];
        }

        $p['hp'] = $amount;

        if (!$p['dead'] && $p['hp'] == 0) {
            $this->die($p);
        }

        $this->SendMsg->send($p['fd'], $this->MsgFactory->healthChanged($p));

        $this->broadcastHealthChange($p);
    }

    public function setMp(&$p, $amount)
    {
        if ($p['mp'] == $amount) {
            return false;
        }

        $p['mp'] = $amount;

        $this->SendMsg->send($p['fd'], $this->MsgFactory->healthChanged($p));

        $this->broadcastHealthChange($p);
    }

    public function die(&$p) {
        $p['hp']   = 0;
        $p['dead'] = true;

        $this->SendMsg->send($p['fd'], $this->MsgFactory->death($p));
        $this->broadcast($p, $this->MsgFactory->objectDied($p['id'], $p['current_direction'], $p['current_location']));

        $this->callDefaultNPC($p, $this->Enum::DefaultNPCTypeDie);
    }

    public function callDefaultNPC(&$p, $calltype, ...$args)
    {
        $key = '';
        switch ($calltype) {
            case $this->Enum::DefaultNPCTypeDie:
                $key = 'UseItem(' . $args[0] . ')';
                break;
        }

        $key = '[@_' . $key . ']';

        // $this->callNPC1($p,);
    }

    public function repairItem(&$p, $unique_id, $special)
    {
        $this->SendMsg->send($p['fd'], $this->MsgFactory->repairItem($unique_id));

        if ($p['dead']) {
            return false;
        }

        if (!$p['calling_npc']
            // ||
            // (!$this->Util->stringEqualFold($p['calling_npc_page'], $this->Enum::StorageKey) && !$special) ||
            // (!$this->Util->stringEqualFold($p['calling_npc_page'], $this->Enum::SRepairKey) && $special)
        ) {
            return false;
        }

        $index = -1;

        foreach ($p['inventory']['items'] as $k => $temp) {
            if (!empty($temp['id']) && $temp['id'] == $unique_id) {
                $index = $k;
                break;
            }
        }

        if (!$temp || empty($temp['isset']) || $index == -1) {
            $this->PlayerObject->receiveChat($p['fd'], '当前物品位置错误', $this->Enum::ChatTypeSystem);
            return false;
        }

        $npc = $this->GameData->getMapNpcInfo($p['map']['info']['id'], $p['calling_npc']);
        if (!$this->Npc->hasType($npc, $temp['info']['type'])) {
            $this->PlayerObject->receiveChat($p['fd'], '您不能修理这个物品。', $this->Enum::ChatTypeSystem);
            return false;
        }

        $cost = $this->Item->repairPrice($temp) * $this->Npc->priceRate($p, $npc, true);

        if ($cost > $p['gold']) {
            $this->PlayerObject->receiveChat($p['fd'], '您没有足够的金币修复物品', $this->Enum::ChatTypeSystem);
            return;
        }

        $p['gold'] -= $cost;

        $this->SendMsg->send($p['fd'], $this->MsgFactory->loseGold($cost));

        if (!$special) {
            $temp['max_dura'] = intval($temp['max_dura'] - ($temp['max_dura'] - $temp['current_dura']) / 30);
        }

        $temp['current_dura'] = $temp['max_dura'];
        $temp['dura_changed'] = false;

        $p['inventory']['items'][$index] = $temp;

        $this->PlayerObject->receiveChat($p['fd'], '您的武器被完全修复了', $this->Enum::ChatTypeSystem);
        $this->SendMsg->send($p['fd'], $this->MsgFactory->itemRepaired($unique_id, $temp['max_dura'], $temp['current_dura']));

        co(function () use ($p, $temp) {
            $this->PlayersList->saveGold($p['id'], $p['gold']);
            $this->PlayersList->saveItemDura($temp);
        });
    }

    //判断玩家是否是攻击者的攻击对象
    public function isAttackTarget($object, $attacker)
    {
        if (!empty($object['dead'])) {
            return true;
        }

        switch ($attacker['object_type']) {
            case $this->Enum::ObjectTypeMonster:

                if ($attacker['ai'] == 6 || $attacker['ai'] == 58) {
                    return $object['pk_points'] >= 200;
                }

                if (empty($attacker['master'])) {
                    break;
                }

                if ($attacker['master']['id'] == $object['id']) {
                    return false;
                }

                switch ($attacker['master']['a_mode']) {
                    case $this->Enum::AttackModeAll:
                        return true;
                        break;

                    case $this->Enum::AttackModeGroup:
                        return true;
                        break;

                    case $this->Enum::AttackModeGuild:
                        return true;
                        break;

                    case $this->Enum::AttackModeEnemyGuild:
                        return false;
                        break;

                    case $this->Enum::AttackModePeace:
                        return false;
                        break;

                    case $this->Enum::AttackModeRedBrown:
                        return $object['pk_points'] >= 200;
                        break;

                    default:
                        # code...
                        break;
                }

                break;

            default:
                # code...
                break;
        }

        return true;
    }

    public function canAttack()
    {
        return true;
    }

    public function getMagic($p, $spell)
    {
        foreach ($p['magics'] as $k => $v) {
            if ($v['spell'] == $spell) {
                return $v['spell'];
            }
        }

        return null;
    }

    public function attack(&$p, $direction, $spell)
    {
        if (!$this->canAttack()) {
            $this->SendMsg->send($p['fd'], $this->MsgFactory->userLocation($p));
            return;
        }

        $level = 0;

        switch ($spell) {
            case $this->Enum::SpellSlaying:
                if (!$p['slaying']) {
                    $spell = $this->Enum::SpellNone;
                } else {
                    $magic = $this->getMagic($p, $this->Enum::SpellSlaying);
                    $level = $magic['level'];
                }

                $p['slaying'] = false;

                break;

            case $this->Enum::SpellDoubleSlash:
                $magic = $this->getMagic($p, $spell);

                if (!$magic || ($magic['info']['base_cost'] + ($magic['level'] * $magic['info']['level_cost'])) > intval($p['mp'])) {
                    $spell = $this->Enum::SpellNone;
                    break;
                }

                $level = $magic['level'];
                $this->changeMP($p, -($magic['info']['base_cost'] + $magic['level'] * $magic['info']['level_cost']));

                break;

            case $spell == $this->Enum::SpellThrusting || $spell == $this->Enum::SpellFlamingSword:
                $magic = $this->getMagic($p, $spell);

                if (!$magic || (!$p['flaming_sword'] && ($spell == $this->Enum::SpellFlamingSword))) {
                    $spell = $this->Enum::SpellNone;
                    break;
                }

                $level = $magic['level'];
                break;

            case $spell == $this->Enum::SpellHalfMoon || $spell == $this->Enum::SpellCrossHalfMoon:
                $magic = $this->getMagic($p, $spell);
                if (!$magic || $magic['info']['base_cost'] + ($magic['level'] * $magic['info']['level_cost']) > intval($p['mp'])) {
                    $spell = $this->Enum::SpellNone;
                    break;
                }

                $level = $magic['level'];
                $this->changeMP($p, -($magic['info']['base_cost'] + $magic['level'] * $magic['info']['level_cost']));
                break;

            case $this->Enum::SpellTwinDrakeBlade:
                $magic = $this->getMagic($p, $spell);
                if (!$p['twin_drake_blade'] || !$magic || $magic['info']['base_cost'] + $magic['level'] * $magic['info']['level_cost'] > intval($p['mp'])) {
                    $spell = $this->Enum::SpellNone;
                    break;
                }

                $level = $magic['level'];
                $this->changeMP($p, -($magic['info']['base_cost'] + $magic['level'] * $magic['info']['level_cost']));
                break;

            default:
                $spell = $this->Enum::SpellNone;
                break;
        }

        if (!$p['slaying']) {
            $magic = $this->getMagic($p, $this->Enum::SpellSlaying);

            if (!$magic && rand(0, 12) <= $magic['level']) {
                $p['slaying'] = true;

                $this->SendMsg->send($p['fd'], $this->MsgFactory->spellToggle($this->Enum::SpellSlaying, $p['slaying']));
            }
        }

        $p['direction'] = $direction;

        $this->SendMsg->send($p['fd'], $this->MsgFactory->userLocation($p));

        $this->broadcast($p, $this->MsgFactory->objectAttack($p, $spell, 0, 0));

        $point = $this->Point->NextPoint($p['current_location'], $p['direction'], 1);

        $damageBase = $this->getAttackPower($p['min_dc'], $p['max_dc']);

        $map    = $p['map'];
        $cellId = $this->Cell->getCellId($point['x'], $point['y'], $map['width']);

        $cellObjects = $this->GameData->getCellObject($map['info']['id'], $cellId);

        if ($cellObjects) {

            foreach ($cellObjects as $key => $object) {

                if ($object['object_type'] == $this->Enum::ObjectTypePlayer) {
                    if (!$this->isAttackTarget($object, $p)) {
                        continue;
                    }
                } elseif ($object['object_type'] == $this->Enum::ObjectTypeMonster) {
                    if (!$this->Monster->isAttackTarget($object, $p)) {
                        continue;
                    }
                } else {
                    continue;
                }

                $defence     = $this->Enum::DefenceTypeACAgility;
                $damageFinal = $damageBase;

                switch ($spell) {

                    //攻杀剑术
                    case $this->Enum::SpellSlaying:
                        $magic       = $this->getMagic($p, $this->Enum::SpellSlaying);
                        $damageFinal = $this->Magic->getDamage($magic, $damageBase);
                        $this->Magic->levelMagic($p, $magic);
                        break;

                    //刺杀剑术
                    case $this->Enum::SpellThrusting:
                        $magic = $this->getMagic($p, $this->Enum::SpellThrusting);
                        $this->Magic->levelMagic($p, $magic);
                        break;

                    //半月弯刀
                    case $this->Enum::SpellHalfMoon:
                        $magic = $this->getMagic($p, $this->Enum::SpellHalfMoon);
                        $this->Magic->levelMagic($p, $magic);
                        break;

                    //圆月弯刀
                    case $this->Enum::SpellCrossHalfMoon:
                        $magic = $this->getMagic($p, $this->Enum::SpellCrossHalfMoon);
                        $this->Magic->levelMagic($p, $magic);
                        break;

                    //双龙斩
                    case $this->Enum::SpellTwinDrakeBlade:
                        $magic                 = $this->getMagic($p, $this->Enum::SpellTwinDrakeBlade);
                        $damageFinal           = $this->Magic->getDamage($magic, $damageBase);
                        $p['twin_drake_blade'] = false;

                        $this->completeAttack($object, $damageFinal, $this->Enum::DefenceTypeAgility, false);

                        $this->Magic->levelMagic($p, $magic);
                        break;

                    //烈火剑法
                    case $this->Enum::SpellFlamingSword:
                        $magic              = $this->getMagic($p, $this->Enum::SpellFlamingSword);
                        $damageFinal        = $this->Magic->getDamage($magic, $damageBase);
                        $p['flaming_sword'] = false;
                        $defence            = $this->Enum::DefenceTypeAC;
                        $this->Magic->levelMagic($p, $magic);
                        break;
                    default:
                        # code...
                        break;
                }

                $this->completeAttack($p, $object, $damageFinal, $defence, true);
            }
        }
    }

    public function completeAttack(&$p, $target, $damage, $defence, $damageWeapon)
    {
        if (!$target) {
            return;
        }

        $p['target'] = [
            'id'          => $target['id'],
            'mapId'       => $target['map']['id'],
            'object_type' => $target['object_type'],
        ];

        if ($target['object_type'] == $this->Enum::ObjectTypePlayer) {
            if (!$this->isAttackTarget($target, $p)) {
                return;
            }

            if ($this->attacked($p, $damage, $defence, $damageWeapon) <= 0) {
                return;
            }

        } elseif ($target['object_type'] == $this->Enum::ObjectTypeMonster) {
            if (!$this->Monster->isAttackTarget($target, $p)) {
                return;
            }

            if ($this->Monster->attacked($p, $damage, $defence, $damageWeapon) <= 0) {
                return;
            }
        }

        foreach ($p['magics'] as $k => $magic) {
            switch ($magic['spell']) {
                case $magic['spell'] == $this->Enum::SpellFencing || $magic['spell'] == $this->Enum::SpellSpiritSword:
                    $this->Magic->levelMagic($p, $magic);
                    break;

            }
        }
    }

    public function broadcastDamageIndicator($object, $type, $damage)
    {
        switch ($object['object_type']) {
            case $this->Enum::ObjectTypePlayer:
                $msg = $this->MsgFactory->damageIndicator($damage, $type, $object['id']);
                $this->SendMsg->send($object['fd'], $msg);
                $this->broadcast($object, $msg);
                break;

            case $this->Enum::ObjectTypeMonster:
                $this->Monster->broadcastDamageIndicator($object, $type, $damage);
                break;
        }
    }

    //获取防御值
    public function getDefencePower($min, $max)
    {
        if ($min < 0) {
            $min = 0;
        }

        if ($min > $max) {
            $max = $min;
        }

        return rand($min, $max + 1);
    }

    public function attacked(&$attacker, $damage, $type, $damageWeapon)
    {
        $armour = 0;

        $target = $this->getTarget($attacker);

        switch ($attacker['object_type']) {
            case $this->Enum::ObjectTypePlayer:
                # code...
                break;

            case $this->Enum::ObjectTypeMonster:
                switch ($type) {
                    case $this->Enum::DefenceTypeACAgility:
                        if (rand(0, $target['agility'] + 1) > $attacker['accuracy']) {
                            $this->broadcastDamageIndicator($target, $this->Enum::DamageTypeMiss, 0);
                            return 0;
                        }

                        $armour = $this->getDefencePower($target['min_ac'], $target['max_ac']);
                        break;

                    case $this->Enum::DefenceTypeAC:
                        $armour = $this->getDefencePower($target['min_ac'], $target['max_ac']);
                        break;

                    case $this->Enum::DefenceTypeMACAgility:

                        if (rand(0, $this->Settings::MagicResistWeight) < $target['magic_resist']) {
                            $this->broadcastDamageIndicator($target, $this->Enum::DamageTypeMiss, 0);
                            return 0;
                        }

                        if (rand(0, $target['agility'] + 1) > $attacker['accuracy']) {
                            return 0;
                        }

                        $armour = $this->getDefencePower($target['min_ac'], $target['max_ac']);
                        break;

                    case $this->Enum::DefenceTypeMAC:

                        if (rand(0, $this->Settings::MagicResistWeight) < $target['magic_resist']) {
                            $this->broadcastDamageIndicator($target, $this->Enum::DamageTypeMiss, 0);
                            return 0;
                        }

                        $armour = $this->getDefencePower($target['min_ac'], $target['max_ac']);
                        break;

                    case $this->Enum::DefenceTypeAgility:

                        if (rand(0, $target['agility'] + 1) > $attacker['accuracy']) {
                            EchoLog('玩家攻击防御类型敏捷');
                            EchoLog(sprintf('被攻击者敏捷: %s  攻击者精准: %s', $target['agility'], $attacker['accuracy']), 'w');

                            $this->broadcastDamageIndicator($target, $this->Enum::DamageTypeMiss, 0);
                            return 0;
                        }
                        break;
                }

                if (rand(0, 100) < $target['reflect']) {
                    if ($target['object_type'] == $this->Enum::ObjectTypePlayer) {
                        if ($this->isAttackTarget($target, $p)) {
                            $this->attacked($attacker, $damage, $type, false);
                            $this->broadcast($target, $this->MsgFactory->objectEffect($attacker['id'], $this->Enum::SpellEffectReflect, 0, 0, 0));
                        }

                    } elseif ($target['object_type'] == $this->Enum::ObjectTypeMonster) {
                        if ($this->Monster->isAttackTarget($target, $p)) {
                            $this->Monster->attacked($attacker, $damage, $type, false);
                            $this->Monster->broadcast($target, $this->MsgFactory->objectEffect($attacker['id'], $this->Enum::SpellEffectReflect, 0, 0, 0));
                        }
                    }

                    return 0;
                }

                $armour = $armour * $target['armour_rate'];
                $damage = $damage * $target['damage_rate'];

                if ($target['magic_shield']) {
                    $damage -= $damage * ($target['magic_shield_lv'] + 2) / 10;
                }

                if ($armour >= $damage) {
                    EchoLog(sprintf('被攻击者: %s  盔甲: %s  伤害: %s', $target['name'], $armour, $damage), 'w');
                    $this->broadcastDamageIndicator($target, $this->Enum::DamageTypeMiss, 0);
                    return 0;
                }

                $this->SendMsg->send($target['fd'], $this->MsgFactory->struck($attacker['fd']));
                $this->broadcast($target, $this->MsgFactory->objectStruck($target, $attacker['id']));
                $p['struck_time'] = time() + 5;

                EchoLog(sprintf('被攻击者:%s  盔甲: %s  伤害: %s 失血: %s', $target['name'], $armour, $damage, intval($armour - $damage)), 'w');
                EchoLog(sprintf('被攻击者:%s  血值: %s  魔法值: %s  最大血值: %s  最大魔法值: %s', $target['name'], $target['hp'], $target['mp'], $target['max_hp'], $target['max_mp']), 'w');

                $this->broadcastDamageIndicator($target, $this->Enum::DamageTypeHit, intval($armour - $damage));
                $this->changeHP($target, intval($armour - $damage));

                return intval($armour - $damage);
                break;
        }

        return 0;
    }

    //获取攻击值
    public function getAttackPower($min, $max)
    {
        if ($min < 0) {
            $min = 0;
        }

        if ($min > $max) {
            $max = $min;
        }

        return rand($min, $max);
    }

    //玩家获取经验
    public function winExp(&$p, $amount, $targetLevel)
    {
        $expPoint = 0;
        $level    = $p['level'];

        if ($level < $targetLevel + 10) {
            $expPoint = $amount;
        } else {
            $expPoint = $amount - intval(round(max($amount / 15, 1) * ($level - ($targetLevel + 10))));
        }

        if ($expPoint <= 0) {
            $expPoint = 1;
        }

        $this->gainExp($p, $expPoint);
    }

    public function gainExp(&$p, $amount)
    {
        $p['experience'] += (int) $amount;

        $this->SendMsg->send($p['fd'], $this->MsgFactory->gainExperience($amount));

        EchoLog(sprintf('玩家:%s  获取经验: %s  当前经验: %s 当前等级最大经验: %s 当前百分比: %s', $p['name'], $amount, $p['experience'], $p['max_experience'],intval($p['experience']/$p['max_experience'])*100), 'w');

        if ($p['experience'] < $p['max_experience']) {
            return;
        }

        //连续升级
        $exp = $p['experience'];

        for ($i = $exp; $i >= $p['max_experience'];) {
            $p['level']++;
            $exp -= $p['max_experience'];
            $this->refreshStats($p);
        }

        $p['experience'] = $exp;

        $this->PlayersList->saveData($p['fd'], $p);

        $this->levelUp($p);
    }

    //提升等级
    public function levelUp(&$p)
    {
        EchoLog(sprintf('玩家:%s  升级成功! 当前等级: %s', $p['name'], $p['level']), 'w');

        $this->refreshStats($p);
        $this->setHp($p, $p['max_hp']);
        $this->setMp($p, $p['max_mp']);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->levelChanged($p['level'], $p['experience'], $p['max_experience']));
        $this->broadcast($p, $this->MsgFactory->objectLeveled($p['id']));

        $this->PlayersList->saveData($p['fd'], $p);
    }
}
