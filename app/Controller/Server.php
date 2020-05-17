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
    }

    public function onStart()
    {
        if (\Hyperf\Utils\Coroutine::inCoroutine()) {
            $this->GameData->loadGameData();
        } else {
            co(function () {
                $this->GameData->loadGameData();
            });
        }
    }

    public function onConnect($server, $fd, $reactorId)
    {
        EchoLog(sprintf('Client: [%s] connect IP: [%s]', $fd, $server->getClientInfo($fd)['remote_ip']), 'i');

        $this->SendMsg->sendPacketData($fd, bytesToString([4, 0, 0, 0]));
    }

    public function onReceive($server, $fd, $reactorId, $data)
    {
        //处理黏包(这里只需要将黏在一起的同一批tcp消息进行处理,底层已经实现单次tcp的收发)
        // $strlen    = strlen($data);
        // $dataArray = [];
        // $i         = 0;
        // while ($i < $strlen) {
        //     $size        = unpack('s', substr($data, $i))[1];
        //     $dataArray[] = substr($data, $i, $size);
        //     $i += $size;
        // }

        // co(function () use ($dataArray, $fd) {
        //     foreach ($dataArray as $k => $v) {
        //         co(function () use ($fd, $v) {
        //             $data = $this->SendMsg->unPacketData($v);

        //             EchoLog(sprintf('Client: [%s] serverReceive: %s', $fd, json_encode($data, JSON_UNESCAPED_UNICODE)), 'i');

        //             if ($data['cmdName']) {
        //                 $this->handler($data['cmdName'], $fd, $data);
        //             }
        //         });
        //     }
        // });

        //看了文档发现swoole有包头包体解析方式,为了性能弃用上面的方式(不过测试1秒1000次并发貌似和上面性能差不多~)
        $data = $this->SendMsg->unPacketData($data);

        $filter = [
            'KEEP_ALIVE',
        ];
        
        if (!empty($data['cmdName']) && !in_array($data['cmdName'], $filter)) {
            EchoLog(sprintf('Client: [%s] serverReceive: %s', $fd, json_encode($data, JSON_UNESCAPED_UNICODE)), 'i');

            if (empty($data['res'])) {
                EchoLog(sprintf('未正确解析数据包: %s', $data['cmdName']), 'w');
            }
        }

        if ($data['cmdName']) {
            $this->handler($data['cmdName'], $fd, $data);
        }
    }

    public function onClose($server, $fd, $reactorId)
    {
        EchoLog(sprintf('Client: [%s] close IP: [%s]', $fd, $server->getClientInfo($fd)['remote_ip']), 'w');

        //保存玩家属性
        $this->PlayersList->saveData($fd);

        //删除玩家
        $this->PlayersList->delPlayersList($fd);

        //删除连接
        $this->delClientInfo($fd);
    }

    public function onShutdown()
    {
        EchoLog("onShutdown");
    }

    public function delClientInfo($fd = null)
    {
        $key = getClientId($fd);
        $this->Redis->del('player:_' . $key);
    }
}
