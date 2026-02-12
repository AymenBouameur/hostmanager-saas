<?php

namespace MajorMedia\Toolbox\Http;

use Backend\Classes\Controller;
use MajorMedia\Toolbox\Models\Message;
use Mail;

/**
 * Messages Back-end Controller
 */
class Messages extends Controller
{

    use \Majormedia\ToolBox\Traits\RetrieveUser;
    use \Majormedia\ToolBox\Traits\GetValidatedInput;
    use \Majormedia\ToolBox\Traits\GetFileFromBase64;

    public $implement = [
        'MajorMedia.ToolBox.Behaviors.RestController'
    ];
    public $restConfig = 'config_rest.yaml';

    public function extendModel($model)
    {
        return $model->active();
    }

    public function store()
    {
        $this->retrieveUser();

        extract(
            $this->GetValidatedInput(
                ['topic', 'problem', 'attachment'],
                [
                    'topic' => 'string|required',
                    'problem' => 'string|required',
                    'attachment' => 'string',
                ]
            )
        );

        if ($attachmentProvided = isset($attachment))
            // get and check if file is valid
            $attachment = $this->getFileFromBase64($attachment);

        // create the problem
        $createdMessage = Message::create([
            'user_id' => $this->user->id,
            'subject' => $topic,
            'message' => $problem,
        ]);

        if ($attachmentProvided)
            $createdMessage->file()->save($attachment)->save();

        Mail::send('majormedia.toolbox::mail.admin_new_message', ['problem' => $data['problem']], function ($message) use ($attachment, $data) {
            $message->to('no-reply@vacaloc.appsmajormedia.ma', 'Admin Person');
            $message->subject($data['topic']);

            if ($attachment) {
                $message->attach($attachment->path);
            }
        });
        return $this->JsonAbort([
            'status' => 'success',
        ], 200);
    }
}
