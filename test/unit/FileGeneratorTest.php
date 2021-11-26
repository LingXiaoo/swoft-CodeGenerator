<?php declare(strict_types=1);

namespace Lingxiao\Swoft\CodeGenerator\test\Unit;

use Lingxiao\Swoft\CodeGenerator\Model\Logic\ControllerGeneratorLogic;
use Lingxiao\Swoft\CodeGenerator\Model\Logic\ModelGeneratorLogic;
use Lingxiao\Swoft\CodeGenerator\Model\Logic\ServiceGeneratorLogic;
use PHPUnit\Framework\TestCase;
use Lingxiao\Swoft\CodeGenerator\FileGenerator;
use Swoft\Bean\BeanFactory;

/**
 * Class FileGeneratorTest
 */
class FileGeneratorTest extends TestCase
{
    /**
     * @throws \Leuffen\TextTemplate\TemplateParsingException
     */
    public function testGen()
    {
        $data = [
            'prefix'    => '/path',
            'className' => 'DemoController',
            'namespace' => 'App\Controller',
        ];

        $gen = new FileGenerator([
            'tplDir' => __DIR__ . '/res',
        ]);

        $code = $gen->setTplFilename('some')->render($data);

        $this->assertTrue(\strpos($code, $data['prefix']) > 0);
        $this->assertTrue(\strpos($code, $data['className']) > 0);
        $this->assertTrue(\strpos($code, $data['namespace']) > 0);
    }

    public function testController(){
        $ControllerLogic = BeanFactory::getBean(ControllerGeneratorLogic::class);
        $name = 'name';
        $path = '@app/Http/Controller';
        $tplDir  = '@codeGenerator/swoft-CodeGenerator/resource/template';
        $ControllerLogic->create([
            (string )$name,
            (string )$path,
            (string )$tplDir,
        ]);
    }

    public function testServer(){
        $ServiceLogic = BeanFactory::getBean(ServiceGeneratorLogic::class);
        $name = 'adfa';
        $path = 'Shop';
        $tplDir  = '@codeGenerator/swoft-CodeGenerator/resource/template';
        $ServiceLogic->create([
            (string )$name,
            (string )$path,
            (string )$tplDir,
        ]);
    }

    public function testModel(){
        $ModelLogic = BeanFactory::getBean(ModelGeneratorLogic::class);
        $name = 'skljl';
        $path = '@app/Model';
        $tplDir  = '@codeGenerator/swoft-CodeGenerator/resource/template';
        $ModelLogic->create([
            (string )$name,
            (string )$path,
            (string )$tplDir,
        ]);
    }

}
