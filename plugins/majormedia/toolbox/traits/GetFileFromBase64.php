<?php namespace Majormedia\ToolBox\Traits;
use System\Models\File;

trait GetFileFromBase64{
	use JsonAbort;

    public function getFileFromBase64($data, $name=null){
        $rawData = base64_decode($data);

        // check file is in base64
        if (base64_encode($rawData) !== $data)
            $this->JsonAbort([
                'status' => 'error',
                'error' => \ErrorCodes::FILE_MUST_BE_BASE64,
            ], 400);

        // Create a temporary file in the system's temporary directory and retrieve its path
        $tmpFile = tmpfile();

        // verify that the temporary file was created successfully
        if (!$tmpFile)
            $this->JsonAbort();

        $tmpFilePath = stream_get_meta_data($tmpFile)['uri'];

        // write data to the file
        fwrite($tmpFile, $rawData);

        // go back to file start to allow reading
        fseek($tmpFile, 0);

        // create a System\Models\File instance from tmpFile to attach to the user
        (
            $createdFile = (new File)->fromData($tmpFile, $name ?? str_random(40))
        )->save();
        
        fclose($tmpFile);

        return $createdFile->refresh();
    }
}