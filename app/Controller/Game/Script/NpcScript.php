<?php
namespace App\Controller\Game\Script;

use App\Controller\AbstractController;

/**
 *
 */
class NpcScript extends AbstractController
{
    public function replaceTemplates($npc, $p, $say)
    {
        $res = [];

        if ($say) {
            foreach ($say as $v) {
                $res[] = $this->replaceTemplateName($npc, $p, $v);
            }
        }
        return $res;
    }

    public function replaceTemplateName($npc, $p, $str)
    {
        preg_match_all('/\<\$\w+\>/', $str, $match);

        if ($match) {
            foreach ($match[0] as $v) {
                $s = substr(trim($v), 2, strlen($v) - 3);

                switch ($s) {
                    case "USERNAME":
                        $str = str_replace($v, $p['Name'], $str);
                        break;

                    case "NPCNAME":
                        $str = str_replace($v, $npc['Name'], $str);
                        break;

                    case "PKPOINT":
                        $str = str_replace($v, $p['PKPoints'], $str);
                        break;

                    case "ARMOUR":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotArmour), $str);
                        break;

                    case "WEAPON":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotWeapon), $str);
                        break;

                    case "RING_L":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotRingL), $str);
                        break;

                    case "RING_R":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotRingR), $str);
                        break;

                    case "NECKLACE":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotNecklace), $str);
                        break;

                    case "BELT":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotBelt), $str);
                        break;

                    case "BOOTS":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotBoots), $str);
                        break;

                    case "STONE":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotStone), $str);
                        break;

                    case "HELMET":
                        $str = str_replace($v, $this->getEquipmentName($p, $this->Eunm::EquipmentSlotHelmet), $str);
                        break;

                    case "GAMEGOLD":
                        $str = str_replace($v, $p['Gold'], $str);
                        break;

                    case "HP":
                        $str = str_replace($v, $p['HP'], $str);
                        break;

                    case "MP":
                        $str = str_replace($v, $p['MP'], $str);
                        break;

                    case "MAXHP":
                        $str = str_replace($v, $p['MaxHP'], $str);
                        break;

                    case "MAXMP":
                        $str = str_replace($v, $p['MaxMP'], $str);
                        break;

                    case "LEVEL":
                        $str = str_replace($v, $p['Level'], $str);
                        break;

                    case "DATE":
                        $str = str_replace($v, date('Y-m-d'), $str);
                        break;

                    default:
                        EchoLog(sprintf('NPC脚本缺少替换文本 Name: %s  %s', $npc['Name'], $v), 'w');
                        break;
                }
            }
        }

        return $str;
    }

    public function getEquipmentName($p, $slot)
    {
        return !empty($p['Equipment']['Items'][$slot]) ? $p['Equipment']['Items'][$slot]['Info']['Name'] : '无';
    }

    public function compareInt($op, $a, $b)
    {
        switch ($op) {
            case '>':
                return $a > $b;
                break;

            case '>=':
                return $a >= $b;
                break;

            case '<':
                return $a < $b;
                break;

            case '<=':
                return $a <= $b;
                break;

            case '==':
                return $a == $b;
                break;

            case '!=':
                return $a != $b;
                break;
        }
    }

    public function CHECKPKPOINT($param, $p)
    {
        return $this->compareInt($param[0], $p['PKPoints'], $param[1]);
    }

    public function LEVEL($param, $p)
    {
        return $this->compareInt($param[0], $p['PKPoints'], $param[1]);
    }

    public function INGUILD($param, $p)
    {
        return true;
    }
}
