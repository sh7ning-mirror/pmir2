<?php
namespace app;

/**
 * opcode映射
 */
class Reflection
{
    private static $mapOpcode = [
        // 账户相关
        'CM_ADDNEWUSER'      => ['app\Auth\User', 'AddNewUser'],
        'CM_CHANGEPASSWORD'  => ['app\Auth\User', 'ChangePassWord'],
        'CM_IDPASSWORD'      => ['app\Auth\User', 'UserLogin'],
        'CM_GETBACKPASSWORD' => ['app\Auth\User', 'GetBackPassWord'],
        'CM_SELECTSERVER'    => ['app\Auth\User', 'SelectServer'],

        //角色相关
        'CM_QUERYCHR'        => ['app\Auth\Character', 'QueryCharacter'],
        'CM_NEWCHR'          => ['app\Auth\Character', 'NewCharacter'],
        'CM_DELCHR'          => ['app\Auth\Character', 'DeleteCharacter'],
        'CM_QUERYDELCHR'     => ['app\Auth\Character', 'QueryDeleteCharacter'],
        'CM_RESDELCHR'       => ['app\Auth\Character', 'RestoreDeleteCharacter'],
        'CM_SELCHR'          => ['app\Auth\Character', 'SelectCharacter'],

    ];

    public static function LoadClass($opcode, $serv, $fd, $data = null, $mapOpcode = null)
    {
        if (!$mapOpcode) {
            $mapOpcode = self::$mapOpcode;
        }

        if (isset($mapOpcode[$opcode]) && $mapinfo = $mapOpcode[$opcode]) {
            if (is_array($mapinfo[0])) {
                foreach ($mapinfo as $k => $v) {
                    self::LoadFunc($v[0], $v[1], $serv, $fd, $data);
                }
            } else {
                self::LoadFunc($mapinfo[0], $mapinfo[1], $serv, $fd, $data);
            }
        } else {
            WORLD_LOG('Unknown opcode: ' . $opcode . ' Client : ' . $fd, 'warning');
        }
    }

    public static function LoadFunc($class, $func, $serv, $fd, $data)
    {
        $classObject = new \ReflectionMethod($class, $func);
        if ($classObject->isStatic()) {
            if ($packdata = $classObject->invokeArgs(null, [$serv, $fd, $data])) {
                Reflection::serversend($serv, $fd, $packdata);
            }
        } else {
            if ($packdata = $classObject->invokeArgs(new $class, [$serv, $fd, $data])) {
                Reflection::serversend($serv, $fd, $packdata);
            }
        }
    }

    public function serversend($serv, $fd, $packdata = null)
    {
        $packdata = '#' . ToStr($packdata) . '!';

        if (env('MSG_DEBUG', false)) {
            WORLD_LOG("Send: " . $packdata, 'info');
        }

        $serv->send($fd, $packdata);
    }
}
