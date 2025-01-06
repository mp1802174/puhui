<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\service\ExcelImport;

class Import extends Command
{
    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Import Excel file');
    }

    protected function execute(Input $input, Output $output)
    {
        $filePath = $input->getArgument('filePath');
        if (!$filePath) {
            $output->writeln('Missing file path');
            return;
        }

        $service = new ExcelImport();
        $service->importDailyRecord($filePath);
    }
} 