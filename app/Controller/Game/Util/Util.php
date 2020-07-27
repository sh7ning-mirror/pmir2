<?php
namespace App\Controller\Game\Util;

/**
 *
 */
class Util
{
    public function stringEqualFold($cmdStr, $nowCmdStr)
    {
        if (is_array($nowCmdStr)) {
            foreach ($nowCmdStr as $k => $v) {
                if (strtoupper($cmdStr) == strtoupper($v)) {
                    return true;
                }
            }
        } elseif (strtoupper($cmdStr) == strtoupper($nowCmdStr)) {
            return true;
        }

        return false;
    }

    public function hasFlagUint16($a, $b)
    {
        return $a & $b != 0;
    }

    public function getDamage($magic, $damageBase)
    {
        return $damageBase + $this->getPower($magic);
    }

    public function getPower($magic)
    {
        return $this->getPower1($magic, $this->mPower($magic));
    }

    public function getPower1($magic, $power)
    {
        return (int) rand(0, $power / 4.0 * ($magic['level'] + 1) + $this->defPower($magic));
    }

    public function mPower($magic)
    {
        if ($magic['info']['m_power_bonus'] > 0) {
            return rand($magic['info']['m_power_base'], $magic['info']['m_power_bonus'] + $magic['info']['m_power_base'] - 1);
        } else {
            return $magic['info']['m_power_base'];
        }
    }

    public function defPower($magic)
    {
        if ($magic['info']['m_power_bonus'] > 0) {
            return rand($magic['info']['power_base'], $magic['infp']['power_bonus'] + $magic['info']['power_base'] + 1);
        } else {
            return $magic['info']['m_power_base'];
        }
    }
}
