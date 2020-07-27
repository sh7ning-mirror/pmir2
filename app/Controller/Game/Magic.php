<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Magic extends AbstractController
{
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

    public function levelMagic($p, $magic)
    {   
        if(!$magic)
        {
            var_dump($magic);
            return;
        }

        $exp = rand(0, 3) + 1;

        $magicLevel = 0;
        $magicNeed  = 0;
        $oldLevel   = $magic['level'];

        switch ($oldLevel) {
            case 0:
                $magicLevel = $magic['info']['level1'];
                $magicNeed  = $magic['info']['need1'];
                break;

            case 1:
                $magicLevel = $magic['info']['level2'];
                $magicNeed  = $magic['info']['need2'];
                break;

            case 2:
                $magicLevel = $magic['info']['level3'];
                $magicNeed  = $magic['info']['need3'];
                break;
        }

        if ($p['level'] < $magicLevel) {
            return;
        }

        $magic['experience'] += $exp;

        if ($magic['experience'] >= $magicNeed) {
            $magic['level']++;
            $magic['experience'] = ($magic['experience'] - $magicNeed);
            $this->PlayerObject->refreshStats($p);
        }

        if ($oldLevel != $magic['level']) {
            $this->SendMsg->send($p['fd'], $this->MsgFactory->magicDelay($magic['spell'], $this->getDelay($magic)));
        }

        $this->SendMsg->send($p['fd'], $this->MsgFactory->magicLeveled($magic['spell'], $magic['level'], $magic['experience']));
    }

    public function getDelay($magic)
    {
        return $magic['info']['delay_base'] - ($magic['level'] * $magic['info']['delay_reduction']);
    }
}
