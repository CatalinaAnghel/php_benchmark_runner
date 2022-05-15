<?php

namespace MepProject\PhpBenchmarkRunner\Helper;

class ExceptionMessages {
    const HOOK_CONFIGURATION_EXCEPTION_MESSAGE = 'Invalid hook configuration: Expected a class hook and received a method hook.';
    const MHOOK_CONFIGURATION_EXCEPTION_MESSAGE = 'Invalid hook configuration: Expected a method hook and received a class hook.';
    const PARAM_PROVIDER_CONFIGURATION_EXCEPTION_MESSAGE = 'Parameter provider invalid configuration';
    const INVALID_CONFIGURATION_EXCEPTION_MESSAGE = 'Invalid configuration';
    const INVALID_REV_CONFIGURATION_EXCEPTION_MESSAGE = 'Invalid revolutions configuration';
    const INVALID_IT_CONFIGURATION_EXCEPTION_MESSAGE = 'Invalid iterations configuration';
    const INVALID_CLASS_HOOK_CONFIG_EXCEPTION_MESSAGE = 'Invalid class hook configuration';
    const INVALID_METHOD_HOOK_CONFIG_EXCEPTION_MESSAGE = 'Invalid method hook configuration';
    const INVALID_PROVIDER_EXCEPTION_MESSAGE = 'Invalid provider';
    const INVALID_SERVICE_LOCATOR_EXCEPTION_MESSAGE = 'The services cannot be instantiated: Invalid Service Locator configuration';
}