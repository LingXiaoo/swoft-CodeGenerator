<?php declare(strict_types=1);

namespace Lingxiao\Swoft\CodeGenerator\test\Unit;

use PHPUnit\Framework\TestCase;
use Lingxiao\Swoft\CodeGenerator\FileGenerator;

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
}
