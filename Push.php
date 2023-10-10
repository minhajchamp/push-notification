<?php

namespace Push\Pusher;

use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Push\Data\SecureKeys;

require 'vendor/autoload.php';

class Push
{
    public $subject = 'minhajchamp@gmail.com';
    private $endPoint, $publicKeyp25, $authToken;
    public $message;
    private WebPush $webPush;

    public function __construct($endPoint, $publicKeyp25, $authToken, $message)
    {
        $this->endPoint = $endPoint;
        $this->publicKeyp25 = $publicKeyp25;
        $this->authToken = $authToken;
        $this->message = $message;
    }

    /**
     * Description: Initializes the Web Push
     * @param null $report
     * @return Push
     * @throws \ErrorException
     */
    public function init($report = null): Push
    {
        $this->report = $report;
        $this->webPush = new WebPush($this->vapidAuth());
        return $this;
    }

    /**
     * Description: It subscribes the current user to WebPush
     * @return Subscription
     * @throws \ErrorException
     */
    public function subscribe(): Subscription
    {
        return Subscription::create([
            "endpoint" => $this->endPoint,
            'publicKey' => $this->publicKeyp25,
            'authToken' => $this->authToken,
            'contentEncoding' => 'aesgcm'
        ]);
    }

    /**
     * Description: It sends the message and subscriber into queueNotification
     * @return void
     * @throws \ErrorException
     * @throws \JsonException
     */
    public function send(): void
    {
        $this->webPush->queueNotification(
            $this->subscribe(),
            json_encode($this->message, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * Description: It generates the report, could be turned off.
     * @return array|string[]
     * @throws \ErrorException
     */
    public function report(): array
    {
        $result = [];
        if ($this->report == 1) {
            foreach ($this->webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();

                if ($report->isSuccess()) {
                    $result['status'] = 200;
                    $result['payload'] = "Message sent successfully for {$endpoint}.<br>";
                } else {
                    $result['status'] = 200;
                    $result = ["Message failed to sent for {$endpoint}: {$report->getReason()}.<br>"];
                }
            }
        }
        return $result;
    }

    /**
     * It returns VAPID keys
     * @return array[]
     */
    public function vapidAuth(): array
    {
        return [
            'VAPID' => [
                'subject' => $this->subject,
                'publicKey' => SecureKeys::PUBLIC_KEY,
                'privateKey' => SecureKeys::PRIVATE_KEY,
            ],
        ];
    }

    /**
     * @return array
     * @throws \ErrorException
     */
    public static function createVapidKeys(): array
    {
        return VAPID::createVapidKeys();
    }

}

$end = 'https://fcm.googleapis.com/fcm/send/ccaQOrIfVLA:APA91bEKFEnWvL7jWNoPWm8lEmhuc__xols3vwxK6WoRVWvgpH7dRhkuvmA2XQFlfh673xtVcU8FxuodA_m4tPpCavKHfLbLtxZJ1az0ltpG6GgV243CUYxzyxUokS0puVwoh8u65_jV';
$publicKeyp25 = 'BBafuF_oRmy7Bz3aIrD21SBDExj7aSGVGAV8KBXyjBTwv0lURkuQQ6_8j5Yk9y0QbjnpOWN92IqjY-z6kQjhguI';
$authToken = 'ErViZEmtiNrmYH6GGrPIjQ';

$push = new Push($end, $publicKeyp25, $authToken, ['title' => 'Hello']);

try {
    $push->init(1)->send();
    print_r($push->report());
} catch (ErrorException $e) {
} catch (JsonException $e) {
}