<?php
namespace App\Controller\Game\Ai;

use App\Controller\AbstractController;
use App\Controller\Game\Ai\Action;
use App\Controller\Game\Ai\RunStatus;
use App\Controller\Game\Ai\Sequence;
use App\Controller\Game\Ai\TreeRoot;

/**
 *
 */
class Brains extends AbstractController
{
    //默认攻击模式
    public function defaultBrain($info)
    {
        $tr = new TreeRoot();
        $p  = new Sequence(
            $this->attackWall($info),
            $this->chaseAndAttack($info),
            $this->wander($info)
        );
        $tr->AddHook('defaultBrain', $p);
        $tr->RebuildTree();

        return $tr;
    }

    //鸡,猪,牛,鹿,羊等行为模式
    public function deerBrain($info)
    {
        $tr = new TreeRoot();
        $p  = new Sequence(
            $this->chaseAndAttack($info),
            $this->wander($info)
        );
        $tr->AddHook('deerBrain', $p);
        $tr->RebuildTree();

        return $tr;
    }

    public function treeBrain($info)
    {
        # code...
    }

    //大刀侍卫
    public function guardBrain($info)
    {
        $tr = new TreeRoot();
        $p  = new Sequence(
            $this->findMonsterInViewRange($info),
            $this->guardAttack($info)
        );
        $tr->AddHook('deerBrain', $p);
        $tr->RebuildTree();

        return $tr;
    }

    //吐丝蜘蛛
    public function spittingSpiderBrain($info)
    {
        # code...
    }

    //弓箭守卫
    public function townArcherBrain($info)
    {
        $tr = new TreeRoot();
        $p  = new Sequence(
            $this->findPlayerByPKPoints200($info),
            $this->watchAndShoot($info)
        );
        $tr->AddHook('deerBrain', $p);
        $tr->RebuildTree();

        return $tr;
    }

    //警戒
    public function attackWall($info)
    {
        return new Action(function () use ($info) {

            if (!$info) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);

            $this->Monster->findTarget($monster);

            if (empty($monster['target'])) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            if ($this->Monster->canAttack($monster) && $this->Monster->inAttackRange($monster)) {
                $this->Monster->attack($monster);

                return RunStatus::SUCCESS;
            } else {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }
        });
    }

    //追杀
    public function chaseAndAttack($info)
    {
        return new Action(function () use ($info) {
            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);

            //判断是否有目标(有前置行为节点,但保险起见还是判断一下)
            if (!empty($monster['target'])) {

                $target = $this->Monster->getTarget($monster);

                if (!empty($target['dead'])) {
                    //目标已死亡,清除目标
                    $monster['target'] = false;
                    $this->GameData->updateMapMonster($info['mapId'], $info['id'], $monster, ['target']);

                    return RunStatus::SUCCESS;
                }

                //判断是否在攻击范围内
                if ($this->Monster->inAttackRange($monster)) {
                    if ($this->Monster->canAttack($monster)) {
                        $this->Monster->attack($monster);
                    }

                    return RunStatus::SUCCESS;
                    return RunStatus::FAILURE;
                } else {
                    $this->Monster->moveTo($monster, $target['current_location']);
                    return RunStatus::SUCCESS;
                    return RunStatus::FAILURE;
                }
            }

            return RunStatus::SUCCESS;
        });
    }

    //守卫追杀
    public function guardAttack($info)
    {
        return new Action(function () use ($info) {
            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);

            //判断是否有目标(有前置行为节点,但保险起见还是判断一下)
            if (!empty($monster['target'])) {

                $target = $this->Monster->getTarget($monster);

                if (!empty($target['dead'])) {
                    //目标已死亡,清除目标
                    $monster['target'] = false;
                    $this->GameData->updateMapMonster($info['mapId'], $info['id'], $monster, ['target']);

                    return RunStatus::SUCCESS;
                }

                if ($this->Monster->canAttack($monster)) {
                    $this->Monster->guardAttack($monster);
                }

                return RunStatus::SUCCESS;
            }

            return RunStatus::SUCCESS;
        });
    }

    //判断是否有目标
    public function hasTarget($info)
    {
        return new Action(function () use ($info) {
            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);

            if (!empty($monster['target'])) {
                return RunStatus::SUCCESS;
            } else {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }
        });
    }

    //游荡(巡逻)
    public function wander($info)
    {
        return new Action(function () use ($info) {
            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);
            if (time() >= $monster['move_time'] && rand(0, 20) < 3) {

                $direction = randomDirection($this->Enum::MirDirectionCount);

                //更新怪物移动时间及方向
                $monster['move_time'] = time() + 3;
                $monster['direction'] = $direction;

                switch (rand(0, 3)) {
                    case 0:
                        $this->Monster->turn($monster);
                        break;

                    default:
                        $this->Monster->walk($monster);
                        break;
                }
            }

            return RunStatus::SUCCESS;
        });
    }

    //在范围内寻找怪物(弓箭手及大刀侍卫)
    public function findMonsterInViewRange($info)
    {
        return new Action(function () use ($info) {
            if (!$info) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);

            $this->Monster->findTarget($monster);

            if (empty($monster['target'])) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            return RunStatus::SUCCESS;
        });
    }

    //寻找pk点达到200的玩家
    public function findPlayerByPKPoints200($info)
    {
        return new Action(function () use ($info) {
            if (!$info) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);

            $this->Monster->findTargetPKPoints($monster);

            if (empty($monster['target'])) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            return RunStatus::SUCCESS;
        });
    }

    public function watchAndShoot($info)
    {
        return new Action(function () use ($info) {
            if (!$info) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            $monster = $this->GameData->getMapMonsterById($info['mapId'], $info['id']);

            if (empty($monster['target'])) {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            $target = $this->Monster->getTarget($monster);

            if (!empty($target['dead'])) {
                //目标已死亡,清除目标
                $monster['target'] = false;
                $this->GameData->updateMapmonster($monster['map']['id'], $monster['id'], $monster, ['target']);
                return RunStatus::SUCCESS;
            }

            if ($this->Monster->canAttack($monster)) {

                if ($this->Point->inRange($monster['current_location'], $target['current_location'], 10)) {
                    return RunStatus::SUCCESS;
                    return RunStatus::FAILURE;
                }

                $monster['direction'] = $this->Point->directionFromPoint($monster['current_location'], $target['current_location']);

                $this->Monster->broadcast($monster, $this->MsgFactory->objectRangeAttack($monster, $target, $this->Enum::SpellNone, 0));

                $monster['attack_time'] = time() + $monster['attack_speed'] * 1000;

                $this->GameData->updateMapMonster($monster['map']['id'], $monster['id'], $monster, ['direction', 'attack_time']);

                return RunStatus::SUCCESS;
            } else {
                return RunStatus::SUCCESS;
                return RunStatus::FAILURE;
            }

            return RunStatus::SUCCESS;
        });
    }
}
