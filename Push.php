<?php

use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

require 'vendor/autoload.php';

class Push
{
    private $publicKey = 'BGcOdUDx04kICiYYEt8ifKGCiq_NuZ7I_9UiogTDpe2WkIOnpdUx8yrJnrHuot9B4f-AOmT39_urJ17fuQ2usWE';
    private $privateKey = 'AsDltFyg5tYfNarSGWomW3_3E20P3CL4cuu0cs2QYpU';

    public $subject = 'minhajchamp@gmail.com';

    public $endPoint, $publicKeyp25, $authToken;

    public $message;

    public static $report;
    private WebPush $webPush;

    public function __construct($endPoint, $publicKeyp25, $authToken, $message)
    {
        $this->endPoint = $endPoint;
        $this->publicKeyp25 = $publicKeyp25;
        $this->authToken = $authToken;
        $this->message = $message;
    }

    public function vapidAuth(): array
    {
        return [
            'VAPID' => [
                'subject' => $this->subject,
                'publicKey' => $this->publicKey,
                'privateKey' => $this->privateKey,
            ],
        ];
    }

    /**
     * @throws ErrorException
     */
    public function init($report = null): Push
    {
        $this->report = $report;
        $this->webPush = new WebPush($this->vapidAuth());
        return $this;
    }

    /**
     * @throws JsonException
     * @throws ErrorException
     */
    public function subscribe(): Subscription
    {
        $subscription = Subscription::create([
            "endpoint" => $this->endPoint,
            'publicKey' => $this->publicKeyp25,
            'authToken' => $this->authToken,
            'contentEncoding' => 'aesgcm'
        ]);

        return $subscription;
    }


    /**
     * @throws ErrorException
     * @throws JsonException
     */
    public function send(): void
    {
        $this->webPush->queueNotification(
            $this->subscribe(),
            json_encode($this->message, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @throws ErrorException
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
     * @throws ErrorException
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