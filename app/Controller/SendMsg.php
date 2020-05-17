<?php
namespace App\Controller;

use App\Controller\AbstractController;

/**
 *
 */
class SendMsg extends AbstractController
{
    public function packetData(array $packetInfo)
    {

        if(isset($packetInfo[1]))
        {
            $data = $this->CodePacket->writePacketData($packetInfo[0], $packetInfo[1]);
        }else{
            $data = '';
        }

        $cmd = pack('s', $this->CodeMap->getServerPackCmd($packetInfo[0]));

        $data = $cmd . $data;

        $len = pack('s', strlen($data) + 2);

        return $len . $data;
    }

    //服务主动发送
    public function send(int $fd, array $packetInfo)
    {
        $this->sendPacketData($fd, $this->packetData($packetInfo), $packetInfo);
    }

    public function sendPacketData(int $fd, string $data = '', array $packetInfo = [])
    {
        $log = [
            'len'    => strlen($data),
            'packet' => stringToBytes($data),
        ];

        if ($packetInfo) {
            if(isset($packetInfo[1]))
            {
                list($log['cmdName'], $log['res']) = $packetInfo;
            }else{
                list($log['cmdName'], $log['res']) = [$packetInfo[0],null];
            }
        }

        $filter = [
            'KEEP_ALIVE',
        ];

        if (!empty($log['cmdName']) && !in_array($log['cmdName'], $filter)) {
            EchoLog(sprintf('Client: [%s] serverSend: %s', $fd, json_encode($log, JSON_UNESCAPED_UNICODE)), 's');
        }

        $this->Server->send($fd, $data);
    }

    public function unPacketData(string $packet): array
    {
        $packetBytes = stringToBytes($packet);

        $cmdInfo   = bytesToString(array_slice($packetBytes, 0, 4));
        $paramInfo = bytesToString(array_slice($packetBytes, 4));

        $param = unpack('slen/scmd/', $cmdInfo);

        $param['cmd'] += 1000;

        $param['packet'] = $packetBytes;

        $param['cmdName'] = $this->CodeMap->getCmdName($param['cmd']);

        if ($param['cmdName']) {
            $param['res'] = $this->CodePacket->readPacketData($param['cmdName'], $paramInfo);
        }

        return $param;
    }
}
