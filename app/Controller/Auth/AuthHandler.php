<?php
declare (strict_types = 1);

namespace App\Controller\Auth;

use App\Controller\AbstractController;

/**
 *
 */
class AuthHandler extends AbstractController
{
    public function keepAlive($fd, $param)
    {
        $data = [
            'time' => $param['res']['time'],
        ];

        return ['KEEP_ALIVE', $data];
    }

    /**
     * 0:版本错误, 请升级游戏客户端.\n游戏即将关闭
     * 1:版本验证成功
     */
    public function clientVersion($fd, $param = [])
    {
        co(function () use ($fd) {

            $p              = $this->PlayerObject->playerInfo;
            $p['game_stage'] = $this->Enum::LOGIN;
            $p['fd']        = $fd;

            $this->PlayerObject->setPlayer($fd, $p);
        });

        return ['CLIENT_VERSION', ['result' => 1]];
    }

    /**
     * 0：服务器暂时不允许创建新账号。
     * 1：账号错误。
     * 2：密码错误。
     * 3：邮件错误。
     * 4：用户名错误。
     * 5：密码提示问题错误。
     * 6：密码提示答案错误。
     * 7：这个账号已经存在。
     * 8：你的账号创建成功。
     */
    public function newAccount($fd, $param = [])
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::LOGIN) {
            return [];
        }

        $data = [
            'account'   => $param['res']['account'],
            'password'  => $param['res']['password'],
            'birth_day' => $param['res']['date_time'],
            'username'  => $param['res']['user_name'],
            'questions' => $param['res']['secret_question'],
            'answers'   => $param['res']['secret_answer'],
            'mail'      => $param['res']['email_address'],
        ];

        $where = [
            'whereInfo' => [
                'where' => [
                    ['account', '=', $param['res']['account']],
                ],
            ],
        ];

        $res = $this->CommonService->getOne('account', $where);

        $Result = 0;
        if ($res['code'] != 2000) {
            $res = $this->CommonService->save('account', $data);

            if ($res['code'] == 2000) {
                $Result = 8;
            }
        } else {
            $Result = 7;
        }

        return ['NEW_ACCOUNT', ['result' => $Result]];
    }

    /*
     * 0：服务器暂时不允许修改密码。
     * 1：账号错误。
     * 2：当前密码错误。
     * 3：新密码错误。
     * 4：账号不存在。
     * 5：不正确的账号密码组合。
     * 6：你的密码修改成功。
     */
    public function changePassword($fd, $param = [])
    {
        $p = $this->PlayerObject->getPlayer($fd);
        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::LOGIN) {
            return [];
        }

        $where = [
            'whereInfo' => [
                'where' => [
                    ['account', '=', $param['res']['account']],
                ],
            ],
        ];

        $res = $this->CommonService->getOne('account', $where);

        if ($res['code'] == 2000) {
            if ($res['data']['password'] == $param['res']['current_password']) {
                $data = [
                    'password' => $param['res']['new_password'],
                ];

                $res = $this->CommonService->upField('account', $where, $data);

                if ($res['code'] == 2000) {
                    $Result = 6;
                }
            } else {
                $Result = 2;
            }
        } else {
            $Result = 4;
        }

        return ['CHANGE_PASSWORD', ['result' => $Result]];
    }

    /*
     * 0：服务器暂时不允许登录。
     * 1：账号错误。
     * 2：密码错误。
     * 3：账号不存在。
     * 4：不正确的账号密码组合。
     */
    public function login($fd, $param = [])
    {
        $p = $this->PlayerObject->getPlayer($fd);

        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::LOGIN) {
            return ['LOGIN', ['result' => 0]];
        }

        $where = [
            'whereInfo' => [
                'where' => [
                    ['account', '=', $param['res']['account']],
                ],
            ],
        ];

        $res = $this->CommonService->getOne('account', $where);

        if ($res['code'] == 2000) {
            if ($res['data']['password'] == $param['res']['password']) {

                $Characters = $this->Character->getAccountCharacters($param['res']['account']);

                co(function () use ($fd, $where, $res, $Characters, $p, $param) {
                    $data = [
                        'login_date' => date('Y-m-d H:i:s'),
                        'login_ip'   => $this->Server->getClientInfo($fd)['remote_ip'],
                    ];

                    $this->CommonService->upField('account', $where, $data);

                    $p['account_id']  = $res['data']['id'];
                    $p['account']    = $param['res']['account'];
                    $p['game_stage']  = $this->Enum::SELECT;
                    $p['characters'] = $Characters;

                    $this->PlayerObject->setPlayer($fd, $p);
                });

                return ['LOGIN_SUCCESS', ['count' => count($Characters), 'characters' => $Characters]];
            } else {
                $Result = 2;
            }
        } else {
            $Result = 1;
        }

        return ['LOGIN', ['result' => $Result]];
    }

    /**
     * 0:服务器暂时不允许创建新角色。
     * 1:角色名不可用。
     * 2:你选择的性别不存在.\n 请联系GM处理。
     * 3:你选择的职业不存在.\n 请联系GM处理。
     * 4:你不能创建超过多少角色
     * 5:这个角色名已存在。
     */
    public function newCharacter($fd, $param = [])
    {
        $p = $this->PlayerObject->getPlayer($fd);

        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::SELECT) {
            return [];
        }

        if (count($p['characters']) >= $this->Enum::AccountCharacter) {
            return ['NEW_CHARACTER', ['result' => 4]];
        }

        $where = [
            'whereInfo' => [
                'where' => [
                    ['name', '=', $param['res']['name']],
                    ['isdel', '=', 1],
                ],
            ],
        ];

        $res = $this->CommonService->getOne('character', $where);

        if ($res['code'] != 2000) {
            $data = [
                'name'   => $param['res']['name'],
                'gender' => $param['res']['gender'],
                'class'  => $param['res']['class'],
            ];

            //获取角色基础数据
            $characterBase = $this->Character->characterBase($param['res']['name'], $param['res']['class'], $param['res']['gender']);

            $res = $this->CommonService->save('character', $characterBase);
            if ($res['code'] == 2000) {

                $data = [
                    'account_id'   => $p['account_id'],
                    'character_id' => $res['data']['id'],
                ];

                $res = $this->CommonService->save('account_character', $data);
                if ($res['code'] == 2000) {
                    $CharInfo = [
                        'index'      => $data['character_id'],
                        'name'       => $param['res']['name'],
                        'level'      => $characterBase['level'],
                        'class'      => $param['res']['class'],
                        'gender'     => $param['res']['gender'],
                        'last_access' => 0,
                    ];

                    $p['characters'][] = $CharInfo;

                    co(function () use ($fd, $p, $data) {
                        $this->PlayerObject->setPlayer($fd, $p);

                        //初始化新手装备
                        $startItems = $this->GameData->getStartItems();

                        foreach ($startItems as $k => $v) {
                            $info = [
                                'item_id'         => $v['id'],
                                'current_dura'    => 100,
                                'max_dura'        => 100,
                                'count'           => 1,
                                'ac'              => $v['min_ac'],
                                'mac'             => $v['min_mac'],
                                'dc'              => $v['min_dc'],
                                'mc'              => $v['min_mc'],
                                'sc'              => $v['min_sc'],
                                'accuracy'        => $v['accuracy'],
                                'agility'         => $v['agility'],
                                'hp'              => $v['hp'],
                                'mp'              => $v['mp'],
                                'attack_speed'    => $v['attack_speed'],
                                'luck'            => $v['luck'],
                                'soul_bound_id'   => $data['character_id'],
                                'bools'           => $v['bools'],
                                'strong'          => $v['strong'],
                                'magic_resist'    => $v['magic_resist'],
                                'poison_resist'   => $v['poison_resist'],
                                'health_recovery' => $v['health_recovery'],
                                'mana_recovery'   => 0,
                                'poison_recovery' => $v['poison_recovery'],
                                'critical_rate'   => $v['critical_rate'],
                                'critical_damage' => $v['critical_damage'],
                                'freezing'        => $v['freezing'],
                                'poison_attack'   => $v['poison_attack'],
                            ];

                            $res = $this->CommonService->save('user_item', $info);

                            if ($res['code'] == 2000) {
                                $info = [
                                    'character_id' => $data['character_id'],
                                    'user_item_id' => $res['data']['id'],
                                    'type'         => $this->Enum::UserItemTypeInventory,
                                    'index'        => $k,
                                ];

                                $this->CommonService->save('character_user_item', $info);
                            }
                        }
                    });

                    return ['NEW_CHARACTER_SUCCESS', ['char_info' => [$CharInfo]]];
                }
            }
        } else {
            $Result = 5;
        }

        return ['NEW_CHARACTER', ['result' => $Result]];
    }

    /**
     * 0:服务器暂时不允许删除角色。
     * 1:你选择的角色不存在.\n 请联系GM处理
     */
    public function deleteCharacter($fd, $param = [])
    {
        $p = $this->PlayerObject->getPlayer($fd);

        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::SELECT) {
            return [];
        }

        $temp = false;

        foreach ($p['characters'] as $k => $v) {
            if ($v['Index'] == $param['res']['character_index']) {
                $temp = true;
                unset($p['characters'][$k]);
                break;
            }
        }

        if (!$temp) {
            return ['NEW_CHARACTER', ['result' => $Result]];
        }

        $where = [
            'whereInfo' => [
                'where' => [
                    ['id', '=', $param['res']['character_index']],
                ],
            ],
        ];

        $data = [
            'isdel' => 2,
        ];

        $res = $this->CommonService->upField('character', $where, $data);

        if ($res['code'] == 2000) {

            co(function () use ($fd, $p) {
                $this->PlayerObject->setPlayer($fd, $p);
            });

            return ['DELETE_CHARACTER_SUCCESS', ['character_index' => $param['res']['character_index']]];
        }

        return ['NEW_CHARACTER', ['result' => 0]];
    }

    /**
     * 0:服务器暂时不允许进入游戏。
     * 1:你没有登录。
     * 2:你的角色无法找到。
     * 3:找不到有效的地图/游戏起始点。
     * 4:成功
     */
    public function startGame($fd, $param = [])
    {
        $p = $this->PlayerObject->getPlayer($fd);

        if (!$p || !isset($p['game_stage']) || $p['game_stage'] != $this->Enum::SELECT) {
            return ['START_GAME', ['result' => 0, 'resolution' => $this->Enum::AllowedResolution]];
        }

        if (!$this->Enum::AllowStartGame) {
            return ['START_GAME', ['result' => 0, 'resolution' => $this->Enum::AllowedResolution]];
        }

        if (!$p['account_id'] || !$p['characters']) {
            return ['START_GAME', ['result' => 1, 'resolution' => $this->Enum::AllowedResolution]];
        }

        $temp = false;

        foreach ($p['characters'] as $k => $v) {
            if ($v['index'] == $param['res']['character_index']) {
                $temp = true;
                break;
            }
        }

        if (!$temp) {
            return ['START_GAME', ['result' => 2, 'resolution' => $this->Enum::AllowedResolution]];
        }

        $where = [
            'whereInfo' => [
                'where' => [
                    ['a.account_id', '=', $p['account_id']],
                    ['b.id', '=', $param['res']['character_index']],
                    ['b.isdel', '=', 1],
                ],
            ],
            'field'     => [
                'b.*',
            ],
            'join'      => [
                ['inner', 'character as b', 'b.id', '=', 'a.character_id'],
            ],
            'pageInfo'  => false,
        ];

        $accountCharacter = $this->CommonService->getOne('account_character as a', $where);
        if ($accountCharacter['code'] != 2000) {
            return ['START_GAME', ['result' => 2, 'resolution' => $this->Enum::AllowedResolution]];
        }

        $where = [
            'whereInfo' => [
                'where' => [
                    ['character_id', '=', $param['res']['character_index']],
                ],
            ],
        ];

        $user_magic = $this->CommonService->getList('user_magic', $where);

        $this->SendMsg->send($fd, ['SET_CONCENTRATION', ['object_id' => $p['account_id'], 'enabled' => 0, 'interrupted' => 0]]);

        $this->SendMsg->send($fd, ['START_GAME', ['result' => 4, 'resolution' => $this->Enum::AllowedResolution]]);

        $this->PlayerObject->updatePlayerInfo($p, $accountCharacter['data'], $user_magic['list']);

        $this->PlayerObject->setPlayer($fd, $p);

        EchoLog(sprintf('玩家登陆: 账户ID(%s) 角色名(%s)', $p['account_id'], $p['name']), 'i');

        // $p['Map'] = $this->GameData->getMap($accountCharacter['data']['current_map_id']);
        $p['map']['info']['id'] = $accountCharacter['data']['current_map_id'];

        $this->PlayersList->addPlayersList($p);

        $this->Map->addObject($p, $this->Enum::ObjectTypePlayer);

        $this->PlayerObject->StartGame($p);
    }
}
