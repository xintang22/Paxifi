<?php namespace Paxifi\Order\Queue;

class OrderQueues
{
    /**
     * Email order invoice email to driver buyer.
     *
     * @param $job
     * @param $data
     */
    public function email($job, $data)
    {
        // Process the job...
        \Mail::send('invoice.email', $data['data'], function ($message) use ($data) {

            $message->from($data['context']['from'], $data['context']['name'])->subject($data['context']['subject']);

            $message->to($data['to']);

            if (isset($data['attach']))
            {
                $message->attach($data['attach'], array('as' => $data['as'], 'mime' => $data['mime']));
            }
        });

        $job->delete();
    }
} 