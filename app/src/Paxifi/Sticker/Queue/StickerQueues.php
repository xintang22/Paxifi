<?php namespace Paxifi\Sticker\Queue;


class StickerQueues
{

    /**
     * Email sticker pdf to driver
     *
     * @param $job
     * @param $data
     */
    public function email($job, $data)
    {
        // Process the send sticker pdf to user email event...
        \Mail::send('sticker.email', [], function ($message) use ($data) {

            $message->from($data['context']['from'], $data['context']['name'])->subject($data['context']['subject']);

            $message->to($data['to']);

            if (isset($data['attach'])) {
                $message->attach($data['attach'], array('as' => $data['as'], 'mime' => $data['mime']));
            }
        });

        $job->delete();
    }
} 