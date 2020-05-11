<?php
namespace App\Controller\Game;

/**
 *
 */
class Npc
{
    public static $itemNameInfoMap;

    public function newNpc($map_id, $id, $npc)
    {
        $sc = getObject('Script')->loadFile($npc['file_name'] . '.txt');

        if (!$sc) {
            EchoLog(sprintf('NPC脚本加载失败: [%s] [%s]', $npc['chinese_name'], $npc['file_name'] . '.txt'), 'e');
            return false;
        }

        $npc = [
            // 'MapObject' => [
            'ID'              => $id,
            'Name'            => $npc['chinese_name'],
            'NameColor'       => ['R' => 0, 'G' => 255, 'B' => 0],
            'Map'             => $map_id,
            'CurrentLocation' => ['X' => $npc['location_x'], 'Y' => $npc['location_y']],
            'Direction'       => rand(0, 1),
            "Dead"            => false,
            "PlayerCount"     => 0,
            'InSafeZone'      => false,
            // ],
            'Image'           => $npc['image'],
            'Light'           => 0, // TODO
            'TurnTime'        => time(),
            'Script'          => $sc,
            'Goods'           => [],
            'BuyBack'         => [],
        ];

        if (!empty($npc['Script']['Goods'])) {
            if (!self::$itemNameInfoMap) {
                self::$itemNameInfoMap = json_decode(getObject('Redis')->set('itemNameInfoMap'), true);
            }

            $Atomic = getObject('Atomic');

            foreach ($npc['Script']['Goods'] as $name) {

                $res = explode(' ', $name);

                $name  = $res[0];
                $count = 1;
                if (count($res) == 2) {
                    $count = (int) $res[1];
                }

                $item = self::$itemNameInfoMap[$name] ?: [];

                if (!$item) {
                    continue;
                }

                $g = etObject('MsgFactory')->newUserItem($item, $Atomic->newObjectID());

                $g['Count']     = $count;
                $npc['Goods'][] = $g;
            }
        }

        return $npc;
    }
}
