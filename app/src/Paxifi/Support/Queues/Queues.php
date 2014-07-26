<?php namespace Paxifi\Support\Queues;

use Paxifi\Store\Repository\Driver\EloquentDriverRepository as Driver;

class Queues
{

    /**
     * Email sticker pdf to driver
     *
     * @param $job
     * @param $data
     */
    public function email($job, $data)
    {
        $view_data = [];

        $view_data = !empty($data['data']) ? $data['data'] : [];

        // Process the send sticker pdf to user email event...
        try {

            \Mail::send($data['template'], $view_data, function ($message) use ($data) {

                $message->from($data['context']['from'], $data['context']['name'])->subject($data['context']['subject']);

                $message->to($data['to']);

                if (isset($data['attach'])) {
                    $message->attach($data['attach'], array('as' => $data['as'], 'mime' => $data['mime']));
                }
            });

            if ($driver = Driver::findByEmail($data['to'])) {

                \Event::fire('paxifi.notifications.emails', [$driver]);
            }

            $job->delete();
        } catch (\Exception $e) {
            dd($e->getMessage());
            return \Response::json(["error" => true, "message" => ""], 500, []);
        }
    }
} 