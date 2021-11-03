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
use RuntimeException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Db\Pool;
use Swoft\Devtool\FileGenerator;
use Swoft\Devtool\Helper\ConsoleHelper;
use Swoft\Devtool\Model\Dao\MigrateDao;
use Swoft\Devtool\Model\Data\SchemaData;
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
     * @var bool
     */
    private $readyGenerateId = false;

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
        $controllerName = 'test';
        $path = '@app/Http/Controller';
        $tplDir = '@devtool/devtool/resource/template';
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
    var_dump($file);
        $config       = [
            'tplFilename' => 'entity',
            'tplDir'      => $tplDir,
            'className'   => '$mappingClass',
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

        $gen  = new FileGenerator($config);

        $fileExists = file_exists($file);

        if (!$fileExists  && !ConsoleHelper::confirm("generate entity $file, Ensure continue?", true)) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if ($fileExists
            && !ConsoleHelper::confirm(
                " entity $file already exists, Ensure continue?",
                false
            )
        ) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        $data = [
            'properties'   => '$propertyStr',
            'methods'      => '$methodStr',
            'tableName'    => '$tableSchema',
            'entityName'   => '$mappingClass',
            'namespace'    => '',
            'tableComment' => '',
            'dbPool'       => '',
        ];
//        if ($gen->renderas($file, $data)) {
//            output()->colored(" Generate entity $file OK!", 'success');
//            return;
//        }

        output()->colored(" Generate entity $file Fail!", 'error');
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

    /**
     * @param array  $colSchema
     * @param string $tplDir
     *
     * @return string
     * @throws TemplateParsingException
     */
    private function generateProperties(array $colSchema, string $tplDir): string
    {
        $entityConfig = [
            'tplFilename' => 'property',
            'tplDir'      => $tplDir,
        ];

        // id
        $id = '*';
        if (!empty($colSchema['key']) && !$this->readyGenerateId && $colSchema['key'] === 'PRI') {
            // Is auto increment
            $auto = $colSchema['extra'] && strpos($colSchema['extra'], 'auto_increment') !== false ? '' :
                'incrementing=false';

            // builder @id
            $id                    = "* @Id($auto)";
            $this->readyGenerateId = true;
        }

        $mappingName = $colSchema['mappingName'];
        $fieldName   = $colSchema['name'];

        // is need map
        $prop = $mappingName === $fieldName ? '' : sprintf('prop="%s"', $mappingName);

        // column name
        $columnName = $mappingName === $fieldName ? '' : sprintf('name="%s"', $fieldName);

        // is need hidden
        $hidden = in_array($mappingName, ['password', 'pwd']) ? 'hidden=true' : '';

        $columnDetail = array_filter([$columnName, $prop, $hidden]);
        $data         = [
            'type'         => $colSchema['phpType'],
            'propertyName' => sprintf('$%s', $mappingName),
            'columnDetail' => $columnDetail ? implode(', ', $columnDetail) : '',
            'id'           => $id,
            'comment'      => trim($colSchema['columnComment']),
        ];

        $gen          = new FileGenerator($entityConfig);
        $propertyCode = $gen->render($data);

        return (string)$propertyCode;
    }

    /**
     * @param array  $colSchema
     * @param string $tplDir
     *
     * @return string
     * @throws TemplateParsingException
     */
    private function generateGetters(array $colSchema, string $tplDir): string
    {
        $getterName = sprintf('get%s', ucfirst($colSchema['mappingName']));
        $config     = [
            'tplFilename' => 'getter',
            'tplDir'      => $tplDir,
        ];
        $data       = [
            'type'       => '?' . $colSchema['originPHPType'],
            'returnType' => $colSchema['phpType'],
            'methodName' => $getterName,
            'property'   => $colSchema['mappingName'],
        ];
        $gen        = new FileGenerator($config);

        return (string)$gen->render($data);
    }

    /**
     * @param array  $colSchema
     * @param string $tplDir
     *
     * @return string
     * @throws TemplateParsingException
     */
    private function generateSetters(array $colSchema, string $tplDir): string
    {
        $setterName = sprintf('set%s', ucfirst($colSchema['mappingName']));

        $config = [
            'tplFilename' => 'setter',
            'tplDir'      => $tplDir,
        ];
        // nullable
        $type = $colSchema['is_nullable'] ? '?' . $colSchema['originPHPType'] : $colSchema['originPHPType'];
        $data = [
            'type'       => $type,
            'paramType'  => $colSchema['phpType'],
            'methodName' => $setterName,
            'paramName'  => sprintf('$%s', $colSchema['mappingName']),
            'property'   => $colSchema['mappingName'],
        ];
        $gen  = new FileGenerator($config);

        return (string)$gen->render($data);
    }
}
