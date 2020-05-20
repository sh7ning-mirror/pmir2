<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class PlayerObject extends AbstractController
{
    public $playerInfo = [
        'fd'                 => null,
        'AccountID'          => null, //账户id
        'GameStage'          => null, //游戏状态
        'MapObject'          => null, //地图信息
        'HP'                 => null, //血量
        'MP'                 => null, //魔法值
        'Level'              => null, //等级
        'Experience'         => null, //经验值
        'MaxExperience'      => null, //最大经验值
        'Gold'               => null, //金币
        'GuildName'          => null, //工会名称
        'GuildRankName'      => null, //公会等级名称
        'Class'              => null, //职业
        'Gender'             => null, //性别
        'Hair'               => null, //发型
        'Light'              => null,
        'Inventory'          => null,
        'Equipment'          => null,
        'QuestInventory'     => null, //任务清单
        'Storage'            => null,
        'Trade'              => null,
        'Refine'             => null,
        'LooksArmour'        => null, //衣服外观
        'LooksWings'         => null, //翅膀外观
        'LooksWeapon'        => null, //武器外观
        'LooksWeaponEffect'  => null, //武器特效
        'SendItemInfo'       => null,
        'CurrentBagWeight'   => null,
        'MaxHP'              => null, //最大血值
        'MaxMP'              => null, //最大魔法值
        'MinAC'              => null, // 物理防御力
        'MaxAC'              => null,
        'MinMAC'             => null, // 魔法防御力
        'MaxMAC'             => null,
        'MinDC'              => null, // 攻击力
        'MaxDC'              => null,
        'MinMC'              => null, // 魔法力
        'MaxMC'              => null,
        'MinSC'              => null, // 道术力
        'MaxSC'              => null,
        'Accuracy'           => null, //精准度
        'Agility'            => null, //敏捷
        'CriticalRate'       => null,
        'CriticalDamage'     => null,
        'MaxBagWeight'       => null, //Other Stats'=>null,
        'MaxWearWeight'      => null,
        'MaxHandWeight'      => null,
        'ASpeed'             => null,
        'Luck'               => null,
        'LifeOnHit'          => null,
        'HpDrainRate'        => null,
        'Reflect'            => null, // TODO
        'MagicResist'        => null,
        'PoisonResist'       => null,
        'HealthRecovery'     => null,
        'SpellRecovery'      => null,
        'PoisonRecovery'     => null,
        'Holy'               => null,
        'Freezing'           => null,
        'PoisonAttack'       => null,
        'ExpRateOffset'      => null,
        'ItemDropRateOffset' => null,
        'MineRate'           => null,
        'GemRate'            => null,
        'FishRate'           => null,
        'CraftRate'          => null,
        'GoldDropRateOffset' => null,
        'AttackBonus'        => null,
        'Magics'             => null,
        'ActionList'         => null,
        'PoisonList'         => null,
        'BuffList'           => null,
        'Health'             => null, // 状态恢复
        'Pets'               => null,
        'PKPoints'           => null,
        'AMode'              => null,
        'PMode'              => null,
        'CallingNPC'         => null,
        'CallingNPCPage'     => null,
        'Slaying'            => null, // TODO
        'FlamingSword'       => null, // TODO
        'TwinDrakeBlade'     => null, // TODO
        'BindMapIndex'       => null, // 绑定的地图 死亡时复活用
        'BindLocation'       => null, // 绑定的坐标 死亡时复活用
        'MagicShield'        => null, // TODO 是否有魔法盾
        'MagicShieldLv'      => null, // TODO 魔法盾等级
        'ArmourRate'         => null, // 防御
        'DamageRate'         => null, // 伤害
        'StruckTime'         => null, // 被攻击硬直时间
        'AllowGroup'         => null, // 是否允许组队
        'GroupMembers'       => null, // 小队成员
        'GroupInvitation'    => null, // 组队邀请人
        'CurrentDirection'   => null,
        'CurrentLocation'    => null,
        'Characters'         => null,
    ];

    public function getPlayer($fd)
    {
        return json_decode($this->Redis->get('player:_' . getClientId($fd)), true);
    }

    public function getIdPlayer($id)
    {
        return json_decode($this->Redis->get('player:character_id_' . $id), true);
    }

    public function setPlayer($fd, $data = null)
    {
        co(function () use ($fd, $data) {
            $this->Redis->set('player:_' . getClientId($fd), json_encode($data, JSON_UNESCAPED_UNICODE));

            if (!empty($data['ID'])) {
                $this->Redis->set('player:character_id_' . $data['ID'], json_encode($data, JSON_UNESCAPED_UNICODE));
            }
        });
    }

    public function updatePlayerInfo(&$p, $accountCharacter, $user_magic)
    {
        $p['GameStage']        = $this->Enum::GAME;
        $p['ID']               = $accountCharacter['id'];
        $p['Name']             = $accountCharacter['name'];
        $p['NameColor']        = ['R' => 255, 'G' => 255, 'B' => 255];
        $p['CurrentDirection'] = $accountCharacter['direction'];
        $p['CurrentLocation']  = ['X' => $accountCharacter['current_location_x'], 'Y' => $accountCharacter['current_location_y']];
        $p['BindLocation']     = ['X' => $accountCharacter['bind_location_x'], 'Y' => $accountCharacter['bind_location_y']];
        $p['BindMapIndex']     = $accountCharacter['bind_map_id'];

        $p['Inventory']      = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeInventory, 46);
        $p['Equipment']      = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeEquipment, 14);
        $p['QuestInventory'] = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeQuestInventory, 40);
        $p['Storage']        = $this->Bag->bagLoadFromDB($accountCharacter['id'], $this->Enum::UserItemTypeStorage, 80);

        $p['Dead']          = 0;
        $p['HP']            = $accountCharacter['hp'];
        $p['MP']            = $accountCharacter['mp'];
        $p['Level']         = $accountCharacter['level'];
        $p['Experience']    = $accountCharacter['experience'];
        $p['Gold']          = $accountCharacter['gold'];
        $p['GuildName']     = ''; //TODO
        $p['GuildRankName'] = ''; //TODO
        $p['Class']         = $accountCharacter['class'];
        $p['Gender']        = $accountCharacter['gender'];
        $p['Hair']          = $accountCharacter['hair'];
        $p['SendItemInfo']  = [];
        $p['MaxExperience'] = $this->GameData->getExpList($accountCharacter['level']);

        if ($user_magic) {
            foreach ($user_magic as $k => $v) {
                $user_magic[$k]['Info'] = $this->GameData->getMagicInfoByID($v['magic_id']);
            }
        }

        $p['Magics'] = $user_magic;

        $p['ActionList'] = []; //TODO
        $p['PoisonList'] = []; //TODO
        $p['BuffList']   = [];

        $health = [
            'HPPotNextTime' => time(),
            'HPPotDuration' => 1 * 60,
            'MPPotNextTime' => time(),
            'MPPotDuration' => 1 * 60,
            'HealNextTime'  => time() + 10,
            'HealDuration'  => 10 * 60,
        ];

        $p['Health'] = $this->MsgFactory->health($health);

        $p['Pets']       = [];
        $p['PKPoints']   = 0;
        $p['AMode']      = $accountCharacter['attack_mode'];
        $p['PMode']      = $accountCharacter['pet_mode'];
        $p['CallingNPC'] = null;
        $p['StruckTime'] = time();
        $p['DamageRate'] = 1.0;
        $p['ArmourRate'] = 1.0;
        $p['AllowGroup'] = $accountCharacter['allow_group'];
        $p['Pets']       = [];

        $StartItems = $this->GameData->getStartItems();

        if ($p['Level'] < 1) {
            foreach ($StartItems as $k => $v) {
                $this->gainItem($p, $v);
            }
        }
    }

    // GainItem 为玩家增加物品，增加成功返回 true
    public function gainItem(&$p, $item)
    {
        $itemInfo                  = $this->MsgFactory->newUserItem($item, $this->Atomic->newObjectID());
        $itemInfo['soul_bound_id'] = $p['ID'];

        if ($itemInfo['Info']['stack_size'] > 1) {
            foreach ($p['Inventory']['Items'] as $k => $v) {

                if (empty($v['isset']) || $itemInfo['Info']['id'] != $v['Info']['id'] || $v['count'] > $itemInfo['Info']['stack_size']) {
                    continue;
                }

                if ($itemInfo['count'] + $v['count'] <= $v['Info']['stack_size']) {

                    $p['Inventory'] = $this->Bag->setCount($p['Inventory'], $k, $v['count'] + $itemInfo['count']);

                    $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedItem($itemInfo['Info']));
                    $this->setPlayer($p['fd'], $p);
                    return true;
                }

                $p['Inventory'] = $this->Bag->setCount($p['Inventory'], $k, $v['count'] + $itemInfo['count']);
                $itemInfo['count'] -= $itemInfo['Info']['stack_size'] - $v['count'];
            }
        }

        $i = 0;
        $j = 46;

        if ($itemInfo['Info']['type'] == $this->Enum::ItemTypePotion
            || $itemInfo['Info']['type'] == $this->Enum::ItemTypeScroll
            || ($itemInfo['Info']['type'] == $this->Enum::ItemTypeScript && $itemInfo['Info']['effect'])
        ) {
            $i = 0;
            $j = 4;
        } elseif ($itemInfo['Info']['type'] == $this->Enum::ItemTypeAmulet) {
            $i = 4;
            $j = 6;
        } else {
            $i = 6;
            $j = 46;
        }

        for ($i = $i; $i < $j; $i++) {

            if ($p['Inventory']['Items'][$i]['isset'] == true) {
                continue;
            }

            $p['Inventory'] = $this->Bag->set($p['ID'], $p['Inventory'], $i, $itemInfo);
            $this->enqueueItemInfo($p, $itemInfo['item_id']);
            $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedItem($itemInfo['Info']));
            $this->refreshBagWeight($p);
            $this->setPlayer($p['fd'], $p);
            return $p;
        }

        for ($i = 0; $i < 46; $i++) {
            if ($p['Inventory']['Items'][$i]['isset'] == true) {
                continue;
            }

            $p['Inventory'] = $this->Bag->set($p['ID'], $p['Inventory'], $i, $itemInfo);
            $this->enqueueItemInfo($p, $itemInfo['item_id']);
            $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedItem($itemInfo['Info']));
            $this->refreshBagWeight($p);
            $this->setPlayer($p['fd'], $p);
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

        $p['Gold'] += $gold;

        $this->PlayersList->saveGold($p['ID'], $p['Gold']);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->gainedGold($gold));
    }

    public function receiveChat($fd, $msg, $type)
    {
        $this->SendMsg->send($fd, ['CHAT', ['Message' => $msg, 'Type' => $type]]);
    }

    public function enqueueItemInfo(&$p, $ItemID)
    {
        if ($p['SendItemInfo']) {
            foreach ($p['SendItemInfo'] as $k => $v) {
                if ($v['id'] == $ItemID) {
                    return $p;
                }
            }
        }

        $item = $this->GameData->getItemInfoByID($ItemID);

        if (!$item) {
            return false;
        }

        $this->SendMsg->send($p['fd'], ['NEW_ITEM_INFO', ['Info' => $item]]);

        $p['SendItemInfo'][] = $item;

        return true;
    }

    public function StartGame($p)
    {
        $this->receiveChat($p['fd'], '[欢迎进入游戏,游戏目前处于测试模式]', $this->Enum::ChatTypeHint);
        $this->receiveChat($p['fd'], '[本模拟器为学习研究使用,禁止一切商业行为]', $this->Enum::ChatTypeHint);
        $this->receiveChat($p['fd'], '[模拟器已经开源,其他人员非法使用与本模拟器无关]', $this->Enum::ChatTypeHint);
        $this->receiveChat($p['fd'], '[有任何意见及建议欢迎加QQ群并联系管理员,群号186510932]', $this->Enum::ChatTypeHint);

        $this->enqueueItemInfos($p);

        $this->refreshStats($p);

        $this->enqueueQuestInfo($p); //任务

        $mapInfo = $this->GameData->getMap($p['Map']['Info']['id']);

        $this->SendMsg->send($p['fd'], ['MAP_INFORMATION', $mapInfo['Info']]);

        $this->SendMsg->send($p['fd'], ['USER_INFORMATION', $this->MsgFactory->userInformation($p)]);

        $this->SendMsg->send($p['fd'], ['TIME_OF_DAY', ['Lights' => $this->Settings->lightSet()]]);

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changeAMode($p['AMode']));

        $this->SendMsg->send($p['fd'], $this->MsgFactory->changePMode($p['PMode']));

        $this->SendMsg->send($p['fd'], ['SWITCH_GROUP', ['AllowGroup' => $p['AllowGroup'] ?: 0]]);

        $this->enqueueAreaObjects($p, null, $this->getCell($mapInfo, $p['CurrentLocation']));

        $this->broadcast($p, ['OBJECT_PLAYER', $this->MsgFactory->objectPlayer($p)]);

        $this->setPlayer($p['fd'], $p);
    }

    public function stopGame($p)
    {
        $this->broadcast($p, $this->MsgFactory->objectRemove($p));
    }

    //物品
    public function enqueueItemInfos(&$p)
    {
        $itemInfos = [];

        if ($p['Inventory']['Items']) {
            foreach ($p['Inventory']['Items'] as $k => $v) {
                if ($v) {
                    $p['Inventory']['Items'][$k]['isset'] = true;
                    $itemInfos[]                          = $this->GameData->getItemInfoByID($v['item_id']);
                } else {
                    $p['Inventory']['Items'][$k]['isset'] = false;
                }

            }
        }

        if ($p['Equipment']['Items']) {
            foreach ($p['Equipment']['Items'] as $k => $v) {
                if ($v) {
                    $p['Equipment']['Items'][$k]['isset'] = true;
                    $itemInfos[]                          = $this->GameData->getItemInfoByID($v['item_id']);
                } else {
                    $p['Equipment']['Items'][$k]['isset'] = false;
                }
            }
        }

        if ($p['QuestInventory']['Items']) {
            foreach ($p['QuestInventory']['Items'] as $k => $v) {
                if ($v) {
                    $p['QuestInventory']['Items'][$k]['isset'] = true;
                    $itemInfos[]                               = $this->GameData->getItemInfoByID($v['item_id']);
                } else {
                    $p['QuestInventory']['Items'][$k]['isset'] = false;
                }
            }
        }

        if ($itemInfos) {
            foreach ($itemInfos as $k => $v) {
                $this->enqueueItemInfo($p, $v['id']);
            }
        }

        return $p;
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
        $baseStats = $this->Settings->getBaseStats($p['Class']); //职业属性

        $p['Accuracy']       = $baseStats['StartAccuracy'];
        $p['Agility']        = $baseStats['StartAgility'];
        $p['CriticalRate']   = $baseStats['StartCriticalRate'];
        $p['CriticalDamage'] = $baseStats['StartCriticalDamage'];

        $ExpList = $this->GameData->getExpList();

        if ($p['Level'] < count($ExpList)) {
            $p['MaxExperience'] = $ExpList[$p['Level'] - 1];
        } else {
            $p['MaxExperience'] = 0;
        }

        $p['MaxHP'] = intval(14 + ($p['Level'] / $baseStats['HpGain'] + $baseStats['HpGainRate']) * $p['Level']);

        $p['MinAC'] = 0;
        if ($baseStats['MinAc'] > 0) {
            $p['MinAC'] = intval($p['Level'] / $baseStats['MinAc']);
        }

        $p['MaxAC'] = 0;
        if ($baseStats['MaxAc'] > 0) {
            $p['MaxAC'] = intval($p['Level'] / $baseStats['MaxAc']);
        }

        $p['MinMAC'] = 0;
        if ($baseStats['MinMac'] > 0) {
            $p['MinMAC'] = intval($p['Level'] / $baseStats['MinMac']);
        }

        $p['MaxMAC'] = 0;
        if ($baseStats['MaxMac'] > 0) {
            $p['MaxMAC'] = intval($p['Level'] / $baseStats['MaxMac']);
        }

        $p['MinDC'] = 0;
        if ($baseStats['MinDc'] > 0) {
            $p['MinDC'] = intval($p['Level'] / $baseStats['MinDc']);
        }

        $p['MaxDC'] = 0;
        if ($baseStats['MaxDc'] > 0) {
            $p['MaxDC'] = intval($p['Level'] / $baseStats['MaxDc']);
        }

        $p['MinMC'] = 0;
        if ($baseStats['MinMc'] > 0) {
            $p['MinMC'] = intval($p['Level'] / $baseStats['MinMc']);
        }

        $p['MaxMC'] = 0;
        if ($baseStats['MaxMc'] > 0) {
            $p['MaxMC'] = intval($p['Level'] / $baseStats['MaxMc']);
        }

        $p['MinSC'] = 0;
        if ($baseStats['MinSc'] > 0) {
            $p['MinSC'] = intval($p['Level'] / $baseStats['MinSc']);
        }

        $p['MaxSC'] = 0;
        if ($baseStats['MaxSc'] > 0) {
            $p['MaxSC'] = intval($p['Level'] / $baseStats['MaxSc']);
        }

        $p['CriticalRate'] = 0;
        if ($baseStats['CritialRateGain'] > 0) {
            $p['CriticalRate'] = intval($p['CriticalRate'] + ($p['Level'] / $baseStats['CritialRateGain']));
        }

        $p['CriticalDamage'] = 0;
        if ($baseStats['CriticalDamageGain'] > 0) {
            $p['CriticalDamage'] = intval($p['CriticalDamage'] + ($p['Level'] / $baseStats['CriticalDamageGain']));
        }

        $p['MaxBagWeight']  = intval(50.0 + $p['Level'] / $baseStats['BagWeightGain'] * $p['Level']);
        $p['MaxWearWeight'] = intval(15.0 + $p['Level'] / $baseStats['WearWeightGain'] * $p['Level']);
        $p['MaxHandWeight'] = intval(12.0 + $p['Level'] / $baseStats['HandWeightGain'] * $p['Level']);

        switch ($p['Class']) {
            case $this->Enum::MirClassWarrior:
                $p['MaxHP'] = intval(14.0 + ($p['Level'] / $baseStats['HpGain'] + $baseStats['HpGainRate'] + $p['Level'] / 20.0) * $p['Level']);
                $p['MaxMP'] = intval(11.0 + ($p['Level'] * 3.5) + ($p['Level'] * $baseStats['MpGainRate']));
                break;

            case $this->Enum::MirClassWizard:
                $p['MaxMP'] = intval(13.0 + (($p['Level'] / 5.0 + 2.0) * 2.2 * $p['Level']) + ($p['Level'] * $baseStats['MpGainRate']));
                break;

            case $this->Enum::MirClassTaoist:
                $p['MaxMP'] = intval((13 + $p['Level'] / 8.0 * 2.2 * $p['Level']) + ($p['Level'] * $baseStats['MpGainRate']));
                break;
        }
    }

    public function refreshBagWeight(&$p)
    {
        $p['CurrentBagWeight'] = 0;

        foreach ($p['Inventory']['Items'] as $k => $v) {
            if ($v && $v['isset']) {
                $item = $this->GameData->getItemInfoByID($v['Info']['id']);
                $p['CurrentBagWeight'] += $item['weight'];
            }
        }
    }

    public function refreshEquipmentStats(&$p)
    {
        $oldLooksWeapon       = $p['LooksWeapon'];
        $oldLooksWeaponEffect = $p['LooksWeaponEffect'];
        $oldLooksArmour       = $p['LooksArmour'];
        $oldLooksWings        = $p['LooksWings'];
        $oldLight             = $p['Light'];

        $p['LooksArmour']       = 0;
        $p['LooksWeapon']       = -1;
        $p['LooksWeaponEffect'] = 0;
        $p['LooksWings']        = 0;

        $ItemInfos = $this->GameData->getItemInfos();
        foreach ($p['Equipment']['Items'] as $temp) {
            if (!$temp || !$temp['isset']) {
                continue;
            }

            $RealItem = $this->GameData->getRealItem($temp['Info'], $p['Level'], $p['Class'], $ItemInfos);

            $p['MinAC']  = toUint16(intval($p['MinAC']) + intval($RealItem['min_ac']));
            $p['MaxAC']  = toUint16(intval($p['MaxAC']) + intval($RealItem['max_ac']) + intval($temp['ac']));
            $p['MinMAC'] = toUint16(intval($p['MinMAC']) + intval($RealItem['min_mac']));
            $p['MaxMAC'] = toUint16(intval($p['MaxMAC']) + intval($RealItem['max_mac']) + intval($temp['mac']));
            $p['MinDC']  = toUint16(intval($p['MinDC']) + intval($RealItem['min_dc']));
            $p['MaxDC']  = toUint16(intval($p['MaxDC']) + intval($RealItem['max_dc']) + intval($temp['dc']));
            $p['MinMC']  = toUint16(intval($p['MinMC']) + intval($RealItem['min_mc']));
            $p['MaxMC']  = toUint16(intval($p['MaxMC']) + intval($RealItem['max_mc']) + intval($temp['mc']));
            $p['MinSC']  = toUint16(intval($p['MinSC']) + intval($RealItem['min_sc']));
            $p['MaxSC']  = toUint16(intval($p['MaxSC']) + intval($RealItem['max_sc']) + intval($temp['sc']));
            $p['MaxHP']  = toUint16(intval($p['MaxHP']) + intval($RealItem['hp']) + intval($temp['hp']));
            $p['MaxMP']  = toUint16(intval($p['MaxMP']) + intval($RealItem['mp']) + intval($temp['mp']));

            $p['MaxBagWeight']  = toUint16(intval($p['MaxBagWeight']) + intval($RealItem['bag_weight']));
            $p['MaxWearWeight'] = toUint16(intval($p['MaxWearWeight']) + intval($RealItem['wear_weight']));
            $p['MaxHandWeight'] = toUint16(intval($p['MaxHandWeight']) + intval($RealItem['hand_weight']));

            $p['ASpeed']   = toInt8(intval($p['ASpeed']) + intval($temp['attack_speed']) + intval($RealItem['attack_speed']));
            $p['Luck']     = toInt8(intval($p['Luck']) + intval($temp['luck']) + intval($RealItem['luck']));
            $p['Accuracy'] = toUint8(intval($p['Accuracy']) + intval($RealItem['accuracy']) + intval($temp['accuracy']));
            $p['Agility']  = toUint8(intval($p['Agility']) + intval($RealItem['agility']) + intval($temp['agility']));

            $p['MagicResist']    = toUint8(intval($p['MagicResist']) + intval($temp['magic_resist']) + intval($RealItem['magic_resist']));
            $p['PoisonResist']   = toUint8(intval($p['PoisonResist']) + intval($temp['poison_resist']) + intval($RealItem['poison_resist']));
            $p['HealthRecovery'] = toUint8(intval($p['HealthRecovery']) + intval($temp['health_recovery']) + intval($RealItem['health_recovery']));
            $p['SpellRecovery']  = toUint8(intval($p['SpellRecovery']) + intval($temp['mana_recovery']) + intval($RealItem['spell_recovery']));
            $p['PoisonRecovery'] = toUint8(intval($p['PoisonRecovery']) + intval($temp['poison_recovery']) + intval($RealItem['poison_recovery']));
            $p['CriticalRate']   = toUint8(intval($p['CriticalRate']) + intval($temp['critical_rate']) + intval($RealItem['critical_rate']));
            $p['CriticalDamage'] = toUint8(intval($p['CriticalDamage']) + intval($temp['critical_damage']) + intval($RealItem['critical_damage']));
            $p['Holy']           = toUint8(intval($p['Holy']) + intval($RealItem['holy']));
            $p['Freezing']       = toUint8(intval($p['Freezing']) + intval($temp['freezing']) + intval($RealItem['freezing']));
            $p['PoisonAttack']   = toUint8(intval($p['PoisonAttack']) + intval($temp['poison_attack']) + intval($RealItem['poison_attack']));
            $p['Reflect']        = toUint8(intval($p['Reflect']) + intval($RealItem['reflect']));
            $p['HpDrainRate']    = toUint8(intval($p['HpDrainRate']) + intval($RealItem['hp_drain_rate']));

            switch ($RealItem['type']) {
                case $this->Enum::ItemTypeArmour:
                    $p['LooksArmour'] = intval($RealItem['shape']);
                    $p['LooksWings']  = intval($RealItem['effect']);
                    break;

                case $this->Enum::ItemTypeWeapon:
                    $p['LooksWeapon']       = intval($RealItem['shape']);
                    $p['LooksWeaponEffect'] = intval($RealItem['effect']);
                    break;
            }
        }

        if ($oldLooksArmour != $p['LooksArmour'] || $oldLooksWeapon != $p['LooksWeapon'] || $oldLooksWeaponEffect != $p['LooksWeaponEffect'] || $oldLooksWings != $p['LooksWings'] || $oldLight != $p['Light']) {
            $this->broadcast($p, $this->getUpdateInfo($p));
        }
    }

    public function getUpdateInfo($p)
    {
        $this->updateConcentration($p);

        return [
            'PLAYER_UPDATE',
            [
                'ObjectID'     => $p['ID'],
                'Weapon'       => $p['LooksWeapon'],
                'WeaponEffect' => $p['LooksWeaponEffect'],
                'Armour'       => $p['LooksArmour'],
                'Light'        => $p['Light'],
                'WingEffect'   => $p['LooksWings'],
            ],
        ];
    }

    public function updateConcentration($p)
    {
        $this->SendMsg->send($p['fd'], ['SET_CONCENTRATION', ['ObjectID' => $p['AccountID'], 'Enabled' => 0, 'Interrupted' => 0]]);
        $this->broadcast($p, ['SET_OBJECT_CONCENTRATION', ['ObjectID' => $p['AccountID'], 'Enabled' => 0, 'Interrupted' => 0]]);
    }

    public function broadcast($p, $msg)
    {
        $this->Map->broadcastP($p['CurrentLocation'], $msg, $p);
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
        if (!empty($p['Magics'])) {
            foreach ($p['Magics'] as $k => $magic) {
                switch ($magic['Spell']) {
                    case $this->Enum::SpellFencing: // 基本剑术
                        $p['Accuracy'] = toUint8(intval($p['Accuracy']) + $magic['Level'] * 3);
                        $p['MaxAC']    = toUint16(intval($p['MaxAC']) + ($magic['Level'] + 1) * 3);
                        break;

                    case $this->Enum::SpellFatalSword: // 刺客的技能 忽略
                        break;

                    case $this->Enum::SpellSpiritSword: // 精神力战法
                        $p['Accuracy'] = toUint8(intval($p['Accuracy']) + $magic['Level']);
                        $p['MaxAC']    = toUint16(intval($p['MaxDC']) + intval($p['MaxSC'] * $magic['Level'] + 1 * 0.1));
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

    public function enqueueAreaObjects($p, $oldCell, $newCell)
    {
        if ($oldCell == null) {
            $this->Map->rangeObject($p);
        }
    }

    public function getCell($map, $CurrentLocation)
    {
        return $this->Map->getCell($map, $CurrentLocation);
    }

    //检查可以地图跳转
    public function checkMovement($point, &$p)
    {
        $movementInfos = $this->GameData->getMovementInfos();

        $maps = $this->GameData->getMap();

        foreach ($movementInfos as $k => $v) {
            if ($v['source_map'] == $p['Map']['Info']['id']) {
                if ($point['X'] == $v['source_x'] && $point['Y'] == $v['source_y']) {
                    $m = $maps[$v['destination_map']] ?? null;
                    if (!$m) {
                        EchoLog(sprintf('未知的地图ID: %s', $maps[$v['destination_map']]), 'e');
                    }

                    $this->teleport($p, $m, ['X' => $v['destination_x'], 'Y' => $v['destination_y']]);
                    return true;
                }
            }
        }

        return false;
    }

    public function teleport(&$p, $m, $point)
    {
        $oldMap = $p['Map'];

        if (!$this->Map->inMap($m, $point['X'], $point['Y'])) {
            return false;
        }

        $this->Map->deleteObject($p, $this->Enum::ObjectTypePlayer);

        $this->broadcast($p, ['OBJECT_TELEPORT_OUT', ['ObjectID' => $p['ID'], 'Type' => 0]]);
        $this->broadcast($p, $this->MsgFactory->objectRemove($p));

        $p['Map']['Info']['id'] = $m['Info']['id'];
        $p['CurrentLocation']   = $point;

        $this->Map->addObject($p, $this->Enum::ObjectTypePlayer);

        $this->broadcastInfo($p); //广播人物

        $this->broadcast($p, ['OBJECT_TELEPORT_OUT', ['ObjectID' => $p['ID'], 'Type' => 0]]);
        $this->broadcast($p, ['OBJECT_TELEPORT_IN', ['ObjectID' => $p['ID'], 'Type' => 0]]);

        $this->broadcastHealthChange($p);

        $this->SendMsg->send($p['fd'], ['MAP_CHANGED', [
            'FileName'     => $m['Info']['file_name'],
            'Title'        => $m['Info']['title'],
            'MiniMap'      => $m['Info']['mini_map'],
            'BigMap'       => $m['Info']['big_map'],
            'Lights'       => $m['Info']['light'],
            'Location'     => $p['CurrentLocation'],
            'Direction'    => $p['CurrentDirection'],
            'MapDarkLight' => $m['Info']['map_dark_light'],
            'Music'        => $m['Info']['music'],
        ]]);

        $this->enqueueAreaObjects($p, null, $this->getCell($m, $p['CurrentLocation']));

        $this->SendMsg->send($p['fd'], ['OBJECT_TELEPORT_IN', ['ObjectID' => $p['ID'], 'Type' => 0]]);
    }

    public function broadcastInfo($p)
    {
        $this->broadcast($p, ['OBJECT_PLAYER', $this->MsgFactory->objectPlayer($p)]);
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
        if ($this->Map->openDoor($p['Map']['Info']['id'], $doorIndex)) {
            $this->SendMsg->send($p['fd'], ['OPENDOOR', ['DoorIndex' => $doorIndex, 'Close' => false]]);
            $this->broadcast($p, ['OPENDOOR', ['DoorIndex' => $doorIndex, 'Close' => false]]);
        }
    }

    //获取用户物品
    public function getUserItemByID($p, $mirGridType, $id)
    {
        $Items = [];

        switch ($mirGridType) {
            case $this->Enum::MirGridTypeInventory:
                $Items = $p['Inventory']['Items'];
                break;

            case $this->Enum::MirGridTypeEquipment:
                $Items = $p['Equipment']['Items'];
                break;

            case $this->Enum::MirGridTypeStorage:
                $Items = $p['Storage']['Items'];
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

    public function callNPC1($p, $npc, $key)
    {
        $say = $this->Npc->callScript($p, $npc, $key);

        if (!$say) {
            EchoLog(sprintf('NPC脚本执行失败 ID: %s  key: %s', $npc['ID'], $key), 'w');
        }

        $p['CallingNPC']     = $npc['ID'];
        $p['CallingNPCPage'] = $key;

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

        $this->setPlayer($p['fd'], $p);
    }

    public function sendNpcGoods($p, $npc)
    {
        if (!empty($npc['Goods'])) {
            foreach ($npc['Goods'] as $key => $item) {
                $this->enqueueItemInfo($p, $item['item_id']);
            }
            $this->SendMsg->send($p['fd'], $this->MsgFactory->npcGoods($npc['Goods'], 1.0, $this->Enum::PanelTypeBuy));
        }
    }

    public function sendStorage($p, $npc)
    {
        if (!empty($p['Storage']['Items'])) {
            foreach ($p['Storage']['Items'] as $key => $item) {
                $this->enqueueItemInfo($p, $item['item_id']);
            }

            $this->SendMsg->send($p['fd'], $this->MsgFactory->userStorage($p['Storage']['Items']));
        }
    }

    public function sendBuyBackGoods($p, $npc, $syncItem)
    {
        $goods = $this->Npc->getPlayerBuyBack($p, $npc);

        if ($syncItem && $goods) {
            foreach ($goods as $key => $item) {
                $this->enqueueItemInfo($p, $item['item_id']);
            }
        }

        $this->SendMsg->send($p['fd'], $this->MsgFactory->npcGoods($goods, 1, $this->Enum::PanelTypeBuy));
    }

    public function takeGold($p, $gold)
    {
        if ($gold > $p['Gold']) {
            EchoLog(sprintf('没有足够的金币 余额: %s  需要: %s', $p['Gold'], $gold), 'w');
            $p['Gold'] = 0;
        } else {
            $p['Gold'] -= $gold;
        }

        co(function () use ($p) {
            $this->setPlayer($p['fd'], $p);

            $this->PlayersList->saveGold($p['ID'], $p['Gold']);
        });

        $this->SendMsg->send($p['fd'], $this->MsgFactory->loseGold($gold));
    }

    # 药水是消耗品，可以治愈或增强玩家的生命。
    # 常用名称形状使用的统计信息描述
    #   普通药水 0 HP / MP逐渐治愈玩家。
    #   太阳药剂 1 HP / MP立即治愈玩家。
    #   神秘水 2 没有允许玩家取消装备被诅咒的物品（仅适用于官方神秘物品）。
    #   Buff 药剂 3 DC / MC / SC / ASpeed / HP / MP / MaxAC / MaxMAC /Durability 为玩家提供相对的增益 抛光时间的长短取决于药水的耐久性。 1 dura = 1分钟。
    #   经验值 4 运气/耐力通过运气统计数据增加玩家获得的经验值百分比。 抛光时间的长短取决于药水的耐久性。 1 dura = 1分钟。
    public function userItemPotion($p, $item)
    {
        $info = $item['Info'];

        switch ($info['shape']) {
            case 0:
                # code...
                break;

            default:
                # code...
                break;
        }
    }

    public function process($p)
    {
        $p = $this->getPlayer($p['fd']);
        if(!$p || $p == 'null')
        {
            return;
        }

        $now = time();
        $this->processBuffs($p);
        $this->processPoison($p);

        $ch = &$p['Health'];

        if ($ch['HPPotValue'] != 0 && $ch['HPPotNextTime'] < $now) {
            $this->changeHp($p, $ch['HPPotPerValue']);
            $ch['HPPotTickTime'] += 1;

            if ($ch['HPPotTickTime'] >= $ch['HPPotTickNum']) {
                $ch['HPPotValue'] = 0; //回复总值
            } else {
                $ch['HPPotNextTime'] = $now; //下次生效时间
            }
        }

        if ($ch['MPPotValue'] != 0 && $ch['MPPotNextTime'] < $now) {
            $this->changeMp($p, $ch['MPPotPerValue']);
            $ch['MPPotTickTime'] += 1;

            if ($ch['MPPotTickTime'] >= $ch['MPPotTickNum']) {
                $ch['MPPotValue'] = 0; //回复总值
            } else {
                $ch['MPPotNextTime'] = $now; //下次生效时间
            }
        }

        if ($ch['HealNextTime'] < $now) {
            $ch['HealNextTime'] = $now;

            $this->changeHp($p, intval($p['MaxHP'] * 0.03) + 1);
            $this->changeMp($p, intval($p['MaxMP'] * 0.03) + 1);
        }

        $this->setPlayer($p['fd'], $p);
    }

    public function processBuffs($p)
    {

    }

    public function processPoison($p)
    {

    }

    public function changeHp(&$p, $amount)
    {
        if ($amount == 0 || $p['Dead'] || $p['HP'] >= $p['MaxHP']) {
            return false;
        }

        $hp = intval($p['HP'] + $amount);

        if ($hp <= 0) {
            $hp = 0;
        }

        $this->setHp($p, $hp);
    }

    public function changeMp(&$p, $amount)
    {
        if ($amount == 0 || $p['Dead'] || $p['MP'] >= $p['MaxMP']) {
            return;
        }

        $mp = intval($p['MP'] + $amount);

        if ($mp <= 0) {
            $mp = 0;
        }

        $this->setMp($p, $mp);
    }

    public function setHp(&$p, $amount)
    {
        if ($p['HP'] == $amount) {
            return;
        }

        if ($amount >= $p['MaxHP']) {
            $amount = $p['MaxHP'];
        }

        $p['HP'] = $amount;

        if (!$p['Dead'] && $p['HP'] == 0) {
            $this->die($p);
        }

        $this->SendMsg->send($p['fd'], $this->MsgFactory->healthChanged($p));

        $this->broadcastHealthChange($p);
    }

    public function setMp(&$p, $amount)
    {
        if ($p['MP'] == $amount) {
            return false;
        }

        $p['MP'] = $amount;

        $this->SendMsg->send($p['fd'], $this->MsgFactory->healthChanged($p));

        $this->broadcastHealthChange($p);
    }

    function die(&$p) {
        $p['HP']   = 0;
        $p['Dead'] = true;

        $this->SendMsg->send($p['fd'], $this->MsgFactory->death($p));
        $this->broadcast($p, $this->MsgFactory->objectDied($p));

        // $this->callDefaultNPC($p, $this->Enum::DefaultNPCTypeDie);
    }

    public function callDefaultNPC(&$p, $calltype, ...$args)
    {
        $key = '';
        switch ($calltype) {
            case $this->Enum::DefaultNPCTypeDie:
                $key = 'UseItem('.$args[0].')';
                break;
        }

        $key = '[@_'.$key.']';
    }

    // func (p *Player) CallDefaultNPC(calltype DefaultNPCType, args ...interface{}) {
    //     var key string

    //     switch calltype {
    //     case DefaultNPCTypeUseItem:
    //         key = fmt.Sprintf("UseItem(%v)", args[0])
    //     }

    //     key = fmt.Sprintf("[@_%s]", key)

    //     p.ActionList.PushAction(DelayedTypeNPC, func() {
    //         p.CallNPC1(env.DefaultNPC, key)
    //     })

    //     p.Enqueue(&server.NPCUpdate{NPCID: env.DefaultNPC.GetID()})
    // }

}
