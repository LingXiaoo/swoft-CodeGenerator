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
use function array_filter;
use function implode;
use function in_array;
use function is_dir;
use function output;
use function rtrim;
use function sprintf;
use function str_replace;
use function strpos;
use function trim;
use function ucfirst;

/**
 * EntityLogic
 * @Bean()
 */
class ControllerLogic
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
        list($controllerName,$path,$tplDir) = $params;
        $this->generateController($controllerName,$path,$tplDir);
    }

    /**
     * @param array  $tableSchema
     * @param string $pool
     * @param string $path
     * @param bool   $isConfirm
     * @param string $fieldPrefix
     * @param string $tplDir
     *
     * @throws DbException
     * @throws TemplateParsingException
     */
    private function generateController(
        string $controllerName,
        string $path,
        string $tplDir
    ): void {
        $file   = alias($path);
        $tplDir = alias($tplDir);
        $controllerName = ucfirst($controllerName.'Controller');
        $config       = [
            'tplFilename' => 'controller',
            'tplDir'      => $tplDir,
            'className'   => $controllerName,
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
        $file .= sprintf('/%s.php', $controllerName);
        $gen  = new FileGenerator($config);

        $fileExists = file_exists($file);

        if (!$fileExists  && !ConsoleHelper::confirm("generate controller $file, Ensure continue?", true)) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if ($fileExists
            && !ConsoleHelper::confirm(
                " controller $file already exists, Ensure continue?",
                false
            )
        ) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        $data = [
            'className'      => $controllerName,
            'namespace'    => $this->getNameSpace($path),
            'prefix' => '/',
        ];
        if ($gen->renderAs($file, $data)) {
            output()->colored(" Generate controller $file OK!", 'success');
            return;
        }

        output()->colored(" Generate controller $file Fail!", 'error');
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
