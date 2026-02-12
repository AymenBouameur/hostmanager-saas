<?php namespace Majormedia\InCore\Http;

use Mail;
use Backend\Classes\Controller;
use MajorMedia\ToolBox\Models\Message;
use Majormedia\ToolBox\Models\Settings;
use Majormedia\ToolBox\Traits\RetrieveUser;
use Majormedia\ToolBox\Traits\GetFileFromBase64;
use Majormedia\ToolBox\Traits\GetValidatedInput;
/**
 * Extras Back-end Controller
 */
class Extras extends Controller
{
    use RetrieveUser,
        GetValidatedInput,
        GetFileFromBase64;

    public $implement = [
        'Majormedia.ToolBox.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function links()
    {
        $fb_link = !empty(Settings::instance()->fb_link) ? Settings::instance()->fb_link : null;
        $yb_link = !empty(Settings::instance()->yb_link) ? Settings::instance()->yb_link : null;
        $x_link = !empty(Settings::instance()->tw_link) ? Settings::instance()->tw_link : null;
        $li_link = !empty(Settings::instance()->li_link) ? Settings::instance()->li_link : null;
        $instagram_link = !empty(Settings::instance()->instagram_link) ? Settings::instance()->instagram_link : null;
        $wapp_link = !empty(Settings::instance()->wapp_link) ? Settings::instance()->wapp_link : null;
        $email = !empty(Settings::instance()->contact_email) ? Settings::instance()->contact_email : null;
        $phone = !empty(Settings::instance()->contact_phone) ? Settings::instance()->contact_phone : null;
        return response()->json([
            'status' => 'success',
            'data' => [
                'fb_link' => $fb_link,
                'yb_link' => $yb_link,
                'x_link' => $x_link,
                'li_link' => $li_link,
                'ig_link' => $instagram_link,
                'phone' => $phone,
                'mail' => $email
                // 'legal_terms' => 'https://petweencare.fr/cgu',
                // 'privacy_policy' => 'https://petweencare.fr/cgu',
            ]
        ], 200);
    }

    public function contactMessages()
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


        $attachment;
        if ($attachmentProvided = isset($attachment))
            $attachment = $this->getFileFromBase64($attachment);

        // create the problem
        $createdMessage = Message::create([
            'user_id' => $this->user->id,
            'subject' => $topic,
            'message' => $problem,
        ]);

        $data = [
            'email' => $this->user->email,
            'topic' => $topic,
            'message' => $problem,
        ];

        if ($attachmentProvided)
            $createdMessage->file()->save($attachment)->save();

        Mail::send('majormedia.incore::mail.admin_new_message', ['data' =>$data ], function ($message) use ($topic,$createdMessage) {
            $message->to('no-reply@vacaloc.appsmajormedia.ma', 'Admin Person');
            $message->subject($topic);
            $message->attach($createdMessage->file->path,
                [
                    'as' => $createdMessage->file->file_name,
                    'mime' => $createdMessage->file->content_type,
                ]
            );
        });
        return response()->json([
            'status' => 'success',
        ], 200);
    }

}
