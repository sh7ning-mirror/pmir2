<?php
namespace App\Controller;

use Hyperf\Utils\ApplicationContext;

/**
 *
 */
class ObjectService
{
    private static $objectRegister = [
        'Process'       => 'App\Controller\GameProcess',
        'NpcData'       => 'App\Controller\Process\NpcData',
        'CellData'      => 'App\Controller\Process\CellData',
        'GameShopData'  => 'App\Controller\Process\GameShopData',
        'ItemData'      => 'App\Controller\Process\ItemData',
        'MagicData'     => 'App\Controller\Process\MagicData',
        'MonsterData'   => 'App\Controller\Process\MonsterData',
        'MovementData'  => 'App\Controller\Process\MovementData',
        'NpcData'       => 'App\Controller\Process\NpcData',
        'QuestData'     => 'App\Controller\Process\QuestData',
        'RespawnData'   => 'App\Controller\Process\RespawnData',
        'SafeZoneData'  => 'App\Controller\Process\SafeZoneData',
        'MapData'       => 'App\Controller\Process\MapData',
        'ExpListData'   => 'App\Controller\Process\ExpListData',
        'AtomicData'    => 'App\Controller\Process\AtomicData',
        'PlayerData'    => 'App\Controller\Process\PlayerData',
        'Server'        => 'Swoole\Server',
        'Redis'         => 'Hyperf\Redis\Redis',
        'MsgRegister'   => 'App\Controller\MsgRegister',
        'Enum'          => 'App\Controller\Common\Enum',
        'CodeMap'       => 'App\Controller\Packet\CodeMap',
        'CodePacket'    => 'App\Controller\Packet\CodePacket',
        'BinaryReader'  => 'App\Controller\Packet\BinaryReader',
        'CommonService' => 'App\Service\Common\CommonService',
        'Character'     => 'App\Controller\Game\Character',
        'GameData'      => 'App\Controller\Game\GameData',
        'Map'           => 'App\Controller\Game\Map',
        'PlayerObject'  => 'App\Controller\Game\PlayerObject',
        'PlayersList'   => 'App\Controller\Game\PlayersList',
        'MapLoader'     => 'App\Controller\Game\MapLoader',
        'Point'         => 'App\Controller\Game\Point',
        'Door'          => 'App\Controller\Game\Door',
        'Cell'          => 'App\Controller\Game\Cell',
        'Bag'           => 'App\Controller\Game\Bag',
        'Checksystem'   => 'App\Libs\Checksystem',
        'Handler'       => 'App\Controller\World\Handler',
        'AuthHandler'   => 'App\Controller\Auth\AuthHandler',
        'Settings'      => 'App\Controller\Game\Settings',
        'SendMsg'       => 'App\Controller\SendMsg',
        'MsgFactory'    => 'App\Controller\Game\MsgFactory',
        'MapObject'     => 'App\Controller\Game\MapObject',
        'Npc'           => 'App\Controller\Game\Npc',
        'Script'        => 'App\Controller\Game\Script\Script',
        'Context'       => 'App\Controller\Game\Script\Context',
        'NpcScript'     => 'App\Controller\Game\Script\NpcScript',
        'Atomic'        => 'App\Controller\Common\Atomic',
        'Util'          => 'App\Controller\Game\Util\Util',
        'Item'          => 'App\Controller\Game\Item',
        'Buff'          => 'App\Controller\Game\Buff',
        'Respawn'       => 'App\Controller\Game\Respawn',
        'Monster'       => 'App\Controller\Game\Monster',
        'GameObject'    => 'App\Controller\Game\GameObject',
        'Event'         => 'App\Controller\Game\Loop\Event',
        'Behavior'      => 'App\Controller\Game\Ai\Behavior',
        'Brains'        => 'App\Controller\Game\Ai\Brains',
        'Magic'         => 'App\Controller\Game\Magic',
    ];

    public static function getObject($objectName = null)
    {
        return !empty(self::$objectRegister[$objectName]) ? ApplicationContext::getContainer()->get(self::$objectRegister[$objectName]) : null;
    }
}
