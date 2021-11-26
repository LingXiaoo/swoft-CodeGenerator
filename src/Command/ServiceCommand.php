<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Lingxiao\Swoft\CodeGenerator\Command;

use Lingxiao\Swoft\CodeGenerator\Model\Logic\ServiceGeneratorLogic;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandArgument;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Throwable;
use function input;

/**
 * Generate entity class by database table names[by <cyan>devtool</cyan>]
 *
 * @Command()
 * @since 2.0
 */
class ServiceCommand
{
    /**
     * @Inject()
     *
     * @var ServiceGeneratorLogic
     */
    private $logic;

    /**
     * Generate database entity
     * @CommandMapping()
     * @CommandArgument(name="name", desc="controller name", type="string")
     * @CommandOption(name="name", desc="controller name", type="string")
     * @CommandOption(name="path", desc="controller path", type="string")
     * @CommandOption(name="tplDir", desc="controller tplDir", type="string")
     * @return void
     */
    public function create(): void
    {
        $name        = input()->get('name', input()->getOpt('name'));
        $service      = input()->get('service', 'Directory');
        $tplDir        = input()->get('tplDir', '@codeGenerator/swoft-CodeGenerator/resource/template');
        try {
            $this->logic->create([
                (string )$name,
                (string )$service,
                (string )$tplDir,
            ]);
        } catch (Throwable $exception) {
            output()->colored($exception->getMessage(), 'error');
        }
    }
}
