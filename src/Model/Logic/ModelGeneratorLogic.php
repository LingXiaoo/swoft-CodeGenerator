<?php declare(strict_types=1);


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
class ModelGeneratorLogic
{

    private $daoName;

    private $entityName;

    private $dataName;

    private $logicName;
    private $daoPath;
    private $dataPath;
    private $logicPath;

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
        list($name,$path,$tplDir) = $params;
        $this->nameInit($name,$path);
        $daoTemplateData = [
            'className'      => $this->daoName,
            'entityName'      => $this->entityName,
            'namespace'    => $this->getNameSpace($this->daoPath),
            'prefix' => '/',
        ];
        $dataTemplateData = [
            'className'      => $this->dataName,
            'entityName'      => $this->entityName,
            'namespace'    => $this->getNameSpace($this->dataPath),
            'prefix' => '/',
        ];
        $logicTemplateData = [
            'className'      => $this->logicName,
            'service'      => $this->logicName,
            'namespace'    => $this->getNameSpace($this->logicPath),
            'prefix' => '/',
        ];
        //生成dao
        $this->generateCode($this->daoName,$this->daoPath,$tplDir,'dao',$daoTemplateData);
        //生成data
        $this->generateCode($this->dataName,$this->dataPath,$tplDir,'data',$dataTemplateData);
        //生成logic
        $this->generateCode($this->logicName,$this->logicPath,$tplDir,'logic',$logicTemplateData);
        //生成CreateLogic
        $this->generateCreateLogic();
        //生成ArrToObject
        $this->generateArrToObject();
    }

    /**
     * 初始化参数
     * @param $name
     * @param $path
     * @param $tplDir
     */
    public function nameInit($name,$path)
    {
        $this->daoName = ucfirst($name.'Dao');
        $this->entityName = ucfirst($name);
        $this->dataName = ucfirst($name.'Data');
        $this->logicName = ucfirst($name.'Logic');
        $this->daoPath = ucfirst($path.'/Dao');
        $this->dataPath = ucfirst($path.'/Data');
        $this->logicPath = ucfirst($path.'/Logic');
    }

    /**
     * @param string $serviceName
     * @param string $path
     * @param string $tplDir
     * @throws TemplateParsingException
     */
    private function generateCode(
        string $name,
        string $path,
        string $tplDir,
        string $type,
        array  $templateData = []

    ): void {
        $file   = alias($path);
        $tplDir = alias($tplDir);
        $name = ucfirst($name);
        $config       = [
            'tplFilename' => $type,
            'tplDir'      => $tplDir,
            'className'   => $name,
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
        $file .= sprintf('/%s.php', $name);
        $gen  = new FileGenerator($config);

        $fileExists = file_exists($file);

        if (!$fileExists  && !ConsoleHelper::confirm("generate $type $file, Ensure continue?", true)) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if ($fileExists
            && !ConsoleHelper::confirm(
                " $type $file already exists, Ensure continue?",
                true
            )
        ) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if (empty($templateData)){
            $templateData = [
                'className'      => $name,
                'namespace'    => $this->getNameSpace($path),
                'prefix' => '/',
            ];
        }
        if ($gen->renderAs($file, $templateData)) {
            output()->colored(" Generate $type $file OK!", 'success');
            return;
        }

        output()->colored(" Generate $type $file Fail!", 'error');
    }

    /**
     * 生成前置类库
     * @throws TemplateParsingException
     */
    private function generateCreateLogic()
    {
        $path = '@app/Model/Traits';
        $file   = alias($path);
        $tplDir = alias('@codeGenerator/resource/template');
        $name = ucfirst('CreateLogic');
        $config       = [
            'tplFilename' => 'createLogic',
            'tplDir'      => $tplDir,
            'className'   => $name,
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

        $file .= sprintf('/%s.php', $name);
        $gen  = new FileGenerator($config);
        $fileExists = file_exists($file);
        if (!$fileExists  && !ConsoleHelper::confirm("generate createLogic $file, Ensure continue?", true)) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if ($gen->renderAs($file)) {
            output()->colored(" Generate createLogic $file OK!", 'success');
            return;
        }
        output()->colored(" Generate createLogic $file Fail!", 'error');
    }

    /**
     * 生成前置类库
     * @throws TemplateParsingException
     */
    private function generateArrToObject()
    {
        $path = '@app/Library/ObjectToArray';
        $file   = alias($path);
        $tplDir = alias('@codeGenerator/resource/template');
        $name = ucfirst('arrToObject');
        $config       = [
            'tplFilename' => 'arrToObject',
            'tplDir'      => $tplDir,
            'className'   => $name,
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

        $file .= sprintf('/%s.php', $name);
        $gen  = new FileGenerator($config);
        $fileExists = file_exists($file);
        if (!$fileExists  && !ConsoleHelper::confirm("generate arrToObject $file, Ensure continue?", true)) {
            output()->writeln(' Quit, Bye!');
            return;
        }
        if ($gen->renderAs($file)) {
            output()->colored(" Generate arrToObject $file OK!", 'success');
            return;
        }
        output()->colored(" Generate arrToObject $file Fail!", 'error');
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
