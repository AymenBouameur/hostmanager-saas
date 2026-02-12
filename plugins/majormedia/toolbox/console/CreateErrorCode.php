<?php namespace Majormedia\ToolBox\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateErrorCode extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'toolbox:errorCode';

    public $errorCodeClasses = [
        'AUTH' => 1,
        'MISSING' => 2,
        'INVALID' => 3,
        'DUPLICATION' => 4,
        'INCORRECT' => 5,
        'EXPIRED' => 6,
        'CONFIRMATION' => 7,
        'NOT_FOUND' => 8,
        'OTHER' => 9,
    ];

    /**
     * @var string The console command description.
     */
    protected $description = 'creates an error code';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(){
        if($this->option('delete'))
            return $this->deleteError();
        return $this->addError();
    }

    public function deleteError(){
        
        $_ = DIRECTORY_SEPARATOR;
        $this->errorName = $this->argument('error');

        $this->errorCodesClassPath = __DIR__ . "{$_}..{$_}utility{$_}ErrorCodes.php";

        $errorCodesFileLines = file($this->errorCodesClassPath, FILE_IGNORE_NEW_LINES);

        // pattern to find error codes by class
        $pattern = "/const {$this->errorName}/";

        // Iterate through each line and find the latest match
        foreach ($errorCodesFileLines as $lineNumber => $lineContent) {
            if (!preg_match($pattern, $lineContent, $matches))
                continue;

            // remove the line
            unset($errorCodesFileLines[$lineNumber]);
            $errorCodesFileLines = array_values($errorCodesFileLines);

            // write to the file
            $errorCodesFile = fopen($this->errorCodesClassPath, 'w');
            fwrite($errorCodesFile, implode(PHP_EOL, $errorCodesFileLines));
            fclose($errorCodesFile);

            return $this->output->success('error deleted successfully');
        }

        $this->info("this error was not found, file wasn't changed");
    }

    public function addError()
    {
        $_ = DIRECTORY_SEPARATOR;
        $this->errorName = $this->argument('error');


        $this->errorCodesClassPath = __DIR__ . "{$_}..{$_}utility{$_}ErrorCodes.php";

        if(!$this->class = $this->errorCodeClasses[$this->argument('class')] ?? null)
            return $this->error('please specify a class when not deleting !');

        $errorCodesFileLines = file($this->errorCodesClassPath, FILE_IGNORE_NEW_LINES);

        // check if code already exists
        foreach ($errorCodesFileLines as $lineNumber => $lineContent)
            if (preg_match("/const {$this->errorName}/", $lineContent, $matches))
                return $this->error('this error already exists !');

        // pattern to find error codes by class
        $pattern = "/{$this->class}\d{5}/";

        $targetLine = null;
        $targetCode = null;

        // Iterate through each line and find the latest match
        foreach ($errorCodesFileLines as $lineNumber => $lineContent) {
            if (preg_match($pattern, $lineContent, $matches)) {
                // Update the latest match information

                // need to add 1 because line numbers start from 1
                // and another 1 to get the line number that we will write to
                // therefore 2
                $targetLine = $lineNumber + 2;

                // latest match is the latest error code
                // +1 is the code we need to insert
                $targetCode = end($matches) + 1;
            }
        }

        // check if this is a new category
        $newCategory = false;
        if($targetCode === null || $targetLine === null)
            $newCategory = true;

        $targetCode ??= $this->class * 100000;
        $targetLine ??= count($errorCodesFileLines);

        array_splice(
            $errorCodesFileLines,
            $targetLine - 1, // lines start from 1
            0,
            ($newCategory ? PHP_EOL : '') . "    const {$this->errorName} = $targetCode;"
        );

        // write to the file
        $errorCodesFile = fopen($this->errorCodesClassPath, 'w');
        fwrite($errorCodesFile, implode(PHP_EOL, $errorCodesFileLines));
        fclose($errorCodesFile);

        $this->output->success("Added ErrorCode {$this->errorName}[$targetCode] at line $targetLine successfully!");
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'error',
                InputArgument::REQUIRED,
                'The name of the constant to create ex: MY_ERROR'
            ],
            [
                'class',
                InputArgument::OPTIONAL,
                'The error code class'
            ],
        ];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['delete', 'd', InputOption::VALUE_NONE, 'delete the errorCode'],
        ];
    }
}
