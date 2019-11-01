<?php
namespace app;

use app\Packet\PacketHandler;

class Test
{
    public function run()
    {
        $data         = '#1<<<<<<`><<<<<<<<!'; //
        $decodePacket = PacketHandler::Decode($data);
        var_dump(json_encode($decodePacket));
        var_dump(ToStr($decodePacket['Data']));die;

        
    }

}
