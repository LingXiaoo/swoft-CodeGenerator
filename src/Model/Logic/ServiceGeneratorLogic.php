<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Lingxiao\Swoft\CodeGenerator\Model\Logic;

use Leuffen\TextTemplate\TemplateParsingException;
use Lingxiao\Swoft\CodeGenerator\FileGenerator;
use Lingxiao\Swoft\CodeGenerator\Helper\ConsoleHelper;
use RuntimeException;
use Swoft\Bean\Annotation\Mapping\Bean;
use function alias;
use function is_dir;
use function output;
use function sprintf;
use function str_replace;
use function ucfirst;

/**
 * EntityLogic
 * @Bean()
 */
class ServiceGeneratorLogic
{

    /**
     * Generate entity
     *
     * @param array $params
     *
     * @throws DbException
     * @throws TemplateParsingException
     */
    public function create(array $params): void
    {
        list($serviceName,$service,$tplDir) = $params;
        $this->generateServiceImp($serviceName,$service,$tplDir);
        $this->generateService($serviceName,$service,$tplDir);
    }

    /**
     * @param string $serviceName
     * @param string $path
     * @param string $tplDir
     * @throws TemplateParsingException
     */
    private function generateService(
        string $serviceName,
        string $service,
        string $tplDir
    ): void {
        $path = '@app/Services/'.$service;
        $file   = alias($path);
        $tplDir = alias($tplDir);
        $serviceName = ucfirst($serviceName.'Service');
        $serviceNameImp = $serviceName.'Imp';
        $config       = [
            'tplFilename' => 'service',
            'tplDir'      => $tplDir,
            'className'   => $serviceName,
        ];

        if (!is_dir($file)) {
            if (!ConsoleHelper::confirm("mkdir path $file, Ensure continue?", true)) {
                output()->writeln(' Quit, Bye!');
                return;
            }
            if (!mkdir($file, 0755, true) && !is_dir($file)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $file));
            }
        }
        $file .= sprintf('/%s.php', $serviceName);
        $gen  = new FileGenerator($config);

        $fileExists = file_exists($file);

        if (!$fileExists  && !ConsoleHelper::confirm("generate service $file, Ensure continue?", true)) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if ($fileExists
            && !ConsoleHelper::confirm(
                " service $file already exists, Ensure continue?",
                false
            )
        ) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        $data = [
            'service'      => $service,
            'className'      => $serviceName,
            'classNameImp'      => $serviceNameImp,
            'namespace'    => $this->getNameSpace($path),
            'prefix' => '/',
        ];
        if ($gen->renderAs($file, $data)) {
            output()->colored(" Generate service $file OK!", 'success');
            return;
        }

        output()->colored(" Generate service $file Fail!", 'error');
    }

    /**
     * @param string $serviceName
     * @param string $path
     * @param string $tplDir
     * @throws TemplateParsingException
     */
    private function generateServiceImp(
        string $serviceImpName,
        string $service,
        string $tplDir
    ): void {
        $path = '@app/Services/'.$service.'/ServiceImp';
        $file   = alias($path);
        $tplDir = alias($tplDir);
        $serviceImpName = ucfirst($serviceImpName.'ServiceImp');
        $config       = [
            'tplFilename' => 'serviceImp',
            'tplDir'      => $tplDir,
            'className'   => $serviceImpName,
        ];

        if (!is_dir($file)) {
            if (!ConsoleHelper::confirm("mkdir path $file, Ensure continue?", true)) {
                output()->writeln(' Quit, Bye!');
                return;
            }
            if (!mkdir($file, 0755, true) && !is_dir($file)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $file));
            }
        }
        $file .= sprintf('/%s.php', $serviceImpName);
        $gen  = new FileGenerator($config);

        $fileExists = file_exists($file);

        if (!$fileExists  && !ConsoleHelper::confirm("generate serviceImp $file, Ensure continue?", true)) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if ($fileExists
            && !ConsoleHelper::confirm(
                " serviceImp $file already exists, Ensure continue?",
                false
            )
        ) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        $data = [
            'className'      => $serviceImpName,
            'namespace'    => $this->getNameSpace($path),
            'prefix' => '/',
        ];
        if ($gen->renderAs($file, $data)) {
            output()->colored(" Generate serviceImp $file OK!", 'success');
            return;
        }

        output()->colored(" Generate serviceImp $file Fail!", 'error');
    }

    /**
     * Get file namespace
     *
     * @param string $path
     *
     * @return string
     */
    private function getNameSpace(string $path): string
    {
        $path = str_replace(['@', '/'], ['', '\\'], $path);
        $path = ucfirst($path);

        return $path;
    }
}
