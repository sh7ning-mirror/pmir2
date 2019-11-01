<?php
namespace app\Auth;

use app\Packet\PacketHandler;
use app\Packet\ServerState;
use app\Server;
use core\query\DB;

/**
 *  英雄角色
 */
class Character
{
    //查询角色
    public static function QueryCharacter($serv, $fd, $data = null)
    {
        $param = gbktoutf8(ToStr($data));

        $UserInfo = [
            'username',
            'cert',
        ];

        PacketHandler::GetValidStr3($param, $UserInfo, '/');

        $where = [
            'username' => $UserInfo['username'],
        ];

        if ($info = DB::table('users')->where($where)->find()) {
            if ($UserInfo['cert'] == $info['cert']) {

                Server::$clientparam[$fd]['UserInfo'] = $info; //缓存数据

                $where = [
                    'user_id' => $info['id'],
                ];

                if ($PlayerList = DB::table('players')->where($where)->select()) {
                    $body = '';
                    foreach ($PlayerList as $k => $v) {
                        $body .= utf8togbk($v['name']) . '/' . $v['job'] . '/' . $v['hair'] . '/' . $v['level'] . '/' . $v['gender'].'/';
                    }

                    $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_QUERYCHR, count($PlayerList), 0, 0, 0);
                    return array_merge(PacketHandler::Encode($EncodeHeader), PacketHandler::Encode($body));
                } else {
                    $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_QUERYCHR_FAIL, ServerState::RoleNotFound, 0, 0, 0);
                }
            } else {
                $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_QUERYCHR_FAIL, ServerState::CertError, 0, 0, 0);
            }
        } else {
            $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_QUERYCHR_FAIL, ServerState::IdNotFound, 0, 0, 0);
        }

        return PacketHandler::Encode($EncodeHeader);
    }

    //创建
    public static function NewCharacter($serv, $fd, $data = null)
    {
        //男战士 test/超人/2/0/0
        //女战士 test/超人/3/0/1
        //男法师 test/超人/2/1/0
        //女法师 test/超人/3/1/1
        //男道士 test/超人/2/2/0
        //女道士 test/超人/3/2/1

        $param = ToStr($data);
        $param = gbktoutf8($param);

        $CharacterInfo = [
            'username',
            'name',
            'hair',
            'job',
            'gender',
        ];

        PacketHandler::GetValidStr3($param, $CharacterInfo, '/');

        unset($CharacterInfo['username']);

        if (empty(Server::$clientparam[$fd]['UserInfo']['id'])) {
            $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_NEWCHR_FAIL, ServerState::SystemErr, 0, 0, 0);
            return PacketHandler::Encode($EncodeHeader);
        }

        $CharacterInfo['user_id'] = Server::$clientparam[$fd]['UserInfo']['id'];
        $CharacterInfo['level']   = 1;

        if (!$CharacterInfo['name']) {
            $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_NEWCHR_FAIL, ServerState::WrongName, 0, 0, 0);
            return PacketHandler::Encode($EncodeHeader);
        }

        $where = [
            'name' => $CharacterInfo['name'],
        ];

        if (DB::table('players')->where($where)->find()) {
            $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_NEWCHR_FAIL, ServerState::NameExist, 0, 0, 0);
        } else {
            if (DB::table('players')->insert($CharacterInfo)) {
                $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_NEWCHR_SUCCESS, 0, 0, 0, 0);
            } else {
                $EncodeHeader = PacketHandler::PacketHeader(ServerState::SM_NEWCHR_FAIL, ServerState::SystemErr, 0, 0, 0);
            }
        }

        return PacketHandler::Encode($EncodeHeader);
    }

    //删除
    public static function DeleteCharacter($serv, $fd, $data = null)
    {

    }

    //查询删除过的角色信息
    public static function QueryDeleteCharacter($serv, $fd, $data = null)
    {
    	
    }

    //恢复删除的角色
    public static function RestoreDeleteCharacter($serv, $fd, $data = null)
    {
    	
    }

    //选择角色进入游戏
    public static function SelectCharacter($serv, $fd, $data = null)
    {
    	
    }
    
}
