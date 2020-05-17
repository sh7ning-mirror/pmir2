<?php
namespace App\Controller\Game\Script;

/**
 *
 */
class Context
{
    public $defaultContext;

    public $ActionType = [
        'MOVE',
        'INSTANCEMOVE',
        'GIVEGOLD',
        'TAKEGOLD',
        'GIVEGUILDGOLD',
        'TAKEGUILDGOLD',
        'GIVECREDIT',
        'TAKECREDIT',
        'GIVEITEM',
        'TAKEITEM',
        'GIVEEXP',
        'GIVEPET',
        'CLEARPETS',
        'ADDNAMELIST',
        'DELNAMELIST',
        'CLEARNAMELIST',
        'GIVEHP',
        'GIVEMP',
        'CHANGELEVEL',
        'SETPKPOINT',
        'REDUCEPKPOINT',
        'INCREASEPKPOINT',
        'CHANGEGENDER',
        'CHANGECLASS',
        'LOCALMESSAGE',
        'GOTO',
        'GIVESKILL',
        'REMOVESKILL',
        'SET',
        'PARAM1',
        'PARAM2',
        'PARAM3',
        'MONGEN',
        'TIMERECALL',
        'TIMERECALLGROUP',
        'BREAKTIMERECALL',
        'MONCLEAR',
        'GROUPRECALL',
        'GROUPTELEPORT',
        'DELAYGOTO',
        'MOV',
        'CALC',
        'GIVEBUFF',
        'REMOVEBUFF',
        'ADDTOGUILD',
        'REMOVEFROMGUILD',
        'REFRESHEFFECTS',
        'CHANGEHAIR',
        'CANGAINEXP',
        'COMPOSEMAIL',
        'ADDMAILITEM',
        'ADDMAILGOLD',
        'SENDMAIL',
        'GROUPGOTO',
        'ENTERMAP',
        'GIVEPEARLS',
        'TAKEPEARLS',
        'MAKEWEDDINGRING',
        'FORCEDIVORCE',
        'GLOBALMESSAGE',
        'LOADVALUE',
        'SAVEVALUE',
        'REMOVEPET',
        'CONQUESTGUARD',
        'CONQUESTGATE',
        'CONQUESTWALL',
        'CONQUESTSIEGE',
        'TAKECONQUESTGOLD',
        'SETCONQUESTRATE',
        'STARTCONQUEST',
        'SCHEDULECONQUEST',
        'OPENGATE',
        'CLOSEGATE',
        'BREAK',
        'ADDGUILDNAMELIST',
        'DELGUILDNAMELIST',
        'CLEARGUILDNAMELIST',
        'LINEMESSAGE',
        'REMOVENAMELIST',
        'CLOSE',
    ];

    public $CheckType = [
        'ISADMIN',
        'LEVEL',
        'CHECKITEM',
        'CHECKGOLD',
        'CHECKGUILDGOLD',
        'CHECKCREDIT',
        'CHECKGENDER',
        'CHECKCLASS',
        'CHECKDAY',
        'CHECKHOUR',
        'CHECKMINUTE',
        'CHECKNAMELIST',
        'CHECKPKPOINT',
        'CHECKRANGE',
        'CHECK',
        'CHECKHUM',
        'CHECKMON',
        'CHECKEXACTMON',
        'RANDOM',
        'GROUPLEADER',
        'GROUPCOUNT',
        'GROUPCHECKNEARBY',
        'PETLEVEL',
        'PETCOUNT',
        'CHECKCALC',
        'INGUILD',
        'CHECKMAP',
        'CHECKQUEST',
        'CHECKRELATIONSHIP',
        'CHECKWEDDINGRING',
        'CHECKPET',
        'HASBAGSPACE',
        'ISNEWHUMAN',
        'CHECKCONQUEST',
        'AFFORDGUARD',
        'AFFORDGATE',
        'AFFORDWALL',
        'AFFORDSIEGE',
        'CHECKPERMISSION',
        'CONQUESTAVAILABLE',
        'CONQUESTOWNER',
        'CHECKGUILDNAMELIST',
        'CHECKBUFF',
        'CHECK',
        'DAYOFWEEK',
        'CHECKLEVEL'
    ];

    public $argParser = [
        'Fun'  => null,
        'Skip' => false,
    ];

    public $scriptFunc = [
        'Name'       => '',
        'Func'       => '',
        'ArgsParser' => [],
        'OptionArgs' => [],
    ];

    public function __construct()
    {
        $this->defaultContext = $this->newContext();
    }

    public function newContext()
    {
        $data = [
            'Checks'  => $this->checks(array_merge($this->scriptFunc, ['ArgsParser' => $this->argParser])),
            'Actions' => $this->action(array_merge($this->scriptFunc, ['ArgsParser' => $this->argParser])),
            'parsers' => $this->argParser,
        ];

        return $data;
    }

    public function checks($param)
    {
        $data = [];
        foreach ($this->CheckType as $k => $v)
        {
            $param['Func'] = $v;
            $param['ArgsParser']['Fun'] = $v;
            $data[$v] = $param;
        }

        return $data;
    }

    public function action($param)
    {
        $data = [];
        foreach ($this->ActionType as $k => $v)
        {
            $param['Func'] = $v;
            $param['ArgsParser']['Fun'] = $v;
            $data[$v] = $param;
        }

        return $data;
    }
}
