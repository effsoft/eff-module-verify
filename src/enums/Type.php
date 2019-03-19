<?php

namespace effsoft\eff\module\verify\enums;

use MabeEnum\Enum;

class Type extends Enum{
    const REGISTER = 1;
    const PASSWORD_FORGOT = 2;
    const PASSWORD_RESET = 3;
}