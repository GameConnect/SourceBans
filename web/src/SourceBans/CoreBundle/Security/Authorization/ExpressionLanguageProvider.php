<?php

namespace SourceBans\CoreBundle\Security\Authorization;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * ExpressionLanguageProvider
 */
class ExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('has_flag', function ($flag) {
                return sprintf('$user->hasFlag(%s)', $flag);
            }, function (array $variables, $flag) {
                return $variables['user']->hasFlag($flag);
            }),
        ];
    }
}
