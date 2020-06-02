<?php
declare (strict_types = 1);

namespace App\Controller;

/**
 *
 */
class MsgRegister
{
    public $msgList = [
        'CLIENT_VERSION'   => ['\App\Controller\Auth\AuthHandler', 'clientVersion'],
        'KEEP_ALIVE'       => ['\App\Controller\Auth\AuthHandler', 'keepAlive'],
        'NEW_ACCOUNT'      => ['\App\Controller\Auth\AuthHandler', 'newAccount'],
        'CHANGE_PASSWORD'  => ['\App\Controller\Auth\AuthHandler', 'changePassword'],
        'LOGIN'            => ['\App\Controller\Auth\AuthHandler', 'login'],
        'NEW_CHARACTER'    => ['\App\Controller\Auth\AuthHandler', 'newCharacter'],
        'DELETE_CHARACTER' => ['\App\Controller\Auth\AuthHandler', 'deleteCharacter'],
        'START_GAME'       => ['\App\Controller\Auth\AuthHandler', 'startGame'],

        'WALK'             => ['\App\Controller\World\Handler', 'walk'],
        'RUN'              => ['\App\Controller\World\Handler', 'run'],
        'TURN'             => ['\App\Controller\World\Handler', 'turn'],
        'LOG_OUT'          => ['\App\Controller\World\Handler', 'logOut'],
        'GAME_OVER'        => ['\App\Controller\World\Handler', 'gameOver'],
        'CHAT'             => ['\App\Controller\World\Handler', 'chat'],
        'OPENDOOR'         => ['\App\Controller\World\Handler', 'openDoor'],
        'REFINE_CANCEL'    => ['\App\Controller\World\Handler', 'refineCancel'],
        'EQUIP_ITEM'       => ['\App\Controller\World\Handler', 'equipItem'],
        'REMOVE_ITEM'      => ['\App\Controller\World\Handler', 'removeItem'],
        'MOVE_ITEM'        => ['\App\Controller\World\Handler', 'moveItem'],
        'CALL_NPC'         => ['\App\Controller\World\Handler', 'callNpc'],
        'BUY_ITEM'         => ['\App\Controller\World\Handler', 'buyItem'],
        'DROP_ITEM'        => ['\App\Controller\World\Handler', 'dropItem'],
        'SELL_ITEM'        => ['\App\Controller\World\Handler', 'sellItem'],
        'PICK_UP'          => ['\App\Controller\World\Handler', 'pickUp'],
        'CHANGE_A_MODE'    => ['\App\Controller\World\Handler', 'changeAMode'],
        'CHANGE_P_MODE'    => ['\App\Controller\World\Handler', 'changePMode'],
        'USE_ITEM'         => ['\App\Controller\World\Handler', 'useItem'],
        'DROP_GOLD'        => ['\App\Controller\World\Handler', 'dropGold'],
        'REPAIR_ITEM'      => ['\App\Controller\World\Handler', 'repairItem'],
    ];
}
