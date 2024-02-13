<?php

namespace app\services;

use app\models\Browser;
use app\models\OperatingSystem;
use Exception;
use foroco\BrowserDetection;
use Yii;


class ParserService
{
    private $resourceHandle;
    private $browserTitles = [];
    private $operatingSystem = [];

    /**
     * @throws Exception
     */
    public function __construct(string $file)
    {
        $this->resourceHandle = fopen($file, 'rb');
        if (!$this->resourceHandle) {
            throw new Exception();
        }
        if($browsers = Browser::find()->all()){
            foreach ($browsers as $item){
                $this->browserTitles[$item->title] = $item->id;
            }
        }
        if($system = OperatingSystem::find()->all()){
            foreach ($system as $item){
                $this->operatingSystem[$item->title] = $item->id;
            }
        }
    }

    /**
     * @return \Generator
     */
    private function getRows(): \Generator
    {
        while (!feof($this->resourceHandle)) {
            yield fgets($this->resourceHandle);
        }

        fclose($this->resourceHandle);
    }

    /**
     * @return array
     */
    public function parse()
    {
        $response = [];
        foreach ($this->getRows() as $row) {
            $response[] = $this->rowPreparer($row);
        }

        return $response;
    }

    /**
     * @param $row
     * @return array
     */
    private function rowPreparer($row)
    {
        $row = trim(preg_replace('/[\r\n]+/m', "\n", $row));

        $ipRegular = implode('|', [
            'ipv4' => '(((25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]))',
            'ipv6full' => '([0-9A-Fa-f]{1,4}(:[0-9A-Fa-f]{1,4}){7})', // 1:1:1:1:1:1:1:1
            'ipv6null' => '(::)',
            'ipv6leading' => '(:(:[0-9A-Fa-f]{1,4}){1,7})', // ::1:1:1:1:1:1:1
            'ipv6mid' => '(([0-9A-Fa-f]{1,4}:){1,6}(:[0-9A-Fa-f]{1,4}){1,6})', // 1:1:1::1:1:1
            'ipv6trailing' => '(([0-9A-Fa-f]{1,4}:){1,7}:)', // 1:1:1:1:1:1:1::
        ]);

        preg_match('~^(?P<ip>(' . $ipRegular . ')+) .*? \[(?P<dateTime>.+)\] .*?\"(?P<request>.+)\" .*? \"(?P<url>.+)\" \"(?P<agent>.+)\"$~', $row, $matches);
        print_r($matches);


        $browser = new BrowserDetection();
        $userAgentData = $browser->getAll($matches['agent']);

        if(!array_key_exists($userAgentData['browser_name'],$this->browserTitles)){
            $browser = new Browser();
            $browser->title = $userAgentData['browser_name'];
            $browser->save();
            $this->browserTitles[$userAgentData['browser_name']] = $browser->id;
        }
        if(!array_key_exists($userAgentData['os_name'],$this->operatingSystem)){
            $system = new OperatingSystem();
            $system->title = $userAgentData['os_name'];
            $system->save();
            $this->operatingSystem[$userAgentData['os_name']] = $system->id;
        }
        $date = date('Y-m-d H:i:s',strtotime($matches['dateTime']));
        return [
            'ip_address' => $matches['ip'] ?? null,
            'request_date' => explode(' ',$date)[0],
            'request_time' => explode(' ',$date)[1],
            'url' => $matches['url'] ?? null,
            'operating_system_id' => $this->operatingSystem[$userAgentData['os_name']],
            'architecture_is_64' => (int) $userAgentData['64bits_mode'],
            'browser_id' => $this->browserTitles[$userAgentData['browser_name']],
        ];

    }
}