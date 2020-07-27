#!/usr/bin/env php
<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

!defined('BASE_PATH') && define('BASE_PATH', __DIR__ . '/');
!defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require BASE_PATH . '/vendor/autoload.php';

//测试重写env
function env($conf, $default)
{
    $config = [
        'DEBUG' => true,
    ];
    return $config[$conf] ?? $default;
}
use App\Controller\Packet\BinaryReader;
use App\Controller\Packet\CodeMap;
use App\Controller\Packet\CodePacket;
use Hyperf\Di\Annotation\Inject;

/**
 *
 */
class Client
{
    /**
     * @Inject
     * @var CodeMap
     */
    protected $CodeMap;

    /**
     * @Inject
     * @var CodePacket
     */
    protected $CodePacket;

    /**
     * @Inject
     * @var BinaryReader
     */
    protected $BinaryReader;

    protected $size = '';

    public function __construct()
    {
        $this->CodeMap      = new CodeMap;
        $this->CodePacket   = new CodePacket;
        $this->BinaryReader = new BinaryReader;
        $this->run();
    }

    public function getHex($arr)
    {
        $hex = String2Hex(bytesToString($arr));

        $str = '';
        $i   = 0;
        while ($i <= strlen($hex) / 2) {
            $str .= substr($hex, $i, 2) . ' ';
            $i += 2;
        }

        EchoLog(sprintf('字节数组转16进制: [%s] ', json_encode($str, JSON_UNESCAPED_UNICODE)), 'i');
    }

    public function writePacketData($cmd, $packet)
    {
        $struct = $this->CodePacket->clientPacketStruct[$cmd] ?? '';

        if ($struct) {
            return $this->BinaryReader->write($struct, $packet);
        } else {
            return $struct;
        }
    }

    public function packetData(array $packetInfo)
    {
        if (isset($packetInfo[1])) {
            $data = $this->writePacketData($packetInfo[0], $packetInfo[1]);
        } else {
            $data = '';
        }

        $cmd = pack('s', $this->CodeMap->getClinetPackCmd($packetInfo[0]));

        $data = $cmd . $data;

        $len = pack('s', strlen($data) + 2);
        return $len . $data;
    }

    public function LOGIN($account, $password)
    {
        $data = [
            'account'  => $account,
            'password' => $password,
        ];

        return $this->packetData(['LOGIN', $data]);
    }

    public function START_GAME($character_index)
    {
        $data = [
            'character_index' => $character_index,
        ];

        return $this->packetData(['START_GAME', $data]);   
    }

    public function run()
    {
        $this->palyer();
    }

    public function palyer()
    {
        $client = new Swoole\Client(SWOOLE_SOCK_TCP);
        if (!$client->connect('127.0.0.1', 7000, -1)) {
            EchoLog("connect failed. Error: {$client->errCode}");
        }

        //CLIENT_VERSION
        //{"len":24,"cmd":1000,"packet":[24,0,0,0,16,0,0,0,170,124,11,209,41,241,81,142,137,41,214,160,138,169,152,239],"cmdName":"CLIENT_VERSION","res":{"VersionHash":[16,0,0,0,-86,124,11,-47,41,-15,81,-114,-119,41,-42,-96,-118,-87,-104,-17]}}
        $client->send(bytesToString([24, 0, 0, 0, 16, 0, 0, 0, 170, 124, 11, 209, 41, 241, 81, 142, 137, 41, 214, 160, 138, 169, 152, 239]));

        while ($packet = $client->recv()) {
            if ($packet) {

                $strlen = strlen($packet);
                $data   = [];
                $i      = 0;
                while ($i < $strlen) {
                    $size   = unpack('s', substr($packet, $i))[1];
                    $data[] = substr($packet, $i, $size);
                    $i += $size;
                }

                foreach ($data as $k => $v) {
                    $cmd = $this->unPacketData($v);
                    switch ($cmd) {
                        case 'CONNECTED':
                            $client->send($this->LOGIN('admin', 'admin'));
                            break;

                        case 'LOGIN_SUCCESS':
                            $client->send($this->START_GAME(3));
                            break;

                        case 'START_GAME':
                            
                        default:
                            # code...
                            break;
                    }
                }
            }
        }
    }

    public function unPacketData(string $packet)
    {
        usleep(10);

        $packetBytes = stringToBytes($packet);

        $cmdInfo   = bytesToString(array_slice($packetBytes, 0, 4));
        $paramInfo = bytesToString(array_slice($packetBytes, 4));

        $param = unpack('slen/scmd/', $cmdInfo);

        $param['cmd'] += 2000;

        $param['packet'] = $packetBytes;

        $param['cmdName'] = $this->CodeMap->getCmdName($param['cmd']);

        EchoLog(sprintf('收到服务端信息: [%s] ', json_encode($param, JSON_UNESCAPED_UNICODE)), 'i');

        return $param['cmdName'];
    }
}

new Client();
