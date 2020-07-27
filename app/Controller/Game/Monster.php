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
        $monsterObject = [
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
            'poison_list'      => [],
            'current_poison'   => $this->Enum::PoisonTypeNone,
            'master'           => '',
        ];

        return $monsterObject;
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

    //怪物帧
    public function process($monster)
    {
        if (!empty($monster['target'])) {
            $target = $this->getTarget($monster);

            if ($monster['map']['id'] != $target['map']['id'] ||
                !$this->isAttackTarget($target, $monster) ||
                $this->Point->inRange($target['current_location'], $monster['current_location'], $this->Map->DataRange)) {
                unset($monster['target']);
                $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);
            }
        }

        //尸体死亡消失时间
        if (!empty($monster['dead']) && $monster['dead_time'] <= time()) {
            //删除格子对象
            $this->Map->deleteObject($monster, $this->Enum::ObjectTypeMonster);

            //同步删除
            $this->broadcast($monster, $this->MsgFactory->objectRemove($monster));
            return;
        }

        //经验所有者
        if(!empty($monster['exp_owner']) && $monster['exp_owner_time'] <= time())
        {
            $monster['exp_owner'] = null;
        }

        //执行AI
        co(function () use ($monster) {
            //获取AI行为树
            $behavior = $this->Behavior->behavior($monster['ai'], ['id' => $monster['id'], 'mapId' => $monster['map']['id']]);

            if (!empty($behavior['root'])) {
                $behavior['root']->Start();
            }
        });

        $this->processBuffs($monster);
        $this->processRegan($monster);
        $this->processPoison($monster);
    }

    //处理怪物增益效果
    public function processBuffs($monster)
    {
        # code...
    }

    //怪物自身回血
    public function processRegan($monster)
    {
        # code...
    }

    //处理怪物中毒效果
    public function processPoison($monster)
    {
        if (!empty($monster['dead'])) {
            return;
        }
    }

    //寻找攻击目标
    public function findTarget(&$monster)
    {
        //如果有攻击目标则返回
        if (!empty($monster['target'])) {
            return true;
        }

        $this->Map->rangeObject($monster, $monster['current_location'], $monster['view_range'], function ($monster, $object) {
            if ($object['id'] == $monster['id'] && !empty($object['object_type'])) {
                return true;
            }

            switch ($object['object_type']) {
                case $this->Enum::ObjectTypeMonster:

                    if (!$this->isAttackTarget($object, $monster)) {
                        return true;
                    }

                    //更新攻击对象
                    $monster['target'] = [
                        'id'          => $object['id'],
                        'mapId'       => $object['map']['id'],
                        'object_type' => $object['object_type'],
                    ];

                    $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);

                    break;

                case $this->Enum::ObjectTypePlayer:

                    if (!$this->PlayerObject->isAttackTarget($object, $monster)) {
                        return true;
                    }

                    //更新攻击对象
                    $monster['target'] = [
                        'id'          => $object['id'],
                        'mapId'       => $object['map']['id'],
                        'object_type' => $object['object_type'],
                    ];

                    $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);

                    break;
            }

            return true;
        });

        //更新信息
        $monster = $this->GameData->getMapMonsterById($monster['map']['id'], $monster['id']);
    }

    //寻找红名
    public function findTargetPKPoints(&$monster)
    {
        $this->Map->rangeObject($monster, $monster['current_location'], $monster['view_range'], function ($monster, $object) {
            if ($object['id'] == $monster['id'] && !empty($object['object_type'])) {
                return true;
            }

            if ($object['object_type'] != $this->Enum::ObjectTypePlayer) {
                return true;
            }

            if (!$this->PlayerObject->isAttackTarget($object, $monster)) {
                return true;
            }

            if ($object['pk_points'] < 200) {
                return true;
            }

            //更新攻击对象
            $monster['target'] = [
                'id'          => $object['id'],
                'mapId'       => $object['map']['id'],
                'object_type' => $object['object_type'],
            ];

            $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);

            return true;
        });

        //更新信息
        $monster = $this->GameData->getMapMonsterById($monster['map']['id'], $monster['id']);
    }

    //是否可以作为攻击目标
    public function isAttackTarget($object, $attacker)
    {
        if (empty($attacker['ai']) || empty($object['ai'])) {
            return true;
        }

        if ($object['ai'] == 6 || $object['ai'] == 58) {
            return false;
        }

        if ($attacker['ai'] == 6) {
            if ($object['ai'] != 1 && $object['ai'] != 2 && $object['ai'] != 3) {
                return true;
            }
        } elseif ($attacker['ai'] == 58) {
            if ($object['ai'] != 1 && $object['ai'] != 2 && $object['ai'] != 3) {
                return true;
            }
        }

        return false;
    }

    //是否在怪物攻击范围内
    public function inAttackRange($monster)
    {
        $target = $this->getTarget($monster);

        if ($monster['map']['id'] != $target['map']['id']) {
            return false;
        }

        return !$this->Point->equal($monster['current_location'], $target['current_location']) && $this->Point->inRange($target['current_location'], $monster['current_location'], 1);
    }

    //是否可以攻击
    public function canAttack($monster)
    {
        return !$monster['dead'];
    }

    //移动
    public function moveTo($monster, $location)
    {
        if ($this->Point->equal($monster['current_location'], $location)) {
            return;
        }

        if ($this->Point->inRange($location, $monster['current_location'], 1) == false) {
            // return;
        }

        $dir = $this->Point->directionFromPoint($monster['current_location'], $location);

        $monster['direction'] = $dir;
        if ($this->walk($monster)) {
            return;
        }
    }

    public function turn($monster)
    {
        $this->broadcast($monster, ['OBJECT_TURN', $this->MsgFactory->objectWalk($monster)]);
    }

    public function walk($monster)
    {
        $point = $this->Point->NextPoint($monster['current_location'], $monster['direction'], 1);

        $oldpos = $monster['current_location'];

        //删除对象
        $this->Map->deleteObject($monster, $this->Enum::ObjectTypeMonster);

        $monster['current_location'] = $point;

        //加入对象
        $this->Map->addObject($monster, $this->Enum::ObjectTypeMonster);

        $this->walkNotify($monster, $oldpos, $point);

        $this->broadcast($monster, ['OBJECT_WALK', $this->MsgFactory->objectWalk($monster)]);

        return true;
    }

    //怪物同步
    public function walkNotify($monster, $oldCell, $newCell)
    {
        $cells = $this->Map->calcDiff($monster['map'], $oldCell, $newCell);

        foreach ($cells as $cellId => $idAdd) {
            if ($idAdd) {
                co(function () use ($monster, $cellId) {
                    $newObjects = $this->GameData->getCellObject($monster['map']['info']['id'], $cellId);
                    if ($newObjects) {
                        foreach ($newObjects as $k => $v) {
                            if ($v['object_type'] == $this->Enum::ObjectTypePlayer) {
                                co(function () use ($monster, $v) {
                                    $this->SendMsg->send($v['fd'], $this->MsgFactory->object($monster));
                                });
                            }
                        }
                    }
                });
            } else {
                co(function () use ($monster, $cellId) {
                    $oldObjects = $this->GameData->getCellObject($monster['map']['info']['id'], $cellId);
                    if ($oldObjects) {
                        foreach ($oldObjects as $k => $v) {
                            if ($v['object_type'] == $this->Enum::ObjectTypePlayer) {
                                co(function () use ($monster, $v) {
                                    $this->SendMsg->send($v['fd'], $this->MsgFactory->objectRemove($monster));
                                });
                            }
                        }
                    }
                });
            }
        }
    }

    public function getTarget($monster)
    {
        switch ($monster['target']['object_type']) {
            case $this->Enum::ObjectTypeMonster:
                $target = $this->GameData->getMapMonsterById($monster['target']['mapId'], $monster['target']['id']);
                break;

            case $this->Enum::ObjectTypePlayer:
                $target = $this->GameData->getMapPlayerInfo($monster['target']['mapId'], $monster['target']['id']);
                break;
        }

        return $target;
    }

    //守卫攻击
    public function guardAttack($monster)
    {
        if (empty($monster['target'])) {
            return false;
        }

        $target = $this->getTarget($monster);

        if (!$target) {
            $monster['target'] = false;

            $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);

            return false;
        }

        if (!$this->isAttackTarget($target, $monster)) {
            return true;
        }

        $point = $this->Point->NextPoint($target['current_location'], $target['direction'], 1);

        $dir = $this->Point->directionFromPoint($point, $target['current_location']);

        $newMonster                     = $monster;
        $newMonster['current_location'] = $point;
        $newMonster['direction']        = $dir;

        $this->broadcast($monster, $this->MsgFactory->objectAttack($newMonster, $this->Enum::SpellNone, 0, 0));

        $this->broadcast($monster, ['OBJECT_TURN', $this->MsgFactory->objectTurn($monster)]);

        $monster['attack_time'] = time() + $monster['attack_speed'] * 1000;

        $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['attack_time']);

        //计算伤害
        $damage = $this->getAttackPower($monster['min_dc'], $monster['max_dc']);

        if ($target['object_type'] == $this->Enum::ObjectTypePlayer) {
            $damage = 9999;
        }

        if ($damage <= 0) {
            return;
        }

        $this->attacked($monster, $damage, $this->Enum::DefenceTypeAgility, false);
    }

    //承受伟大魔神的怒火吧 干死丫的
    public function attack($monster)
    {
        if (empty($monster['target'])) {
            return false;
        }

        $target = $this->getTarget($monster);

        if (!$target) {
            $monster['target'] = false;

            $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);

            return false;
        }

        if (!$this->isAttackTarget($target, $monster)) {
            return true;
        }

        if ($target['object_type'] == $this->Enum::ObjectTypePlayer && $target['game_stage'] != $this->Enum::GAME) {
            EchoLog(sprintf('攻击者:%s 失去目标:%s ', $monster['name'], $target['name']), 'w');

            $monster['target'] = false;

            $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);

            return false;
        }

        EchoLog(sprintf('攻击者:%s AI:%s 目标:%s', $monster['name'], $monster['ai'], $target['name']), 'i');

        $monster['direction'] = $this->Point->directionFromPoint($monster['current_location'], $target['current_location']);

        $this->broadcast($monster, $this->MsgFactory->objectAttack($monster, $this->Enum::SpellNone, 0, 0));

        $monster['attack_time'] = time() + $monster['attack_speed'];

        $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['direction', 'attack_time']);

        //计算伤害
        $damage = $this->getAttackPower($monster['min_dc'], $monster['max_dc']);

        if ($damage <= 0) {
            return;
        }

        $this->attacked($monster, $damage, $this->Enum::DefenceTypeAgility, false);
    }

    //被攻击了
    public function attacked(&$attacker, $damage, $defenceType, $damageWeapon)
    {
        $monster = $this->getTarget($attacker);

        if (empty($monster['target']) && $this->isAttackTarget($attacker, $monster)) {
            $monster['target'] = [
                'id'          => $attacker['id'],
                'mapId'       => $attacker['map']['id'],
                'object_type' => $attacker['object_type'],
            ];

            $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['target']);
        }

        $armour = 0;

        switch ($defenceType) {
            case $this->Enum::DefenceTypeACAgility:

                if (rand(0, $monster['agility']) > $attacker['accuracy']) {
                    $this->broadcastDamageIndicator($monster, $this->Enum::DamageTypeMiss, 0);
                    return false;
                }

                $armour = $this->getDefencePower($monster['min_ac'], $monster['max_ac']);

                break;

            case $this->Enum::DefenceTypeMACAgility:

                if (rand(0, $monster['agility']) > $attacker['accuracy']) {
                    $this->broadcastDamageIndicator($monster, $this->Enum::DamageTypeMiss, 0);
                    return false;
                }

                $armour = $this->getDefencePower($monster['min_mac'], $monster['max_mac']);

                break;

            case $this->Enum::DefenceTypeMAC:

                $armour = $this->getDefencePower($monster['min_mac'], $monster['max_mac']);

                break;

            case $this->Enum::DefenceTypeAgility:
                if (rand(0, $monster['agility']) > $attacker['accuracy']) {
                    $this->broadcastDamageIndicator($monster, $this->Enum::DamageTypeMiss, 0);
                    return false;
                }

                break;
        }

        $armour = $armour * $monster['armour_rate'];
        $damage = $damage * $monster['damage_rate'];
        $value  = (int) $damage - $armour;

        EchoLog(sprintf('攻击者伤害:%s 被攻击者防御:%s 真实伤害值:%s', $damage, $armour, $value), 'w');

        if ($value <= 0) {
            $this->broadcastDamageIndicator($monster, $this->Enum::DamageTypeMiss, 0);
            return false;
        }

        switch ($attacker['object_type']) {
            case $this->Enum::ObjectTypeMonster:

                if ($attacker['ai'] == 6 || $attacker['ai'] == 58) {
                    $monster['exp_owner'] = null;
                } elseif (!empty($attacker['master'])) {
                    $master = $this->getTarget($monster);
                    if ($attacker['map']['id'] != $master['map']['id'] || !$this->Point->inRange($attacker['current_location'], $master['current_location'], $this->Map->DataRange)) {
                        $monster['exp_owner'] = null;
                    } else {
                        $exp_owner = $this->getTarget($monster);

                        if (!$exp_owner || !empty($exp_owner['dead'])) {
                            $monster['exp_owner'] = $master;
                        }

                        if ($monster['exp_owner']['id'] == $master['id']) {
                            $monster['exp_owner_time'] = time() + 5;
                        }
                    }
                }

                break;

            case $this->Enum::ObjectTypePlayer:

                if (empty($monster['exp_owner']) || !empty($exp_owner['dead'])) {
                    $monster['exp_owner'] = $attacker;
                }

                if ($monster['exp_owner']['id'] == $attacker['id']) {
                    $monster['exp_owner_time'] = time() + 5;
                }

                break;
        }

        $this->broadcast($monster, $this->MsgFactory->objectStruck($monster, $attacker['id']));

        $this->broadcastDamageIndicator($monster, $this->Enum::DamageTypeHit, $value);

        $this->changeHP($monster, -$value);

        if (!empty($monster['dead'])) {
            $attacker['target'] = false;
            $this->GameData->updateMapMonster($attacker['map']['id'], $attacker['id'], $attacker, [
                'target',
                'exp_owner',
                'exp_owner_time',
            ]);
        }
    }

    //改变怪物血量
    public function changeHP(&$monster, $amount)
    {
        if (!empty($monster['dead'])) {
            return;
        }

        $value = $monster['hp'] + $amount;

        if ($value == $monster['hp']) {
            return;
        }

        if ($value <= 0) {
            $this->die($monster);
            $monster['hp'] = 0;
            $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['hp', 'dead', 'dead_time']);
        } else {
            $monster['hp'] = $value;
        }

        $percent = (int) ($monster['hp'] / $monster['max_hp'] * 100);

        EchoLog(sprintf('怪物血值:%s 最大血值:%s 百分比:%s', $monster['hp'], $monster['max_hp'], $percent), 'w');

        $this->broadcast($monster, $this->MsgFactory->objectHealth($monster['id'], $percent, 5));

        $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['hp', 'dead', 'dead_time']);
    }

    //死亡
    function die(&$monster) {
        if (!empty($monster['dead'])) {
            return;
        }

        $monster['hp']        = 0;
        $monster['dead']      = true;
        $monster['dead_time'] = time() + config('dead_time');

        $this->broadcast($monster, $this->MsgFactory->objectDied($monster['id'], $monster['direction'], $monster['current_location']));

        //获取经验
        if (!empty($monster['exp_owner']) && empty($monster['master'])) {
            $exp_owner = $this->getTarget($monster);
            if ($exp_owner['object_type'] == $this->Enum::ObjectTypePlayer) {
                $p = &$this->PlayerData::$players[$exp_owner['fd']];
                $this->PlayerObject->winExp($p, $monster['experience'], $monster['level']);
            }
        }

        //怪物掉落
        $this->drop($monster);
    }

    //怪物掉落
    public function drop($monster)
    {
        $dropInfos = $this->GameData->getDrop($monster['name']);

        if (!$dropInfos) {
            return;
        }

        $mapItems = [];

        foreach ($dropInfos as $drop) {

            if (!empty($drop['quest_required'])) {
                continue;
            }

            if (rand(0, $drop['high']) > $drop['low']) {
                continue;
            }

            if ($drop['item_name'] == 'Gold') {
                $mapItems[] = $this->Item->newGold($monster['map'], $drop['count']);
                continue;
            }

            $info = $this->GameData->getItemInfosName($drop['item_name']);

            if (!$info) {
                continue;
            }

            $mapItems[] = $this->Item->newItem($monster['map'], $this->MsgFactory->newUserItem($info, $this->Atomic->newObjectID()));
        }

        if ($mapItems) {
            foreach ($mapItems as $k => $item) {
                $dropMsg = $this->Item->drop($item, $monster['current_location'], 3);

                if (!$dropMsg) {
                    EchoLog(sprintf('掉落错误:%s', $dropMsg), 'w');
                }
            }
        }
    }

    public function broadcastDamageIndicator($monster, $type, $damage)
    {
        $this->broadcast($monster, $this->MsgFactory->damageIndicator($damage, $type, $monster['id']));
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

        return rand($min, $max + 1);
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

        return rand($min, $max);
    }
}
