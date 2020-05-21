<?php
namespace App\Controller\Game;

use App\Controller\AbstractController;

/**
 *
 */
class Settings extends AbstractController
{
    public $baseStats = [
        0 => [
            'hp_gain'               => 4,
            'hp_gain_rate'          => 4.5,
            'mp_gain_rate'          => 0,
            'bag_weight_gain'       => 3,
            'wear_weight_gain'      => 20,
            'hand_weight_gain'      => 13,
            'min_ac'                => 0,
            'max_ac'                => 7,
            'min_mac'               => 0,
            'max_mac'               => 0,
            'min_dc'                => 5,
            'max_dc'                => 5,
            'min_mc'                => 0,
            'max_mc'                => 0,
            'min_sc'                => 0,
            'max_sc'                => 0,
            'start_agility'         => 15,
            'start_accuracy'        => 5,
            'start_critical_rate'   => 0,
            'start_critical_damage' => 0,
            'critial_rate_gain'     => 0,
            'critical_damage_gain'  => 0,
        ],
        1 => [
            'hp_gain'               => 15,
            'hp_gain_rate'          => 1.8,
            'mp_gain_rate'          => 0,
            'bag_weight_gain'       => 5,
            'wear_weight_gain'      => 100,
            'hand_weight_gain'      => 90,
            'min_ac'                => 0,
            'max_ac'                => 0,
            'min_mac'               => 0,
            'max_mac'               => 0,
            'min_dc'                => 7,
            'max_dc'                => 7,
            'min_mc'                => 7,
            'max_mc'                => 7,
            'min_sc'                => 0,
            'max_sc'                => 0,
            'start_agility'         => 15,
            'start_accuracy'        => 5,
            'start_critical_rate'   => 0,
            'start_critical_damage' => 0,
            'critial_rate_gain'     => 0,
            'critical_damage_gain'  => 0,
        ],
        2 => [
            'hp_gain'               => 6,
            'hp_gain_rate'          => 2.5,
            'mp_gain_rate'          => 0,
            'bag_weight_gain'       => 4,
            'wear_weight_gain'      => 50,
            'hand_weight_gain'      => 42,
            'min_ac'                => 0,
            'max_ac'                => 0,
            'min_mac'               => 12,
            'max_mac'               => 6,
            'min_dc'                => 7,
            'max_dc'                => 7,
            'min_mc'                => 0,
            'max_mc'                => 0,
            'min_sc'                => 7,
            'max_sc'                => 7,
            'start_agility'         => 18,
            'start_accuracy'        => 5,
            'start_critical_rate'   => 0,
            'start_critical_damage' => 0,
            'critial_rate_gain'     => 0,
            'critical_damage_gain'  => 0,
        ],
    ];

    public function getBaseStats($class)
    {
        return $this->baseStats[$class];
    }

    //灯光设置 TODO 定时器控制
    public function lightSet($light = null)
    {
        if (!empty($light)) {
            return intval($light);
        }

        $date = date('H');

        $light = 0;

        switch ($date) {
            case $date >= 5 && $date < 8:
                $light = $this->Enum::LightSettingDawn;
                break;

            case $date >= 8 && $date < 17:
                $light = $this->Enum::LightSettingDay;
                break;

            case $date >= 17 && $date < 20:
                $light = $this->Enum::LightSettingEvening;
                break;

            case $date >= 20 || $date < 5:
                // $light = $this->Enum::LightSettingNight; //太黑了 改为傍晚
                $light = $this->Enum::LightSettingEvening;
                break;

            default:
                $light = $this->Enum::LightSettingNormal;
                break;
        }

        return intval($light);
    }
}
