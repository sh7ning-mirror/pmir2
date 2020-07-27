<?php
declare (strict_types = 1);

namespace App\Controller;

use App\Controller\AbstractController;

/**
 *
 */
class Server extends AbstractController
{
    public function beforeStart()
    {
        $this->Checksystem->check();

        $str = "

        MMMMMMMM       MMMM    MMMM     MMM     MMMMMMMMM        MMMMM
        MMM   MMM      MMMM   MMMMM     MMM     MMM   MMMM      MMM MMM
        MMM    MMM     MMMM   MMMMM     MMM     MMM    MMM     MMM   MMM
        MMM    MMM     MMMMM  MMMMM     MMM     MMM    MMM           MMM
        MMM    MMM     MMMMM MMMMMM     MMM     MMM    MMM          MMMM
        MMM   MMM      MMMMM MMMMMM     MMM     MMM   MMMM          MMM
        MMMMMMMM       MMMMMMMM MMM     MMM     MMMMMMMM           MMMM
        MMM            MMMMMMMM MMM     MMM     MMM  MMMM         MMMM
        MMM            MMM MMMM MMM     MMM     MMM   MMM        MMMM
        MMM            MMM MMM  MMM     MMM     MMM    MMM      MMM
        MMM            MMM      MMM     MMM     MMM    MMM     MMMM
        MMM            MMM      MMM     MMM     MMM    MMMM    MMMMMMMMM
            ";
        EchoLog($str, null, true);

        EchoLog('Server version ' . env('VERSION', '1.0.1'), null, true);
        EchoLog('author by.fan <fan3750060@163.com>', null, true);

        //业务进程
        $this->Process->start('GameProcess');
    }

    public function onStart()
    {
        // $this->GameData->loadGameData();
    }

    public function onConnect($server, $fd, $reactorId)
    {
        EchoLog(sprintf('Client: [%s] connect IP: [%s]', $fd, $server->getClientInfo($fd)['remote_ip']), 'i');

        $this->SendMsg->sendPacketData($fd, bytesToString([4, 0, 0, 0]));
    }

    public function onReceive($server, $fd, $reactorId, $data)
    {
        $data = $this->SendMsg->unPacketData($data);

        $filter = [
            'KEEP_ALIVE',
        ];

        if (!empty($data['cmdName']) && !in_array($data['cmdName'], $filter)) {
            EchoLog(sprintf('Client: [%s] serverReceive: %s', $fd, json_encode($data, JSON_UNESCAPED_UNICODE)), 'i');

            $pack_filter = [
                'PICK_UP',
            ];
            if (empty($data['res']) && !in_array($data['cmdName'], $pack_filter)) {
                EchoLog(sprintf('未正确解析数据包: %s', $data['cmdName']), 'w');
            }
        }

        if ($data['len'] < 4) {
            return;
        }

        if ($data['cmdName']) {
            $this->Process->send([$data['cmdName'], $fd, $data]);
        }
    }

    public function onClose($server, $fd, $reactorId)
    {
        EchoLog(sprintf('Client: [%s] close IP: [%s]', $fd, $server->getClientInfo($fd)['remote_ip']), 'w');

        $this->Process->send(['GAME_OVER', $fd, []]);
    }

    public function onShutdown()
    {
        EchoLog("onShutdown");
    }
}
