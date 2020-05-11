<?php
namespace App\Controller\Game;

/**
 *
 */
class MsgFactory
{
    //用户信息
    public function userInformation($p)
    {
        $data = [
            'ObjectID'                  => $p['ID'],
            'RealID'                    => $p['ID'],
            'Name'                      => $p['Name'],
            'GuildName'                 => $p['GuildName'],
            'GuildRank'                 => $p['GuildRankName'],
            'NameColor'                 => Int32(pack('c4', $p['NameColor']['R'], $p['NameColor']['G'], $p['NameColor']['B'], 255)),
            'Class'                     => $p['Class'],
            'Gender'                    => $p['Gender'],
            'Level'                     => $p['Level'],
            'Location'                  => $p['CurrentLocation'],
            'Direction'                 => $p['CurrentDirection'],
            'Hair'                      => $p['Hair'],
            'HP'                        => $p['HP'],
            'MP'                        => $p['MP'],
            'Experience'                => $p['Experience'],
            'MaxExperience'             => $p['MaxExperience'],
            'LevelEffect'               => getObject('Enum')::LevelEffectsNone,
            'InventoryBool'             => $p['Inventory']['Items'] ? true : false,
            'Inventory'                 => $p['Inventory']['Items'],
            'EquipmentBool'             => $p['Equipment']['Items'] ? true : false,
            'Equipment'                 => $p['Equipment']['Items'],
            'QuestInventoryBool'        => $p['QuestInventory']['Items'] ? true : false,
            'QuestInventory'            => $p['QuestInventory']['Items'],
            'Gold'                      => $p['Gold'] ?: 0,
            'Credit'                    => 100, // TODO
            'HasExpandedStorage'        => false, // TODO
            'ExpandedStorageExpiryTime' => 0, // TODO
            'test'                      => 0, // 未知
            'ClientMagics'              => [], // TODO,
        ];

        if ($data['InventoryBool']) {
            $data['InventoryCount'] = count($p['Inventory']['Items']);
        }

        if ($data['EquipmentBool']) {
            $data['EquipmentCount'] = count($p['Equipment']['Items']);
        }

        if ($data['QuestInventoryBool']) {
            $data['QuestInventoryCount'] = count($p['QuestInventory']['Items']);
        }

        return $data;
    }

    //玩家对象
    public function objectPlayer($p)
    {
        $Enum = getObject('Enum');

        $data = [
            'ObjectID'         => $p['ID'],
            'Name'             => $p['Name'],
            'GuildName'        => $p['GuildName'],
            'GuildRankName'    => $p['GuildRankName'],
            'NameColor'        => Int32(pack('c4', $p['NameColor']['R'], $p['NameColor']['G'], $p['NameColor']['B'], 255)),
            'Class'            => $p['Class'],
            'Gender'           => $p['Gender'],
            'Level'            => $p['Level'],
            'Location'         => $p['CurrentLocation'],
            'Direction'        => $p['CurrentDirection'],
            'Hair'             => $p['Hair'],
            'Light'            => $p['Light'] ?: 0,
            'Weapon'           => $p['LooksWeapon'],
            'WeaponEffect'     => $p['LooksWeaponEffect'],
            'Armour'           => $p['LooksArmour'],
            'Poison'           => $Enum::PoisonTypeNone, //TODO
            'Dead'             => $p['Dead'],
            'Hidden'           => false,
            'Effect'           => $Enum::SpellEffectNone, //TODO
            'WingEffect'       => $p['LooksWings'],
            'Extra'            => false, //TODO
            'MountType'        => -1, //TODO
            'RidingMount'      => false, //TODO
            'Fishing'          => false, //TODO
            'TransformType'    => -1, //TODO
            'ElementOrbEffect' => 0, //TODO
            'ElementOrbLvl'    => 0, //TODO
            'ElementOrbMax'    => 200, //TODO
            'Buffs'            => [0, 0, 0, 0], //TODO
            'LevelEffects'     => $Enum::LevelEffectsNone, //TODO
        ];

        return $data;
    }

    public function objectTurn($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['CurrentDirection'],
        ];
    }

    public function objectWalk($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['CurrentDirection'],
        ];
    }

    public function objectRun($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['CurrentDirection'],
        ];
    }

    public function objectMonster($m)
    {
        return [
            'ObjectID'          => $m['ID'],
            'Name'              => $m['Name'],
            'NameColor'         => Int32(pack('c4', $m['NameColor']['R'], $m['NameColor']['G'], $m['NameColor']['B'], 255)),
            'Location'          => $m['CurrentLocation'],
            'Image'             => $m['Image'],
            'Direction'         => $m['CurrentDirection'],
            'Effect'            => $m['Effect'],
            'AI'                => $m['AI'],
            'Light'             => $m['Light'],
            'Dead'              => $m['Dead'],
            'Skeleton'          => false,
            'Poison'            => $m['Poison'],
            'Hidden'            => false,
            'ShockTime'         => 0,
            'BindingShotCenter' => false,
            'Extra'             => false,
            'ExtraByte'         => 0,
        ];
    }

    public function objectChat($p, $msg, $chatType)
    {
        return [
            'ObjectID' => $p['ID'],
            'Text'     => $p['Name'] . ':' . $msg,
            'Type'     => $chatType,
        ];
    }

    public function objectNPC($object)
    {
        return [
            'ObjectID'  => $object['ID'],
            'Name'      => $object['Name'],
            'NameColor' => Int32(pack('c4', $object['NameColor']['R'], $object['NameColor']['G'], $object['NameColor']['B'], 255)),
            'Image'     => $object['Image'],
            'Color'     => 0,
            'Location'  => $object['CurrentLocation'],
            'Direction' => $object['Direction'],
            'QuestIDs'  => [0],
        ];
    }

    public function newUserItem($itemInfo, $ID)
    {
        return [
            'ID'             => $ID,
            'ItemID'         => $itemInfo['id'],
            'CurrentDura'    => 100,
            'MaxDura'        => 100,
            'Count'          => 1,
            'AC'             => $itemInfo['min_ac'],
            'MAC'            => $itemInfo['max_ac'],
            'DC'             => $itemInfo['min_dc'],
            'MC'             => $itemInfo['min_mc'],
            'SC'             => $itemInfo['min_sc'],
            'Accuracy'       => $itemInfo['accuracy'],
            'Agility'        => $itemInfo['agility'],
            'HP'             => $itemInfo['hp'],
            'MP'             => $itemInfo['mp'],
            'AttackSpeed'    => $itemInfo['attack_speed'],
            'Luck'           => $itemInfo['luck'],
            'SoulBoundId'    => 0,
            'Bools'          => 0,
            'Strong'         => 0,
            'MagicResist'    => 0,
            'PoisonResist'   => 0,
            'HealthRecovery' => 0,
            'ManaRecovery'   => 0,
            'PoisonRecovery' => 0,
            'CriticalRate'   => 0,
            'CriticalDamage' => 0,
            'Freezing'       => 0,
            'PoisonAttack'   => 0,
            'Info'           => $itemInfo,
        ];
    }

    public function playerUpdate($p)
    {
        return [
            'ObjectID'     => $p['ID'],
            'Light'        => $p['Light'],
            'Weapon'       => $p['LooksWeapon'],
            'WeaponEffect' => $p['LooksWeaponEffect'],
            'Armour'       => $p['LooksArmour'],
            'WingEffect'   => $p['LooksWings'],
        ];
    }
}
