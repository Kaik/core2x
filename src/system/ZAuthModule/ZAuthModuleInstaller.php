<?php

namespace Zikula\ZAuthModule;

use Zikula\Core\AbstractExtensionInstaller;

class ZAuthModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        'Zikula\ZAuthModule\Entity\AuthenticationMappingEntity'
    ];

    public function install()
    {
        $this->schemaTool->create($this->entities);
        $this->setVars($this->getDefaultModvars());

        return true;
    }

    public function upgrade($oldversion)
    {
        return true;
    }

    public function uninstall()
    {
        $this->schemaTool->drop($this->entities);

        return true;
    }

    /**
     * @return array An array of all current module variables, with their default values, suitable for {@link setVars()}.
     */
    private function getDefaultModvars()
    {
        return [
            ZAuthConstant::MODVAR_HASH_METHOD                           => ZAuthConstant::DEFAULT_HASH_METHOD,
            ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH               => ZAuthConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
            ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED       => ZAuthConstant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
            ZAuthConstant::MODVAR_PASSWORD_REMINDER_ENABLED             => ZAuthConstant::DEFAULT_PASSWORD_REMINDER_ENABLED,
            ZAuthConstant::MODVAR_PASSWORD_REMINDER_MANDATORY           => ZAuthConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY,
        ];
    }
}