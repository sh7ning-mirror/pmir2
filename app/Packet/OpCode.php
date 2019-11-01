<?php
namespace app\Packet;

/**
 * 操作码
 */
class OpCode
{
    // 账户登录相关
    const CM_PROTOCOL        = 2000;
    const CM_IDPASSWORD      = 2001; //登录 客户端向服务器发送ID和密码
    const CM_IDPASSWORD_2    = 22001;
    const CM_ADDNEWUSER      = 2002; //注册
    const CM_CHANGEPASSWORD  = 2003; //修改密码
    const CM_UPDATEUSER      = 2004; // 更新注册资料
    const CM_RANDOMCODE      = 2006; // 取验证码 20080612
    const CM_GETBACKPASSWORD = 2010; // 密码找回

    const Unknown5001 = 5001; //未知

    // 角色相关
    const CM_QUERYCHR     = 100; //查询角色
    const CM_NEWCHR       = 101; //新增角色
    const CM_DELCHR       = 102; //删除角色
    const CM_SELCHR       = 103; //选择角色进入游戏
    const CM_SELECTSERVER = 104; //选择服务器
    const CM_QUERYDELCHR  = 105; // 查询删除过的角色信息 20080706
    const CM_RESDELCHR    = 106; // 恢复删除的角色 20080706

    public static $OpCodeMap;

    //加载操作码
    public static function LoadOpCode()
    {
        //获取类的所有常量
        $objClass = new \ReflectionClass(new self);
        $arrConst = $objClass->getConstants();

        $OpCodeList = [];
        foreach ($arrConst as $k => $v) {
            $OpCodeList[$v] = $k;
        }

        WORLD_LOG('Load Opcode Success , The total number: ' . count($OpCodeList), 'success');

        self::$OpCodeMap = $OpCodeList;
    }

    //获取操作码
    public static function GetOpCode($OpCode, $fd)
    {
        $OpCodeName = isset(self::$OpCodeMap[$OpCode]) ? self::$OpCodeMap[$OpCode] : false;

        if ($OpCodeName) {
            AUTH_LOG('[' . $OpCodeName . '] Client : ' . $fd, 'warning');
        }

        return $OpCodeName;
    }
}
