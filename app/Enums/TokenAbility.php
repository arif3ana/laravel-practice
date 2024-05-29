<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The TokenAbility enum.
 *
 * @method static self ISSUE_ACCESS_TOKEN()
 * @method static self ACCESS_API()
 */
class TokenAbility extends Enum
{
    const ISSUE_ACCESS_TOKEN = 'issue-access-token';
    const ACCESS_API = 'access-api';
}
